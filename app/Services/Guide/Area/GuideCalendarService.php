<?php

namespace App\Services\Guide\Area;

use App\Models\Booking;
use App\Models\CalendarSchedule;
use App\Models\Guiding;
use App\Models\User;
use App\Services\Checkout\BlockedDateRanges;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Guide Area › Calendar & availability. Day states (spec 7.2) are worked out from the same
 * sources the public tour calendar uses: whether a tour can be booked on a day is exactly
 * Guiding::getBlockedEvents(), so the guide sees what guests see. On top, this service names
 * the reason (booking, request, block, private calendar, tour rules) and writes the guide's
 * own blocks as calendar_schedule `custom_schedule` rows (no tour = whole day, tour = that
 * tour only).
 */
class GuideCalendarService
{
    public const STATE_PAST = 'past';
    public const STATE_BOOKED = 'booked';
    public const STATE_PENDING = 'pending';
    public const STATE_BLOCKED = 'blocked';
    public const STATE_CLOSED = 'closed';
    public const STATE_PARTIAL = 'partial';
    public const STATE_AVAILABLE = 'available';

    /** Longest period one "Block days" can cover. */
    public const MAX_BLOCK_DAYS = 366;

    private const GUIDE_BLOCK_TYPES = ['custom_schedule', 'vacation_schedule'];

    /** Live tours of the guide (the calendar's per-tour switches). */
    public function liveTours(User $guide): Collection
    {
        return Guiding::query()->where('user_id', $guide->id)->where('status', 1)->orderBy('title')->get();
    }

    /**
     * Every day of the month grid (Monday-first weeks, days outside the month are null).
     *
     * @return array{month: string, label: string, weeks: list<list<?array>>}
     */
    public function month(User $guide, CarbonImmutable $month): array
    {
        $first = $month->startOfMonth();
        $last = $month->endOfMonth();
        $days = $this->days($guide, $first, $last);

        $weeks = [];
        $week = array_fill(0, $first->dayOfWeekIso - 1, null);
        foreach ($days as $day) {
            $week[] = $day;
            if (count($week) === 7) {
                $weeks[] = $week;
                $week = [];
            }
        }
        if ($week !== []) {
            $weeks[] = array_pad($week, 7, null);
        }

        return ['month' => $first->format('Y-m'), 'label' => $first->translatedFormat('F Y'), 'weeks' => $weeks];
    }

    /**
     * One day with its items and per-tour switches (selected-day panel).
     *
     * @return array<string, mixed>
     */
    public function day(User $guide, CarbonImmutable $date): array
    {
        return $this->days($guide, $date, $date)[0];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function days(User $guide, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $tours = $this->liveTours($guide);
        $today = CarbonImmutable::today();
        $fromDate = $from->toDateString();
        $toDate = $to->toDateString();

        // Per tour: the public calendar's blocked ranges.
        $blockedByTour = $tours->mapWithKeys(fn (Guiding $tour) => [$tour->id => BlockedDateRanges::merge($tour->getBlockedEvents())]);

        $bookings = Booking::query()
            ->with(['guiding:id,title', 'registeredUser:id,firstname,lastname', 'guestUser:id,firstname,lastname'])
            ->whereIn('guiding_id', Guiding::query()->select('id')->where('user_id', $guide->id))
            ->where(fn (Builder $q) => $q->where('status', 'accepted')
                ->orWhere(fn (Builder $p) => $p->where('status', 'pending')->where(fn (Builder $e) => $e->whereNull('expires_at')->orWhere('expires_at', '>', now()))))
            ->whereBetween('book_date', [$fromDate, $to->endOfDay()->toDateTimeString()])
            ->get()
            ->groupBy(fn (Booking $b) => substr((string) $b->book_date, 0, 10));

        $blocks = CalendarSchedule::query()
            ->where('user_id', $guide->id)
            ->whereIn('type', self::GUIDE_BLOCK_TYPES)
            ->whereBetween('date', [$fromDate, $toDate])
            ->get()
            ->groupBy(fn (CalendarSchedule $s) => substr((string) $s->date, 0, 10));

        $days = [];
        foreach (CarbonPeriod::create($from, $to) as $carbon) {
            $date = CarbonImmutable::instance($carbon);
            $key = $date->toDateString();
            $dayBookings = $bookings->get($key, collect());
            $dayBlocks = $blocks->get($key, collect());
            $wholeDay = $dayBlocks->whereNull('guiding_id');

            $confirmed = $dayBookings->firstWhere('status', 'accepted');
            $pending = $dayBookings->firstWhere('status', 'pending');

            $tourStates = $tours->map(function (Guiding $tour) use ($key, $blockedByTour, $dayBlocks) {
                $bookable = ! $this->inRanges($key, $blockedByTour[$tour->id]);
                $manual = $dayBlocks->where('guiding_id', $tour->id)->isNotEmpty();

                return [
                    'id' => (int) $tour->id,
                    'title' => (string) $tour->title,
                    'bookable' => $bookable,
                    // Off because the guide switched it off (can be switched on again here), or
                    // because of something else (tour rules, a booking, a whole-day block).
                    'reason' => $bookable ? null : ($manual ? 'manual' : 'rules'),
                ];
            })->values()->all();

            $bookableCount = count(array_filter($tourStates, fn (array $t) => $t['bookable']));

            $state = match (true) {
                $date->lt($today) => self::STATE_PAST,
                $confirmed !== null => self::STATE_BOOKED,
                $pending !== null => self::STATE_PENDING,
                $wholeDay->isNotEmpty() => self::STATE_BLOCKED,
                $tours->isEmpty() => self::STATE_AVAILABLE,
                $bookableCount === count($tourStates) => self::STATE_AVAILABLE,
                $bookableCount === 0 => self::STATE_CLOSED,
                default => self::STATE_PARTIAL,
            };

            $days[] = [
                'date' => $key,
                'day' => (int) $date->day,
                'is_today' => $date->isSameDay($today),
                'state' => $state,
                'bookable_count' => $bookableCount,
                'tour_count' => count($tourStates),
                'tours' => $tourStates,
                'bookings' => $dayBookings->map(fn (Booking $b) => [
                    'id' => (int) $b->id,
                    'status' => $b->status === 'accepted' ? 'booked' : 'pending',
                    'tour' => (string) ($b->guiding?->title ?? ''),
                    'guest' => trim(($b->user?->firstname ?? '').' '.($b->status === 'accepted' ? ($b->user?->lastname ?? '') : mb_substr((string) ($b->user?->lastname ?? ''), 0, 1).'.')),
                    'expires_at' => $b->expires_at,
                ])->values()->all(),
                'blocks' => $wholeDay->map(fn (CalendarSchedule $s) => [
                    'id' => (int) $s->id,
                    'source' => $s->source === CalendarSchedule::SOURCE_ICAL ? 'ical' : 'manual',
                    'note' => $this->blockNote($s),
                ])->values()->all(),
            ];
        }

        return $days;
    }

    /**
     * Blocks a day or a period for all tours (whole day) or one tour. Booked and pending days
     * are skipped (spec 7.6). Returns how many days were blocked and how many were skipped.
     *
     * @return array{blocked: int, skipped: int}
     */
    public function block(User $guide, CarbonImmutable $from, CarbonImmutable $to, ?int $tourId = null, ?string $note = null): array
    {
        if ($tourId !== null) {
            $this->ownTour($guide, $tourId);
        }

        $from = $from->max(CarbonImmutable::today());
        if ($to->lt($from)) {
            return ['blocked' => 0, 'skipped' => 0];
        }
        $to = $to->min($from->addDays(self::MAX_BLOCK_DAYS - 1));

        $taken = collect($this->days($guide, $from, $to))
            ->filter(fn (array $d) => in_array($d['state'], [self::STATE_BOOKED, self::STATE_PENDING], true))
            ->pluck('date')
            ->all();

        $blocked = 0;
        DB::transaction(function () use ($guide, $from, $to, $tourId, $note, $taken, &$blocked) {
            foreach (CarbonPeriod::create($from, $to) as $carbon) {
                $date = $carbon->toDateString();
                if (in_array($date, $taken, true)) {
                    continue;
                }

                $exists = CalendarSchedule::query()
                    ->where('user_id', $guide->id)
                    ->where('type', 'custom_schedule')
                    ->whereDate('date', $date)
                    ->where(fn (Builder $q) => $tourId === null ? $q->whereNull('guiding_id') : $q->where('guiding_id', $tourId))
                    ->exists();

                if (! $exists) {
                    CalendarSchedule::create([
                        'type' => 'custom_schedule',
                        'source' => CalendarSchedule::SOURCE_MANUAL,
                        'date' => $date,
                        'note' => $note !== null && trim($note) !== '' ? mb_substr(trim($note), 0, 255) : null,
                        'user_id' => $guide->id,
                        'guiding_id' => $tourId,
                    ]);
                }
                $blocked++;
            }
        });

        return ['blocked' => $blocked, 'skipped' => count($taken)];
    }

    /**
     * Reopens a whole day the guide blocked. Busy days from a private calendar stay (they come
     * back with the next sync — change them in that calendar).
     *
     * @return int Rows removed.
     */
    public function unblockDay(User $guide, CarbonImmutable $date): int
    {
        return CalendarSchedule::query()
            ->where('user_id', $guide->id)
            ->whereIn('type', self::GUIDE_BLOCK_TYPES)
            ->whereNull('guiding_id')
            ->whereDate('date', $date->toDateString())
            ->where(fn (Builder $q) => $q->whereNull('source')->orWhere('source', '!=', CalendarSchedule::SOURCE_ICAL))
            ->delete();
    }

    /**
     * The per-tour switch on a day (spec CA-04): off = "can't be booked on this day" for that
     * tour only; on = removes the guide's block for that tour on that day.
     */
    public function setTourOpen(User $guide, int $tourId, CarbonImmutable $date, bool $open): void
    {
        $this->ownTour($guide, $tourId);

        if ($open) {
            CalendarSchedule::query()
                ->where('user_id', $guide->id)
                ->where('type', 'custom_schedule')
                ->where('guiding_id', $tourId)
                ->whereDate('date', $date->toDateString())
                ->delete();

            return;
        }

        $this->block($guide, $date, $date, $tourId);
    }

    private function ownTour(User $guide, int $tourId): Guiding
    {
        $tour = Guiding::query()->where('user_id', $guide->id)->find($tourId);
        abort_if($tour === null, 404);

        return $tour;
    }

    private function blockNote(CalendarSchedule $block): ?string
    {
        $note = trim((string) $block->note);

        // Old calendar rows carry generic English placeholders; show nothing instead.
        return $note === '' || in_array($note, ['Custom blocked date', 'Blocked'], true) ? null : mb_substr($note, 0, 80);
    }

    /** @param  list<array{from: string, due: string}>  $ranges */
    private function inRanges(string $date, array $ranges): bool
    {
        foreach ($ranges as $range) {
            if ($date >= $range['from'] && $date <= $range['due']) {
                return true;
            }
        }

        return false;
    }
}
