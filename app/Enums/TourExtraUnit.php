<?php

namespace App\Enums;

/**
 * How a tour extra (guidings.pricing_extra item) is charged. Extras saved before the unit
 * existed carry no unit and are per person, which is how checkout always priced them.
 */
enum TourExtraUnit: string
{
    case PerPerson = 'per_person';
    case PerBooking = 'per_booking';
    case PerItem = 'per_item';

    public static function fromListing(mixed $value): self
    {
        return is_string($value) ? (self::tryFrom($value) ?? self::PerPerson) : self::PerPerson;
    }

    /**
     * Quantity checkout charges for a party size: per person scales with guests; a booking
     * fee or a single item is charged once (checkout does not ask for an item count).
     */
    public function checkoutQuantity(int $persons): int
    {
        return $this === self::PerPerson ? max(1, $persons) : 1;
    }

    public function label(): string
    {
        return __('newguidings.extra_unit_'.$this->value);
    }
}
