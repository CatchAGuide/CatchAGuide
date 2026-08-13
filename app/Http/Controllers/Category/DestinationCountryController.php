<?php

namespace App\Http\Controllers\Category;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Services\CategoryPage\CategoryPageContentService;
use App\Services\Homepage\HomepageMixedOfferSelector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DestinationCountryController extends Controller
{
    public function __construct(
        private HomepageMixedOfferSelector $mixedOffers,
        private CategoryPageContentService $categoryContent,
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
        $countries = Country::query()->get();

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

    public function country(Request $request, string $country): View
    {
        $countryRow = Country::with(['translations', 'fish_charts', 'fish_size_limits', 'fish_time_limits'])
            ->whereSlug($country)
            ->firstOrFail();

        $locale = app()->getLocale();
        $rowData = $this->categoryContent->applyScopedContentToModel(
            $countryRow,
            CategoryPageEntityType::GEO_COUNTRY,
            CategoryPageScope::GLOBAL,
            $locale,
            null,
            false,
        );

        $faq = $this->categoryContent->resolveFaqsForEntityDisplay(
            CategoryPageEntityType::GEO_COUNTRY,
            $rowData->id,
            CategoryPageScope::GLOBAL,
            $locale,
            null,
            false,
        );

        $placeName = $rowData->name;
        $offerModules = $this->mixedOffers->byModuleForDestination($countryRow);

        return view('pages.category.country', [
            'row_data' => $rowData,
            'destination_type' => 'country',
            'destination_route' => 'destination.country',
            'show_geo_carousels' => false,
            'show_offers_catalog' => false,
            'regions' => collect(),
            'cities' => collect(),
            'region_count' => 0,
            'city_count' => 0,
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
                'tour' => route('guidings.destination', ['country' => $countryRow->slug]),
                'camp' => route('vacations.camps.show', ['slug' => $countryRow->slug]),
                'trip' => route('vacations.trips.show', ['slug' => $countryRow->slug]),
            ],
        ]);
    }

    public function redirectLegacyGeo(Request $request, string $country): RedirectResponse
    {
        Country::query()->whereSlug($country)->firstOrFail();

        return redirect()->route(
            'destination.country',
            array_merge(['country' => $country], $request->query()),
            301,
        );
    }

    private function geoCollection(Country $entity, string $relation): Collection
    {
        if ($entity->relationLoaded($relation)) {
            return $entity->getRelation($relation);
        }

        return $entity->{$relation}()->get();
    }
}
