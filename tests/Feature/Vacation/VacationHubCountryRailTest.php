<?php

namespace Tests\Feature\Vacation;

use App\Domain\Vacation\Pillar;
use App\Domain\Vacation\ViewModels\PillarTileViewModel;
use App\Domain\Vacation\ViewModels\VacationHubViewModel;
use App\Services\Vacation\VacationHubPageService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Mockery;
use Tests\TestCase;

class VacationHubCountryRailTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');

        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

        Cache::flush();

        $hub = new VacationHubViewModel(
            campTile: new PillarTileViewModel(
                pillar: Pillar::Camp,
                title: 'Camps',
                description: 'Camp desc',
                listingCount: 2,
                countryCount: 1,
                minPrice: 100,
                currency: 'EUR',
                url: route('vacations.camps.index'),
            ),
            tripTile: new PillarTileViewModel(
                pillar: Pillar::Trip,
                title: 'Trips',
                description: 'Trip desc',
                listingCount: 3,
                countryCount: 2,
                minPrice: 200,
                currency: 'EUR',
                url: route('vacations.trips.index'),
            ),
            popularListings: collect(),
            newTrips: collect(),
            showNewTripsRail: false,
            newCamps: collect(),
            showNewCampsRail: false,
            countryGrid: collect([
                [
                    'destination' => null,
                    'slug' => 'spain',
                    'name' => 'Spain',
                    'sub_title' => null,
                    'camps' => 2,
                    'trips' => 3,
                    'thumbnail_path' => 'countries/spain.jpg',
                    'countrycode' => 'ES',
                ],
                [
                    'destination' => null,
                    'slug' => 'sweden',
                    'name' => 'Sweden',
                    'sub_title' => null,
                    'camps' => 1,
                    'trips' => 0,
                    'thumbnail_path' => null,
                    'countrycode' => 'SE',
                ],
            ]),
            faqItems: [],
            totalTrips: 3,
            totalCamps: 2,
        );

        $service = Mockery::mock(VacationHubPageService::class);
        $service->shouldReceive('build')->andReturn($hub);
        $this->app->instance(VacationHubPageService::class, $service);
    }

    public function test_hub_renders_homepage_style_country_rail(): void
    {
        $response = $this->get(route('vacations.index'));

        $response->assertOk();
        $response->assertSee('vacation-country-rail', false);
        $response->assertSee('data-vac-dest-rail="countries"', false);
        $response->assertSee(__('vacations.hub_country_slider_title'), false);
        $response->assertSee(__('vacations.hub_country_slider_subtitle'), false);
        $response->assertSee(route('vacations.country', 'spain', false), false);
        $response->assertSee(route('vacations.country', 'sweden', false), false);
        $response->assertSee('Spain', false);
        $response->assertSee('fi fi-es', false);
        $response->assertSee('fi fi-se', false);
        $response->assertSee(__('vacations.hub_country_trips_camps', ['trips' => 3, 'camps' => 2]), false);
        $response->assertDontSee('vacation-country-slider__swiper', false);
    }
}
