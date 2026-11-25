<?php

namespace App\Http\Controllers;

use App\Enums\GuestBookingType;
use App\Services\Customer\CustomerBookingService;
use App\Services\GuestBooking\BookingAccessTokenService;
use App\Services\GuestBooking\GuestBookingEmailSession;
use App\Services\GuestBooking\GuestBookingSession;
use App\Services\GuestBooking\GuestLoginLinkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * "Find my booking" (plan "Guest Booking Access", Part 2): email form, sign-in link, the list
 * of every booking under that email, and sign out.
 *
 * The emailed link (/my-bookings/sign-in/{token}) only parks the token in the session and
 * redirects, so the token never reaches the address bar, analytics or a referrer. It's used
 * up by the "Show my bookings" button (POST), so mail scanners that open links don't spend it.
 */
class FindMyBookingController extends Controller
{
    private const PRIVATE_HEADERS = [
        'Referrer-Policy' => 'no-referrer',
        'Cache-Control' => 'no-store, private',
        'X-Robots-Tag' => 'noindex, nofollow',
    ];

    public function __construct(
        private readonly GuestLoginLinkService $links,
        private readonly GuestBookingEmailSession $emailSession,
    ) {}

    public function form(): Response|RedirectResponse
    {
        if ($this->emailSession->current() !== null) {
            return redirect()->route('guest-bookings.list');
        }

        return $this->page('guest-bookings.find');
    }

    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'string', 'email', 'max:191']]);

        $result = $this->links->request($data['email'], (string) $request->ip(), app()->getLocale());
        if ($result === GuestLoginLinkService::RESULT_THROTTLED) {
            return back()->withInput()->withErrors(['email' => __('guest_bookings.find.throttled')]);
        }

        return redirect()->route('guest-bookings.find')->with('find_booking_sent', true);
    }

    public function enter(string $token): RedirectResponse
    {
        $this->emailSession->holdPending($token);

        return redirect()->route('guest-bookings.confirm', [], 303)->withHeaders(self::PRIVATE_HEADERS);
    }

    public function confirm(): Response
    {
        $pending = $this->emailSession->pending();
        $usable = $pending !== null && $this->links->findUsable($pending) !== null;

        return $this->page('guest-bookings.confirm', ['usable' => $usable], $usable ? 200 : 410);
    }

    public function start(): Response|RedirectResponse
    {
        $pending = $this->emailSession->pending();
        $link = $pending !== null ? $this->links->findUsable($pending) : null;
        if ($link === null) {
            return $this->page('guest-bookings.confirm', ['usable' => false], 410);
        }

        $this->links->consume($link);
        $this->emailSession->start($link);
        $this->emailSession->forgetPending();

        return redirect()->route('guest-bookings.list', [], 303);
    }

    public function list(): Response|RedirectResponse
    {
        if ($this->emailSession->current() === null) {
            return redirect()->route('guest-bookings.find')->with('find_booking_expired', true);
        }

        return $this->page('guest-bookings.list');
    }

    public function signOut(): RedirectResponse
    {
        $link = $this->emailSession->current();
        if ($link !== null) {
            $this->links->signOut($link);
        }
        $this->emailSession->end();

        return redirect()->route('guest-bookings.find')->with('find_booking_signed_out', true);
    }

    /** A card's own page (calendar, voucher PDF): same per-booking page as the email link. */
    public function open(string $type, int $id, CustomerBookingService $bookings, BookingAccessTokenService $tokens, GuestBookingSession $session): RedirectResponse
    {
        $owner = $this->emailSession->owner();
        if ($owner === null) {
            return redirect()->route('guest-bookings.find')->with('find_booking_expired', true);
        }

        $bookable = $bookings->findFor($owner, GuestBookingType::from($type), $id);
        $access = $bookable !== null && $tokens->plainTokenFor($bookable) !== null ? $tokens->active($bookable) : null;
        abort_if($access === null, 404);

        if ($session->grant($access)) {
            $tokens->markOpened($access);
        }

        return redirect()->route('guest-booking.show', ['access' => $access->id]);
    }

    /** @param  array<string, mixed>  $data */
    private function page(string $view, array $data = [], int $status = 200): Response
    {
        return response()->view($view, $data, $status)->withHeaders(self::PRIVATE_HEADERS);
    }
}
