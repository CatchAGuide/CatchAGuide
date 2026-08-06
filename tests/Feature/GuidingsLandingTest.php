<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class GuidingsLandingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');

        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
    }

    public function test_guidings_landing_renders_former_homepage(): void
    {
        $response = $this->get(route('guidings.landing'));

        $response->assertOk();
        $response->assertViewIs('pages.newhome-latest');
        $response->assertViewHas('CategoryPage');
        $response->assertViewHas('CategoryPageMethods');
    }

    public function test_guidings_landing_shows_header_search_and_category_nav(): void
    {
        $response = $this->get(route('guidings.landing'));

        $response->assertOk();
        $response->assertSee('floating-search-container', false);
        $response->assertSee('id="global-search"', false);
        $response->assertSee('id="searchPlaceDesktop"', false);
        $response->assertSee('categories-row', false);
        $response->assertSee(__('homepage.filter-fishing-near-me'), false);
        $response->assertSee(__('homepage.header-vacations'), false);
        $response->assertSee(__('homepage.header-title'), false);
        $response->assertDontSee('home-compact', false);
        $response->assertDontSee('no-searchbar', false);
    }

    public function test_guidings_landing_is_not_captured_by_slug_redirect(): void
    {
        $response = $this->get('/guidings/landing');

        $response->assertOk();
        $response->assertViewIs('pages.newhome-latest');
    }
}
