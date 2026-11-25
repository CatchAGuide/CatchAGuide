<?php

namespace App\Services\Customer;

use App\Enums\BookingStatus;
use App\Enums\VoucherOrderKind;
use App\Models\Booking;
use App\Models\CampVacationBooking;
use App\Models\TripBooking;
use App\Models\User;
use App\Models\VoucherOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Everything a signed-in user booked as a guest: tour bookings, camp and trip requests and
 * tour vouchers they bought. Always scoped to the user's own rows (user_id, and is_guest = 0
 * for tours, because bookings.user_id holds a user_guests id on guest bookings).
 */
class CustomerBookingService
{
    public const TAB_TOURS = 'tours';
    public const TAB_CAMPS = 'camps';
    public const TAB_TRIPS = 'trips';
    public const TAB_VOUCHERS = 'vouchers';

    public const TABS = [self::TAB_TOURS, self::TAB_CAMPS, self::TAB_TRIPS, self::TAB_VOUCHERS];

    public const FILTER_ALL = 'all';
    public const FILTER_OPEN = 'open';
    public const FILTER_CONFIRMED = 'confirmed';
    public const FILTER_COMPLETED = 'completed';
    public const FILTER_CLOSED = 'closed';

    public const FILTERS = [self::FILTER_ALL, self::FILTER_OPEN, self::FILTER_CONFIRMED, self::FILTER_COMPLETED, self::FILTER_CLOSED];

    /** Tour date; old rows without book_date fall back to the request date. */
    private const TOUR_DATE = 'COALESCE(bookings.book_date, bookings.created_at)';

    /** Tour bookings, newest tour date first, filtered by status tile. */
    public function paginateTours(User $user, string $status = self::FILTER_ALL, ?int $perPage = null): LengthAwarePaginator
    {
        $query = $this->tours($user);
        $this->applyStatus($query, $status);

        return $query->orderByRaw(self::TOUR_DATE.' DESC')
            ->orderByDesc('bookings.id')
            ->paginate($perPage ?? $this->perPage(), pageName: 'page')
            ->withQueryString();
    }

    /** @return array<string, int> one count per status tile */
    public function tourCounts(User $user): array
    {
        $today = Carbon::today()->toDateString();
        $now = Carbon::now()->toDateTimeString();
        $row = $this->tourScope($user)->toBase()
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

    /** @return array<string, int> rows per tab */
    public function tabCounts(User $user): array
    {
        return [
            self::TAB_TOURS => $this->tourScope($user)->count(),
            self::TAB_CAMPS => $this->camps($user)->count(),
            self::TAB_TRIPS => $this->trips($user)->count(),
            self::TAB_VOUCHERS => $this->vouchers($user)->count(),
        ];
    }

    public function paginateCamps(User $user, ?int $perPage = null): LengthAwarePaginator
    {
        return $this->camps($user)
            ->with(['camp', 'accommodation', 'rentalBoat', 'guiding'])
            ->orderByDesc('created_at')->orderByDesc('id')
            ->paginate($perPage ?? $this->perPage())->withQueryString();
    }

    public function paginateTrips(User $user, ?int $perPage = null): LengthAwarePaginator
    {
        return $this->trips($user)
            ->with('trip')
            ->orderByDesc('created_at')->orderByDesc('id')
            ->paginate($perPage ?? $this->perPage())->withQueryString();
    }

    public function paginateVouchers(User $user, ?int $perPage = null): LengthAwarePaginator
    {
        return $this->vouchers($user)
            ->with(['guiding', 'guide'])
            ->orderByDesc('created_at')->orderByDesc('id')
            ->paginate($perPage ?? $this->perPage())->withQueryString();
    }

    /** Confirmed tours from today, soonest first (Overview "Your next trips"). */
    public function upcomingTours(User $user, int $limit = 3): Collection
    {
        return $this->tours($user)
            ->where('status', BookingStatus::Confirmed->value)
            ->whereRaw(self::TOUR_DATE.' >= ?', [Carbon::today()->toDateString()])
            ->orderByRaw(self::TOUR_DATE.' ASC')
            ->limit($limit)
            ->get();
    }

    /** Requests still waiting for the guide's answer. */
    public function openTours(User $user, int $limit = 3): Collection
    {
        $query = $this->tours($user);
        $this->applyStatus($query, self::FILTER_OPEN);

        return $query->orderBy('expires_at')->orderByDesc('bookings.id')->limit($limit)->get();
    }

    /** Camp and trip requests the team is still working on, newest first. */
    public function openVacationRequests(User $user, int $limit = 3): Collection
    {
        $open = [CampVacationBooking::STATUS_OPEN, CampVacationBooking::STATUS_IN_PROCESS];

        return $this->camps($user)->whereIn('status', $open)->with('camp')->latest()->limit($limit)->get()
            ->concat($this->trips($user)->whereIn('status', $open)->with('trip')->latest()->limit($limit)->get())
            ->sortByDesc('created_at')
            ->take($limit)
            ->values();
    }

    /**
     * Past tours the user can still rate. Same checks as Booking::canBeReviewed(), done in
     * a few queries for the whole list instead of several per booking.
     */
    public function toursToRate(User $user, int $limit = 3): Collection
    {
        return $this->tours($user)
            ->where('status', BookingStatus::Confirmed->value)
            ->whereRaw(self::TOUR_DATE.' < ?', [Carbon::today()->toDateString()])
            ->where(fn (Builder $q) => $q->whereNull('is_reviewed')->orWhere('is_reviewed', false))
            ->whereNotNull('token')
            ->whereDoesntHave('review')
            ->orderByRaw(self::TOUR_DATE.' DESC')
            ->limit(10)
            ->get()
            ->filter(fn (Booking $b) => $b->canBeReviewed())
            ->take($limit)
            ->values();
    }

    /** Tour booking of this user, or null. */
    public function findTour(User $user, int $id): ?Booking
    {
        return $this->tours($user)->whereKey($id)->first();
    }

    /** Page the tour booking is on (deep links ?booking=id from old /profile/showbooking). */
    public function tourPage(User $user, int $id, ?int $perPage = null): int
    {
        $ids = $this->tourScope($user)
            ->orderByRaw(self::TOUR_DATE.' DESC')->orderByDesc('bookings.id')
            ->pluck('bookings.id')->map(fn ($v) => (int) $v)->all();
        $index = array_search($id, $ids, true);

        return $index === false ? 1 : intdiv((int) $index, $perPage ?? $this->perPage()) + 1;
    }

    /**
     * One tour booking with what its card needs, whoever it belongs to. Only for callers that
     * already checked access (the per-booking guest link).
     */
    public function tourForCard(int $id): ?Booking
    {
        return $this->withCardData(Booking::query())->whereKey($id)->first();
    }

    /** Tour bookings with what the cards need. */
    private function tours(User $user): Builder
    {
        return $this->withCardData($this->tourScope($user));
    }

    private function withCardData(Builder $query): Builder
    {
        return $query
            ->select('bookings.*')
            ->with(['guiding.user', 'calendar_schedule', 'blocked_event'])
            ->withExists(['statusChanges as expired_by_job' => fn (Builder $q) => $q
                ->whereIn('to_status', BookingStatus::CANCELLED_STORED)
                ->where('reason', BookingStatus::expiryReason())]);
    }

    private function tourScope(User $user): Builder
    {
        return Booking::query()
            ->where('user_id', $user->id)
            ->where('is_guest', false)
            ->whereNotNull('status')
            ->where('status', '!=', '');
    }

    private function camps(User $user): Builder
    {
        return CampVacationBooking::query()->where('user_id', $user->id);
    }

    private function trips(User $user): Builder
    {
        return TripBooking::query()->where('user_id', $user->id);
    }

    /** Tours bought as a voucher (the landing page's open requests have no tour yet). */
    private function vouchers(User $user): Builder
    {
        return VoucherOrder::query()
            ->where('user_id', $user->id)
            ->where(fn (Builder $q) => $q->whereNull('kind')->orWhere('kind', VoucherOrderKind::Order->value));
    }

    private function applyStatus(Builder $query, string $status): void
    {
        $today = Carbon::today()->toDateString();
        $now = Carbon::now();

        match ($status) {
            self::FILTER_OPEN => $query->where('status', 'pending')->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', $now)),
            self::FILTER_CONFIRMED => $query->where('status', 'accepted')->whereRaw(self::TOUR_DATE.' >= ?', [$today]),
            self::FILTER_COMPLETED => $query->where('status', 'accepted')->whereRaw(self::TOUR_DATE.' < ?', [$today]),
            self::FILTER_CLOSED => $query->where(fn (Builder $q) => $q
                ->whereNotIn('status', ['pending', 'accepted'])
                ->orWhere(fn (Builder $p) => $p->where('status', 'pending')->where('expires_at', '<=', $now))),
            default => null,
        };
    }

    private function perPage(): int
    {
        return (int) config('guide_area.customer.per_page', 8);
    }
}
