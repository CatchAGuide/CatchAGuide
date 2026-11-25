<?php

namespace App\Services\Customer;

use App\Models\User;
use InvalidArgumentException;

/**
 * Whose bookings to show: a signed-in user (their own rows by user_id), or an email address
 * the guest proved they can read ("Find my booking"): every tour, camp, trip and voucher booked
 * with that email, as a guest or with an account (Q9).
 */
final readonly class BookingOwner
{
    private function __construct(
        public ?User $user,
        public ?string $email,
    ) {}

    public static function user(User $user): self
    {
        return new self($user, null);
    }

    public static function email(string $email): self
    {
        $email = self::normalize($email);
        if ($email === '') {
            throw new InvalidArgumentException('Empty email');
        }

        return new self(null, $email);
    }

    public static function from(User|self $owner): self
    {
        return $owner instanceof self ? $owner : self::user($owner);
    }

    public function isEmail(): bool
    {
        return $this->email !== null;
    }

    public static function normalize(string $email): string
    {
        return mb_strtolower(trim($email));
    }
}
