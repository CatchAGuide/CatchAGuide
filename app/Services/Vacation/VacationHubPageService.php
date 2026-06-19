<?php

namespace App\Services\Vacation;

use App\Domain\Vacation\Pillar;
use App\Domain\Vacation\ViewModels\PillarTileViewModel;
use App\Domain\Vacation\ViewModels\VacationHubViewModel;
use App\Presenters\Vacation\CampCardPresenter;
use App\Presenters\Vacation\TripCardPresenter;
use App\Repositories\Vacation\CampListingRepository;
use App\Repositories\Vacation\TripListingRepository;
use App\Repositories\Vacation\VacationDestinationRepository;

class VacationHubPageService
{
    public function __construct(
        private CampListingRepository $camps,
        private TripListingRepository $trips,
        private VacationDestinationRepository $destinations,
        private PopularListingSelector $popular,
        private CampCardPresenter $campPresenter,
        private TripCardPresenter $tripPresenter,
    ) {}

    public function build(): VacationHubViewModel
    {
        $totalCamps = $this->camps->countActive();
        $totalTrips = $this->trips->countActive();
        $campMin = $this->camps->minEntryPrice();
        $tripMin = $this->trips->minEntryPrice();

        $campTile = new PillarTileViewModel(
            pillar: Pillar::Camp,
            title: __('vacations.pillar_camps_title'),
            description: __('vacations.pillar_camps_desc'),
            listingCount: $totalCamps,
            countryCount: $this->camps->countCountriesWithListings(),
            minPrice: $campMin,
            currency: 'EUR',
            url: route('vacations.camps.index'),
        );

        $tripTile = new PillarTileViewModel(
            pillar: Pillar::Trip,
            title: __('vacations.pillar_trips_title'),
            description: __('vacations.pillar_trips_desc'),
            listingCount: $totalTrips,
            countryCount: $this->trips->countCountriesWithListings(),
            minPrice: $tripMin,
            currency: 'EUR',
            url: route('vacations.trips.index'),
        );

        $newTripsLimit = (int) config('vacations.new_trips_rail_limit', 6);
        $newTrips = $this->trips->listNewest($newTripsLimit)
            ->map(fn ($t) => $this->tripPresenter->present($t));

        $faqItems = collect(config('vacations.hub_faq', []))->map(fn ($item) => [
            'question' => __($item['question_key']),
            'answer' => __($item['answer_key']),
        ])->all();

        $inspiration = collect(config('vacations.inspiration_tiles', []))->map(function ($tile) {
            $query = http_build_query($tile['query'] ?? []);
            $url = url($tile['url'] . ($query ? '?' . $query : ''));

            return [
                'title' => __($tile['title_key']),
                'url' => $url,
            ];
        });

        return new VacationHubViewModel(
            campTile: $campTile,
            tripTile: $tripTile,
            popularListings: $this->popular->mixedForHub(),
            newTrips: $newTrips,
            showNewTripsRail: $totalTrips > 0 && $totalTrips <= (int) config('vacations.new_trips_rail_max_catalog', 30),
            countryGrid: $this->destinations->countriesForHubGrid(),
            inspirationTiles: $inspiration,
            faqItems: $faqItems,
            totalTrips: $totalTrips,
            totalCamps: $totalCamps,
        );
    }
}
