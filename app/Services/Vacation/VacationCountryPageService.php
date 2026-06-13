<?php

namespace App\Services\Vacation;

use App\Domain\Vacation\Pillar;
use App\Domain\Vacation\VacationListingFilter;
use App\Domain\Vacation\ViewModels\PillarSectionViewModel;
use App\Domain\Vacation\ViewModels\VacationCountryViewModel;
use App\Repositories\Vacation\CampListingRepository;
use App\Repositories\Vacation\TripListingRepository;
use App\Repositories\Vacation\VacationDestinationRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class VacationCountryPageService
{
    public function __construct(
        private VacationDestinationRepository $destinations,
        private CampListingRepository $camps,
        private TripListingRepository $trips,
        private VacationFilterApplicator $filterApplicator,
    ) {}

    public function build(Request $request, string $countrySlug): VacationCountryViewModel
    {
        $destination = $this->destinations->mergeCountryContent($countrySlug);
        if ($destination === null) {
            abort(404);
        }

        $filter = VacationListingFilter::fromRequest($request->all(), $countrySlug);
        $perPage = (int) config('vacations.country_page_per_page', 6);

        $showTripsSection = $filter->pillar !== 'camps';
        $showCampsSection = $filter->pillar !== 'trips';

        $tripsTotal = $showTripsSection
            ? $this->trips->queryForCountry($filter)->count()
            : 0;

        $campsTotal = $showCampsSection
            ? $this->camps->queryForCountry($filter)->count()
            : 0;

        $trips = $showTripsSection
            ? $this->trips->paginateForCountry($filter, $perPage)
            : new LengthAwarePaginator([], 0, $perPage);

        $camps = $showCampsSection
            ? $this->camps->paginateForCountry($filter, $perPage)
            : new LengthAwarePaginator([], 0, $perPage);

        $tripsSection = new PillarSectionViewModel(
            pillar: Pillar::Trip,
            countryName: translate($destination->name),
            count: $tripsTotal,
            visible: $showTripsSection,
        );

        $campsSection = new PillarSectionViewModel(
            pillar: Pillar::Camp,
            countryName: translate($destination->name),
            count: $campsTotal,
            visible: $showCampsSection,
        );

        return new VacationCountryViewModel(
            destination: $destination,
            filter: $filter,
            tripsSection: $tripsSection,
            campsSection: $campsSection,
            trips: $trips,
            camps: $camps,
            tripsTotal: $tripsTotal,
            campsTotal: $campsTotal,
            faq: $destination->faq,
            fishChart: $destination->fish_chart,
            speciesOptions: collect($this->filterApplicator->speciesOptionsForCountry($countrySlug)),
            mapMarkers: $this->buildMapMarkers($countrySlug, $filter),
        );
    }

    private function buildMapMarkers(string $countrySlug, VacationListingFilter $filter): array
    {
        $markers = [];

        if ($filter->showsTrips() && $filter->pillar !== 'camps') {
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

        if ($filter->showsCamps() && $filter->pillar !== 'trips') {
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
}
