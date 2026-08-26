<?php

namespace Tests\Feature\Guidings;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Domain\Offers\OfferListingFilter;
use App\Domain\Offers\ViewModels\OfferCatalogViewModel;
use App\Models\CategoryEntity;
use App\Models\Language;
use App\Services\Offers\OfferCatalogPageService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\URL;
use Mockery;
use Tests\TestCase;

class GuidingsDestinationTest extends TestCase
{
    use DatabaseTransactions;

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

    private function createCountry(string $slugPrefix): CategoryEntity
    {
        return CategoryEntity::countries()->create([
            'type' => 'country',
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
        $response->assertSee('cag-site-nav--overlay', false);
        $response->assertDontSee('hero-tour.webp', false);
        $response->assertSee('data-category-header-shell', false);
        $response->assertSee('offers-page-header__hero', false);
        $response->assertSee('categoryHeroSearchPlace', false);
        $response->assertSee('data-offers-persons-stepper', false);
        $response->assertSee('action="'.url('/guidings/alloffers').'"', false);
        $response->assertDontSee('action="'.url('/offers').'"', false);
        $response->assertDontSee('guidings-page-header__segment--fish', false);
        $response->assertDontSee('navbar-custom short-header long-header', false);
    }

    public function test_guidings_destination_country_shows_country_switch_dropdown_targeting_guidings_pages(): void
    {
        $country = $this->createCountry('spanien-country-switch');

        $this->bindToursCatalog(fn () => $this->viewModel(
            catalogUrl: route('guidings.destination', ['country' => $country->slug]),
        ));

        $response = $this->get(route('guidings.destination', ['country' => $country->slug]));

        $response->assertOk();
        $response->assertSee('data-offers-region-redirect', false);
        $response->assertSee(
            'value="'.route('guidings.destination', ['country' => $country->slug]).'"',
            false
        );
        $response->assertSee('value="'.route('guidings.index').'"', false);
    }

    public function test_guidings_country_still_renders_region_and_city_carousels(): void
    {
        $country = $this->createCountry('spanien-geo-carousel');
        $region = CategoryEntity::regions()->create([
            'type' => 'region',
            'country_id' => $country->id,
            'name' => 'Guidings Region '.$country->slug,
            'slug' => 'guidings-region-'.$country->slug,
        ]);
        CategoryEntity::cities()->create([
            'type' => 'city',
            'country_id' => $country->id,
            'region_id' => $region->id,
            'name' => 'Guidings City '.$country->slug,
            'slug' => 'guidings-city-'.$country->slug,
        ]);

        $this->bindToursCatalog(fn () => $this->viewModel(
            catalogUrl: route('guidings.destination', ['country' => $country->slug]),
        ));

        $response = $this->get(route('guidings.destination', ['country' => $country->slug]));

        $response->assertOk();
        $response->assertSee(__('destination.all_region'), false);
        $response->assertSee(__('destination.all_cities'), false);
        $response->assertSee(__('destination.regions_subtitle'), false);
        $response->assertSee(__('destination.cities_subtitle'), false);
        $response->assertSee($region->name, false);
        $response->assertSee('Guidings City '.$country->slug, false);
        $response->assertSee('cag-home-species__card', false);
        $response->assertSee('cag-dest-geo-rail', false);
        $response->assertSee('data-species-spotlight', false);
        $response->assertSee('data-geo-rail="regions"', false);
        $response->assertSee('data-geo-rail="cities"', false);
        $response->assertDontSee('id="carousel-regions"', false);
        $response->assertDontSee('id="carousel-cities"', false);
        $response->assertDontSee('data-offers-place', false);
        $response->assertDontSee('class="offers-catalog__context', false);
        $response->assertSee(route('guidings.destination', [
            'country' => $country->slug,
            'region' => $region->slug,
        ], false), false);
    }

    public function test_guidings_region_renders_compact_city_rail(): void
    {
        $country = $this->createCountry('spanien-region-geo');
        $region = CategoryEntity::regions()->create([
            'type' => 'region',
            'country_id' => $country->id,
            'name' => 'Catalonia',
            'slug' => 'catalonia-geo-'.$country->slug,
            'filters' => [
                'place' => 'Catalonia',
                'placeLat' => '41.3',
                'placeLng' => '2.1',
            ],
        ]);
        $city = CategoryEntity::cities()->create([
            'type' => 'city',
            'country_id' => $country->id,
            'region_id' => $region->id,
            'name' => 'Barcelona',
            'slug' => 'barcelona-geo-'.$country->slug,
        ]);

        Language::query()->create([
            'source_id' => (string) $region->id,
            'type' => CategoryPageEntityType::GEO_REGION,
            'scope' => CategoryPageScope::TOURS,
            'language' => app()->getLocale(),
            'title' => 'Tours Catalonia Geo Title',
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
        $response->assertDontSee(__('destination.all_region'), false);
        $response->assertSee(__('destination.all_cities'), false);
        $response->assertSee($city->name, false);
        $response->assertSee('data-geo-rail="cities"', false);
        $response->assertDontSee('data-geo-rail="regions"', false);
        $response->assertSee(route('guidings.destination', [
            'country' => $country->slug,
            'region' => $region->slug,
            'city' => $city->slug,
        ], false), false);
    }

    public function test_guidings_city_renders_sibling_city_rail(): void
    {
        $country = $this->createCountry('spanien-city-geo');
        $region = CategoryEntity::regions()->create([
            'type' => 'region',
            'country_id' => $country->id,
            'name' => 'Catalonia',
            'slug' => 'catalonia-city-'.$country->slug,
        ]);
        $city = CategoryEntity::cities()->create([
            'type' => 'city',
            'country_id' => $country->id,
            'region_id' => $region->id,
            'name' => 'Barcelona',
            'slug' => 'barcelona-city-'.$country->slug,
        ]);
        $sibling = CategoryEntity::cities()->create([
            'type' => 'city',
            'country_id' => $country->id,
            'region_id' => $region->id,
            'name' => 'Girona',
            'slug' => 'girona-city-'.$country->slug,
        ]);

        Language::query()->create([
            'source_id' => (string) $city->id,
            'type' => CategoryPageEntityType::GEO_CITY,
            'scope' => CategoryPageScope::TOURS,
            'language' => app()->getLocale(),
            'title' => 'Tours Barcelona Geo Title',
            'sub_title' => 'City subtitle',
            'introduction' => 'City intro',
            'content' => 'City body',
            'faq_title' => '',
        ]);

        $this->bindToursCatalog(fn () => $this->viewModel(
            catalogUrl: route('guidings.destination', [
                'country' => $country->slug,
                'region' => $region->slug,
                'city' => $city->slug,
            ]),
        ));

        $response = $this->get(route('guidings.destination', [
            'country' => $country->slug,
            'region' => $region->slug,
            'city' => $city->slug,
        ]));

        $response->assertOk();
        $response->assertDontSee(__('destination.all_region'), false);
        $response->assertSee(__('destination.all_cities'), false);
        $response->assertSee($sibling->name, false);
        $response->assertSee('data-geo-rail="cities"', false);
        $response->assertSee(route('guidings.destination', [
            'country' => $country->slug,
            'region' => $region->slug,
            'city' => $sibling->slug,
        ], false), false);
    }

    public function test_guidings_destination_region_resolves_under_country(): void
    {
        $country = $this->createCountry('spanien-region');
        $region = CategoryEntity::regions()->create([
            'type' => 'region',
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
            'guidings/offer/{slug}',
            app('router')->getRoutes()->getByName('guidings.show')->uri()
        );
        $this->assertSame(
            'guidings/{id}/{slug}',
            app('router')->getRoutes()->getByName('guidings.show.legacy')->uri()
        );
        $this->assertSame(
            'guidings/{country}/{region?}/{city?}',
            app('router')->getRoutes()->getByName('guidings.destination')->uri()
        );
        $this->assertSame(
            'guidings/countries',
            app('router')->getRoutes()->getByName('guidings.countries')->uri()
        );
        $this->assertSame(
            'guidings',
            app('router')->getRoutes()->getByName('guidings.landing')->uri()
        );
        $this->assertSame(
            'guidings/alloffers',
            app('router')->getRoutes()->getByName('guidings.index')->uri()
        );
    }

    public function test_guidings_countries_index_links_to_guidings_destination(): void
    {
        $country = $this->createCountry('spanien-countries');

        $response = $this->get(route('guidings.countries'));

        $response->assertOk();
        $response->assertViewIs('pages.countries.index');
        $response->assertViewHas('destination_route', 'guidings.destination');
        $response->assertSee(route('guidings.destination', ['country' => $country->slug], false), false);
        $response->assertDontSee(route('destination.country', ['country' => $country->slug], false), false);
        $response->assertSee('cag-site-nav--overlay', false);
        $response->assertDontSee('hero-tour.webp', false);
        $response->assertSee('data-category-header-shell', false);
        $response->assertSee('offers-page-header__hero', false);
        $response->assertDontSee('navbar-custom short-header long-header', false);
        $response->assertDontSee('guidings-page-header__segment--fish', false);
    }

    public function test_guidings_countries_is_not_captured_as_destination_country(): void
    {
        $response = $this->get('/guidings/countries');

        $response->assertOk();
        $response->assertViewIs('pages.countries.index');
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
