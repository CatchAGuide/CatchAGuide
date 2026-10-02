<?php

namespace App\Services\Checkout\Trip;

use App\Models\TripBooking;
use App\Models\User;
use Illuminate\Contracts\Session\Session;

/**
 * Decides who may open a trip request's confirmation page. Request ids are sequential, so only
 * the browser session that just sent the request, or the signed-in user who sent it, gets
 * through (same rule as CampBookingConfirmationAccess).
 */
class TripBookingConfirmationAccess
{
    private const SESSION_KEY = 'checkout.confirmed_trip_request_ids';

    private const MAX_REMEMBERED = 10;

    public function __construct(
        private readonly Session $session,
    ) {}

    public function grant(TripBooking $booking): void
    {
        $ids = array_values(array_diff($this->rememberedIds(), [(int) $booking->getKey()]));
        $ids[] = (int) $booking->getKey();

        $this->session->put(self::SESSION_KEY, array_slice($ids, -self::MAX_REMEMBERED));
    }

    public function allows(TripBooking $booking, ?User $user): bool
    {
        if (in_array((int) $booking->getKey(), $this->rememberedIds(), true)) {
            return true;
        }

        return $user !== null
            && $booking->user_id !== null
            && (int) $booking->user_id === (int) $user->getKey();
    }

    /**
     * @return list<int>
     */
    private function rememberedIds(): array
    {
        return array_values(array_map('intval', (array) $this->session->get(self::SESSION_KEY, [])));
    }
}
