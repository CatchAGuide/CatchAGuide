<?php

namespace Tests\Feature\Vacation;

use App\Domain\Vacation\VacationListingFilter;
use App\Domain\Vacation\VacationPillar;
use App\Domain\Vacation\ViewModels\VacationPillarIndexViewModel;
use App\Services\Vacation\VacationPillarPageService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Mockery;
use Tests\TestCase;

class VacationListingLayoutTest extends TestCase
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

    public function test_catalog_layout_puts_map_above_filters_and_sort_on_the_right(): void
    {
        $html = Blade::render(
            '<x-vacation.catalog-layout
                :has-map="$hasMap"
                :filter="$filter"
                :trips-total="3"
                :camps-total="10"
                :species-options="$speciesOptions"
                :pillar-links="$pillarLinks"
                title="Spain Camps"
            >Listings</x-vacation.catalog-layout>',
            [
                'hasMap' => true,
                'filter' => VacationListingFilter::fromRequest([]),
                'speciesOptions' => collect([['id' => 1, 'name' => 'Pike']]),
                'pillarLinks' => [
                    'all' => '/vacations/all-offers',
                    'trips' => '/vacations/trips',
                    'camps' => '/vacations/camps',
                ],
            ],
        );

        $sidebarStart = strpos($html, 'vacation-country__sidebar');
        $listingsStart = strpos($html, 'vacation-country__listings');
        $this->assertNotFalse($sidebarStart);
        $this->assertNotFalse($listingsStart);
        $this->assertLessThan($listingsStart, $sidebarStart);

        $sidebar = substr($html, $sidebarStart, $listingsStart - $sidebarStart);
        $this->assertLessThan(
            strpos($sidebar, 'vacation-country__sidebar-filters'),
            strpos($sidebar, 'vacation-country__map-card'),
        );
        $this->assertStringNotContainsString('name="sortby"', $sidebar);
        $this->assertStringContainsString('name="species"', $sidebar);

        $listings = substr($html, $listingsStart);
        $this->assertStringContainsString('vacation-country__toolbar', $listings);
        $this->assertStringContainsString('justify-content-between', $listings);
        $this->assertStringContainsString('ms-auto', $listings);
        $this->assertStringContainsString('Spain Camps', $listings);
        $this->assertStringContainsString('data-vacation-sort-form', $listings);
        $this->assertStringContainsString('data-vacation-sort-select', $listings);
        $this->assertStringContainsString('name="sortby"', $listings);
        $this->assertStringContainsString(__('vacations.filter_sort'), $listings);
        $this->assertStringContainsString('Listings', $listings);
    }

    public function test_camps_listing_page_uses_offers_style_map_filter_sort_layout(): void
    {
        $this->bindPillarIndex(fn () => new VacationPillarIndexViewModel(
            pillar: VacationPillar::Camps,
            filter: VacationListingFilter::fromRequest([]),
            listings: new LengthAwarePaginator([], 0, 9),
            cards: collect(),
            countries: collect(),
            speciesOptions: collect([['id' => 1, 'name' => 'Pike']]),
            tripsTotal: 3,
            campsTotal: 10,
            faq: collect(),
            mapMarkers: [
                [
                    'id' => 1,
                    'lat' => 40.4,
                    'lng' => -3.7,
                    'title' => 'Spain Camp',
                    'pillar' => 'camp',
                    'url' => '/vacations/camps/test',
                ],
            ],
        ));

        $response = $this->get(route('vacations.camps.index'));

        $response->assertOk();
        $html = $response->getContent();
        $this->assertStringContainsString('vacation-country__map-card', $html);
        $this->assertStringContainsString('vacation-country__toolbar', $html);
        $this->assertStringContainsString('data-vacation-sort-select', $html);
        $this->assertLessThan(
            strpos($html, 'vacation-country__sidebar-filters'),
            strpos($html, 'vacation-country__map-card'),
        );
    }

    public function test_trips_listing_page_uses_the_same_catalog_layout(): void
    {
        $this->bindPillarIndex(fn () => new VacationPillarIndexViewModel(
            pillar: VacationPillar::Trips,
            filter: VacationListingFilter::fromRequest([]),
            listings: new LengthAwarePaginator([], 0, 9),
            cards: collect(),
            countries: collect(),
            speciesOptions: collect(),
            tripsTotal: 3,
            campsTotal: 10,
            faq: collect(),
        ));

        $response = $this->get(route('vacations.trips.index'));

        $response->assertOk();
        $response->assertSee('vacation-country__toolbar', false);
        $response->assertSee('data-vacation-sort-form', false);
    }

    public function test_country_and_pillar_listing_views_share_the_catalog_layout(): void
    {
        foreach ([
            resource_path('views/pages/vacations/pillar-index.blade.php'),
            resource_path('views/pages/vacations/country.blade.php'),
        ] as $path) {
            $this->assertStringContainsString(
                'x-vacation.catalog-layout',
                (string) file_get_contents($path),
            );
        }
    }

    /**
     * @param  callable(): VacationPillarIndexViewModel  $factory
     */
    private function bindPillarIndex(callable $factory): void
    {
        $mock = Mockery::mock(VacationPillarPageService::class);
        $mock->shouldReceive('buildIndex')->andReturnUsing($factory);
        $this->app->instance(VacationPillarPageService::class, $mock);
    }
}
