<?php

namespace App\Http\Livewire\Guide;

use App\Services\Guide\Area\GuideCalendarService;
use App\Services\Guide\Area\GuideCalendarSyncService;
use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;
use App\Services\Guide\Area\GuideOptimizationService;
use App\Support\GuideAreaNav;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Guide Area › Calendar & availability (/guide/calendar): month grid with day states, the
 * selected-day panel (block / unblock the day, switch single tours off for that day), the
 * "Block days" form and calendar sync. Thin: GuideCalendarService / GuideCalendarSyncService.
 */
#[Layout('layouts.guide-area')]
class Calendar extends Component
{
    #[Url(except: '')]
    public string $month = '';

    #[Url(as: 'day', except: '')]
    public string $selected = '';

    public bool $showBlockForm = false;

    public string $blockMode = 'single';

    public string $blockFrom = '';

    public string $blockTo = '';

    public int $blockTour = 0;

    public string $blockNote = '';

    public bool $showImportForm = false;

    public string $importName = '';

    public string $importUrl = '';

    public function mount(): void
    {
        $this->month = $this->validMonth($this->month)->format('Y-m');
        $this->selected = $this->validDay($this->selected)->toDateString();
    }

    public function previousMonth(): void
    {
        $this->month = $this->validMonth($this->month)->subMonth()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->month = $this->validMonth($this->month)->addMonth()->format('Y-m');
    }

    public function select(string $date): void
    {
        $this->selected = $this->validDay($date)->toDateString();
    }

    public function openBlockForm(): void
    {
        $this->resetErrorBag();
        $this->showBlockForm = true;
        $this->blockFrom = $this->blockFrom ?: max($this->selected, CarbonImmutable::today()->toDateString());
        $this->blockTo = $this->blockTo ?: $this->blockFrom;
    }

    public function blockDays(GuideCalendarService $calendar): void
    {
        $today = CarbonImmutable::today()->toDateString();
        $this->validate([
            'blockMode' => ['required', Rule::in(['single', 'period'])],
            'blockFrom' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.$today],
            'blockTo' => [Rule::requiredIf($this->blockMode === 'period'), 'nullable', 'date_format:Y-m-d', 'after_or_equal:blockFrom'],
            'blockTour' => ['integer', 'min:0'],
            'blockNote' => ['nullable', 'string', 'max:255'],
        ], [
            'blockFrom.after_or_equal' => __('guide_area.calendar.block.from_past'),
            'blockTo.after_or_equal' => __('guide_area.calendar.block.to_before_from'),
            'blockTo.required' => __('guide_area.calendar.block.to_required'),
        ], [
            'blockFrom' => __('guide_area.calendar.block.from'),
            'blockTo' => __('guide_area.calendar.block.to'),
        ]);

        $from = CarbonImmutable::parse($this->blockFrom);
        $to = $this->blockMode === 'period' ? CarbonImmutable::parse($this->blockTo) : $from;
        $result = $calendar->block(auth()->user(), $from, $to, $this->blockTour ?: null, $this->blockNote);

        $this->reset('showBlockForm', 'blockFrom', 'blockTo', 'blockNote', 'blockTour');
        $this->blockMode = 'single';
        $this->toast($result['skipped'] > 0
            ? trans_choice('guide_area.calendar.block.skipped', $result['skipped'], ['count' => $result['skipped']])
            : trans_choice('guide_area.calendar.block.done', $result['blocked'], ['count' => $result['blocked']]));
    }

    public function blockSelectedDay(GuideCalendarService $calendar): void
    {
        $day = $this->validDay($this->selected);
        $result = $calendar->block(auth()->user(), $day, $day);
        $this->toast($result['blocked'] > 0 ? __('guide_area.calendar.day_blocked') : __('guide_area.calendar.day_not_blockable'), $result['blocked'] > 0 ? 'success' : 'error');
    }

    public function unblockSelectedDay(GuideCalendarService $calendar): void
    {
        $calendar->unblockDay(auth()->user(), $this->validDay($this->selected));
        $this->toast(__('guide_area.calendar.day_unblocked'));
    }

    public function setTour(int $tourId, bool $open, GuideCalendarService $calendar): void
    {
        $calendar->setTourOpen(auth()->user(), $tourId, $this->validDay($this->selected), $open);
        $this->toast($open ? __('guide_area.calendar.tour_opened') : __('guide_area.calendar.tour_closed'));
    }

    public function addImport(GuideCalendarSyncService $sync): void
    {
        $this->validate([
            'importName' => ['nullable', 'string', 'max:100'],
            'importUrl' => ['required', 'url', 'max:1000'],
        ], [], ['importUrl' => __('guide_area.calendar.sync.url')]);

        $result = $sync->addImport(auth()->user(), $this->importName, trim($this->importUrl));
        if (! $result['ok']) {
            $this->addError('importUrl', $result['error']);

            return;
        }

        $this->reset('importName', 'importUrl', 'showImportForm');
        $this->toast(__('guide_area.calendar.sync.added'));
    }

    public function syncNow(int $feedId, GuideCalendarSyncService $sync): void
    {
        $result = $sync->syncNow(auth()->user(), $feedId);
        $this->toast($result['ok'] ? __('guide_area.calendar.sync.synced') : $result['error'], $result['ok'] ? 'success' : 'error');
    }

    public function disconnect(int $feedId, GuideCalendarSyncService $sync): void
    {
        $sync->disconnect(auth()->user(), $feedId);
        $this->toast(__('guide_area.calendar.sync.disconnected'));
    }

    public function setExportOption(string $option, bool $value, GuideCalendarSyncService $sync): void
    {
        if (in_array($option, ['include_pending', 'include_blocks'], true)) {
            $sync->setExportOptions(auth()->user(), [$option => $value]);
            $this->toast(__('guide_area.calendar.sync.export_saved'));
        }
    }

    public function regenerateExport(GuideCalendarSyncService $sync): void
    {
        $sync->regenerateExport(auth()->user());
        $this->toast(__('guide_area.calendar.sync.regenerated'));
    }

    public function render(GuideCalendarService $calendar, GuideCalendarSyncService $sync)
    {
        // Profile / calendar changes can change the optimization score (sidebar badge, Overview).
        app(GuideOptimizationService::class)->forget(auth()->user());

        $guide = auth()->user();
        $month = $this->validMonth($this->month);
        $selected = $this->validDay($this->selected);
        $export = $sync->export($guide);

        return view('livewire.guide.calendar', [
            'grid' => $calendar->month($guide, $month),
            'day' => $calendar->day($guide, $selected),
            'selectedDate' => $selected,
            'tours' => $calendar->liveTours($guide),
            'imports' => $sync->imports($guide),
            'export' => $export,
            'exportSettings' => (array) ($export->feed_settings ?? []) + ['include_pending' => true, 'include_blocks' => false],
            'today' => CarbonImmutable::today()->toDateString(),
            'weekdays' => collect(range(0, 6))->map(fn (int $i) => CarbonImmutable::parse('2026-11-16')->addDays($i)->translatedFormat('D'))->all(),
        ])->title(__('guide_area.calendar.title'));
    }

    private function validMonth(string $value): CarbonImmutable
    {
        return preg_match('/^\d{4}-\d{2}$/', $value) ? CarbonImmutable::createFromFormat('Y-m-d', $value.'-01')->startOfDay() : CarbonImmutable::today()->startOfMonth();
    }

    private function validDay(string $value): CarbonImmutable
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? CarbonImmutable::parse($value)->startOfDay() : CarbonImmutable::today();
    }

    private function toast(string $message, string $type = 'success'): void
    {
        $this->dispatch('guide-area-toast', message: $message, type: $type);
        app(GuideOptimizationService::class)->forget(auth()->user());
        $this->dispatch('guide-area-badges', badges: GuideAreaNav::badges(auth()->user()));
    }
}
