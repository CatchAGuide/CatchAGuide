<?php

namespace App\Services\GuestBooking;

use App\Mail\Guest\GuestFindBookingsMail;
use App\Models\BookingAccessEvent;
use App\Models\GuestLoginLink;
use App\Services\Customer\BookingOwner;
use App\Services\Customer\CustomerBookingService;
use App\Services\Sales\SalesLinks;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * "Find my booking" (plan "Guest Booking Access", Part 2). The guest enters an email; when it
 * has bookings, it gets a single-use link valid for 30 minutes. The answer on screen is the
 * same either way, so the page never tells whether an email has bookings.
 */
class GuestLoginLinkService
{
    public const RESULT_SENT = 'sent';
    public const RESULT_THROTTLED = 'throttled';

    public const LINK_MINUTES = 30;
    public const SESSION_DAYS = 7;

    private const TOKEN_LENGTH = 48;

    // Each email costs money and the form can be abused, so the limits stay tight (plan Part 2).
    private const PER_EMAIL_PER_HOUR = 3;
    private const PER_IP_PER_HOUR = 10;

    public function __construct(
        private readonly CustomerBookingService $bookings,
        private readonly SalesLinks $links,
    ) {}

    /**
     * RESULT_THROTTLED only for the IP limit (that says nothing about the email). Over the
     * email limit, or without bookings, nothing is sent and the answer is still RESULT_SENT.
     */
    public function request(string $email, string $ip, string $locale): string
    {
        $email = BookingOwner::normalize($email);

        $ipKey = 'find-booking:ip:'.sha1($ip);
        if (RateLimiter::tooManyAttempts($ipKey, self::PER_IP_PER_HOUR)) {
            return self::RESULT_THROTTLED;
        }
        RateLimiter::hit($ipKey, 3600);

        $emailKey = 'find-booking:email:'.sha1($email);
        if (RateLimiter::tooManyAttempts($emailKey, self::PER_EMAIL_PER_HOUR)) {
            return self::RESULT_SENT;
        }
        RateLimiter::hit($emailKey, 3600);

        if (! $this->hasBookings($email)) {
            return self::RESULT_SENT;
        }

        $plain = Str::random(self::TOKEN_LENGTH);
        GuestLoginLink::query()->create([
            'email' => $email,
            'token_hash' => GuestLoginLink::hashToken($plain),
            'locale' => $locale,
            'expires_at' => now()->addMinutes(self::LINK_MINUTES),
        ]);

        $url = $this->links->absolute(route('guest-bookings.enter', ['token' => $plain], false), $locale);
        Mail::to($email)->queue((new GuestFindBookingsMail($url, $locale))->locale($locale));
        $this->log($email, BookingAccessEvent::LOGIN_SENT);

        return self::RESULT_SENT;
    }

    public function hasBookings(string $email): bool
    {
        return array_sum($this->bookings->tabCounts(BookingOwner::email($email))) > 0;
    }

    /** The link behind a plain token, while it can still be used; null otherwise. */
    public function findUsable(string $plain): ?GuestLoginLink
    {
        if (strlen($plain) !== self::TOKEN_LENGTH || ! ctype_alnum($plain)) {
            return null;
        }

        $hash = GuestLoginLink::hashToken($plain);
        $link = GuestLoginLink::query()->where('token_hash', $hash)->first();

        return $link !== null && hash_equals($link->token_hash, $hash) && $link->isUsable() ? $link : null;
    }

    /** Uses the link up and starts its 7-day sign-in (Q4). */
    public function consume(GuestLoginLink $link): void
    {
        $link->forceFill([
            'used_at' => now(),
            'session_expires_at' => now()->addDays(self::SESSION_DAYS),
        ])->save();
        $this->log($link->email, BookingAccessEvent::LOGIN_USED);
    }

    public function signOut(GuestLoginLink $link): void
    {
        $link->forceFill(['signed_out_at' => now()])->save();
        $this->log($link->email, BookingAccessEvent::SIGNED_OUT);
    }

    private function log(string $email, string $event): void
    {
        BookingAccessEvent::query()->create(['email' => $email, 'event' => $event]);
    }
}
