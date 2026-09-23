<?php

namespace App\Services\Seo;

use App\Models\CategoryEntity;
use App\Repositories\Guiding\GuidingCategoryAvailabilityRepository;
use App\Repositories\Vacation\VacationDestinationRepository;
use App\Services\Homepage\HomepageMixedOfferSelector;
use App\Services\Offers\OfferCatalogPageService;
use Illuminate\Support\Facades\Cache;

/**
 * Minimum-inventory rule for facet pages (country, region, city, pillar×country). A page below
 * the threshold may still render, but is noindexed and left out of every sitemap. Pages and
 * sitemap contributors both ask this class, so the two can never disagree. See CLAUDE.md's
 * "SEO / catalog page conventions".
 */
class CatalogInventoryGate
{
    /** Country, species and method pages need at least one product. */
    public const MIN_FACET_LISTINGS = 1;

    /** Region and city pages need at least three. */
    public const MIN_GEO_LISTINGS = 3;

    private const CACHE_MINUTES = 60;

    public function __construct(
        private readonly VacationDestinationRepository $vacationDestinations,
        private readonly GuidingCategoryAvailabilityRepository $guidingAvailability,
        private readonly OfferCatalogPageService $offerCatalog,
        private readonly HomepageMixedOfferSelector $mixedOffers,
    ) {}

    /**
     * /vacations/{country} (pillar null) or /vacations/{trips|camps}/{country}.
     */
    public function vacationCountryIndexable(string $countrySlug, ?string $pillar = null): bool
    {
        $row = $this->vacationDestinations->hubGridCountry($countrySlug);
        if ($row === null) {
            return false;
        }

        $count = match ($pillar) {
            'trips' => (int) ($row['trips'] ?? 0),
            'camps' => (int) ($row['camps'] ?? 0),
            default => (int) ($row['trips'] ?? 0) + (int) ($row['camps'] ?? 0),
        };

        return $count >= self::MIN_FACET_LISTINGS;
    }

    /**
     * /guidings/{country}/{region?}/{city?}.
     */
    public function guidingDestinationIndexable(CategoryEntity $country, ?CategoryEntity $region = null, ?CategoryEntity $city = null): bool
    {
        if ($region === null && $city === null) {
            return $this->guidingAvailability->hasGuidingsForCountry($country->slug, $country->countrycode);
        }

        return $this->guidingGeoTourCount($country, $region, $city) >= self::MIN_GEO_LISTINGS;
    }

    /**
     * /destination/{country}/{region?}/{city?} — counts the tours, camps and trips the page's
     * offer modules draw from.
     */
    public function destinationIndexable(CategoryEntity $country, ?CategoryEntity $region = null, ?CategoryEntity $city = null): bool
    {
        $key = implode('_', ['destination_geo_listing_count_v1', $country->id, $region?->id ?? 0, $city?->id ?? 0]);
        $count = (int) Cache::remember(
            $key,
            now()->addMinutes(self::CACHE_MINUTES),
            fn () => $this->mixedOffers->countForDestination($country, $region, $city),
        );

        $minimum = $region === null && $city === null ? self::MIN_FACET_LISTINGS : self::MIN_GEO_LISTINGS;

        return $count >= $minimum;
    }

    private function guidingGeoTourCount(CategoryEntity $country, ?CategoryEntity $region, ?CategoryEntity $city): int
    {
        $key = implode('_', ['guiding_geo_tour_count_v1', $country->id, $region?->id ?? 0, $city?->id ?? 0]);

        return (int) Cache::remember(
            $key,
            now()->addMinutes(self::CACHE_MINUTES),
            fn () => $this->offerCatalog->countToursForDestination($country, $region, $city),
        );
    }
}
