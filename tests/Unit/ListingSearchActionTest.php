<?php

namespace Tests\Unit;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ListingSearchActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');
    }

    public function test_guidings_routes_use_guidings_catalog(): void
    {
        $this->bindNamedRequest('/guidings', 'guidings.landing');
        $this->assertSame(route('guidings.index'), listing_search_action());

        $this->bindNamedRequest('/guidings/alloffers', 'guidings.index');
        $this->assertSame(route('guidings.index'), listing_search_action());

        $this->bindNamedRequest('/guidings/niederlande', 'guidings.destination');
        $this->assertSame(route('guidings.index'), listing_search_action());
    }

    public function test_offers_and_destination_routes_use_offers_catalog(): void
    {
        $this->bindNamedRequest('/offers', 'offers.index');
        $this->assertSame(route('offers.index'), listing_search_action());

        $this->bindNamedRequest('/destination', 'destination');
        $this->assertSame(route('offers.index'), listing_search_action());

        $this->bindNamedRequest('/targets', 'targets.index');
        $this->assertSame(route('offers.index'), listing_search_action());
    }

    public function test_explicit_override_wins(): void
    {
        $this->bindNamedRequest('/guidings', 'guidings.landing');

        $this->assertSame(route('offers.index'), listing_search_action(route('offers.index')));
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
