<?php

namespace App\Services\Vacation;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Domain\Vacation\CountrySlug;
use App\Domain\Vacation\VacationListingFilter;
use App\Domain\Vacation\VacationPillar;
use App\Domain\Vacation\ViewModels\VacationPillarIndexViewModel;
use App\Models\Country;
use App\Presenters\Vacation\CampCardPresenter;
use App\Presenters\Vacation\TripCardPresenter;
use App\Repositories\Vacation\CampListingRepository;
use App\Repositories\Vacation\TripListingRepository;
use App\Repositories\Vacation\VacationDestinationRepository;
use App\Services\CategoryPage\CategoryPageContentService;
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
        private CategoryPageContentService $categoryContent,
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
        ?Country $destination,
    ): VacationPillarIndexViewModel {
        $filter = VacationListingFilter::fromRequest(
            array_merge($request->all(), ['pillar' => $pillar->value]),
            $countrySlug,
        );
        $perPage = (int) config('vacations.pillar_index_per_page', 9);

        [$listings, $cards] = match ($pillar) {
            VacationPillar::Trips => $this->tripListings($filter, $perPage),
            VacationPillar::Camps => $this->campListings($filter, $perPage, $destination?->id),
        };

        $tripsTotal = $this->trips->queryForCountry($filter)->count();
        $campsTotal = $this->camps->queryForCountry($filter)->count();

        $locale = app()->getLocale();
        $scope = match ($pillar) {
            VacationPillar::Trips => CategoryPageScope::TRIPS,
            VacationPillar::Camps => CategoryPageScope::CAMPS,
        };

        // Trips/camps must not inherit vacations content via shared country translations.
        if ($destination !== null && $destination->id) {
            $destination = $this->categoryContent->applyScopedContentToModel(
                $destination,
                CategoryPageEntityType::GEO_COUNTRY,
                $scope,
                $locale,
                null,
            );
        }

        $destinationFaq = ($destination !== null && $destination->id)
            ? $this->categoryContent->resolveFaqsForEntityDisplay(
                CategoryPageEntityType::GEO_COUNTRY,
                $destination->id,
                $scope,
                $locale,
                null,
            )
            : collect();

        return new VacationPillarIndexViewModel(
            pillar: $pillar,
            filter: $filter,
            listings: $listings,
            cards: $cards,
            countries: $this->destinations->countriesForHubGrid(),
            speciesOptions: collect($this->filterApplicator->speciesOptionsForCountry($countrySlug)),
            accommodationTypeOptions: $filter->showsCampFacets()
                ? collect($this->filterApplicator->accommodationTypeOptions())
                : collect(),
            tripsTotal: $tripsTotal,
            campsTotal: $campsTotal,
            faq: $destination === null ? $this->resolveFaq($pillar) : $destinationFaq,
            destination: $destination,
            mapMarkers: $this->buildMapMarkers($filter, $pillar),
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
     * @return array<int, array<string, mixed>>
     */
    private function buildMapMarkers(VacationListingFilter $filter, VacationPillar $pillar): array
    {
        if ($pillar === VacationPillar::Trips) {
            $trips = $this->trips->queryForCountry($filter)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get(['id', 'title', 'slug', 'location', 'latitude', 'longitude', 'thumbnail_path', 'price_per_person', 'currency']);

            $this->viewTranslation->applyToCollection($trips, ListingTranslationService::TYPE_TRIP);

            return \App\Support\Maps\MapMarkerCollection::fromTrips($trips);
        }

        if ($pillar === VacationPillar::Camps) {
            $camps = $this->camps->queryForCountry($filter)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->with(['accommodations', 'specialOffers'])
                ->get(['id', 'title', 'slug', 'location', 'latitude', 'longitude', 'thumbnail_path']);

            $this->viewTranslation->applyToCollection($camps, ListingTranslationService::TYPE_CAMP);

            return \App\Support\Maps\MapMarkerCollection::fromCamps($camps);
        }

        return [];
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
