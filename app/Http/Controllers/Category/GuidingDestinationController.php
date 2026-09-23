<?php

namespace App\Http\Controllers\Category;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Domain\Vacation\CountrySlug;
use App\Http\Controllers\Controller;
use App\Http\Controllers\GuidingsController;
use App\Models\CategoryEntity;
use App\Repositories\Guiding\GuidingCategoryAvailabilityRepository;
use App\Services\CategoryPage\CategoryPageContentService;
use App\Services\Homepage\HomepageCountrySelector;
use App\Services\Offers\OfferCatalogPageService;
use App\Services\Seo\CatalogInventoryGate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class GuidingDestinationController extends Controller
{
    public function __construct(
        private OfferCatalogPageService $offerCatalog,
        private CategoryPageContentService $categoryContent,
        private HomepageCountrySelector $homepageCountries,
        private GuidingCategoryAvailabilityRepository $guidingAvailability,
        private CatalogInventoryGate $inventoryGate,
    ) {}

    public function index(): View
    {
        $countries = CategoryEntity::countries()->get()
            ->filter(fn (CategoryEntity $country) => $this->guidingAvailability->hasGuidingsForCountry($country->slug, $country->countrycode))
            ->values();

        return view('pages.countries.index', [
            'countries' => $countries,
            'destination_route' => 'guidings.destination',
        ]);
    }

    public function show(Request $request, string $country, ?string $region = null, ?string $city = null): View|RedirectResponse
    {
        // Uppercase/umlaut country slugs (e.g. "Österreich") must 301 to their canonical lowercase
        // form, same as /vacations/{country} and /destination/{country} — otherwise MySQL's
        // case-insensitive collation lets both casings resolve as separate, mutually-
        // uncanonicalized 200 pages (see CLAUDE.md's "SEO / catalog page conventions").
        if (CountrySlug::needsCanonicalRedirect($country)) {
            return redirect()->route('guidings.destination', array_filter([
                'country' => CountrySlug::canonicalize($country),
                'region' => $region,
                'city' => $city,
            ]), 301);
        }

        $countryRow = CategoryEntity::countries()
            ->whereSlug($country)
            ->first();

        // One-segment unknown slugs are legacy guiding URLs.
        if ($countryRow === null) {
            if ($region === null && $city === null) {
                return app(GuidingsController::class)->redirectToNewFormat($country);
            }

            abort(404);
        }

        // A country with zero publicly visible tours has no page — nor do its
        // regions/cities, since they're all subsets of the same country.
        if (! $this->guidingAvailability->hasGuidingsForCountry($countryRow->slug, $countryRow->countrycode)) {
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

        if ($cityRow) {
            $rowData = $cityRow;
            $destinationType = 'city';
            $entityType = CategoryPageEntityType::GEO_CITY;
        } elseif ($regionRow) {
            $rowData = $regionRow;
            $destinationType = 'region';
            $entityType = CategoryPageEntityType::GEO_REGION;
        } else {
            $rowData = $countryRow;
            $destinationType = 'country';
            $entityType = CategoryPageEntityType::GEO_COUNTRY;
        }

        $locale = app()->getLocale();
        $scope = CategoryPageScope::TOURS;

        // Tours destination pages use Tours content only — no Global inheritance.
        $rowData = $this->categoryContent->applyScopedContentToModel(
            $rowData,
            $entityType,
            $scope,
            $locale,
            null,
            false,
        );

        // Region/city rails only link pages that clear the inventory gate — a gated page is
        // noindexed, so linking it from the hub would keep feeding it to crawlers.
        $regions = CategoryEntity::regions()->with('country')->where('country_id', $countryRow->id)->get()
            ->filter(fn (CategoryEntity $region) => $this->inventoryGate->guidingDestinationIndexable($countryRow, $region))
            ->values();

        if ($regionRow) {
            $cities = CategoryEntity::cities()->with(['country', 'region'])
                ->where('country_id', $countryRow->id)
                ->where('region_id', $regionRow->id)
                ->get();
        } else {
            $cities = CategoryEntity::cities()->with(['country', 'region'])
                ->where('country_id', $countryRow->id)
                ->get();
        }
        $cities = $cities
            ->filter(fn (CategoryEntity $city) => $city->region !== null
                && $this->inventoryGate->guidingDestinationIndexable($countryRow, $city->region, $city))
            ->values();

        $faq = $this->categoryContent->resolveFaqsForEntityDisplay(
            $entityType,
            $rowData->id,
            $scope,
            $locale,
            null,
            false,
        );

        $vm = $this->offerCatalog->buildForToursDestination(
            $request,
            $countryRow,
            $regionRow,
            $cityRow,
        );

        return view('pages.category.country', [
            'row_data' => $rowData,
            'destination_type' => $destinationType,
            'destination_route' => 'guidings.destination',
            'show_geo_carousels' => true,
            'show_offers_catalog' => true,
            'regions' => $regions,
            'cities' => $cities,
            'region_count' => $regions->count(),
            'city_count' => $cities->count(),
            'countryOptions' => $this->homepageCountries->featured()
                ->filter(fn (array $option) => $this->guidingAvailability->hasGuidingsForCountry($option['slug'], $option['countrycode'] ?? null))
                ->map(fn (array $option) => [
                    'slug' => $option['slug'],
                    'name' => $option['name'],
                    'url' => route('guidings.destination', ['country' => $option['slug']]),
                ])
                ->values(),
            'faq' => $faq,
            'fish_chart' => $this->geoCollection($rowData, 'fish_charts'),
            'fish_size_limit' => $this->geoCollection($rowData, 'fish_size_limits'),
            'fish_time_limit' => $this->geoCollection($rowData, 'fish_time_limits'),
            'vm' => $vm,
            'noindex' => ! $this->inventoryGate->guidingDestinationIndexable($countryRow, $regionRow, $cityRow),
        ]);
    }

    private function geoCollection(CategoryEntity $entity, string $relation): Collection
    {
        return $entity->{$relation}();
    }
}
