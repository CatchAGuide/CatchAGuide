<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\DestinationFaq;
use App\Models\DestinationFishChart;
use App\Models\DestinationFishSizeLimit;
use App\Models\DestinationFishTimeLimit;
use App\Models\Region;
use App\Services\Offers\OfferCatalogPageService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DestinationCountryController extends Controller
{
    public function __construct(
        private OfferCatalogPageService $offerCatalog,
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
        } elseif ($regionRow) {
            $rowData = $regionRow;
            $destinationType = 'region';
            $destinationId = $regionRow->id;
        } else {
            $rowData = $countryRow;
            $destinationType = 'country';
            $destinationId = $countryRow->id;
        }

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

        $locale = app()->getLocale();
        $faq = DestinationFaq::where('destination_id', $destinationId)
            ->where('language', $locale)
            ->get();
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
