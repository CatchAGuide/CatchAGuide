<?php

namespace Tests\Feature\Vacation;

use App\Domain\Vacation\Pillar;
use App\Domain\Vacation\ViewModels\PillarTileViewModel;
use App\Domain\Vacation\ViewModels\VacationHubViewModel;
use App\Models\CategoryEntity;
use App\Repositories\Vacation\VacationDestinationRepository;
use App\Services\Vacation\VacationHubPageService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Mockery;
use Tests\TestCase;

class VacationCountriesIndexTest extends TestCase
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
        ]);
    }

    private function hubGridRow(CategoryEntity $country, int $trips, int $camps): array
    {
        return [
            'destination' => $country,
            'slug' => $country->slug,
            'name' => $country->name,
            'sub_title' => null,
            'camps' => $camps,
            'trips' => $trips,
            'thumbnail_path' => null,
            'countrycode' => $country->countrycode,
        ];
    }

    private function mockHubGrid(array $rows): void
    {
        $mock = Mockery::mock(VacationDestinationRepository::class);
        $mock->shouldReceive('countriesForHubGrid')->andReturn(collect($rows));
        $this->app->instance(VacationDestinationRepository::class, $mock);
    }

    public function test_route_is_registered_ahead_of_the_country_catch_all(): void
    {
        $this->assertSame(
            'vacations/countries',
            app('router')->getRoutes()->getByName('vacations.countries')->uri()
        );
    }

    public function test_vacations_countries_index_links_to_vacations_country(): void
    {
        $withListings = $this->createCountry('spain-vac');
        $withoutListings = $this->createCountry('empty-vac');

        $this->mockHubGrid([
            $this->hubGridRow($withListings, trips: 1, camps: 2),
        ]);

        $response = $this->get(route('vacations.countries'));

        $response->assertOk();
        $response->assertViewIs('pages.countries.index');
        $response->assertViewHas('destination_route', 'vacations.country');
        $response->assertSee(route('vacations.country', $withListings->slug, false), false);
        $response->assertDontSee(route('vacations.country', $withoutListings->slug, false), false);
        $response->assertSee('cag-site-nav--overlay', false);
    }

    public function test_vacations_countries_index_scopes_country_links_to_the_requested_pillar(): void
    {
        $tripsCountry = $this->createCountry('trips-only');
        $campsCountry = $this->createCountry('camps-only');

        $this->mockHubGrid([
            $this->hubGridRow($tripsCountry, trips: 3, camps: 0),
            $this->hubGridRow($campsCountry, trips: 0, camps: 2),
        ]);

        $response = $this->get(route('vacations.countries', ['pillar' => 'trips']));

        $response->assertOk();
        $response->assertViewHas('destination_route', 'vacations.trips.show');
        $response->assertSee(route('vacations.trips.show', $tripsCountry->slug, false), false);
        $response->assertDontSee(route('vacations.trips.show', $campsCountry->slug, false), false);
    }

    public function test_vacations_countries_index_excludes_country_without_any_listings(): void
    {
        $country = $this->createCountry('no-listings');

        $this->mockHubGrid([]);

        $response = $this->get(route('vacations.countries'));

        $response->assertOk();
        $response->assertDontSee(route('vacations.country', $country->slug, false), false);
    }

    public function test_vacations_hub_country_rail_links_to_see_all_countries(): void
    {
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
            newListings: collect(),
            showNewListingsRail: false,
            countryGrid: collect([
                [
                    'destination' => null,
                    'slug' => 'spain',
                    'name' => 'Spain',
                    'sub_title' => null,
                    'camps' => 2,
                    'trips' => 3,
                    'thumbnail_path' => null,
                    'countrycode' => 'ES',
                ],
            ]),
            faqItems: [],
            totalTrips: 3,
            totalCamps: 2,
            targetFishTiles: collect(),
            testimonials: collect(),
        );

        $service = Mockery::mock(VacationHubPageService::class);
        $service->shouldReceive('build')->andReturn($hub);
        $this->app->instance(VacationHubPageService::class, $service);

        $response = $this->get(route('vacations.index'));

        $response->assertOk();
        $response->assertSee(route('vacations.countries', [], false), false);
        $response->assertSee(__('vacations.hub_country_slider_see_all'), false);
    }
}
