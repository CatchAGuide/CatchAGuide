<?php

namespace App\Http\Controllers\Category;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Http\Controllers\Controller;
use App\Http\Controllers\GuidingsController;
use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use App\Services\CategoryPage\CategoryPageContentService;
use App\Services\Offers\OfferCatalogPageService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class GuidingDestinationController extends Controller
{
    public function __construct(
        private OfferCatalogPageService $offerCatalog,
        private CategoryPageContentService $categoryContent,
    ) {}

    public function index(): View
    {
        $countries = Country::query()->get();

        return view('pages.countries.index', [
            'countries' => $countries,
            'destination_route' => 'guidings.destination',
        ]);
    }

    public function show(Request $request, string $country, ?string $region = null, ?string $city = null)
    {
        $countryRow = Country::with(['translations', 'fish_charts', 'fish_size_limits', 'fish_time_limits'])
            ->whereSlug($country)
            ->first();

        // One-segment unknown slugs are legacy guiding URLs.
        if ($countryRow === null) {
            if ($region === null && $city === null) {
                return app(GuidingsController::class)->redirectToNewFormat($country);
            }

            abort(404);
        }

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
            'regions' => $regions,
            'cities' => $cities,
            'region_count' => $regions->count(),
            'city_count' => $cities->count(),
            'faq' => $faq,
            'fish_chart' => $this->geoCollection($rowData, 'fish_charts'),
            'fish_size_limit' => $this->geoCollection($rowData, 'fish_size_limits'),
            'fish_time_limit' => $this->geoCollection($rowData, 'fish_time_limits'),
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
