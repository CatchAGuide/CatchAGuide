<?php

namespace App\Services\GuestBooking;

use App\Enums\GuestBookingType;
use App\Models\BookingAccessEvent;
use App\Models\BookingAccessToken;
use App\Services\Sales\SalesLinks;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Per-booking links (/b/{token}) for tours, camps, trips and vouchers. A link is made the
 * first time an email needs it and reused by every later email for that booking, so the guest
 * always has the same link. It never expires; an admin can turn it off, and the next email
 * carries a new one. Tokens are 48 random characters, looked up by their sha256.
 */
class BookingAccessTokenService
{
    private const TOKEN_LENGTH = 48;

    public function __construct(private readonly SalesLinks $links) {}

    /**
     * Absolute link for the booking on the domain of $locale (catchaguide.de / .com). Null when
     * the booking isn't saved or has no email (nobody could receive the link).
     */
    public function urlFor(Model $bookable, ?string $locale = null): ?string
    {
        $token = $this->plainTokenFor($bookable);
        if ($token === null) {
            return null;
        }

        return $this->links->absolute(route('guest-booking.enter', ['token' => $token], false), $locale ?? app()->getLocale());
    }

    /** The active token's plain value, made on first use. */
    public function plainTokenFor(Model $bookable): ?string
    {
        if (! GuestBookingType::supports($bookable) || ! $bookable->exists) {
            return null;
        }

        $type = GuestBookingType::forModel($bookable);
        $email = $type->email($bookable);
        if ($email === null) {
            return null;
        }

        $existing = $this->active($bookable);
        if ($existing !== null) {
            return $existing->plainToken();
        }

        return DB::transaction(function () use ($bookable, $type, $email) {
            // Two emails rendered at once must not make two links.
            $existing = $this->activeQuery($type, (int) $bookable->getKey())->lockForUpdate()->first();
            if ($existing !== null) {
                return $existing->plainToken();
            }

            return $this->issue($type, (int) $bookable->getKey(), $email)[1];
        });
    }

    /** Active token row by the plain token from a link, or null. */
    public function find(string $plainToken): ?BookingAccessToken
    {
        if (strlen($plainToken) !== self::TOKEN_LENGTH || ! ctype_alnum($plainToken)) {
            return null;
        }

        $row = BookingAccessToken::query()->where('token_hash', BookingAccessToken::hashToken($plainToken))->first();

        // The database lookup is by hash; compare again in constant time all the same.
        if ($row === null || ! hash_equals($row->token_hash, BookingAccessToken::hashToken($plainToken))) {
            return null;
        }

        return $row->isActive() ? $row : null;
    }

    public function active(Model $bookable): ?BookingAccessToken
    {
        if (! GuestBookingType::supports($bookable) || ! $bookable->exists) {
            return null;
        }

        return $this->activeQuery(GuestBookingType::forModel($bookable), (int) $bookable->getKey())->first();
    }

    /**
     * Turns the booking's link off (for example after an email was forwarded by mistake). The
     * next email or "Send booking link" makes a new one. Returns whether a link was active.
     */
    public function revoke(Model $bookable, ?int $employeeId = null): bool
    {
        $token = $this->active($bookable);
        if ($token === null) {
            return false;
        }

        $token->forceFill(['revoked_at' => now()])->save();
        $this->log($token, BookingAccessEvent::REVOKED, $employeeId);

        return true;
    }

    public function markOpened(BookingAccessToken $token): void
    {
        $token->forceFill(['last_used_at' => now()])->save();
        $this->log($token, BookingAccessEvent::OPENED);
    }

    public function log(BookingAccessToken $token, string $event, ?int $employeeId = null): void
    {
        BookingAccessEvent::query()->create([
            'booking_access_token_id' => $token->id,
            'email' => $token->email,
            'bookable_type' => $token->bookable_type->value,
            'bookable_id' => $token->bookable_id,
            'event' => $event,
            'employee_id' => $employeeId,
        ]);
    }

    /** @return array{0: BookingAccessToken, 1: string} */
    private function issue(GuestBookingType $type, int $id, string $email): array
    {
        $plain = Str::random(self::TOKEN_LENGTH);

        $token = BookingAccessToken::query()->create([
            'bookable_type' => $type,
            'bookable_id' => $id,
            'email' => $email,
            'token_hash' => BookingAccessToken::hashToken($plain),
            'token_encrypted' => Crypt::encryptString($plain),
        ]);
        $this->log($token, BookingAccessEvent::CREATED);

        return [$token, $plain];
    }

    private function activeQuery(GuestBookingType $type, int $id)
    {
        return BookingAccessToken::query()
            ->where('bookable_type', $type->value)
            ->where('bookable_id', $id)
            ->whereNull('revoked_at')
            ->latest('id');
    }
}
