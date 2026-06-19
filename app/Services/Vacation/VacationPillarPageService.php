<?php

namespace App\Services\Vacation;

use App\Domain\Vacation\VacationListingFilter;
use App\Domain\Vacation\VacationPillar;
use App\Domain\Vacation\ViewModels\VacationPillarIndexViewModel;
use App\Presenters\Vacation\CampCardPresenter;
use App\Presenters\Vacation\TripCardPresenter;
use App\Repositories\Vacation\CampListingRepository;
use App\Repositories\Vacation\TripListingRepository;
use App\Repositories\Vacation\VacationDestinationRepository;
use Illuminate\Http\Request;

class VacationPillarPageService
{
    public function __construct(
        private VacationDestinationRepository $destinations,
        private TripListingRepository $trips,
        private CampListingRepository $camps,
        private TripCardPresenter $tripPresenter,
        private CampCardPresenter $campPresenter,
        private VacationFilterApplicator $filterApplicator,
    ) {}

    public function buildIndex(Request $request, VacationPillar $pillar): VacationPillarIndexViewModel
    {
        $filter = VacationListingFilter::fromRequest($request->all(), $request->get('country'));

        return $this->build(
            pillar: $pillar,
            request: $request,
            countrySlug: $filter->country,
            destination: null,
        );
    }

    public function buildCountry(Request $request, VacationPillar $pillar, string $countrySlug): VacationPillarIndexViewModel
    {
        $countrySlug = strtolower($countrySlug);
        $resolved = $this->destinations->resolveCountryPage($countrySlug, $pillar->value);

        if ($resolved === null) {
            abort(404);
        }

        return $this->build(
            pillar: $pillar,
            request: $request,
            countrySlug: $resolved['slug'],
            destination: $resolved['destination'],
        );
    }

    private function build(
        VacationPillar $pillar,
        Request $request,
        ?string $countrySlug,
        ?\App\Models\Destination $destination,
    ): VacationPillarIndexViewModel {
        $filter = VacationListingFilter::fromRequest($request->all(), $countrySlug);
        $perPage = (int) config('vacations.pillar_index_per_page', 9);

        $filterAll = new VacationListingFilter(
            pillar: $pillar->value,
            species: $filter->species,
            duration: $filter->duration,
            country: $countrySlug,
            sortBy: $filter->sortBy,
        );

        [$listings, $cards] = match ($pillar) {
            VacationPillar::Trips => $this->tripListings($filterAll, $perPage),
            VacationPillar::Camps => $this->campListings($filterAll, $perPage, $destination?->id),
        };

        return new VacationPillarIndexViewModel(
            pillar: $pillar,
            filter: $filterAll,
            listings: $listings,
            cards: $cards,
            countries: $this->destinations->countriesForHubGrid(),
            speciesOptions: collect($this->filterApplicator->speciesOptionsForCountry($countrySlug)),
            destination: $destination,
            mapMarkers: $this->buildMapMarkers($filterAll, $pillar),
        );
    }

    /**
     * @return array<int, array{lat: float, lng: float, title: string, url: string, pillar: string}>
     */
    private function buildMapMarkers(VacationListingFilter $filter, VacationPillar $pillar): array
    {
        $markers = [];

        if ($pillar === VacationPillar::Trips) {
            foreach ($this->trips->queryForCountry($filter)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get(['title', 'slug', 'latitude', 'longitude']) as $trip) {
                $markers[] = [
                    'lat' => (float) $trip->latitude,
                    'lng' => (float) $trip->longitude,
                    'title' => $trip->title,
                    'url' => route('vacations.trips.show', $trip->slug),
                    'pillar' => 'trip',
                ];
            }
        }

        if ($pillar === VacationPillar::Camps) {
            foreach ($this->camps->queryForCountry($filter)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get(['title', 'slug', 'latitude', 'longitude']) as $camp) {
                $markers[] = [
                    'lat' => (float) $camp->latitude,
                    'lng' => (float) $camp->longitude,
                    'title' => translate($camp->title),
                    'url' => route('vacations.camps.show', $camp->slug),
                    'pillar' => 'camp',
                ];
            }
        }

        return $markers;
    }

    /**
     * @return array{0: \Illuminate\Contracts\Pagination\LengthAwarePaginator, 1: \Illuminate\Support\Collection}
     */
    private function tripListings(VacationListingFilter $filter, int $perPage): array
    {
        $listings = $this->trips->paginateForCountry($filter, $perPage);
        $cards = collect($listings->items())->map(fn ($trip) => $this->tripPresenter->presentListRow($trip));

        return [$listings, $cards];
    }

    /**
     * @return array{0: \Illuminate\Contracts\Pagination\LengthAwarePaginator, 1: \Illuminate\Support\Collection}
     */
    private function campListings(VacationListingFilter $filter, int $perPage, ?int $destinationId = null): array
    {
        $listings = $this->camps->paginateForCountry($filter, $perPage);
        $cards = collect($listings->items())->map(fn ($camp) => $this->campPresenter->presentListRow($camp, $destinationId));

        return [$listings, $cards];
    }
}
