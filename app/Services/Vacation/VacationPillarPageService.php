<?php

namespace App\Services\Vacation;

use App\Domain\Vacation\CountrySlug;
use App\Domain\Vacation\VacationListingFilter;
use App\Domain\Vacation\VacationPillar;
use App\Domain\Vacation\ViewModels\VacationPillarIndexViewModel;
use App\Presenters\Vacation\CampCardPresenter;
use App\Presenters\Vacation\TripCardPresenter;
use App\Repositories\Vacation\CampListingRepository;
use App\Repositories\Vacation\TripListingRepository;
use App\Repositories\Vacation\VacationDestinationRepository;
use App\Services\Translation\ListingTranslationService;
use App\Services\Translation\ListingViewTranslationService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class VacationPillarPageService
{
    public function __construct(
        private VacationDestinationRepository $destinations,
        private TripListingRepository $trips,
        private CampListingRepository $camps,
        private TripCardPresenter $tripPresenter,
        private CampCardPresenter $campPresenter,
        private VacationFilterApplicator $filterApplicator,
        private ListingViewTranslationService $viewTranslation,
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
        $countrySlug = CountrySlug::canonicalize($countrySlug) ?? strtolower($countrySlug);
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
            country: $countrySlug,
            sortBy: $filter->sortBy,
        );

        [$listings, $cards] = match ($pillar) {
            VacationPillar::Trips => $this->tripListings($filterAll, $perPage),
            VacationPillar::Camps => $this->campListings($filterAll, $perPage, $destination?->id),
        };

        $tripsTotal = $this->trips->queryForCountry($filterAll)->count();
        $campsTotal = $this->camps->queryForCountry($filterAll)->count();

        return new VacationPillarIndexViewModel(
            pillar: $pillar,
            filter: $filterAll,
            listings: $listings,
            cards: $cards,
            countries: $this->destinations->countriesForHubGrid(),
            speciesOptions: collect($this->filterApplicator->speciesOptionsForCountry($countrySlug)),
            tripsTotal: $tripsTotal,
            campsTotal: $campsTotal,
            faq: $destination === null ? $this->resolveFaq($pillar) : collect(),
            destination: $destination,
            mapMarkers: $this->buildMapMarkers($filterAll, $pillar),
        );
    }

    private function resolveFaq(VacationPillar $pillar): Collection
    {
        $dbFaqs = get_faqs_by_page($pillar->faqPageKey());

        if ($dbFaqs->isNotEmpty()) {
            return $dbFaqs;
        }

        return collect(config('vacations.'.$pillar->faqConfigKey(), config('vacations.hub_faq', [])))
            ->map(fn (array $item) => (object) [
                'question' => __($item['question_key']),
                'answer' => __($item['answer_key']),
            ]);
    }

    /**
     * @return array<int, array{lat: float, lng: float, title: string, url: string, pillar: string}>
     */
    private function buildMapMarkers(VacationListingFilter $filter, VacationPillar $pillar): array
    {
        $markers = [];

        if ($pillar === VacationPillar::Trips) {
            $trips = $this->trips->queryForCountry($filter)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get(['id', 'title', 'slug', 'latitude', 'longitude']);

            $this->viewTranslation->applyToCollection($trips, ListingTranslationService::TYPE_TRIP);

            foreach ($trips as $trip) {
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
            $camps = $this->camps->queryForCountry($filter)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get(['id', 'title', 'slug', 'latitude', 'longitude']);

            $this->viewTranslation->applyToCollection($camps, ListingTranslationService::TYPE_CAMP);

            foreach ($camps as $camp) {
                $markers[] = [
                    'lat' => (float) $camp->latitude,
                    'lng' => (float) $camp->longitude,
                    'title' => $camp->title,
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
