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

    public function test_browse_links_append_destinations_after_catalog(): void
    {
        $this->bindNamedRequest('/', 'welcome');

        $keys = array_column(SitePrimaryNav::browseLinks(), 'key');

        $this->assertSame(['offers', 'tours', 'vacations', 'destinations'], $keys);
        $this->assertSame(__('homepage.footer_destinations'), SitePrimaryNav::browseLinks()[3]['label']);
        $this->assertSame(route('destination'), SitePrimaryNav::browseLinks()[3]['url']);
        $this->assertSame('fas fa-map-marker-alt', SitePrimaryNav::browseLinks()[3]['icon']);
    }

    public function test_destination_pages_highlight_destinations_in_browse_links(): void
    {
        $this->bindNamedRequest('/destination', 'destination');
        $this->assertSame('destinations', SitePrimaryNav::activeSection());
        $this->assertTrue(SitePrimaryNav::browseLinks()[3]['active']);

        $this->bindNamedRequest('/destination/deutschland', 'destination.country');
        $this->assertSame('destinations', SitePrimaryNav::activeSection());
    }

    public function test_bottom_nav_matches_burger_browse_links_plus_login(): void
    {
        $this->bindNamedRequest('/', 'welcome');

        $links = SitePrimaryNav::bottomNavLinks();
        $keys = array_column($links, 'key');

        $this->assertSame(['offers', 'tours', 'vacations', 'destinations', 'account'], $keys);
        $this->assertSame(__('homepage.header-login'), $links[4]['label']);
        $this->assertTrue($links[4]['opens_login']);
        $this->assertSame('fas fa-th-large', $links[0]['icon']);
        $this->assertSame('fas fa-ship', $links[1]['icon']);
        $this->assertSame('fas fa-suitcase-rolling', $links[2]['icon']);
        $this->assertSame('fas fa-map-marker-alt', $links[3]['icon']);
        $this->assertFalse($links[0]['active']);
    }

    public function test_catalog_and_homepage_use_overlay_header(): void
    {
        $this->bindNamedRequest('/', 'welcome');
        $this->assertTrue(SitePrimaryNav::isHomepage());
        $this->assertTrue(SitePrimaryNav::usesOverlayHeader());
        $this->assertFalse(SitePrimaryNav::usesLayoutNav());
        $this->assertTrue(SitePrimaryNav::usesLayoutBottomNav());

        $this->bindNamedRequest('/offers', 'offers.index');
        $this->assertTrue(SitePrimaryNav::usesOverlayHeader());
        $this->assertFalse(SitePrimaryNav::usesLayoutNav());
        $this->assertTrue(SitePrimaryNav::usesLayoutBottomNav());

        $this->bindNamedRequest('/guidings', 'guidings.landing');
        $this->assertTrue(SitePrimaryNav::usesOverlayHeader());

        $this->bindNamedRequest('/guidings/offer/sea-trout', 'guidings.show');
        $this->assertTrue(SitePrimaryNav::usesOverlayHeader());
        $this->assertFalse(SitePrimaryNav::usesLayoutBottomNav());
        $this->assertTrue(SitePrimaryNav::isProductDetailPage());

        $this->bindNamedRequest('/vacations', 'vacations.index');
        $this->assertTrue(SitePrimaryNav::usesOverlayHeader());
        $this->assertTrue(SitePrimaryNav::usesVacationLoadingOverlay());

        $this->bindNamedRequest('/destination', 'destination');
        $this->assertTrue(SitePrimaryNav::usesOverlayHeader());

        $this->bindNamedRequest('/guidings/methods', 'guidings.methods');
        $this->assertTrue(SitePrimaryNav::usesOverlayHeader());
        $this->assertFalse(SitePrimaryNav::usesLayoutPageHeader());

        $this->bindNamedRequest('/guidings/targets', 'guidings.targets.index');
        $this->assertTrue(SitePrimaryNav::usesOverlayHeader());
        $this->assertFalse(SitePrimaryNav::usesLayoutPageHeader());

        $this->bindNamedRequest('/trips/sweden-trip', 'trips.show');
        $this->assertTrue(SitePrimaryNav::usesOverlayHeader());
        $this->assertTrue(SitePrimaryNav::usesVacationLoadingOverlay());
        $this->assertFalse(SitePrimaryNav::usesLayoutBottomNav());
        $this->assertTrue(SitePrimaryNav::isProductDetailPage());
    }

    public function test_profile_and_content_pages_use_layout_page_header(): void
    {
        $this->bindNamedRequest('/profile', 'profile.index');
        $this->assertFalse(SitePrimaryNav::usesOverlayHeader());
        $this->assertTrue(SitePrimaryNav::usesLayoutNav());
        $this->assertTrue(SitePrimaryNav::usesLayoutPageHeader());
        $this->assertTrue(SitePrimaryNav::usesLayoutBottomNav());
        $this->assertFalse(SitePrimaryNav::usesVacationLoadingOverlay());

        $this->bindNamedRequest('/about-us', 'additional.about_us');
        $this->assertTrue(SitePrimaryNav::usesLayoutPageHeader());
        $this->assertTrue(SitePrimaryNav::usesLayoutBottomNav());

        $this->bindNamedRequest('/contact', 'additional.contact');
        $this->assertTrue(SitePrimaryNav::usesLayoutNav());
        $this->assertTrue(SitePrimaryNav::usesLayoutPageHeader());

        $this->bindNamedRequest('/partner', 'additional.partner');
        $this->assertTrue(SitePrimaryNav::usesOverlayHeader());
        $this->assertFalse(SitePrimaryNav::usesLayoutNav());
        $this->assertFalse(SitePrimaryNav::usesLayoutPageHeader());
        $this->assertTrue(SitePrimaryNav::usesLayoutBottomNav());

        $this->bindNamedRequest('/faq', 'law.faq');
        $this->assertTrue(SitePrimaryNav::usesLayoutPageHeader());

        $this->bindNamedRequest('/fishing-magazine', 'blog.index');
        $this->assertTrue(SitePrimaryNav::usesLayoutPageHeader());
    }

    public function test_checkout_keeps_solid_header_without_page_header_or_mobile_catalog_bar(): void
    {
        $this->bindNamedRequest('/checkout', 'checkout.index');

        $this->assertTrue(SitePrimaryNav::usesLayoutNav());
        $this->assertFalse(SitePrimaryNav::usesLayoutPageHeader());
        $this->assertFalse(SitePrimaryNav::usesLayoutBottomNav());
        $this->assertTrue(SitePrimaryNav::isCheckoutPage());
    }

    public function test_tour_camp_and_trip_product_pages_skip_the_mobile_catalog_bar(): void
    {
        $this->bindNamedRequest('/guidings/offer/sea-trout', 'guidings.show');
        $this->assertTrue(SitePrimaryNav::isProductDetailPage());
        $this->assertFalse(SitePrimaryNav::usesLayoutBottomNav());

        $this->bindNamedRequest('/trips/sweden-trip', 'trips.show');
        $this->assertTrue(SitePrimaryNav::isProductDetailPage());
        $this->assertFalse(SitePrimaryNav::usesLayoutBottomNav());

        $this->bindNamedRequest('/vacations-v2/12', 'vacations.v2');
        $this->assertTrue(SitePrimaryNav::isProductDetailPage());
        $this->assertFalse(SitePrimaryNav::usesLayoutBottomNav());
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
