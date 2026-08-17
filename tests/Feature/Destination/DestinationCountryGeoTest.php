<?php

namespace Tests\Feature\Destination;

use App\Models\City;
use App\Models\Country;
use App\Models\CountryTranslation;
use App\Models\Region;
use App\Services\Homepage\HomepageMixedOfferSelector;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Mockery;
use Tests\TestCase;

class DestinationCountryGeoTest extends TestCase
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

    public function test_destination_country_hides_region_and_city_carousels(): void
    {
        $country = $this->createCountry('spanien-no-geo');
        $region = Region::query()->create([
            'country_id' => $country->id,
            'name' => 'Unique Dest Region '.$country->slug,
            'slug' => 'dest-region-'.$country->slug,
        ]);
        City::query()->create([
            'country_id' => $country->id,
            'region_id' => $region->id,
            'name' => 'Unique Dest City '.$country->slug,
            'slug' => 'dest-city-'.$country->slug,
        ]);

        $this->bindDestinationOffers();

        $response = $this->get(route('destination.country', ['country' => $country->slug]));

        $response->assertOk();
        $response->assertDontSee(__('destination.all_region'), false);
        $response->assertDontSee(__('destination.all_cities'), false);
        $response->assertDontSee($region->name, false);
        $response->assertDontSee('Unique Dest City '.$country->slug, false);
        $response->assertDontSee('id="carousel-regions"', false);
        $response->assertDontSee('id="carousel-cities"', false);
        $response->assertDontSee('data-geo-rail="regions"', false);
        $response->assertDontSee('data-geo-rail="cities"', false);
        $response->assertSee('cag-site-nav--overlay', false);
        $response->assertDontSee('hero-tour.webp', false);
        $response->assertSee('data-category-header-shell', false);
        $response->assertSee('offers-page-header__hero', false);
        $response->assertSee('Fishing in Spain', false);
        $response->assertDontSee('navbar-custom short-header long-header', false);
    }

    public function test_destination_region_url_redirects_to_country(): void
    {
        $country = $this->createCountry('spanien-legacy-region');
        $region = Region::query()->create([
            'country_id' => $country->id,
            'name' => 'Catalonia',
            'slug' => 'catalonia-'.$country->slug,
        ]);

        $response = $this->get('/destination/'.$country->slug.'/'.$region->slug);

        $response->assertRedirect(route('destination.country', ['country' => $country->slug], false));
        $response->assertStatus(301);
    }

    public function test_destination_city_url_redirects_to_country_and_keeps_query(): void
    {
        $country = $this->createCountry('spanien-legacy-city');
        $region = Region::query()->create([
            'country_id' => $country->id,
            'name' => 'Catalonia',
            'slug' => 'catalonia-'.$country->slug,
        ]);
        $city = City::query()->create([
            'country_id' => $country->id,
            'region_id' => $region->id,
            'name' => 'Barcelona',
            'slug' => 'barcelona-'.$country->slug,
        ]);

        $response = $this->get('/destination/'.$country->slug.'/'.$region->slug.'/'.$city->slug.'?type=tour');

        $response->assertRedirect(route('destination.country', [
            'country' => $country->slug,
            'type' => 'tour',
        ], false));
        $response->assertStatus(301);
    }

    public function test_destination_country_route_is_country_only(): void
    {
        $this->assertSame(
            'destination/{country}',
            app('router')->getRoutes()->getByName('destination.country')->uri()
        );
        $this->assertSame(
            'guidings/{country}/{region?}/{city?}',
            app('router')->getRoutes()->getByName('guidings.destination')->uri()
        );
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
            'title' => 'Fishing in Spain',
            'sub_title' => 'Discover Spanish waters',
            'introduction' => 'Intro text for Spain.',
            'content' => 'Body content for Spain.',
        ]);

        return $country->fresh(['translations']);
    }

    private function bindDestinationOffers(): void
    {
        $mock = Mockery::mock(HomepageMixedOfferSelector::class);
        $mock->shouldReceive('byModuleForDestination')->andReturn([
            'tour' => collect(),
            'camp' => collect(),
            'trip' => collect(),
        ]);
        $this->app->instance(HomepageMixedOfferSelector::class, $mock);
    }
}
