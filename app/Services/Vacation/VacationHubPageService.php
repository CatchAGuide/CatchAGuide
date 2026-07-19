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

        $newCampsLimit = (int) config('vacations.new_camps_rail_limit', 6);
        $newCamps = $this->camps->listNewest($newCampsLimit)
            ->map(fn ($c) => $this->campPresenter->present($c));

        $faqItems = get_faqs_by_page('vacations')
            ->map(fn ($item) => [
                'question' => $item->question,
                'answer' => $item->answer,
            ]);

        if ($faqItems->isEmpty()) {
            $faqItems = collect(config('vacations.hub_faq', []))->map(fn ($item) => [
                'question' => __($item['question_key']),
                'answer' => __($item['answer_key']),
            ]);
        }

        return new VacationHubViewModel(
            campTile: $campTile,
            tripTile: $tripTile,
            popularListings: $this->popular->mixedForHub(),
            newTrips: $newTrips,
            showNewTripsRail: $totalTrips > 0 && $totalTrips <= (int) config('vacations.new_trips_rail_max_catalog', 30),
            newCamps: $newCamps,
            showNewCampsRail: $totalCamps > 0 && $totalCamps <= (int) config('vacations.new_camps_rail_max_catalog', 30),
            countryGrid: $this->destinations->countriesForHubGrid(),
            faqItems: $faqItems->all(),
            totalTrips: $totalTrips,
            totalCamps: $totalCamps,
        );
    }
}
