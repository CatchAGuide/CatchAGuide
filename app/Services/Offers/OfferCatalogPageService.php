<?php

namespace App\Services\Offers;

use App\Domain\Offers\OfferListingFilter;
use App\Domain\Offers\ViewModels\OfferCatalogViewModel;
use App\Domain\Vacation\CountrySlug;
use App\Models\Camp;
use App\Models\Guiding;
use App\Models\Trip;
use App\Presenters\Offers\TourCardPresenter;
use App\Presenters\Vacation\CampCardPresenter;
use App\Presenters\Vacation\TripCardPresenter;
use App\Repositories\Vacation\CampListingRepository;
use App\Repositories\Vacation\TripListingRepository;
use App\Repositories\Vacation\VacationDestinationRepository;
use App\Services\Location\GeospatialSearchService;
use App\Services\Vacation\VacationFilterApplicator;
use App\Support\Maps\MapMarkerCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class OfferCatalogPageService
{
    public function __construct(
        private TripListingRepository $trips,
        private CampListingRepository $camps,
        private VacationDestinationRepository $destinations,
        private VacationFilterApplicator $filterApplicator,
        private TourCardPresenter $tourPresenter,
        private TripCardPresenter $tripPresenter,
        private CampCardPresenter $campPresenter,
        private GeospatialSearchService $geoSearch,
    ) {}

    public function build(Request $request): OfferCatalogViewModel
    {
        $filter = OfferListingFilter::fromRequest($request->all());
        $vacationFilter = $filter->toVacationFilter();
        $perPage = (int) config('offers.per_page', 9);
        $hasGeo = $filter->placeLat !== null && $filter->placeLng !== null;

        $tourQuery = $this->queryTours($filter, $request);
        $tripQuery = $this->queryTrips($filter, $vacationFilter);
        $campQuery = $this->queryCamps($filter, $vacationFilter);

        $toursTotal = (clone $tourQuery)->count();
        $tripsTotal = (clone $tripQuery)->count();
        $campsTotal = (clone $campQuery)->count();

        $listings = $this->buildListingsPaginator($filter, $tourQuery, $tripQuery, $campQuery, $perPage);
        $cards = collect($listings->items())->map(function (array $item) {
            $card = match ($item['type']) {
                'tour' => $this->tourPresenter->presentListRow($item['model']),
                'trip' => $this->tripPresenter->presentListRow($item['model']),
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
            ->map(function (array $item) {
                $card = match ($item['type']) {
                    'tour' => $this->tourPresenter->presentListRow($item['model']),
                    'trip' => $this->tripPresenter->presentListRow($item['model']),
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

        return new OfferCatalogViewModel(
            filter: $filter,
            listings: $listings,
            cards: $cards,
            toursTotal: $toursTotal,
            tripsTotal: $tripsTotal,
            campsTotal: $campsTotal,
            listingsTotal: $listingsTotal,
            speciesOptions: collect($this->filterApplicator->speciesOptionsForCountry($filter->country)),
            countries: $this->destinations->countriesForHubGrid()->map(fn ($row) => [
                'slug' => $row['slug'],
                'name' => $row['name'],
            ])->values(),
            faq: $this->resolveFaq(),
            mapMarkers: $this->buildMapMarkers($filter, $tourQuery, $tripQuery, $campQuery, $suggestedItems),
            suggestedCards: $suggestedCards,
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
        $needsPrice = in_array($filter->sortBy, ['price-asc', 'price-desc'], true);
        $keys = collect();

        if ($filter->showsTours()) {
            $tours = (clone $tourQuery)->get([
                'guidings.id',
                'guidings.created_at',
                'guidings.price',
                'guidings.prices',
                'guidings.price_type',
                'guidings.max_guests',
            ]);
            $keys = $keys->concat($tours->map(fn (Guiding $guiding) => [
                'type' => 'tour',
                'id' => (int) $guiding->id,
                'created_at' => $guiding->created_at,
                'price' => $needsPrice ? ($guiding->getLowestPrice() ?: null) : null,
            ]));
        }

        if ($filter->showsTrips()) {
            $trips = (clone $tripQuery)->get(['id', 'created_at', 'price_per_person']);
            $keys = $keys->concat($trips->map(fn (Trip $trip) => [
                'type' => 'trip',
                'id' => (int) $trip->id,
                'created_at' => $trip->created_at,
                'price' => $needsPrice ? $trip->price_per_person : null,
            ]));
        }

        if ($filter->showsCamps()) {
            $campBuilder = clone $campQuery;
            if ($needsPrice) {
                $campBuilder->with(['accommodations', 'specialOffers']);
            }
            $camps = $campBuilder->get($needsPrice
                ? ['*']
                : ['id', 'created_at']);
            $keys = $keys->concat($camps->map(fn (Camp $camp) => [
                'type' => 'camp',
                'id' => (int) $camp->id,
                'created_at' => $camp->created_at,
                'price' => $needsPrice ? $camp->getLowestAccommodationOrOfferPrice() : null,
            ]));
        }

        $merged = $this->sortListingItems($keys, $filter);
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
     * @param  Collection<int, array{type: string, model: mixed, created_at: mixed, price: mixed}>  $items
     * @return Collection<int, array{type: string, model: mixed}>
     */
    private function sortListingItems(Collection $items, OfferListingFilter $filter): Collection
    {
        return match ($filter->sortBy) {
            'price-asc' => $items->sortBy(fn ($item) => $item['price'] ?? PHP_FLOAT_MAX)->values(),
            'price-desc' => $items->sortByDesc(fn ($item) => $item['price'] ?? 0)->values(),
            default => $items->sortByDesc('created_at')->values(),
        };
    }

    private function queryTours(OfferListingFilter $filter, Request $request): Builder
    {
        $query = Guiding::query()->publiclyVisible();

        if ($filter->species !== null) {
            $needle = strtolower($filter->species);
            $query->whereRaw('LOWER(CAST(target_fish AS CHAR)) LIKE ?', ['%'.$needle.'%']);
        }

        if ($filter->numGuests !== null) {
            $query->where('max_guests', '>=', $filter->numGuests);
        }

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

        if ($filter->numGuests !== null) {
            $guests = $filter->numGuests;
            $query->where(function (Builder $q) use ($guests) {
                $q->whereNull('group_size_max')
                    ->orWhere('group_size_max', '>=', $guests);
            });
        }

        return $this->applyListingGeo($query, $filter, 'latitude', 'longitude');
    }

    private function queryCamps(OfferListingFilter $filter, $vacationFilter): Builder
    {
        $query = $this->camps->queryForCountry($vacationFilter);

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

        return $this->applyListingGeo($query, $filter, 'latitude', 'longitude');
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
                $marker = array_merge($marker, MapMarkerCollection::moduleFields(MapMarkerCollection::MODULE_TOUR));
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
            $marker = array_merge($marker, MapMarkerCollection::moduleFields(MapMarkerCollection::MODULE_TOUR));
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
