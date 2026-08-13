<?php

namespace App\Http\Controllers\Category;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use App\Services\CategoryPage\CategoryPageContentService;
use App\Services\Offers\OfferCatalogPageService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DestinationCountryController extends Controller
{
    public function __construct(
        private OfferCatalogPageService $offerCatalog,
        private CategoryPageContentService $categoryContent,
    ) {}

    public function index(): View
    {
        $locale = app()->getLocale();
        $hub = $this->categoryContent->destinationHubFields($locale);
        $countries = Country::query()->get();

        return view('pages.countries.index', [
            'countries' => $countries,
            'destination_route' => 'destination.country',
            'title' => $hub['title'],
            'sub_title' => $hub['sub_title'],
            'introduction' => $hub['introduction'],
        ]);
    }

    public function country(Request $request, string $country, ?string $region = null, ?string $city = null): View
    {
        $countryRow = Country::with(['translations', 'fish_charts', 'fish_size_limits', 'fish_time_limits'])
            ->whereSlug($country)
            ->firstOrFail();

        $regionRow = null;
        $cityRow = null;

        if ($region) {
            $regionRow = Region::with(['translations', 'country', 'fish_charts', 'fish_size_limits', 'fish_time_limits'])
                ->whereSlug($region)
                ->where('country_id', $countryRow->id)
                ->firstOrFail();
        }

        if ($city) {
            $cityRow = City::with(['translations', 'country', 'region', 'fish_charts', 'fish_size_limits', 'fish_time_limits'])
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
        $scope = CategoryPageScope::GLOBAL;

        // Global destination pages use Global content only — no Tours/legacy inheritance.
        $rowData = $this->categoryContent->applyScopedContentToModel(
            $rowData,
            $entityType,
            $scope,
            $locale,
            null,
            false,
        );

        $regions = Region::with('country')->where('country_id', $countryRow->id)->get();

        if ($regionRow) {
            $cities = City::with(['country', 'region'])
                ->where('country_id', $countryRow->id)
                ->where('region_id', $regionRow->id)
                ->get();
        } else {
            $cities = City::with(['country', 'region'])
                ->where('country_id', $countryRow->id)
                ->get();
        }

        $faq = $this->categoryContent->resolveFaqsForEntityDisplay(
            $entityType,
            $rowData->id,
            $scope,
            $locale,
            null,
            false,
        );

        $fishChart = $this->geoCollection($rowData, 'fish_charts');
        $fishSizeLimit = $this->geoCollection($rowData, 'fish_size_limits');
        $fishTimeLimit = $this->geoCollection($rowData, 'fish_time_limits');

        $vm = $this->offerCatalog->buildForDestination(
            $request,
            $countryRow,
            $regionRow,
            $cityRow,
        );

        return view('pages.category.country', [
            'row_data' => $rowData,
            'destination_type' => $destinationType,
            'destination_route' => 'destination.country',
            'regions' => $regions,
            'cities' => $cities,
            'region_count' => $regions->count(),
            'city_count' => $cities->count(),
            'faq' => $faq,
            'fish_chart' => $fishChart,
            'fish_size_limit' => $fishSizeLimit,
            'fish_time_limit' => $fishTimeLimit,
            'vm' => $vm,
        ]);
    }

    private function geoCollection(Country|Region|City $entity, string $relation): Collection
    {
        if ($entity->relationLoaded($relation)) {
            return $entity->getRelation($relation);
        }

        return $entity->{$relation}()->get();
    }
}
