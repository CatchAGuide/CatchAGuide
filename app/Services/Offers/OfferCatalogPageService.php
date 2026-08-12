<?php

namespace App\Services\Offers;

use App\Domain\Offers\DestinationOfferScope;
use App\Domain\Offers\OfferListingFilter;
use App\Domain\Offers\ViewModels\OfferCatalogViewModel;
use App\Domain\Vacation\CountrySlug;
use App\Domain\Vacation\VacationListingFilter;
use App\Models\AccommodationType;
use App\Models\Camp;
use App\Models\City;
use App\Models\Country;
use App\Models\Guiding;
use App\Models\Method;
use App\Models\Region;
use App\Models\Review;
use App\Models\Trip;
use App\Models\Water;
use App\Models\Target;
use App\Presenters\Offers\TourCardPresenter;
use App\Presenters\Vacation\CampCardPresenter;
use App\Presenters\Vacation\TripCardPresenter;
use App\Repositories\Vacation\CampListingRepository;
use App\Repositories\Vacation\TripListingRepository;
use App\Repositories\Vacation\VacationDestinationRepository;
use App\Services\GuidingFilterService;
use App\Services\Location\GeospatialSearchService;
use App\Services\Vacation\VacationFilterApplicator;
use App\Support\Maps\MapMarkerCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class OfferCatalogPageService
{
    /**
     * Prior mean (1–10 scale) for Bayesian recommended ranking.
     * Pulls sparse high averages toward a neutral-good baseline.
     */
    private const RECOMMENDED_PRIOR_MEAN = 7.0;

    /**
     * Prior weight in “virtual reviews” — higher = more reviews needed to outrank the prior.
     */
    private const RECOMMENDED_PRIOR_WEIGHT = 5;

    public function __construct(
        private TripListingRepository $trips,
        private CampListingRepository $camps,
        private VacationDestinationRepository $destinations,
        private VacationFilterApplicator $filterApplicator,
        private TourCardPresenter $tourPresenter,
        private TripCardPresenter $tripPresenter,
        private CampCardPresenter $campPresenter,
        private GeospatialSearchService $geoSearch,
        private GuidingFilterService $guidingFilters,
        private OfferFilterService $offerFilters,
    ) {}

    public function build(Request $request): OfferCatalogViewModel
    {
        return $this->buildFromInput($request->all(), $request);
    }

    public function buildForDestination(
        Request $request,
        Country $country,
        ?Region $region = null,
        ?City $city = null,
    ): OfferCatalogViewModel {
        $input = DestinationOfferScope::mergeIntoRequest(
            $request->all(),
            $country,
            $region,
            $city,
        );

        return $this->buildFromInput(
            $input,
            $request,
            catalogUrl: $request->url(),
            lockDestinationScope: true,
            includeFaq: false,
        );
    }

    /**
     * Target-fish category pages: lock species and list tours + vacation offers.
     */
    public function buildForTargetFish(Request $request, int $speciesId): OfferCatalogViewModel
    {
        $input = $request->all();
        $input['species'] = [$speciesId];

        return $this->buildFromInput(
            $input,
            $request,
            catalogUrl: $request->url(),
            lockSpeciesScope: true,
            includeFaq: false,
        );
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private function buildFromInput(
        array $input,
        Request $request,
        ?string $catalogUrl = null,
        bool $lockDestinationScope = false,
        bool $lockSpeciesScope = false,
        bool $includeFaq = true,
    ): OfferCatalogViewModel {
        $filter = OfferListingFilter::fromRequest($input);
        $vacationFilter = $filter->toVacationFilter();
        $vacationGeoFilter = $this->vacationFilterWithoutSpecies($vacationFilter);
        $perPage = (int) config('offers.per_page', 9);
        $hasGeo = $filter->placeLat !== null && $filter->placeLng !== null;

        $tourQuery = $this->queryTours($filter, $request);
        $tripQuery = $this->queryTrips($filter, $vacationGeoFilter);
        $campQuery = $this->queryCamps($filter, $vacationGeoFilter);

        $toursTotal = (clone $tourQuery)->count();
        $tripsTotal = (clone $tripQuery)->count();
        $campsTotal = (clone $campQuery)->count();

        $listings = $this->buildListingsPaginator($filter, $tourQuery, $tripQuery, $campQuery, $perPage);
        $numGuests = $filter->numGuests;
        $cards = collect($listings->items())->map(function (array $item) use ($numGuests) {
            $card = match ($item['type']) {
                'tour' => $this->tourPresenter->presentListRow($item['model'], $numGuests),
                'trip' => $this->tripPresenter->presentListRow($item['model'], $numGuests),
                default => $this->campPresenter->presentListRow($item['model']),
            };
            $card['badge'] = __('offers.badge_'.$card['type']);

            return $card;
        });

        $listingsTotal = $this->resolveListingsTotal($filter, $toursTotal, $tripsTotal, $campsTotal);

        // Same trigger as guidings nearby section: empty or sparse main results.
        $maxMainForNearby = (int) config('location_search.nearby_section_max_main_results', 12);
        $shouldSuggest = $listingsTotal === 0 || $listingsTotal <= $maxMainForNearby;

        $suggestedItems = collect();
        if ($shouldSuggest && $hasGeo) {
            $suggestedItems = $this->buildNearbySuggestedItems(
                $filter,
                $filter->placeLat,
                $filter->placeLng,
                excludeTourIds: (clone $tourQuery)->pluck('guidings.id')->map(fn ($id) => (int) $id)->all(),
                excludeTripIds: (clone $tripQuery)->pluck('id')->map(fn ($id) => (int) $id)->all(),
                excludeCampIds: (clone $campQuery)->pluck('id')->map(fn ($id) => (int) $id)->all(),
            );
        } elseif ($shouldSuggest && ! $hasGeo && $listingsTotal === 0) {
            $suggestedItems = $this->buildRandomSuggestedItems($filter);
        }

        $suggestedCards = $suggestedItems
            ->map(function (array $item) use ($numGuests) {
                $card = match ($item['type']) {
                    'tour' => $this->tourPresenter->presentListRow($item['model'], $numGuests),
                    'trip' => $this->tripPresenter->presentListRow($item['model'], $numGuests),
                    default => $this->campPresenter->presentListRow($item['model']),
                };
                $card['badge'] = __('offers.badge_'.$card['type']);
                $card['is_suggested'] = true;

                return $card;
            })
            ->values();

        // Chip / results counts must match what the page actually lists (main + nearby suggestions).
        $toursTotal += $suggestedItems->where('type', 'tour')->count();
        $tripsTotal += $suggestedItems->where('type', 'trip')->count();
        $campsTotal += $suggestedItems->where('type', 'camp')->count();
        $listingsTotal = $this->resolveListingsTotal($filter, $toursTotal, $tripsTotal, $campsTotal);

        $countries = $lockDestinationScope
            ? collect()
            : $this->destinations->countriesForHubGrid()->map(fn ($row) => [
                'slug' => $row['slug'],
                'name' => $row['name'],
            ])->values();

        return new OfferCatalogViewModel(
            filter: $filter,
            listings: $listings,
            cards: $cards,
            toursTotal: $toursTotal,
            tripsTotal: $tripsTotal,
            campsTotal: $campsTotal,
            listingsTotal: $listingsTotal,
            speciesOptions: $lockSpeciesScope
                ? collect()
                : $this->offerFilters->speciesOptions($filter->country, $filter->countryShort),
            countries: $countries,
            methodOptions: $this->methodOptions(),
            waterOptions: $this->waterOptions(),
            tourDurationOptions: $this->tourDurationOptions(),
            tripDurationOptions: $this->tripDurationOptions(),
            accommodationTypeOptions: $this->accommodationTypeOptions(),
            faq: $includeFaq ? $this->resolveFaq() : collect(),
            mapMarkers: $this->buildMapMarkers($filter, $tourQuery, $tripQuery, $campQuery, $suggestedItems),
            suggestedCards: $suggestedCards,
            catalogUrl: $catalogUrl,
            lockDestinationScope: $lockDestinationScope,
            lockSpeciesScope: $lockSpeciesScope,
        );
    }

    private function resolveListingsTotal(
        OfferListingFilter $filter,
        int $toursTotal,
        int $tripsTotal,
        int $campsTotal,
    ): int {
        return match (true) {
            $filter->type === 'tour' => $toursTotal,
            $filter->type === 'vacation' && $filter->vacation === 'trip' => $tripsTotal,
            $filter->type === 'vacation' && $filter->vacation === 'camp' => $campsTotal,
            $filter->type === 'vacation' => $tripsTotal + $campsTotal,
            default => $toursTotal + $tripsTotal + $campsTotal,
        };
    }

    /**
     * Merge tours + trips + camps into one sorted catalog, hydrating only the current page.
     *
     * @return LengthAwarePaginator<int, array{type: string, model: mixed}>
     */
    private function buildListingsPaginator(
        OfferListingFilter $filter,
        Builder $tourQuery,
        Builder $tripQuery,
        Builder $campQuery,
        int $perPage,
    ): LengthAwarePaginator {
        $sortBy = $filter->effectiveSortBy();
        $needsPrice = in_array($sortBy, ['price-asc', 'price-desc'], true);
        $needsNearest = $sortBy === 'nearest';
        $needsRecommended = $sortBy === 'recommended';
        $origin = $needsNearest ? $filter->nearestOrigin() : null;
        // No coordinates yet: nearest falls back to recommended until geolocation arrives.
        if ($needsNearest && $origin === null) {
            $needsNearest = false;
            $needsRecommended = true;
            $sortBy = 'recommended';
        }

        $keys = collect();

        if ($filter->showsTours()) {
            $tourColumns = [
                'guidings.id',
                'guidings.created_at',
                'guidings.price',
                'guidings.prices',
                'guidings.price_type',
                'guidings.max_guests',
                'guidings.user_id',
            ];
            if ($needsNearest) {
                $tourColumns[] = 'guidings.lat';
                $tourColumns[] = 'guidings.lng';
            }
            $tours = (clone $tourQuery)->get($tourColumns);
            $tourRecommended = $needsRecommended
                ? $this->recommendedScoresByGuideId($tours->pluck('user_id')->filter()->unique()->map(fn ($id) => (int) $id)->all())
                : [];
            $keys = $keys->concat($tours->map(fn (Guiding $guiding) => [
                'type' => 'tour',
                'id' => (int) $guiding->id,
                'created_at' => $guiding->created_at,
                'price' => $needsPrice ? ($guiding->getLowestPrice() ?: null) : null,
                'rating' => $needsRecommended
                    ? ($tourRecommended[(int) $guiding->user_id] ?? null)
                    : null,
                'distance' => ($needsNearest && $origin !== null)
                    ? $this->distanceKm($origin['lat'], $origin['lng'], $guiding->lat, $guiding->lng)
                    : null,
            ]));
        }

        if ($filter->showsTrips()) {
            $tripColumns = ['id', 'created_at', 'price_per_person'];
            if ($needsNearest) {
                $tripColumns[] = 'latitude';
                $tripColumns[] = 'longitude';
            }
            $trips = (clone $tripQuery)->get($tripColumns);
            $keys = $keys->concat($trips->map(fn (Trip $trip) => [
                'type' => 'trip',
                'id' => (int) $trip->id,
                'created_at' => $trip->created_at,
                'price' => $needsPrice ? $trip->price_per_person : null,
                'rating' => null,
                'distance' => ($needsNearest && $origin !== null)
                    ? $this->distanceKm($origin['lat'], $origin['lng'], $trip->latitude, $trip->longitude)
                    : null,
            ]));
        }

        if ($filter->showsCamps()) {
            $campBuilder = clone $campQuery;
            if ($needsPrice) {
                $campBuilder->with(['accommodations', 'specialOffers']);
            }
            $campColumns = $needsPrice
                ? ['*']
                : ($needsNearest ? ['id', 'created_at', 'latitude', 'longitude'] : ['id', 'created_at']);
            $camps = $campBuilder->get($campColumns);
            $keys = $keys->concat($camps->map(fn (Camp $camp) => [
                'type' => 'camp',
                'id' => (int) $camp->id,
                'created_at' => $camp->created_at,
                'price' => $needsPrice ? $camp->getLowestAccommodationOrOfferPrice() : null,
                'rating' => null,
                'distance' => ($needsNearest && $origin !== null)
                    ? $this->distanceKm($origin['lat'], $origin['lng'], $camp->latitude, $camp->longitude)
                    : null,
            ]));
        }

        $merged = $this->sortListingItems($keys, $filter, $sortBy);
        $page = LengthAwarePaginator::resolveCurrentPage();
        $total = $merged->count();
        $pageKeys = $merged->slice(($page - 1) * $perPage, $perPage)->values();
        $pageItems = $this->hydrateListingPage($pageKeys, $tourQuery, $tripQuery, $campQuery);

        return new LengthAwarePaginator(
            $pageItems->all(),
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->except('page')],
        );
    }

    /**
     * @param  Collection<int, array{type: string, id: int, created_at: mixed, price: mixed}>  $pageKeys
     * @return Collection<int, array{type: string, model: mixed}>
     */
    private function hydrateListingPage(
        Collection $pageKeys,
        Builder $tourQuery,
        Builder $tripQuery,
        Builder $campQuery,
    ): Collection {
        if ($pageKeys->isEmpty()) {
            return collect();
        }

        $tourIds = $pageKeys->where('type', 'tour')->pluck('id')->all();
        $tripIds = $pageKeys->where('type', 'trip')->pluck('id')->all();
        $campIds = $pageKeys->where('type', 'camp')->pluck('id')->all();

        $toursById = $tourIds === []
            ? collect()
            : (clone $tourQuery)
                ->with(['user.reviews', 'boatType'])
                ->whereIn('guidings.id', $tourIds)
                ->get()
                ->keyBy('id');

        $tripsById = $tripIds === []
            ? collect()
            : (clone $tripQuery)
                ->whereIn('id', $tripIds)
                ->get()
                ->keyBy('id');

        $campsById = $campIds === []
            ? collect()
            : (clone $campQuery)
                ->with(['rentalBoats', 'facilities', 'guidings.guidingMethods', 'accommodations', 'specialOffers'])
                ->whereIn('id', $campIds)
                ->get()
                ->keyBy('id');

        return $pageKeys->map(function (array $key) use ($toursById, $tripsById, $campsById) {
            $model = match ($key['type']) {
                'tour' => $toursById->get($key['id']),
                'trip' => $tripsById->get($key['id']),
                default => $campsById->get($key['id']),
            };

            if ($model === null) {
                return null;
            }

            return [
                'type' => $key['type'],
                'model' => $model,
            ];
        })->filter()->values();
    }

    /**
     * @param  Collection<int, array{type: string, model: mixed, created_at: mixed, price: mixed, rating?: float|null, distance?: float|null}>  $items
     * @return Collection<int, array{type: string, model: mixed}>
     */
    private function sortListingItems(Collection $items, OfferListingFilter $filter, ?string $sortBy = null): Collection
    {
        $sortBy ??= $filter->effectiveSortBy();

        return match ($sortBy) {
            'price-asc' => $items->sortBy(fn ($item) => $item['price'] ?? PHP_FLOAT_MAX)->values(),
            'price-desc' => $items->sortByDesc(fn ($item) => $item['price'] ?? 0)->values(),
            'nearest' => $items->sortBy(fn ($item) => $item['distance'] ?? PHP_FLOAT_MAX)->values(),
            'newest' => $items->sortByDesc('created_at')->values(),
            default => $items->sort(function (array $a, array $b) {
                // `rating` holds the Bayesian recommended score (avg + review count).
                $ratingA = $a['rating'] ?? null;
                $ratingB = $b['rating'] ?? null;
                if ($ratingA !== null && $ratingB !== null && abs($ratingA - $ratingB) > 0.0001) {
                    return $ratingB <=> $ratingA;
                }
                if ($ratingA !== null && $ratingB === null) {
                    return -1;
                }
                if ($ratingA === null && $ratingB !== null) {
                    return 1;
                }

                return ($b['created_at'] ?? null) <=> ($a['created_at'] ?? null);
            })->values(),
        };
    }

    /**
     * Bayesian recommended score per guide: blends average score with review volume
     * so a single perfect review cannot outrank a strong, well-reviewed guide.
     *
     * score = (C × m + avg × n) / (C + n)
     *
     * @param  array<int, int>  $guideIds
     * @return array<int, float>
     */
    private function recommendedScoresByGuideId(array $guideIds): array
    {
        if ($guideIds === []) {
            return [];
        }

        $rows = Review::query()
            ->whereIn('guide_id', $guideIds)
            ->selectRaw('guide_id, AVG(grandtotal_score) as avg_score, COUNT(*) as review_count')
            ->groupBy('guide_id')
            ->get();

        $scores = [];
        foreach ($rows as $row) {
            $avg = (float) $row->avg_score;
            $count = (int) $row->review_count;
            if ($count < 1) {
                continue;
            }
            $scores[(int) $row->guide_id] = $this->bayesianRecommendedScore($avg, $count);
        }

        return $scores;
    }

    private function bayesianRecommendedScore(float $averageScore, int $reviewCount): float
    {
        $priorWeight = self::RECOMMENDED_PRIOR_WEIGHT;
        $priorMean = self::RECOMMENDED_PRIOR_MEAN;

        return (($priorWeight * $priorMean) + ($averageScore * $reviewCount))
            / ($priorWeight + $reviewCount);
    }

    private function distanceKm(float $fromLat, float $fromLng, mixed $toLat, mixed $toLng): ?float
    {
        if ($toLat === null || $toLng === null || $toLat === '' || $toLng === '') {
            return null;
        }
        if (! is_numeric($toLat) || ! is_numeric($toLng)) {
            return null;
        }

        $lat1 = deg2rad($fromLat);
        $lat2 = deg2rad((float) $toLat);
        $deltaLat = deg2rad((float) $toLat - $fromLat);
        $deltaLng = deg2rad((float) $toLng - $fromLng);
        $a = sin($deltaLat / 2) ** 2
            + cos($lat1) * cos($lat2) * sin($deltaLng / 2) ** 2;

        return 6371 * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function queryTours(OfferListingFilter $filter, Request $request): Builder
    {
        $query = Guiding::query()->publiclyVisible();

        if ($filter->hasSpeciesFilter()) {
            $this->applyOptimizedSpeciesFilter($query, $filter, 'tours', 'guidings.id', 'target_fish');
        }

        if ($filter->numGuests !== null) {
            $query->where('max_guests', '>=', $filter->numGuests);
        }

        $this->applyTourFacets($query, $filter);

        if ($filter->placeLat !== null && $filter->placeLng !== null) {
            $geo = Guiding::locationFilter(
                $filter->city,
                $filter->country ?? $request->input('country'),
                $filter->region,
                null,
                $filter->placeLat,
                $filter->placeLng,
                function_exists('guidingLocationGeoParams') ? guidingLocationGeoParams($request) : $filter->geoSearchParams(),
            );
            $ids = $geo['ids'] ?? [];
            if ($ids === []) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereIn('guidings.id', $ids);
        }

        if ($filter->country !== null && $filter->country !== '') {
            $variants = CountrySlug::storageVariants($filter->country, $filter->countryShort);
            $query->where(function (Builder $q) use ($variants) {
                foreach ($variants as $variant) {
                    $lower = mb_strtolower($variant, 'UTF-8');
                    $q->orWhereRaw('LOWER(country) = ?', [$lower])
                        ->orWhereRaw('LOWER(country_iso) = ?', [$lower]);
                }
            });
        }

        return $query;
    }

    private function queryTrips(OfferListingFilter $filter, $vacationFilter): Builder
    {
        $query = $this->trips->queryForCountry($vacationFilter);

        if ($filter->hasSpeciesFilter()) {
            $this->applyOptimizedSpeciesFilter($query, $filter, 'trips', 'trips.id', 'target_species');
        }

        if ($filter->numGuests !== null) {
            $guests = $filter->numGuests;
            $query->where(function (Builder $q) use ($guests) {
                $q->whereNull('group_size_max')
                    ->orWhere('group_size_max', '>=', $guests);
            });
        }

        $this->applyTripFacets($query, $filter);

        return $this->applyListingGeo($query, $filter, 'latitude', 'longitude');
    }

    private function queryCamps(OfferListingFilter $filter, $vacationFilter): Builder
    {
        $query = $this->camps->queryForCountry($vacationFilter);

        if ($filter->hasSpeciesFilter()) {
            $this->applyOptimizedSpeciesFilter($query, $filter, 'camps', 'camps.id', 'target_fish');
        }

        if ($filter->numGuests !== null) {
            $guests = $filter->numGuests;
            $query->whereHas('accommodations', function (Builder $q) use ($guests) {
                $q->where('accommodations.status', 'active')
                    ->where(function (Builder $capacity) use ($guests) {
                        $capacity->whereNull('accommodations.max_occupancy')
                            ->orWhere('accommodations.max_occupancy', '>=', $guests);
                    });
            });
        }

        $this->applyCampFacets($query, $filter);

        return $this->applyListingGeo($query, $filter, 'latitude', 'longitude');
    }

    private function applyTourFacets(Builder $query, OfferListingFilter $filter): void
    {
        if (! $filter->showsTourFacets()) {
            return;
        }

        $params = array_filter([
            'methods' => $filter->methodIds !== []
                ? array_map(static fn (int $id): string => (string) $id, $filter->methodIds)
                : null,
            'water' => $filter->waterIds !== []
                ? array_map(static fn (int $id): string => (string) $id, $filter->waterIds)
                : null,
            'duration_types' => $filter->durationTypes !== [] ? $filter->durationTypes : null,
        ]);

        if ($params === []) {
            return;
        }

        $ids = $this->guidingFilters->getFilteredGuidingIds(Request::create('/', 'GET', $params));
        $query->whereIn('guidings.id', $ids === [] ? [0] : $ids);
    }

    private function applyTripFacets(Builder $query, OfferListingFilter $filter): void
    {
        if (! $filter->showsTripFacets() || $filter->tripDuration === null) {
            return;
        }

        match ($filter->tripDuration) {
            '1-3' => $query->whereBetween('duration_days', [1, 3]),
            '4-7' => $query->whereBetween('duration_days', [4, 7]),
            '8+' => $query->where('duration_days', '>=', 8),
            default => null,
        };
    }

    private function applyCampFacets(Builder $query, OfferListingFilter $filter): void
    {
        if (! $filter->showsCampFacets()) {
            return;
        }

        if ($filter->accommodationTypeId !== null) {
            $typeId = $filter->accommodationTypeId;
            $query->whereHas('accommodations', function (Builder $q) use ($typeId) {
                $q->where('accommodations.status', 'active')
                    ->where('accommodations.accommodation_type', $typeId);
            });
        }

        if ($filter->hasGuiding === true) {
            $query->whereHas('guidings');
        } elseif ($filter->hasGuiding === false) {
            $query->whereDoesntHave('guidings');
        }

        if ($filter->hasRentalBoat === true) {
            $query->whereHas('rentalBoats');
        } elseif ($filter->hasRentalBoat === false) {
            $query->whereDoesntHave('rentalBoats');
        }
    }

    /**
     * @return Collection<int, array{id: int, name: string}>
     */
    private function methodOptions(): Collection
    {
        return Method::query()
            ->orderByRaw("CASE WHEN ? = 'en' THEN name_en ELSE name END", [app()->getLocale()])
            ->get(['id', 'name', 'name_en'])
            ->map(fn (Method $method) => [
                'id' => (int) $method->id,
                'name' => (string) $method->name,
            ])
            ->values();
    }

    /**
     * @return Collection<int, array{id: int, name: string}>
     */
    private function waterOptions(): Collection
    {
        return Water::query()
            ->orderByRaw("CASE WHEN ? = 'en' THEN name_en ELSE name END", [app()->getLocale()])
            ->get(['id', 'name', 'name_en'])
            ->map(fn (Water $water) => [
                'id' => (int) $water->id,
                'name' => (string) $water->name,
            ])
            ->values();
    }

    /**
     * @return Collection<int, array{value: string, label: string}>
     */
    private function tourDurationOptions(): Collection
    {
        return collect(OfferListingFilter::TOUR_DURATION_TYPES)->map(fn (string $value) => [
            'value' => $value,
            'label' => __('guidings.'.$value),
        ]);
    }

    /**
     * @return Collection<int, array{value: string, label: string}>
     */
    private function tripDurationOptions(): Collection
    {
        return collect(OfferListingFilter::TRIP_DURATION_BUCKETS)->map(fn (string $value) => [
            'value' => $value,
            'label' => __('offers.filter_duration_'.$value),
        ]);
    }

    /**
     * @return Collection<int, array{id: int, name: string}>
     */
    private function accommodationTypeOptions(): Collection
    {
        return AccommodationType::query()
            ->active()
            ->ordered()
            ->get(['id', 'name', 'name_en'])
            ->map(fn (AccommodationType $type) => [
                'id' => (int) $type->id,
                'name' => (string) $type->name,
            ])
            ->values();
    }

    private function vacationFilterWithoutSpecies(VacationListingFilter $filter): VacationListingFilter
    {
        return new VacationListingFilter(
            pillar: $filter->pillar,
            speciesIds: [],
            speciesNames: [],
            country: $filter->country,
            sortBy: $filter->sortBy,
            countryShort: $filter->countryShort,
        );
    }

    /**
     * @param  'tours'|'trips'|'camps'  $pillar
     */
    private function applyOptimizedSpeciesFilter(
        Builder $query,
        OfferListingFilter $filter,
        string $pillar,
        string $idColumn,
        string $fallbackColumn,
    ): void {
        [$speciesIds, $customNames] = $this->resolvedSpeciesSelection($filter);
        $listingIdsFromCatalog = $this->offerFilters->listingIdsForSpecies($pillar, $speciesIds);
        $listingIdsFromCustom = $this->offerFilters->listingIdsForCustomSpecies($pillar, $customNames);

        if ($listingIdsFromCatalog !== null || $listingIdsFromCustom !== null) {
            $listingIds = array_values(array_unique(array_merge(
                $listingIdsFromCatalog ?? [],
                $listingIdsFromCustom ?? [],
            )));
            $query->whereIn($idColumn, $listingIds !== [] ? $listingIds : [0]);

            return;
        }

        $this->filterApplicator->applySpeciesColumnFilter(
            $query,
            $fallbackColumn,
            $speciesIds,
            $customNames !== [] ? $customNames : $filter->speciesNames,
        );
    }

    /**
     * Split selection into catalog target ids and unmatched custom names.
     *
     * @return array{0: list<int>, 1: list<string>}
     */
    private function resolvedSpeciesSelection(OfferListingFilter $filter): array
    {
        $ids = $filter->speciesIds;
        $customNames = [];

        if ($filter->speciesNames === []) {
            return [$ids, $customNames];
        }

        $nameKeys = [];
        foreach ($filter->speciesNames as $name) {
            $trimmed = trim((string) $name);
            if ($trimmed === '') {
                continue;
            }
            $nameKeys[mb_strtolower($trimmed, 'UTF-8')] = $trimmed;
        }

        if ($nameKeys === []) {
            return [$ids, $customNames];
        }

        $resolvedByKey = [];
        Target::query()
            ->where(function (Builder $q) use ($nameKeys) {
                foreach (array_keys($nameKeys) as $lower) {
                    $q->orWhereRaw('LOWER(name) = ?', [$lower])
                        ->orWhereRaw('LOWER(name_en) = ?', [$lower]);
                }
            })
            ->get(['id', 'name', 'name_en'])
            ->each(function (Target $target) use (&$resolvedByKey) {
                $attrs = $target->getAttributes();
                foreach (['name', 'name_en'] as $field) {
                    $value = mb_strtolower(trim((string) ($attrs[$field] ?? '')), 'UTF-8');
                    if ($value !== '') {
                        $resolvedByKey[$value] = (int) $target->id;
                    }
                }
            });

        foreach ($nameKeys as $lower => $label) {
            if (isset($resolvedByKey[$lower])) {
                $ids[] = $resolvedByKey[$lower];
            } else {
                $customNames[] = $label;
            }
        }

        return [
            array_values(array_unique($ids)),
            array_values(array_unique($customNames)),
        ];
    }

    /**
     * Align trip/camp geo with tours: country searches use country filter (or bbox),
     * not a tight city radius around the country centroid.
     */
    private function applyListingGeo(Builder $query, OfferListingFilter $filter, string $latCol, string $lngCol): Builder
    {
        if ($filter->placeLat === null || $filter->placeLng === null) {
            return $query;
        }

        $geoParams = $filter->geoSearchParams();
        $placeTypes = $this->geoSearch->normalizePlaceTypes($geoParams['place_types'] ?? null);
        $scope = $this->geoSearch->detectScope($placeTypes, $geoParams);

        // Country-level place (e.g. Spain): country column filter already applied via repository.
        $isCountryPlace = $scope === GeospatialSearchService::SCOPE_COUNTRY
            || (filled($filter->country) && blank($filter->city) && blank($filter->region));

        if ($isCountryPlace && filled($filter->country)) {
            return $query;
        }

        $bounds = $this->geoSearch->normalizeBounds($geoParams);
        if ($bounds !== null) {
            return $query
                ->whereNotNull($latCol)
                ->whereNotNull($lngCol)
                ->whereBetween($latCol, [$bounds['sw_lat'], $bounds['ne_lat']])
                ->whereBetween($lngCol, [$bounds['sw_lng'], $bounds['ne_lng']]);
        }

        $scopeConfig = config("location_search.scopes.{$scope}", config('location_search.scopes.city'));
        $radiusKm = (int) ($scopeConfig['radius_fallback_km'] ?? 20);
        $meters = $radiusKm * 1000;

        return $query
            ->whereNotNull($latCol)
            ->whereNotNull($lngCol)
            ->whereRaw("ST_Distance_Sphere(point({$lngCol}, {$latCol}), point(?, ?)) <= ?", [
                $filter->placeLng,
                $filter->placeLat,
                $meters,
            ]);
    }

    /**
     * Nearby suggestions across tours + trips + camps (merged, nearest first).
     *
     * @param  array<int, int>  $excludeTourIds
     * @param  array<int, int>  $excludeTripIds
     * @param  array<int, int>  $excludeCampIds
     * @return Collection<int, array{type: string, model: mixed, distance: float|null}>
     */
    private function buildNearbySuggestedItems(
        OfferListingFilter $filter,
        float $latitude,
        float $longitude,
        array $excludeTourIds = [],
        array $excludeTripIds = [],
        array $excludeCampIds = [],
    ): Collection {
        $perType = 10;
        $mergedLimit = 12;
        $items = collect();

        if ($filter->showsTours()) {
            $items = $items->concat(
                $this->nearbySuggestedTours($latitude, $longitude, $excludeTourIds, $perType)
                    ->map(fn (Guiding $guiding) => [
                        'type' => 'tour',
                        'model' => $guiding,
                        'distance' => isset($guiding->distance) ? (float) $guiding->distance : null,
                    ])
            );
        }

        if ($filter->showsTrips()) {
            $items = $items->concat(
                $this->nearbySuggestedTrips($latitude, $longitude, $excludeTripIds, $perType)
                    ->map(fn ($trip) => [
                        'type' => 'trip',
                        'model' => $trip,
                        'distance' => isset($trip->distance) ? (float) $trip->distance : null,
                    ])
            );
        }

        if ($filter->showsCamps()) {
            $items = $items->concat(
                $this->nearbySuggestedCamps($latitude, $longitude, $excludeCampIds, $perType)
                    ->map(fn ($camp) => [
                        'type' => 'camp',
                        'model' => $camp,
                        'distance' => isset($camp->distance) ? (float) $camp->distance : null,
                    ])
            );
        }

        return $items
            ->sortBy(fn (array $item) => $item['distance'] ?? PHP_FLOAT_MAX)
            ->take($mergedLimit)
            ->values();
    }

    /**
     * @return Collection<int, array{type: string, model: mixed, distance: float|null}>
     */
    private function buildRandomSuggestedItems(OfferListingFilter $filter): Collection
    {
        $items = collect();

        if ($filter->showsTours()) {
            $items = $items->concat(
                $this->randomSuggestedTours()->map(fn (Guiding $guiding) => [
                    'type' => 'tour',
                    'model' => $guiding,
                    'distance' => null,
                ])
            );
        }

        if ($filter->showsTrips()) {
            $items = $items->concat(
                $this->trips->listForHub(5)->map(fn ($trip) => [
                    'type' => 'trip',
                    'model' => $trip,
                    'distance' => null,
                ])
            );
        }

        if ($filter->showsCamps()) {
            $items = $items->concat(
                $this->camps->listForHub(5)->map(fn ($camp) => [
                    'type' => 'camp',
                    'model' => $camp,
                    'distance' => null,
                ])
            );
        }

        return $items->shuffle(1234)->take(12)->values();
    }

    /**
     * @param  array<int, int>  $excludeIds
     * @return Collection<int, Guiding>
     */
    private function nearbySuggestedTours(
        float $latitude,
        float $longitude,
        array $excludeIds = [],
        int $limit = 10,
    ): Collection {
        return Guiding::query()
            ->select(['guidings.*'])
            ->selectRaw('ST_Distance_Sphere(point(lng, lat), point(?, ?)) as distance', [
                $longitude,
                $latitude,
            ])
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->whereRaw('ST_Distance_Sphere(point(lng, lat), point(?, ?)) <= ?', [
                $longitude,
                $latitude,
                200 * 1000,
            ])
            ->when($excludeIds !== [], fn (Builder $q) => $q->whereNotIn('guidings.id', $excludeIds))
            ->publiclyVisible()
            ->with(['user.reviews', 'boatType'])
            ->orderByRaw('CASE WHEN distance IS NULL THEN 1 ELSE 0 END')
            ->orderBy('distance')
            ->limit($limit)
            ->get();
    }

    /**
     * @param  array<int, int>  $excludeIds
     * @return Collection<int, Trip>
     */
    private function nearbySuggestedTrips(
        float $latitude,
        float $longitude,
        array $excludeIds = [],
        int $limit = 10,
    ): Collection {
        return Trip::query()
            ->select(['trips.*'])
            ->selectRaw('ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) as distance', [
                $longitude,
                $latitude,
            ])
            ->where('status', 'active')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereRaw('ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) <= ?', [
                $longitude,
                $latitude,
                200 * 1000,
            ])
            ->when($excludeIds !== [], fn (Builder $q) => $q->whereNotIn('id', $excludeIds))
            ->orderByRaw('CASE WHEN distance IS NULL THEN 1 ELSE 0 END')
            ->orderBy('distance')
            ->limit($limit)
            ->get();
    }

    /**
     * @param  array<int, int>  $excludeIds
     * @return Collection<int, Camp>
     */
    private function nearbySuggestedCamps(
        float $latitude,
        float $longitude,
        array $excludeIds = [],
        int $limit = 10,
    ): Collection {
        return Camp::query()
            ->select(['camps.*'])
            ->selectRaw('ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) as distance', [
                $longitude,
                $latitude,
            ])
            ->where('status', 'active')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereRaw('ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) <= ?', [
                $longitude,
                $latitude,
                200 * 1000,
            ])
            ->when($excludeIds !== [], fn (Builder $q) => $q->whereNotIn('id', $excludeIds))
            ->with(['rentalBoats', 'facilities', 'guidings.guidingMethods', 'accommodations', 'specialOffers'])
            ->orderByRaw('CASE WHEN distance IS NULL THEN 1 ELSE 0 END')
            ->orderBy('distance')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Guiding>
     */
    private function randomSuggestedTours(): Collection
    {
        return Guiding::query()
            ->publiclyVisible()
            ->with(['user.reviews', 'boatType'])
            ->inRandomOrder('1234')
            ->limit(5)
            ->get();
    }

    /**
     * @param  Collection<int, array{type: string, model: mixed}>  $suggestedItems
     */
    private function buildMapMarkers(
        OfferListingFilter $filter,
        Builder $tourQuery,
        Builder $tripQuery,
        Builder $campQuery,
        Collection $suggestedItems,
    ): array {
        $markers = [];

        if ($filter->showsTours()) {
            $guidings = (clone $tourQuery)
                ->whereNotNull('lat')
                ->whereNotNull('lng')
                ->get(['id', 'title', 'slug', 'location', 'lat', 'lng', 'thumbnail_path', 'price', 'prices', 'price_type', 'max_guests', 'duration', 'duration_type', 'is_boat']);

            foreach ($guidings as $guiding) {
                $guiding->title = translate($guiding->title);
                $guiding->location = translate($guiding->location);
            }

            $markers = array_merge($markers, $this->normalizeTourMarkers(
                MapMarkerCollection::fromGuidings($guidings),
                'tour',
            ));
        }

        if ($filter->showsTrips()) {
            $trips = (clone $tripQuery)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get(['id', 'title', 'slug', 'location', 'latitude', 'longitude', 'thumbnail_path', 'price_per_person', 'currency']);

            $markers = array_merge($markers, MapMarkerCollection::fromTrips($trips));
        }

        if ($filter->showsCamps()) {
            $camps = (clone $campQuery)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->with(['accommodations', 'specialOffers'])
                ->get(['id', 'title', 'slug', 'location', 'latitude', 'longitude', 'thumbnail_path']);

            foreach ($camps as $camp) {
                $camp->title = translate($camp->title);
            }

            $markers = array_merge($markers, MapMarkerCollection::fromCamps($camps));
        }

        $suggestedTours = $suggestedItems->where('type', 'tour')->pluck('model')->values();
        $suggestedTrips = $suggestedItems->where('type', 'trip')->pluck('model')->values();
        $suggestedCamps = $suggestedItems->where('type', 'camp')->pluck('model')->values();

        if ($suggestedTours->isNotEmpty()) {
            $suggestedForMap = $suggestedTours->map(function (Guiding $guiding) {
                $clone = clone $guiding;
                $clone->title = translate($guiding->title);
                $clone->location = translate($guiding->location);

                return $clone;
            });
            $grayIds = $suggestedForMap->pluck('id')->map(fn ($id) => (int) $id)->all();
            $suggestedMarkers = MapMarkerCollection::fromGuidings($suggestedForMap, $grayIds);
            foreach ($suggestedMarkers as &$marker) {
                $marker['pillar'] = 'tour';
                $marker['variant'] = 'gray';
                $marker['badge'] = __('offers.badge_tour');
                $marker['cta'] = __('offers.see_details');
                $marker = array_merge($marker, MapMarkerCollection::moduleFields(
                    MapMarkerCollection::MODULE_TOUR,
                    $marker['id'] ?? null,
                ));
            }
            unset($marker);
            $markers = array_merge($markers, $suggestedMarkers);
        }

        if ($suggestedTrips->isNotEmpty()) {
            $tripMarkers = MapMarkerCollection::fromTrips($suggestedTrips);
            foreach ($tripMarkers as &$marker) {
                $marker['variant'] = 'gray';
            }
            unset($marker);
            $markers = array_merge($markers, $tripMarkers);
        }

        if ($suggestedCamps->isNotEmpty()) {
            foreach ($suggestedCamps as $camp) {
                $camp->title = translate($camp->title);
            }
            $campMarkers = MapMarkerCollection::fromCamps($suggestedCamps);
            foreach ($campMarkers as &$marker) {
                $marker['variant'] = 'gray';
            }
            unset($marker);
            $markers = array_merge($markers, $campMarkers);
        }

        return $markers;
    }

    /**
     * @param  array<int, array<string, mixed>>  $markers
     * @return array<int, array<string, mixed>>
     */
    private function normalizeTourMarkers(array $markers, string $variant): array
    {
        foreach ($markers as &$marker) {
            $marker['pillar'] = 'tour';
            $marker['variant'] = $variant;
            $marker['badge'] = __('offers.badge_tour');
            $marker['cta'] = __('offers.see_details');
            $marker = array_merge($marker, MapMarkerCollection::moduleFields(
                MapMarkerCollection::MODULE_TOUR,
                $marker['id'] ?? null,
            ));
            if (! empty($marker['price'])) {
                $marker['priceLabel'] = __('vacations.price_from_per_person', [
                    'price' => '€'.number_format((float) $marker['price'], 0),
                ]);
            }
        }
        unset($marker);

        return $markers;
    }

    private function resolveFaq(): Collection
    {
        $dbFaqs = collect(get_faqs_by_page('offers'));
        if ($dbFaqs->isNotEmpty()) {
            return $dbFaqs;
        }

        return collect(config('offers.faq', []))
            ->map(fn (array $item) => (object) [
                'question' => __($item['question'] ?? ''),
                'answer' => __($item['answer'] ?? ''),
            ])
            ->filter(fn ($item) => filled($item->question))
            ->values();
    }
}
