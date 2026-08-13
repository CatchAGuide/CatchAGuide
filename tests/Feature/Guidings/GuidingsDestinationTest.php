<?php

namespace Tests\Feature\Guidings;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Domain\Offers\OfferListingFilter;
use App\Domain\Offers\ViewModels\OfferCatalogViewModel;
use App\Models\Country;
use App\Models\CountryTranslation;
use App\Models\Language;
use App\Models\Region;
use App\Services\Offers\OfferCatalogPageService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\URL;
use Mockery;
use Tests\TestCase;

class GuidingsDestinationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');

        $this->withoutMiddleware([
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \App\Http\Middleware\DDoSProtectionMiddleware::class,
        ]);
    }

    private function createCountry(string $slugPrefix): Country
    {
        $country = Country::query()->create([
            'name' => 'Spanien',
            'slug' => $slugPrefix.'-'.uniqid(),
            'countrycode' => 'ES',
            'filters' => [
                'place' => 'Spain',
                'placeLat' => '40.4',
                'placeLng' => '-3.7',
                'country' => 'Spain',
            ],
        ]);

        CountryTranslation::query()->create([
            'country_id' => $country->id,
            'language' => app()->getLocale(),
            'title' => 'Legacy Spain Title',
            'sub_title' => 'Legacy subtitle',
            'introduction' => 'Legacy intro',
            'content' => 'Legacy body',
        ]);

        return $country->fresh(['translations']);
    }

    public function test_guidings_destination_uses_tours_content_not_global(): void
    {
        $country = $this->createCountry('spanien-tours');

        Language::query()->create([
            'source_id' => (string) $country->id,
            'type' => CategoryPageEntityType::GEO_COUNTRY,
            'scope' => CategoryPageScope::GLOBAL,
            'language' => app()->getLocale(),
            'title' => 'Global Spain Title',
            'sub_title' => 'Global subtitle',
            'introduction' => 'Global intro',
            'content' => 'Global body',
            'faq_title' => '',
        ]);

        Language::query()->create([
            'source_id' => (string) $country->id,
            'type' => CategoryPageEntityType::GEO_COUNTRY,
            'scope' => CategoryPageScope::TOURS,
            'language' => app()->getLocale(),
            'title' => 'Tours Spain Guidings Title',
            'sub_title' => 'Tours subtitle',
            'introduction' => 'Tours intro',
            'content' => 'Tours body',
            'faq_title' => '',
        ]);

        $this->bindToursCatalog(fn () => $this->viewModel(
            catalogUrl: route('guidings.destination', ['country' => $country->slug]),
        ));

        $response = $this->get(route('guidings.destination', ['country' => $country->slug]));

        $response->assertOk();
        $response->assertSee('Tours Spain Guidings Title', false);
        $response->assertDontSee('Global Spain Title', false);
        $response->assertSee('name="type"', false);
        $response->assertSee('value="tour"', false);
    }

    public function test_guidings_destination_region_resolves_under_country(): void
    {
        $country = $this->createCountry('spanien-region');
        $region = Region::query()->create([
            'country_id' => $country->id,
            'name' => 'Catalonia',
            'slug' => 'catalonia-'.uniqid(),
            'filters' => [
                'place' => 'Catalonia',
                'placeLat' => '41.3',
                'placeLng' => '2.1',
            ],
        ]);

        Language::query()->create([
            'source_id' => (string) $region->id,
            'type' => CategoryPageEntityType::GEO_REGION,
            'scope' => CategoryPageScope::TOURS,
            'language' => app()->getLocale(),
            'title' => 'Tours Catalonia Title',
            'sub_title' => 'Region subtitle',
            'introduction' => 'Region intro',
            'content' => 'Region body',
            'faq_title' => '',
        ]);

        $this->bindToursCatalog(fn () => $this->viewModel(
            catalogUrl: route('guidings.destination', [
                'country' => $country->slug,
                'region' => $region->slug,
            ]),
        ));

        $response = $this->get(route('guidings.destination', [
            'country' => $country->slug,
            'region' => $region->slug,
        ]));

        $response->assertOk();
        $response->assertSee('Tours Catalonia Title', false);
    }

    public function test_numeric_guiding_show_route_still_wins_over_destination(): void
    {
        $this->assertSame(
            'guidings/{id}/{slug}',
            app('router')->getRoutes()->getByName('guidings.show')->uri()
        );
        $this->assertSame(
            'guidings/{country}/{region?}/{city?}',
            app('router')->getRoutes()->getByName('guidings.destination')->uri()
        );
    }

    private function bindToursCatalog(callable $factory): void
    {
        $mock = Mockery::mock(OfferCatalogPageService::class);
        $mock->shouldReceive('buildForToursDestination')->andReturnUsing($factory);
        $this->app->instance(OfferCatalogPageService::class, $mock);
    }

    private function viewModel(?string $catalogUrl = null): OfferCatalogViewModel
    {
        $filter = OfferListingFilter::fromRequest([
            'type' => 'tour',
            'country' => 'spanien',
            'place' => 'Spain',
            'placeLat' => '40.4',
            'placeLng' => '-3.7',
        ]);

        $paginator = new LengthAwarePaginator([], 0, 9, 1, [
            'path' => $catalogUrl ?? route('guidings.index'),
        ]);

        return new OfferCatalogViewModel(
            filter: $filter,
            listings: $paginator,
            cards: collect(),
            toursTotal: 0,
            tripsTotal: 0,
            campsTotal: 0,
            listingsTotal: 0,
            speciesOptions: collect(),
            countries: collect(),
            methodOptions: collect(),
            waterOptions: collect(),
            tourDurationOptions: collect(),
            tripDurationOptions: collect(),
            accommodationTypeOptions: collect(),
            faq: collect(),
            mapMarkers: [],
            suggestedCards: collect(),
            catalogUrl: $catalogUrl,
            lockDestinationScope: true,
            lockTourScope: true,
        );
    }
}
