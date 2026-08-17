<?php

namespace App\Services\Vacation;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Domain\Vacation\Pillar;
use App\Domain\Vacation\VacationListingFilter;
use App\Domain\Vacation\ViewModels\PillarSectionViewModel;
use App\Domain\Vacation\ViewModels\VacationCountryViewModel;
use App\Models\Country;
use App\Repositories\Vacation\CampListingRepository;
use App\Repositories\Vacation\TripListingRepository;
use App\Repositories\Vacation\VacationDestinationRepository;
use App\Services\CategoryPage\CategoryPageContentService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class VacationCountryPageService
{
    public function __construct(
        private VacationDestinationRepository $destinations,
        private CampListingRepository $camps,
        private TripListingRepository $trips,
        private VacationFilterApplicator $filterApplicator,
        private CategoryPageContentService $categoryContent,
    ) {}

    public function build(Request $request, string $countrySlug): VacationCountryViewModel
    {
        $resolved = $this->destinations->resolveCountryPage($countrySlug);
        if ($resolved === null) {
            abort(404);
        }

        return $this->buildPage(
            $request,
            $resolved['slug'],
            $resolved['destination'],
        );
    }

    public function buildAllOffers(Request $request): VacationCountryViewModel
    {
        $country = new Country([
            'slug' => 'all-offers',
            'name' => __('vacations.all_offers_title'),
        ]);

        return $this->buildPage($request, null, $country);
    }

    private function buildPage(Request $request, ?string $countrySlug, Country $country): VacationCountryViewModel
    {
        $input = $request->all();
        if ($countrySlug === null) {
            unset($input['country']);
        }

        $filter = VacationListingFilter::fromRequest($input, $countrySlug);
        $perPage = (int) config('vacations.country_page_per_page', 6);
        $countryLabel = translate($country->name);

        $showTripsSection = $filter->pillar !== 'camps';
        $showCampsSection = $filter->pillar !== 'trips';

        $tripsTotal = $this->trips->queryForCountry($filter)->count();
        $campsTotal = $this->camps->queryForCountry($filter)->count();

        $trips = $showTripsSection
            ? $this->trips->paginateForCountry($filter, $perPage)
            : new LengthAwarePaginator([], 0, $perPage);

        $camps = $showCampsSection
            ? $this->camps->paginateForCountry($filter, $perPage)
            : new LengthAwarePaginator([], 0, $perPage);

        $tripsSection = new PillarSectionViewModel(
            pillar: Pillar::Trip,
            countryName: $countryLabel,
            count: $tripsTotal,
            visible: $showTripsSection,
        );

        $campsSection = new PillarSectionViewModel(
            pillar: Pillar::Camp,
            countryName: $countryLabel,
            count: $campsTotal,
            visible: $showCampsSection,
        );

        $listingsTotal = match ($filter->pillar) {
            'trips' => $tripsTotal,
            'camps' => $campsTotal,
            default => $tripsTotal + $campsTotal,
        };
        $listings = $this->buildListingsPaginator($filter, $trips, $camps, $perPage);

        $locale = app()->getLocale();
        if ($country->id) {
            $country = $this->categoryContent->applyScopedContentToModel(
                $country,
                CategoryPageEntityType::GEO_COUNTRY,
                CategoryPageScope::VACATIONS,
                $locale,
                null,
                false,
            );
        }

        $faq = $country->id
            ? $this->categoryContent->resolveFaqsForEntityDisplay(
                CategoryPageEntityType::GEO_COUNTRY,
                $country->id,
                CategoryPageScope::VACATIONS,
                $locale,
                null,
                false,
            )
            : collect();

        return new VacationCountryViewModel(
            destination: $country,
            filter: $filter,
            tripsSection: $tripsSection,
            campsSection: $campsSection,
            trips: $trips,
            camps: $camps,
            listings: $listings,
            tripsTotal: $tripsTotal,
            campsTotal: $campsTotal,
            listingsTotal: $listingsTotal,
            faq: $faq,
            fishChart: $country->fish_charts ?? collect(),
            speciesOptions: collect($this->filterApplicator->speciesOptionsForCountry($countrySlug)),
            accommodationTypeOptions: $filter->showsCampFacets()
                ? collect($this->filterApplicator->accommodationTypeOptions())
                : collect(),
            mapMarkers: $this->buildMapMarkers($filter),
        );
    }

    private function buildListingsPaginator(
        VacationListingFilter $filter,
        LengthAwarePaginator $trips,
        LengthAwarePaginator $camps,
        int $perPage,
    ): LengthAwarePaginator {
        if ($filter->pillar === 'trips') {
            return $this->wrapPaginatorItems($trips, 'trip');
        }

        if ($filter->pillar === 'camps') {
            return $this->wrapPaginatorItems($camps, 'camp');
        }

        $tripItems = $this->trips->queryForCountry($filter)
            ->get()
            ->map(fn ($trip) => [
                'type' => 'trip',
                'model' => $trip,
                'created_at' => $trip->created_at,
                'price' => $trip->price_per_person,
            ]);

        $campItems = $this->camps->queryForCountry($filter)
            ->with(['rentalBoats', 'facilities', 'guidings.guidingMethods', 'accommodations'])
            ->get()
            ->map(fn ($camp) => [
                'type' => 'camp',
                'model' => $camp,
                'created_at' => $camp->created_at,
                'price' => $camp->getLowestAccommodationOrOfferPrice(),
            ]);

        $merged = $this->sortListingItems($tripItems->concat($campItems), $filter);

        $page = LengthAwarePaginator::resolveCurrentPage();
        $total = $merged->count();

        return new LengthAwarePaginator(
            $merged->slice(($page - 1) * $perPage, $perPage)->values()->all(),
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->except('page')],
        );
    }

    private function wrapPaginatorItems(LengthAwarePaginator $paginator, string $type): LengthAwarePaginator
    {
        $items = collect($paginator->items())->map(fn ($model) => [
            'type' => $type,
            'model' => $model,
        ])->all();

        return new LengthAwarePaginator(
            $items,
            $paginator->total(),
            $paginator->perPage(),
            $paginator->currentPage(),
            ['path' => $paginator->path(), 'query' => request()->except('page')],
        );
    }

    private function sortListingItems(Collection $items, VacationListingFilter $filter): Collection
    {
        return match ($filter->sortBy) {
            'price-asc' => $items->sortBy(fn ($item) => $item['price'] ?? PHP_FLOAT_MAX)->values(),
            'price-desc' => $items->sortByDesc(fn ($item) => $item['price'] ?? 0)->values(),
            default => $items->sortByDesc('created_at')->values(),
        };
    }

    private function buildMapMarkers(VacationListingFilter $filter): array
    {
        $markers = [];

        if ($filter->showsTrips() && $filter->pillar !== 'camps') {
            $trips = $this->trips->queryForCountry($filter)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get(['id', 'title', 'slug', 'location', 'latitude', 'longitude', 'thumbnail_path', 'price_per_person', 'currency']);

            $markers = array_merge($markers, \App\Support\Maps\MapMarkerCollection::fromTrips($trips));
        }

        if ($filter->showsCamps() && $filter->pillar !== 'trips') {
            $camps = $this->camps->queryForCountry($filter)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->with(['accommodations', 'specialOffers'])
                ->get(['id', 'title', 'slug', 'location', 'latitude', 'longitude', 'thumbnail_path']);

            foreach ($camps as $camp) {
                $camp->title = translate($camp->title);
            }

            $markers = array_merge($markers, \App\Support\Maps\MapMarkerCollection::fromCamps($camps));
        }

        return $markers;
    }
}
