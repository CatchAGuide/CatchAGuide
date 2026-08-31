<?php

namespace App\Services\Homepage;

use App\Domain\Offers\DestinationOfferGeoScope;
use App\Domain\Vacation\VacationListingFilter;
use App\Models\CategoryEntity;
use App\Models\Guiding;
use App\Presenters\Offers\TourCardPresenter;
use App\Presenters\Vacation\CampCardPresenter;
use App\Presenters\Vacation\TripCardPresenter;
use App\Repositories\Vacation\CampListingRepository;
use App\Repositories\Vacation\TripListingRepository;
use App\Services\Offers\OfferFilterService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class HomepageMixedOfferSelector
{
    public function __construct(
        private CampListingRepository $camps,
        private TripListingRepository $trips,
        private CampCardPresenter $campPresenter,
        private TripCardPresenter $tripPresenter,
        private TourCardPresenter $tourPresenter,
        private OfferFilterService $offerFilters,
    ) {}

    /**
     * One collection per product module for homepage carousels.
     *
     * @return array{tour: Collection, camp: Collection, trip: Collection}
     */
    public function byModule(?int $perType = null): array
    {
        $perType = $perType ?? 8;
        $cacheKey = 'homepage_offer_modules_v2_'.app()->getLocale().'_'.$perType;

        return Cache::remember($cacheKey, now()->addMinutes(20), function () use ($perType) {
            return [
                'tour' => $this->popularGuidings($perType)
                    ->map(fn (Guiding $g) => $this->tourPresenter->present($g))
                    ->values(),
                'camp' => $this->camps->listForHub($perType)
                    ->map(fn ($c) => $this->campPresenter->present($c))
                    ->values(),
                'trip' => $this->trips->listForHub($perType)
                    ->map(fn ($t) => $this->tripPresenter->present($t))
                    ->values(),
            ];
        });
    }

    /**
     * Popular rails scoped to a destination country (and optional region/city).
     *
     * @return array{tour: Collection, camp: Collection, trip: Collection}
     */
    public function byModuleForDestination(
        CategoryEntity $country,
        ?CategoryEntity $region = null,
        ?CategoryEntity $city = null,
        ?int $perType = null,
    ): array {
        $perType = $perType ?? 8;
        $cacheKey = implode('_', [
            'destination_offer_modules_v1',
            app()->getLocale(),
            (string) $country->id,
            (string) ($region?->id ?? '0'),
            (string) ($city?->id ?? '0'),
            (string) $perType,
        ]);

        return Cache::remember($cacheKey, now()->addMinutes(20), function () use ($country, $region, $city, $perType) {
            return [
                'tour' => $this->popularGuidingsForDestination($perType, $country, $region, $city)
                    ->map(fn (Guiding $g) => $this->tourPresenter->present($g))
                    ->values(),
                'camp' => $this->campsForDestination($perType, $country, $region, $city)
                    ->map(fn ($c) => $this->campPresenter->present($c))
                    ->values(),
                'trip' => $this->tripsForDestination($perType, $country, $region, $city)
                    ->map(fn ($t) => $this->tripPresenter->present($t))
                    ->values(),
            ];
        });
    }

    /**
     * Popular rails scoped to a target fish species (global target-fish category page).
     *
     * @return array{tour: Collection, camp: Collection, trip: Collection}
     */
    public function byModuleForTargetFish(int $speciesId, ?int $perType = null): array
    {
        $perType = $perType ?? 8;
        $cacheKey = implode('_', [
            'target_fish_offer_modules_v1',
            app()->getLocale(),
            (string) $speciesId,
            (string) $perType,
        ]);

        return Cache::remember($cacheKey, now()->addMinutes(20), function () use ($speciesId, $perType) {
            return [
                'tour' => $this->popularGuidingsForSpecies($perType, $speciesId)
                    ->map(fn (Guiding $g) => $this->tourPresenter->present($g))
                    ->values(),
                'camp' => $this->campsForSpecies($perType, $speciesId)
                    ->map(fn ($c) => $this->campPresenter->present($c))
                    ->values(),
                'trip' => $this->tripsForSpecies($perType, $speciesId)
                    ->map(fn ($t) => $this->tripPresenter->present($t))
                    ->values(),
            ];
        });
    }

    /**
     * Flat mixed set (tests / legacy). Prefer byModule() for the homepage.
     */
    public function mixed(?int $limit = null): Collection
    {
        $limit = $limit ?? 12;
        $perType = (int) max(1, (int) ceil($limit / 3));
        $modules = $this->byModule($perType);

        return $modules['tour']
            ->merge($modules['camp'])
            ->merge($modules['trip'])
            ->take($limit)
            ->values();
    }

    private function popularGuidings(int $limit): Collection
    {
        return Guiding::query()
            ->withCount('bookings')
            ->publiclyVisible()
            ->orderByDesc('bookings_count')
            ->limit($limit)
            ->get();
    }

    private function popularGuidingsForDestination(
        int $limit,
        CategoryEntity $country,
        ?CategoryEntity $region,
        ?CategoryEntity $city,
    ): Collection {
        $query = Guiding::query()
            ->withCount('bookings')
            ->publiclyVisible();

        DestinationOfferGeoScope::apply($query, $country, $region, $city, includeCountryIso: true);

        return $query
            ->orderByDesc('bookings_count')
            ->limit($limit)
            ->get();
    }

    private function campsForDestination(
        int $limit,
        CategoryEntity $country,
        ?CategoryEntity $region,
        ?CategoryEntity $city,
    ): Collection {
        $query = $this->camps->queryForCountry($this->vacationFilter($country));
        DestinationOfferGeoScope::apply($query, $country, $region, $city);

        return $query
            ->with(['rentalBoats', 'facilities', 'guidings.guidingMethods', 'accommodations'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    private function tripsForDestination(
        int $limit,
        CategoryEntity $country,
        ?CategoryEntity $region,
        ?CategoryEntity $city,
    ): Collection {
        $query = $this->trips->queryForCountry($this->vacationFilter($country));
        DestinationOfferGeoScope::apply($query, $country, $region, $city);

        return $query
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    private function vacationFilter(CategoryEntity $country): VacationListingFilter
    {
        return VacationListingFilter::fromRequest([
            'country' => $country->slug,
            'country_short' => $country->countrycode,
        ]);
    }

    private function popularGuidingsForSpecies(int $limit, int $speciesId): Collection
    {
        $ids = $this->offerFilters->listingIdsForSpecies('tours', [$speciesId]) ?? [];
        if ($ids === []) {
            return collect();
        }

        return Guiding::query()
            ->withCount('bookings')
            ->publiclyVisible()
            ->whereIn('guidings.id', $ids)
            ->orderByDesc('bookings_count')
            ->limit($limit)
            ->get();
    }

    private function campsForSpecies(int $limit, int $speciesId): Collection
    {
        $ids = $this->offerFilters->listingIdsForSpecies('camps', [$speciesId]) ?? [];
        if ($ids === []) {
            return collect();
        }

        return $this->camps->queryForCountry(VacationListingFilter::fromRequest([]))
            ->whereIn('camps.id', $ids)
            ->with(['rentalBoats', 'facilities', 'guidings.guidingMethods', 'accommodations'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    private function tripsForSpecies(int $limit, int $speciesId): Collection
    {
        $ids = $this->offerFilters->listingIdsForSpecies('trips', [$speciesId]) ?? [];
        if ($ids === []) {
            return collect();
        }

        return $this->trips->queryForCountry(VacationListingFilter::fromRequest([]))
            ->whereIn('trips.id', $ids)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

}
