<?php

namespace App\Http\Livewire\Guide;

use App\Enums\TourStatus;
use App\Models\Guiding;
use App\Presenters\Guiding\GuideTourPresenter;
use App\Services\Guide\Area\GuideTourService;
use App\Services\Guide\Area\TourActionException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Guide Area › My fishing tours (/guide/tours): tabs by status, search, go live, deactivate,
 * duplicate. Thin: rules live in GuideTourService (shared with /api/guide/tours).
 */
#[Layout('layouts.guide-area')]
class Tours extends Component
{
    use WithPagination;

    #[Url(except: 'all')]
    public string $status = 'all';

    #[Url(except: '')]
    public string $search = '';

    /** Gap labels per tour id, shown after a failed "Go live". */
    public array $gapsFor = [];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function setStatus(string $status): void
    {
        $this->status = $status === 'all' || TourStatus::fromKey($status) ? $status : 'all';
        $this->resetPage();
    }

    public function goLive(int $id, GuideTourService $tours): void
    {
        $guiding = $this->tour($id);

        try {
            $tours->goLive(auth()->user(), $guiding);
            unset($this->gapsFor[$id]);
            $this->dispatch('guide-area-toast', message: __('guide_area.tours.went_live'));
        } catch (TourActionException $e) {
            $this->gapsFor[$id] = array_map(fn (string $gap) => __('guide_area.tours.gaps.'.$gap), $e->gaps);
            $this->dispatch('guide-area-toast', message: $e->getMessage());
        }
    }

    public function deactivate(int $id, GuideTourService $tours): void
    {
        $tours->deactivate($this->tour($id));
        $this->dispatch('guide-area-toast', message: __('guide_area.tours.deactivated'));
    }

    public function duplicate(int $id, GuideTourService $tours): void
    {
        $guiding = $this->tour($id);
        $this->authorize('duplicate', $guiding);
        $tours->duplicate($guiding);

        $this->status = 'draft';
        $this->resetPage();
        $this->dispatch('guide-area-toast', message: __('guide_area.tours.duplicated'));
    }

    public function render(GuideTourService $tours, GuideTourPresenter $presenter)
    {
        $guide = auth()->user();
        $page = $tours->paginate($guide, TourStatus::fromKey($this->status), $this->search);
        $page->setCollection($page->getCollection()->map(fn (Guiding $g) => $presenter->present($g)));

        return view('livewire.guide.tours', [
            'tours' => $page,
            'counts' => $tours->counts($guide, $this->search),
            'tabs' => ['all', TourStatus::Live->key(), TourStatus::Draft->key(), TourStatus::Deactivated->key()],
            'canPublish' => $guide->canPublishGuidings(),
        ])->title(__('guide_area.tours.title'));
    }

    /** Only the guide's own tours can be acted on; ids from the client are looked up through them. */
    private function tour(int $id): Guiding
    {
        $guiding = Guiding::query()->where('user_id', auth()->id())->findOrFail($id);
        $this->authorize('update', $guiding);

        return $guiding;
    }
}
