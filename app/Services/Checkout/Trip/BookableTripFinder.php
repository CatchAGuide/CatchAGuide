<?php

namespace App\Services\Checkout\Trip;

use App\Domain\Vacation\BookableListingPolicy;
use App\Models\Trip;
use App\Services\Translation\ListingTranslationService;
use App\Services\Translation\ListingViewTranslationService;

/**
 * Loads a trip for the checkout: only bookable (active) trips, with their departures, localized
 * from the stored listing translations (no live translation calls).
 */
class BookableTripFinder
{
    public function __construct(
        private readonly BookableListingPolicy $policy,
        private readonly ListingViewTranslationService $translations,
    ) {}

    public function findBySlug(string $slug): ?Trip
    {
        $trip = Trip::query()
            ->with('availabilityDates')
            ->where('slug', $slug)
            ->where('status', $this->policy->activeStatus())
            ->first();

        if ($trip === null || ! $this->policy->isBookable($trip)) {
            return null;
        }

        $this->translations->applyToModel($trip, ListingTranslationService::TYPE_TRIP);

        return $trip;
    }
}
