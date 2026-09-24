<?php

namespace App\Http\Controllers\Category;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Domain\Vacation\CountrySlug;
use App\Http\Controllers\Controller;
use App\Models\CategoryEntity;
use App\Repositories\Guiding\GuidingCategoryAvailabilityRepository;
use App\Repositories\Vacation\VacationDestinationRepository;
use App\Services\CategoryPage\CategoryPageContentService;
use App\Services\Homepage\HomepageMixedOfferSelector;
use App\Services\Seo\CatalogInventoryGate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DestinationCountryController extends Controller
{
    public function __construct(
        private HomepageMixedOfferSelector $mixedOffers,
        private CategoryPageContentService $categoryContent,
        private GuidingCategoryAvailabilityRepository $guidingAvailability,
        private VacationDestinationRepository $destinations,
        private CatalogInventoryGate $inventoryGate,
    ) {}

    public function index(): View
    {
        $locale = app()->getLocale();
        $hub = $this->categoryContent->destinationHubFields($locale);
        $faq = $this->categoryContent->resolveFaqsForEntityDisplay(
            CategoryPageEntityType::DESTINATION_HUB,
            CategoryPageEntityType::DESTINATION_HUB_SOURCE_ID,
            CategoryPageScope::GLOBAL,
            $locale,
            null,
            false,
        );

        // A country card must not show with zero tours AND zero active
        // camps/trips — listings on either side keep it eligible.
        $vacationCountrySlugs = $this->destinations->countriesForHubGrid()
            ->map(fn (array $row) => CountrySlug::canonicalize($row['slug']) ?? $row['slug'])
            ->flip();

        $countries = CategoryEntity::countries()->get()
            ->filter(fn (CategoryEntity $country) => $this->guidingAvailability->hasGuidingsForCountry($country->slug, $country->countrycode)
                || $vacationCountrySlugs->has(CountrySlug::canonicalize($country->slug) ?? strtolower((string) $country->slug)))
            ->values();

        return view('pages.countries.index', [
            'countries' => $countries,
            'destination_route' => 'destination.country',
            'title' => $hub['title'],
            'sub_title' => $hub['sub_title'],
            'introduction' => $hub['introduction'],
            'content' => $hub['content'],
            'faq_title' => $hub['faq_title'],
            'faq' => $faq,
        ]);
    }

    /**
     * Country, region, and city destination pages all render through here — mirrors
     * GuidingDestinationController::show()'s region/city resolution so the vacations-side
     * destination pages have the same region/city depth as the tours-side ones instead of
     * collapsing region/city URLs into a content-free redirect to the country page.
     */
    public function show(Request $request, string $country, ?string $region = null, ?string $city = null): View|RedirectResponse
    {
        // Uppercase/umlaut country slugs (e.g. "Österreich") must 301 to their canonical lowercase
        // form, same as /vacations/{country} — otherwise MySQL's case-insensitive collation lets
        // both casings resolve as separate, mutually-uncanonicalized 200 pages (see CLAUDE.md's
        // "SEO / catalog page conventions").
        if (CountrySlug::needsCanonicalRedirect($country)) {
            return redirect()->route('destination.country', array_filter([
                'country' => CountrySlug::canonicalize($country),
                'region' => $region,
                'city' => $city,
            ]), 301);
        }

        $countryRow = CategoryEntity::countries()
            ->whereSlug($country)
            ->firstOrFail();

        // A country with zero tours AND zero active camps/trips has no page, nor do its
        // regions/cities — they're all subsets of the same country's availability. Mirrors
        // the destination hub filtering in index().
        $hasTours = $this->guidingAvailability->hasGuidingsForCountry($countryRow->slug, $countryRow->countrycode);
        $hasVacations = $this->destinations->hubGridCountry($countryRow->slug) !== null;

        if (! $hasTours && ! $hasVacations) {
            abort(404);
        }

        $regionRow = null;
        $cityRow = null;

        if ($region) {
            $regionRow = CategoryEntity::regions()->with('country')
                ->whereSlug($region)
                ->where('country_id', $countryRow->id)
                ->firstOrFail();
        }

        if ($city) {
            $cityRow = CategoryEntity::cities()->with(['country', 'region'])
                ->whereSlug($city)
                ->where('country_id', $countryRow->id)
                ->where('region_id', $regionRow->id)
                ->firstOrFail();
        }

        $entityRow = $cityRow ?? $regionRow ?? $countryRow;
        $destinationType = match (true) {
            $cityRow !== null => 'city',
            $regionRow !== null => 'region',
            default => 'country',
        };
        $entityType = match ($destinationType) {
            'city' => CategoryPageEntityType::GEO_CITY,
            'region' => CategoryPageEntityType::GEO_REGION,
            default => CategoryPageEntityType::GEO_COUNTRY,
        };

        $locale = app()->getLocale();
        $rowData = $this->categoryContent->applyScopedContentToModel(
            $entityRow,
            $entityType,
            CategoryPageScope::GLOBAL,
            $locale,
            null,
            false,
        );

        $faq = $this->categoryContent->resolveFaqsForEntityDisplay(
            $entityType,
            $rowData->id,
            CategoryPageScope::GLOBAL,
            $locale,
            null,
            false,
        );

        // Region/city rails only link pages that clear the inventory gate (gated pages are noindexed).
        $regions = CategoryEntity::regions()->with('country')
            ->where('country_id', $countryRow->id)
            ->get()
            ->filter(fn (CategoryEntity $region) => $this->inventoryGate->destinationIndexable($countryRow, $region))
            ->values();

        $cities = ($regionRow
            ? CategoryEntity::cities()->with(['country', 'region'])
                ->where('country_id', $countryRow->id)
                ->where('region_id', $regionRow->id)
                ->get()
            : CategoryEntity::cities()->with(['country', 'region'])
                ->where('country_id', $countryRow->id)
                ->get())
            ->filter(fn (CategoryEntity $city) => $city->region !== null
                && $this->inventoryGate->destinationIndexable($countryRow, $city->region, $city))
            ->values();

        $placeName = $rowData->name;
        $offerModules = $this->mixedOffers->byModuleForDestination($countryRow, $regionRow, $cityRow);

        return view('pages.category.country', [
            'row_data' => $rowData,
            'destination_type' => $destinationType,
            'destination_route' => 'destination.country',
            'show_geo_carousels' => true,
            'show_offers_catalog' => false,
            'regions' => $regions,
            'cities' => $cities,
            'region_count' => $regions->count(),
            'city_count' => $cities->count(),
            'faq' => $faq,
            'fish_chart' => $this->geoCollection($rowData, 'fish_charts'),
            'fish_size_limit' => $this->geoCollection($rowData, 'fish_size_limits'),
            'fish_time_limit' => $this->geoCollection($rowData, 'fish_time_limits'),
            'offerModules' => $offerModules,
            'offersTitle' => __('destination.popular_title', ['place' => $placeName]),
            'offersEmptyMessage' => __('destination.popular_empty', ['place' => $placeName]),
            'offersSectionClass' => 'cag-dest-offers',
            'offersVariant' => 'destination',
            'offerBrowseUrls' => [
                'tour' => route('guidings.destination', array_filter([
                    'country' => $countryRow->slug,
                    'region' => $regionRow?->slug,
                    'city' => $cityRow?->slug,
                ])),
                'camp' => route('vacations.camps.show', ['slug' => $countryRow->slug]),
                'trip' => route('vacations.trips.show', ['slug' => $countryRow->slug]),
            ],
            'noindex' => ! $this->inventoryGate->destinationIndexable($countryRow, $regionRow, $cityRow),
        ]);
    }

    private function geoCollection(CategoryEntity $entity, string $relation): Collection
    {
        return $entity->{$relation}();
    }
}
