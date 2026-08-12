<?php

namespace App\Http\Controllers\Category;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\DestinationFaq;
use App\Models\DestinationFishChart;
use App\Models\DestinationFishSizeLimit;
use App\Models\DestinationFishTimeLimit;
use App\Models\Region;
use App\Services\CategoryPage\CategoryPageContentService;
use App\Services\Offers\OfferCatalogPageService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DestinationCountryController extends Controller
{
    public function __construct(
        private OfferCatalogPageService $offerCatalog,
        private CategoryPageContentService $categoryContent,
    ) {}

    public function index(): View
    {
        $countries = Country::all();

        return view('pages.countries.index', compact('countries'));
    }

    public function country(Request $request, string $country, ?string $region = null, ?string $city = null): View
    {
        $countryRow = Country::with('translations')->whereSlug($country)->firstOrFail();

        $regionRow = null;
        $cityRow = null;

        if ($region) {
            $regionRow = Region::with(['translations', 'country'])
                ->whereSlug($region)
                ->where('country_id', $countryRow->id)
                ->firstOrFail();
        }

        if ($city) {
            $cityRow = City::with(['translations', 'country', 'region'])
                ->whereSlug($city)
                ->where('country_id', $countryRow->id)
                ->where('region_id', $regionRow->id)
                ->firstOrFail();
        }

        if ($cityRow) {
            $rowData = $cityRow;
            $destinationType = 'city';
            $destinationId = $cityRow->id;
            $entityType = CategoryPageEntityType::GEO_CITY;
            $scope = CategoryPageScope::TOURS;
        } elseif ($regionRow) {
            $rowData = $regionRow;
            $destinationType = 'region';
            $destinationId = $regionRow->id;
            $entityType = CategoryPageEntityType::GEO_REGION;
            $scope = CategoryPageScope::TOURS;
        } else {
            $rowData = $countryRow;
            $destinationType = 'country';
            $destinationId = $countryRow->id;
            $entityType = CategoryPageEntityType::GEO_COUNTRY;
            $scope = CategoryPageScope::GLOBAL;
        }

        $locale = app()->getLocale();

        $legacyResolver = match ($destinationType) {
            'country' => fn (string $loc) => $this->categoryContent->legacyCountryLanguage($countryRow, $loc),
            'region' => fn (string $loc) => $this->categoryContent->legacyRegionLanguage($regionRow, $loc),
            'city' => fn (string $loc) => $this->categoryContent->legacyCityLanguage($cityRow, $loc),
        };

        $rowData = $this->categoryContent->applyScopedContentToModel(
            $rowData,
            $entityType,
            $scope,
            $locale,
            fn (string $loc) => $legacyResolver($loc),
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
            $destinationId,
            $scope,
            $locale,
            fn (string $loc) => DestinationFaq::where('destination_id', $destinationId)
                ->where('language', $loc)
                ->get(),
        );

        $fishChart = DestinationFishChart::where('destination_id', $destinationId)->get();
        $fishSizeLimit = DestinationFishSizeLimit::where('destination_id', $destinationId)->get();
        $fishTimeLimit = DestinationFishTimeLimit::where('destination_id', $destinationId)->get();

        $vm = $this->offerCatalog->buildForDestination(
            $request,
            $countryRow,
            $regionRow,
            $cityRow,
        );

        return view('pages.category.country', [
            'row_data' => $rowData,
            'destination_type' => $destinationType,
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
}
