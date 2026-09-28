<?php

namespace App\Services\Checkout;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Contracts\Session\Session;

/**
 * Decides who may open a booking's thank-you page. Booking ids are sequential, so the page
 * must not render for anyone who merely guesses a URL: only the browser session that just
 * created the booking, or the signed-in customer who owns it, gets through.
 */
class BookingConfirmationAccess
{
    private const SESSION_KEY = 'checkout.confirmed_booking_ids';

    /** Enough for a few bookings in one session without growing the session unbounded. */
    private const MAX_REMEMBERED = 10;

    public function __construct(
        private readonly Session $session,
    ) {}

    public function grant(Booking $booking): void
    {
        $ids = array_values(array_diff($this->rememberedIds(), [(int) $booking->getKey()]));
        $ids[] = (int) $booking->getKey();

        $this->session->put(self::SESSION_KEY, array_slice($ids, -self::MAX_REMEMBERED));
    }

    public function allows(Booking $booking, ?User $user): bool
    {
        if (in_array((int) $booking->getKey(), $this->rememberedIds(), true)) {
            return true;
        }

        return $user !== null
            && ! $booking->is_guest
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
