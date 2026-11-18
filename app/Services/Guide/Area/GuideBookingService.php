<?php

namespace App\Services\Guide\Area;

use App\Enums\BookingStatus;
use App\Events\BookingStatusChanged;
use App\Models\BlockedEvent;
use App\Models\Booking;
use App\Models\User;
use App\Services\Booking\BookingRejectionService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Guide Area › Bookings (page, Overview and /api/guide/bookings): a guide's tour bookings with
 * the spec's filters and the three guide actions. Today's rules stay: the same statuses, the
 * same decline form rules (RejectionRequest) and the same mails (BookingAcceptedListener).
 * Added from the spec: accept re-checks the day in a transaction (one booking per guide per
 * day), and a confirmed booking can be cancelled with a reason.
 */
class GuideBookingService
{
    public const PER_PAGE = 10;

    /** Tile / status filters (spec BK-03). `closed` = declined, cancelled and expired. */
    public const FILTER_ALL = 'all';
    public const FILTER_OPEN = 'open';
    public const FILTER_CONFIRMED = 'confirmed';
    public const FILTER_COMPLETED = 'completed';
    public const FILTER_CLOSED = 'closed';

    public const FILTERS = [self::FILTER_ALL, self::FILTER_OPEN, self::FILTER_CONFIRMED, self::FILTER_COMPLETED, self::FILTER_CLOSED];

    public const PERIOD_ALL = 'all';
    public const PERIODS = ['all', 'upcoming', 'past', 'this_month', 'next_3_months'];

    public const CANCEL_REASONS = ['weather', 'illness', 'equipment', 'other'];

    /** Tour date: book_date, or the creation date for the few old rows without one. */
    private const TOUR_DATE = 'COALESCE(bookings.book_date, bookings.created_at)';

    public function __construct(private readonly BookingRejectionService $rejections) {}

    /**
     * @param  array{status?: string, tour?: int|null, period?: string, search?: string}  $filters
     */
    public function paginate(User $guide, array $filters = [], int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        return $this->ordered($this->filtered($guide, $filters), $filters)->paginate($perPage)->withQueryString();
    }

    /**
     * Tile counts with every filter applied except the status (spec 5.3).
     *
     * @param  array{tour?: int|null, period?: string, search?: string}  $filters
     * @return array<string, int>
     */
    public function counts(User $guide, array $filters = []): array
    {
        $today = Carbon::today()->toDateString();
        $now = Carbon::now()->toDateTimeString();
        $row = $this->filtered($guide, ['status' => self::FILTER_ALL] + $filters, lean: true)
            ->toBase()
            ->selectRaw('COUNT(*) AS total_count')
            ->selectRaw("SUM(CASE WHEN status = 'pending' AND (expires_at IS NULL OR expires_at > ?) THEN 1 ELSE 0 END) AS open_count", [$now])
            ->selectRaw("SUM(CASE WHEN status = 'accepted' AND ".self::TOUR_DATE.' >= ? THEN 1 ELSE 0 END) AS confirmed_count', [$today])
            ->selectRaw("SUM(CASE WHEN status = 'accepted' AND ".self::TOUR_DATE.' < ? THEN 1 ELSE 0 END) AS completed_count', [$today])
            ->first();

        $total = (int) ($row->total_count ?? 0);
        $open = (int) ($row->open_count ?? 0);
        $confirmed = (int) ($row->confirmed_count ?? 0);
        $completed = (int) ($row->completed_count ?? 0);

        return [
            self::FILTER_ALL => $total,
            self::FILTER_OPEN => $open,
            self::FILTER_CONFIRMED => $confirmed,
            self::FILTER_COMPLETED => $completed,
            self::FILTER_CLOSED => max(0, $total - $open - $confirmed - $completed),
        ];
    }

    /** Open requests, soonest reply deadline first (New & open, Overview "Waiting for you"). */
    public function openRequests(User $guide, ?int $limit = null): Collection
    {
        return $this->base($guide)
            ->where('status', 'pending')
            ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->orderBy('expires_at')
            ->orderBy('bookings.id')
            ->when($limit, fn (Builder $q) => $q->limit($limit))
            ->get();
    }

    public function openCount(User $guide): int
    {
        return $this->base($guide)
            ->where('status', 'pending')
            ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->count();
    }

    /** Next confirmed tours from today (Overview "Upcoming tours"). */
    public function upcoming(User $guide, int $limit = 5): Collection
    {
        return $this->base($guide)
            ->where('status', 'accepted')
            ->whereRaw(self::TOUR_DATE.' >= ?', [Carbon::today()->toDateString()])
            ->orderByRaw(self::TOUR_DATE.' ASC')
            ->limit($limit)
            ->get();
    }

    /** Page (1-based) the booking is on for the given filters: deep links ?booking=id (spec 5.6). */
    public function pageOf(User $guide, Booking $booking, array $filters = [], int $perPage = self::PER_PAGE): int
    {
        $ids = $this->ordered($this->filtered($guide, $filters), $filters)
            ->setEagerLoads([])
            ->pluck('bookings.id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $index = array_search((int) $booking->id, $ids, true);

        return $index === false ? 1 : intdiv((int) $index, $perPage) + 1;
    }

    public function find(User $guide, int $id): ?Booking
    {
        return $this->base($guide)->whereKey($id)->first();
    }

    /**
     * Open → Confirmed. Locks the guide's bookings on that day so two accepts (two devices, or
     * admin and guide) can't confirm the same day twice.
     *
     * @throws GuideBookingActionException not_open | expired | day_taken
     */
    public function accept(Booking $booking): Booking
    {
        $accepted = DB::transaction(function () use ($booking) {
            $locked = Booking::with('guiding')->whereKey($booking->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'pending') {
                throw new GuideBookingActionException('not_open');
            }
            if ($locked->expires_at && Carbon::parse($locked->expires_at)->isPast()) {
                throw new GuideBookingActionException('expired');
            }
            if ($this->dayTaken($locked)) {
                throw new GuideBookingActionException('day_taken');
            }

            $locked->status = 'accepted';
            $locked->save();

            // Legacy calendar rows (blocked_events) still mark accepted bookings as type booking.
            if ($locked->blocked_event_id && ($event = BlockedEvent::find($locked->blocked_event_id))) {
                $event->type = 'booking';
                $event->save();
            }

            return $locked;
        });

        event(new BookingStatusChanged($accepted, 'accepted'));

        return $accepted;
    }

    /**
     * Open → Declined with the guide's message and alternative dates. Validation (message length,
     * dates bookable) is the same as the emailed decline form: RejectionRequest.
     *
     * @param  list<string>  $alternativeDates  Y-m-d
     *
     * @throws GuideBookingActionException not_open
     */
    public function decline(Booking $booking, string $message, array $alternativeDates): void
    {
        sort($alternativeDates);

        if (! $this->rejections->reject($booking, $message, array_values(array_unique($alternativeDates)))) {
            throw new GuideBookingActionException('not_open');
        }
    }

    /**
     * Confirmed → Cancelled by the guide, only before the tour date (spec 5.2). The day is
     * released for new requests (see Guiding::getBlockedEvents) and no commission is charged.
     *
     * @throws GuideBookingActionException not_confirmed | past
     */
    public function cancel(Booking $booking, string $reason): Booking
    {
        $cancelled = DB::transaction(function () use ($booking, $reason) {
            $locked = Booking::whereKey($booking->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'accepted') {
                throw new GuideBookingActionException('not_confirmed');
            }
            $date = $locked->getBookingDate();
            if ($date && $date->copy()->startOfDay()->lt(Carbon::today())) {
                throw new GuideBookingActionException('past');
            }

            $locked->forceFill([
                'status' => 'cancelled',
                'cancel_reason' => in_array($reason, self::CANCEL_REASONS, true) ? $reason : 'other',
                'cancelled_by' => 'guide',
                'cancelled_at' => now(),
            ])->save();

            return $locked;
        });

        event(new BookingStatusChanged($cancelled, 'cancelled'));

        return $cancelled;
    }

    /** Another confirmed booking of the same guide on the same day (any of their tours). */
    public function dayTaken(Booking $booking): bool
    {
        $date = $booking->getBookingDate();
        if (! $date || ! $booking->guiding) {
            return false;
        }

        return Booking::query()
            ->whereKeyNot($booking->getKey())
            ->where('status', 'accepted')
            ->whereHas('guiding', fn (Builder $q) => $q->where('user_id', $booking->guiding->user_id))
            ->whereDate('book_date', $date->toDateString())
            ->lockForUpdate()
            ->exists();
    }

    /** Bookings of the guide's tours, with what the cards need. */
    public function base(User $guide): Builder
    {
        return $this->scope($guide)
            ->select('bookings.*')
            ->with(['guiding', 'registeredUser', 'guestUser', 'calendar_schedule', 'blocked_event'])
            ->withExists(['statusChanges as expired_by_job' => fn (Builder $q) => $q
                ->whereIn('to_status', BookingStatus::CANCELLED_STORED)
                ->where('reason', BookingStatus::expiryReason())]);
    }

    /** Just the guide's bookings (no columns, relations): for counts. */
    private function scope(User $guide): Builder
    {
        return Booking::query()
            ->whereHas('guiding', fn (Builder $q) => $q->where('user_id', $guide->id))
            ->whereNotNull('status')
            ->where('status', '!=', '');
    }

    /** Open requests by reply deadline; everything else newest tour date first (spec 5.3). */
    private function ordered(Builder $query, array $filters): Builder
    {
        if (($filters['status'] ?? self::FILTER_ALL) === self::FILTER_OPEN) {
            $query->orderBy('expires_at');
        } else {
            $query->orderByRaw(self::TOUR_DATE.' DESC');
        }

        return $query->orderByDesc('bookings.id');
    }

    /**
     * @param  array{status?: string, tour?: int|null, period?: string, search?: string}  $filters
     */
    private function filtered(User $guide, array $filters, bool $lean = false): Builder
    {
        $query = $lean ? $this->scope($guide) : $this->base($guide);
        $today = Carbon::today();

        if ($tour = (int) ($filters['tour'] ?? 0)) {
            $query->where('guiding_id', $tour);
        }

        match ($filters['period'] ?? self::PERIOD_ALL) {
            'upcoming' => $query->whereRaw(self::TOUR_DATE.' >= ?', [$today->toDateString()]),
            'past' => $query->whereRaw(self::TOUR_DATE.' < ?', [$today->toDateString()]),
            'this_month' => $query->whereRaw(self::TOUR_DATE.' BETWEEN ? AND ?', [$today->copy()->startOfMonth()->toDateString(), $today->copy()->endOfMonth()->toDateTimeString()]),
            'next_3_months' => $query->whereRaw(self::TOUR_DATE.' BETWEEN ? AND ?', [$today->toDateString(), $today->copy()->addMonths(3)->endOfDay()->toDateTimeString()]),
            default => null,
        };

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $id = (int) ltrim($search, '#');
            $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $search).'%';
            $query->where(fn (Builder $q) => $q
                ->when($id > 0, fn (Builder $w) => $w->orWhere('bookings.id', $id))
                ->orWhere('bookings.email', 'like', $like)
                ->orWhereHas('guiding', fn (Builder $g) => $g->where('title', 'like', $like))
                ->orWhere(fn (Builder $w) => $w->where('is_guest', false)->whereHas('registeredUser', fn (Builder $u) => $u->where(fn (Builder $n) => $n->where('firstname', 'like', $like)->orWhere('lastname', 'like', $like))))
                ->orWhere(fn (Builder $w) => $w->where('is_guest', true)->whereHas('guestUser', fn (Builder $u) => $u->where(fn (Builder $n) => $n->where('firstname', 'like', $like)->orWhere('lastname', 'like', $like)))));
        }

        $now = Carbon::now();
        match ($filters['status'] ?? self::FILTER_ALL) {
            self::FILTER_OPEN => $query->where('status', 'pending')->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', $now)),
            self::FILTER_CONFIRMED => $query->where('status', 'accepted')->whereRaw(self::TOUR_DATE.' >= ?', [$today->toDateString()]),
            self::FILTER_COMPLETED => $query->where('status', 'accepted')->whereRaw(self::TOUR_DATE.' < ?', [$today->toDateString()]),
            self::FILTER_CLOSED => $query->where(fn (Builder $q) => $q
                ->whereNotIn('status', ['pending', 'accepted'])
                ->orWhere(fn (Builder $p) => $p->where('status', 'pending')->where('expires_at', '<=', $now))),
            default => null,
        };

        return $query;
    }
}
