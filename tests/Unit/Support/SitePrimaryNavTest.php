<?php

namespace Tests\Unit\Support;

use App\Support\SitePrimaryNav;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class SitePrimaryNavTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');
    }

    public function test_links_are_ordered_all_offers_tours_vacations(): void
    {
        $this->bindNamedRequest('/', 'welcome');

        $keys = array_column(SitePrimaryNav::links(), 'key');

        $this->assertSame(['offers', 'tours', 'vacations'], $keys);
        $this->assertSame(__('offers.nav_label'), SitePrimaryNav::links()[0]['label']);
        $this->assertSame(route('offers.index'), SitePrimaryNav::links()[0]['url']);
        $this->assertSame(route('guidings.landing'), SitePrimaryNav::links()[1]['url']);
        $this->assertSame(route('vacations.index'), SitePrimaryNav::links()[2]['url']);
        $this->assertNull(SitePrimaryNav::activeSection());
    }

    public function test_offers_listing_is_the_active_section(): void
    {
        $this->bindNamedRequest('/offers', 'offers.index');

        $this->assertSame('offers', SitePrimaryNav::activeSection());
        $this->assertTrue(SitePrimaryNav::links()[0]['active']);
        $this->assertFalse(SitePrimaryNav::links()[1]['active']);
        $this->assertFalse(SitePrimaryNav::links()[2]['active']);
    }

    public function test_guidings_pages_highlight_fishing_tours(): void
    {
        $this->bindNamedRequest('/guidings', 'guidings.landing');
        $this->assertSame('tours', SitePrimaryNav::activeSection());

        $this->bindNamedRequest('/guidings/alloffers', 'guidings.index');
        $this->assertSame('tours', SitePrimaryNav::activeSection());

        $this->bindNamedRequest('/guidings/offer/sea-trout', 'guidings.show');
        $this->assertSame('tours', SitePrimaryNav::activeSection());
    }

    public function test_vacation_pages_highlight_fishing_vacations(): void
    {
        $this->bindNamedRequest('/vacations', 'vacations.index');
        $this->assertSame('vacations', SitePrimaryNav::activeSection());

        $this->bindNamedRequest('/vacations/trips/sweden', 'vacations.trips.show');
        $this->assertSame('vacations', SitePrimaryNav::activeSection());

        $this->bindNamedRequest('/trips/sweden-trip', 'trips.show');
        $this->assertSame('vacations', SitePrimaryNav::activeSection());
    }

    public function test_homepage_does_not_highlight_a_section(): void
    {
        $this->bindNamedRequest('/', 'welcome');

        foreach (SitePrimaryNav::links() as $link) {
            $this->assertFalse($link['active']);
        }
    }

    private function bindNamedRequest(string $uri, string $routeName): void
    {
        $request = Request::create($uri, 'GET');
        $route = new Route(['GET'], ltrim($uri, '/'), static fn () => null);
        $route->name($routeName);
        $request->setRouteResolver(static fn () => $route);
        $this->app->instance('request', $request);
    }
}
