<?php

namespace App\Services\Booking;

use App\Models\Booking;
use App\Models\BookingStatusChange;
use App\Models\Employee;
use Carbon\Carbon;

/**
 * Status history for guiding bookings. Their status changes in many places (admin modal, the
 * guide's email links and profile, the guest's cancellation, the expiry command), so the change is
 * logged from Booking's "updated" event and the actor is worked out from the request here.
 */
class GuidingBookingStatusLogger
{
    /** Guide answers a request through the links in the booking email (no login). */
    private const GUIDE_TOKEN_ROUTES = ['booking.accept', 'booking.reject', 'booking.rejection'];

    public function __construct(private readonly BookingStatusHistory $history)
    {
    }

    public function log(Booking $booking, ?string $from): void
    {
        $to = (string) $booking->status;
        if ($to === '' || $from === $to) {
            return;
        }

        $employee = auth('employees')->user();
        $employee = $employee instanceof Employee ? $employee : null;

        $this->history->record($booking, $from, $to, $employee, $employee ? null : $this->reason($booking, $from, $to));
    }

    private function reason(Booking $booking, ?string $from, string $to): ?string
    {
        if (request()->route() === null) {
            // No HTTP request: update:booking-status cancels pending requests the guide never answered.
            return $from === 'pending' && $to === 'cancelled' && $booking->expires_at && Carbon::parse($booking->expires_at)->isPast()
                ? BookingStatusChange::REASON_EXPIRED
                : null;
        }

        if (request()->routeIs(...self::GUIDE_TOKEN_ROUTES)) {
            return BookingStatusChange::REASON_GUIDE;
        }

        $userId = auth('web')->id();
        if ($userId === null) {
            return null;
        }

        if ((int) $userId === (int) $booking->guiding?->user_id) {
            return BookingStatusChange::REASON_GUIDE;
        }

        return ! $booking->is_guest && (int) $userId === (int) $booking->user_id
            ? BookingStatusChange::REASON_GUEST
            : null;
    }
}
