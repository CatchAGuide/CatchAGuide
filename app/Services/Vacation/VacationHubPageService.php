<?php

namespace App\Services\Vacation;

use App\Domain\Vacation\Pillar;
use App\Domain\Vacation\ViewModels\PillarTileViewModel;
use App\Domain\Vacation\ViewModels\VacationHubViewModel;
use App\Models\Trip;
use App\Presenters\Vacation\CampCardPresenter;
use App\Presenters\Vacation\TripCardPresenter;
use App\Repositories\Vacation\CampListingRepository;
use App\Repositories\Vacation\TripListingRepository;
use App\Repositories\Vacation\VacationDestinationRepository;
use App\Services\Reviews\TestimonialSelector;

class VacationHubPageService
{
    public function __construct(
        private CampListingRepository $camps,
        private TripListingRepository $trips,
        private VacationDestinationRepository $destinations,
        private PopularListingSelector $popular,
        private CampCardPresenter $campPresenter,
        private TripCardPresenter $tripPresenter,
        private VacationTargetFishSelector $targetFish,
        private TestimonialSelector $testimonialSelector,
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

        $newListingsLimit = (int) config('vacations.new_listings_rail_limit', 8);
        $newListings = $this->trips->listNewest($newListingsLimit)
            ->concat($this->camps->listNewest($newListingsLimit))
            ->sortByDesc('created_at')
            ->take($newListingsLimit)
            ->values()
            ->map(fn ($listing) => $listing instanceof Trip
                ? $this->tripPresenter->present($listing)
                : $this->campPresenter->present($listing));

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
            newListings: $newListings,
            showNewListingsRail: $newListings->isNotEmpty()
                && ($totalTrips + $totalCamps) <= (int) config('vacations.new_listings_rail_max_catalog', 60),
            countryGrid: $this->destinations->countriesForHubGrid(),
            faqItems: $faqItems->all(),
            totalTrips: $totalTrips,
            totalCamps: $totalCamps,
            targetFishTiles: $this->targetFish->forHub((int) config('vacations.hub_target_fish_limit', 8)),
            testimonials: $this->testimonialSelector->latest((int) config('vacations.hub_testimonials_limit', 6)),
        );
    }
}
