<?php

namespace Tests\Feature;

use App\Domain\CategoryPage\CategoryPageScope;
use App\Services\Homepage\HomepageCountrySelector;
use Illuminate\Support\Facades\URL;
use Mockery;
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
        $response->assertViewHas('featuredCountries');
    }

    public function test_guidings_landing_shows_header_search_and_category_nav(): void
    {
        $response = $this->get(route('guidings.landing'));

        $response->assertOk();
        $response->assertSee('cag-site-nav--overlay', false);
        $response->assertSee('data-category-header-shell', false);
        $response->assertSee('data-category-header-search', false);
        $response->assertSee('categoryHeroSearchPlace', false);
        $response->assertSee('action="'.url('/guidings/alloffers').'"', false);
        $response->assertDontSee('action="'.url('/offers').'"', false);
        $response->assertSee(__('homepage.filter-fishing-near-me'), false);
        $response->assertSee(__('homepage.header-vacations'), false);
        $response->assertSee(__('homepage.header-title'), false);
        $response->assertDontSee('floating-search-container', false);
        $response->assertDontSee('navbar-custom short-header long-header', false);
    }

    public function test_guidings_landing_is_not_captured_by_slug_redirect(): void
    {
        $response = $this->get('/guidings');

        $response->assertOk();
        $response->assertViewIs('pages.newhome-latest');
    }

    public function test_legacy_guidings_landing_paths_redirect_permanently(): void
    {
        $this->get('/guidings/l')->assertRedirect('/guidings')->assertStatus(301);
        $this->get('/guidings/landing')->assertRedirect('/guidings')->assertStatus(301);
    }

    public function test_guidings_landing_country_carousel_uses_guidings_destination_routes(): void
    {
        $countries = Mockery::mock(HomepageCountrySelector::class);
        $countries->shouldReceive('featured')->once()->with(null, CategoryPageScope::TOURS)->andReturn(collect([
            [
                'slug' => 'deutschland',
                'name' => 'Germany',
                'thumbnail' => '/assets/images/300x300.png',
                'countrycode' => 'DE',
                'from_price' => 90,
                'from_price_label' => 'from €90 / Person',
            ],
            [
                'slug' => 'spanien',
                'name' => 'Spain',
                'thumbnail' => '/assets/images/300x300.png',
                'countrycode' => 'ES',
                'from_price' => 120,
                'from_price_label' => 'from €120 / Person',
            ],
            [
                'slug' => 'dänemark',
                'name' => 'Denmark',
                'thumbnail' => '/assets/images/300x300.png',
                'countrycode' => 'DK',
                'from_price' => 80,
                'from_price_label' => 'from €80 / Person',
            ],
        ]));
        $this->app->instance(HomepageCountrySelector::class, $countries);

        $response = $this->get(route('guidings.landing'));

        $response->assertOk();
        $response->assertSee('data-dest-rail', false);
        $response->assertSee('cag-home-destinations', false);
        $response->assertSee(route('guidings.destination', ['country' => 'deutschland'], false), false);
        $response->assertSee(route('guidings.destination', ['country' => 'spanien'], false), false);
        $response->assertSee(route('guidings.destination', ['country' => 'dänemark'], false), false);
        $response->assertSee('Germany', false);
        $response->assertDontSee(__('homepage.countries_all'), false);
        $response->assertDontSee(route('guidings.countries', [], false), false);
        $response->assertDontSee('/destination/deutschland', false);
        $response->assertDontSee('/guidings/Denmark', false);
        $response->assertDontSee('href="'.route('destination', [], false).'"', false);
        $response->assertSee(route('guidings.methods', [], false), false);
        $response->assertDontSee(route('category.types', ['type' => 'methods'], false), false);
        $response->assertSee(route('targets.index'), false);
        $response->assertDontSee(route('category.types', ['type' => 'targets'], false), false);
    }
}
