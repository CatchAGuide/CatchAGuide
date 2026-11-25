<?php

namespace App\Services\GuestBooking;

use App\Models\GuestLoginLink;
use App\Services\Customer\BookingOwner;
use Illuminate\Support\Facades\Cookie;

/**
 * The 7-day "Find my booking" sign-in of this browser. The login session lasts two hours, so
 * the sign-in lives in its own cookie (encrypted by Laravel, HTTP only) holding the id of the
 * used login link; the row decides whether it still counts, so "Sign out" ends it for good.
 * The cookie holds no email.
 */
class GuestBookingEmailSession
{
    public const COOKIE = 'cag_find_booking';

    /** Plain token between the email link and the "Show my bookings" button. */
    private const PENDING_KEY = 'guest_login_pending';

    private ?GuestLoginLink $resolved = null;

    private bool $looked = false;

    public function start(GuestLoginLink $link): void
    {
        Cookie::queue(Cookie::make(
            self::COOKIE,
            (string) $link->id,
            GuestLoginLinkService::SESSION_DAYS * 24 * 60,
            null,
            null,
            null,
            true,
            false,
            'lax',
        ));
        $this->resolved = $link;
        $this->looked = true;
    }

    public function current(): ?GuestLoginLink
    {
        if ($this->looked) {
            return $this->resolved;
        }
        $this->looked = true;

        $id = request()->cookie(self::COOKIE);
        if (! is_string($id) || ! ctype_digit($id)) {
            return null;
        }

        $link = GuestLoginLink::query()->find((int) $id);

        return $this->resolved = ($link?->isSignedIn() ? $link : null);
    }

    public function owner(): ?BookingOwner
    {
        $link = $this->current();

        return $link ? BookingOwner::email($link->email) : null;
    }

    public function end(): void
    {
        Cookie::queue(Cookie::forget(self::COOKIE));
        $this->resolved = null;
        $this->looked = true;
    }

    public function holdPending(string $plainToken): void
    {
        session()->put(self::PENDING_KEY, $plainToken);
    }

    public function pending(): ?string
    {
        $token = session()->get(self::PENDING_KEY);

        return is_string($token) ? $token : null;
    }

    public function forgetPending(): void
    {
        session()->forget(self::PENDING_KEY);
    }
}
