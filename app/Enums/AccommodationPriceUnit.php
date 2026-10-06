<?php

namespace App\Enums;

/**
 * How an accommodation's nightly price is charged: for the whole unit, or per guest.
 */
enum AccommodationPriceUnit: string
{
    case PerNight = 'per_night';
    case PerPersonNight = 'per_person_night';

    public static function fromListing(mixed $value): self
    {
        return is_string($value) ? (self::tryFrom($value) ?? self::PerNight) : self::PerNight;
    }

    /**
     * Factor on the nightly price for a party: the guests for per-person pricing, else 1.
     */
    public function guestFactor(int $persons): int
    {
        return $this === self::PerPersonNight ? max(1, $persons) : 1;
    }

    public function label(): string
    {
        return __('accommodations.price_unit_'.$this->value);
    }
}
