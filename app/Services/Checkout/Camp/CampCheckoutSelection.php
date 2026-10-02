<?php

namespace App\Services\Checkout\Camp;

/**
 * What a guest picked in the camp checkout. Ids are only references: CampCheckoutPricing
 * ignores any id that isn't bookable at this camp.
 */
final class CampCheckoutSelection
{
    public function __construct(
        public readonly int $nights,
        public readonly int $persons,
        public readonly ?int $accommodationId = null,
        public readonly ?int $rentalBoatId = null,
        public readonly ?int $guidingId = null,
        public readonly ?int $specialOfferId = null,
    ) {}
}
