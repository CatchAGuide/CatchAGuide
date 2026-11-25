<?php

namespace App\Services\GuestBooking;

use App\Models\BookingAccessToken;

/**
 * Which booking links this browser session has opened. /b/{token} adds the link and redirects
 * to /your-booking/{id}, so the token leaves the address bar before the page (and Google Tag
 * Manager) loads, the same as the reschedule link. Opening the page in another browser needs
 * the email link again.
 */
class GuestBookingSession
{
    private const KEY = 'guest_booking_access';

    /** Keeps the session small: the most recent links only. */
    private const MAX = 20;

    /** Adds the link; returns false when this session had it already. */
    public function grant(BookingAccessToken $token): bool
    {
        $ids = $this->ids();
        if (in_array($token->id, $ids, true)) {
            return false;
        }

        $ids[] = (int) $token->id;
        session()->put(self::KEY, array_slice($ids, -self::MAX));

        return true;
    }

    public function allows(BookingAccessToken $token): bool
    {
        return $token->isActive() && in_array((int) $token->id, $this->ids(), true);
    }

    /** @return list<int> */
    private function ids(): array
    {
        return array_values(array_map('intval', (array) session()->get(self::KEY, [])));
    }
}
