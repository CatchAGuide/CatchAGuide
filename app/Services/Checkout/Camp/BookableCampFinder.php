<?php

namespace App\Services\Checkout\Camp;

use App\Domain\Vacation\BookableListingPolicy;
use App\Models\Camp;
use App\Services\Translation\ListingTranslationService;
use App\Services\Translation\ListingViewTranslationService;

/**
 * Loads a camp for the checkout: only bookable (active) camps, with exactly the bookable
 * accommodations, boats, tours and special offers CampCheckoutPricing prices, localized from
 * the stored listing translations (no live translation calls).
 */
class BookableCampFinder
{
    public function __construct(
        private readonly BookableListingPolicy $policy,
        private readonly ListingViewTranslationService $translations,
    ) {}

    public function findBySlug(string $slug): ?Camp
    {
        $camp = Camp::query()
            ->with(CampCheckoutPricing::relations())
            ->where('slug', $slug)
            ->where('status', $this->policy->activeStatus())
            ->first();

        if ($camp === null || ! $this->policy->isBookable($camp)) {
            return null;
        }

        $this->translations->applyToModel($camp, ListingTranslationService::TYPE_CAMP);
        $this->translations->applyToCollection($camp->accommodations, ListingTranslationService::TYPE_ACCOMMODATION);
        $this->translations->applyToCollection($camp->rentalBoats, ListingTranslationService::TYPE_RENTAL_BOAT);
        $this->translations->applyToGuidings($camp->guidings);
        $this->translations->applyToCollection($camp->specialOffers, ListingTranslationService::TYPE_SPECIAL_OFFER);

        return $camp;
    }
}
