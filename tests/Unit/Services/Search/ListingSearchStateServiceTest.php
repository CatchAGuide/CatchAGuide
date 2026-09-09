<?php

namespace Tests\Unit\Services\Search;

use App\Services\Search\ListingSearchStateService;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Tests\TestCase;

class ListingSearchStateServiceTest extends TestCase
{
    public function test_remember_stores_a_free_text_place_search(): void
    {
        $service = new ListingSearchStateService;

        $service->remember($this->bindNamedRequest('/offers', 'offers.index', [
            'place' => 'Spain',
            'placeLat' => '40.1',
            'placeLng' => '-3.7',
            'city' => 'Madrid',
            'country' => 'Spain',
            'region' => 'Madrid',
        ]));

        $this->assertSame([
            'place' => 'Spain',
            'placeLat' => '40.1',
            'placeLng' => '-3.7',
            'city' => 'Madrid',
            'country' => 'Spain',
            'region' => 'Madrid',
        ], $service->current());
    }

    public function test_remember_ignores_free_text_place_without_coordinates(): void
    {
        $service = new ListingSearchStateService;

        // The header's own JS blocks submitting free text without a resolved place,
        // so a request like this should never really reach the server -- but if it
        // does, it must not clobber a previously stored, valid search.
        $service->remember($this->bindNamedRequest('/offers', 'offers.index', [
            'place' => 'Spain',
        ]));

        $this->assertSame('', $service->current()['place']);
    }

    public function test_remember_captures_country_only_searches(): void
    {
        $service = new ListingSearchStateService;

        $request = $this->bindNamedRequest('/vacations/spain', 'vacations.country', route: 'vacations/{country}');
        $service->remember($request);

        $this->assertSame('spain', $service->current()['country']);
        $this->assertSame('', $service->current()['place']);
    }

    public function test_remember_treats_all_offers_as_an_explicit_reset(): void
    {
        $service = new ListingSearchStateService;

        $service->remember($this->bindNamedRequest('/vacations/spain', 'vacations.country', route: 'vacations/{country}'));
        $this->assertSame('spain', $service->current()['country']);

        $service->remember($this->bindNamedRequest('/vacations/all-offers', 'vacations.all-offers'));
        $this->assertSame('all-offers', $service->current()['country']);
    }

    public function test_remember_does_not_overwrite_prior_search_when_request_carries_no_signal(): void
    {
        $service = new ListingSearchStateService;

        $service->remember($this->bindNamedRequest('/offers', 'offers.index', [
            'place' => 'Spain',
            'placeLat' => '40.1',
            'placeLng' => '-3.7',
        ]));

        $service->remember($this->bindNamedRequest('/offers', 'offers.index', []));

        $this->assertSame('Spain', $service->current()['place']);
    }

    public function test_resolve_from_request_prefers_live_query_over_persisted_search(): void
    {
        $service = new ListingSearchStateService;

        $service->remember($this->bindNamedRequest('/offers', 'offers.index', [
            'place' => 'Spain',
            'placeLat' => '40.1',
            'placeLng' => '-3.7',
        ]));

        $resolved = $service->resolveFromRequest($this->bindNamedRequest('/offers', 'offers.index', [
            'place' => 'Portugal',
            'placeLat' => '39.4',
            'placeLng' => '-8.2',
        ]));

        $this->assertSame('Portugal', $resolved['place']);
    }

    public function test_resolve_from_request_falls_back_to_persisted_search(): void
    {
        $service = new ListingSearchStateService;

        $service->remember($this->bindNamedRequest('/offers', 'offers.index', [
            'place' => 'Spain',
            'placeLat' => '40.1',
            'placeLng' => '-3.7',
        ]));

        $resolved = $service->resolveFromRequest($this->bindNamedRequest('/guidings/offer/sea-trout', 'guidings.show'));

        $this->assertSame('Spain', $resolved['place']);
    }

    public function test_current_returns_blank_shape_when_nothing_persisted(): void
    {
        $service = new ListingSearchStateService;

        $this->assertSame([
            'place' => '',
            'placeLat' => '',
            'placeLng' => '',
            'city' => '',
            'country' => '',
            'region' => '',
        ], $service->current());
    }

    private function bindNamedRequest(string $uri, string $routeName, array $query = [], ?string $route = null): Request
    {
        $request = Request::create($uri, 'GET', $query);
        $definition = new Route(['GET'], ltrim($route ?? $uri, '/'), static fn () => null);
        $definition->name($routeName);
        $request->setRouteResolver(static fn () => $definition);
        $definition->bind($request);
        $this->app->instance('request', $request);

        return $request;
    }
}
