<?php

namespace Tests\Feature;

use App\Enums\GuideStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class SiteNavTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');
    }

    public function test_guest_login_link_uses_flex_alignment_with_nav_actions(): void
    {
        $html = View::make('layouts.partials.site-nav', [
            'overlay' => true,
            'idPrefix' => 'site',
        ])->render();

        $this->assertMatchesRegularExpression(
            '/class="cag-site-nav__login[^"]*d-md-inline-flex/',
            $html
        );
        $this->assertDoesNotMatchRegularExpression(
            '/class="cag-site-nav__login[^"]*\bd-md-inline"/',
            $html
        );
        $this->assertStringContainsString('cag-site-nav__cta', $html);
        $this->assertStringContainsString(__('homepage.header-login'), $html);
        $this->assertStringContainsString(__('homepage.filter-fishing-near-me'), $html);
        $this->assertStringContainsString(__('homepage.header-become-guide'), $html);
        $this->assertPrimaryNavOrder($html);
        $this->assertStringNotContainsString('aria-current="page"', $html);
        $this->assertStringContainsString('fa-bars', $html);
        $this->assertStringNotContainsString('fa-user-circle', $html);
        $this->assertStringContainsString('data-bs-target="#mobileMenuModal"', $html);
        $this->assertStringContainsString(__('homepage.header-menu'), $html);
    }

    public function test_offers_listing_highlights_all_offers(): void
    {
        $this->bindNamedRequest('/offers', 'offers.index');

        $html = View::make('layouts.partials.site-nav', [
            'overlay' => true,
            'idPrefix' => 'site',
        ])->render();

        $this->assertPrimaryNavOrder($html);
        $this->assertNavItemIsActive($html, route('offers.index'));
        $this->assertNavItemIsInactive($html, route('guidings.landing'));
        $this->assertNavItemIsInactive($html, route('vacations.index'));
    }

    public function test_guidings_landing_highlights_fishing_tours(): void
    {
        $this->bindNamedRequest('/guidings', 'guidings.landing');

        $html = View::make('layouts.partials.site-nav', [
            'overlay' => true,
            'idPrefix' => 'site',
        ])->render();

        $this->assertNavItemIsActive($html, route('guidings.landing'));
        $this->assertNavItemIsInactive($html, route('offers.index'));
    }

    public function test_vacations_listing_highlights_fishing_vacations(): void
    {
        $this->bindNamedRequest('/vacations', 'vacations.index');

        $html = View::make('layouts.partials.site-nav', [
            'overlay' => true,
            'idPrefix' => 'site',
        ])->render();

        $this->assertNavItemIsActive($html, route('vacations.index'));
        $this->assertNavItemIsInactive($html, route('offers.index'));
    }

    public function test_mobile_menu_matches_desktop_order_and_active_state(): void
    {
        $this->bindNamedRequest('/offers', 'offers.index');

        $html = View::make('layouts.partials.site-mobile-menu')->render();

        $this->assertPrimaryNavOrder($html);
        $this->assertStringContainsString('cag-site-mobile-menu__item is-active', $html);
        $this->assertNavItemIsActive($html, route('offers.index'));
        $this->assertStringContainsString(__('homepage.footer_destinations'), $html);
        $this->assertStringContainsString(__('homepage.header-login'), $html);
    }

    public function test_mobile_menu_restores_legacy_support_items(): void
    {
        config(['guide_onboarding.new_onboarding_enabled' => true]);

        $html = View::make('layouts.partials.site-mobile-menu')->render();

        $this->assertStringContainsString(__('homepage.filter-magazine'), $html);
        $this->assertStringContainsString(__('homepage.header-signup'), $html);
        $this->assertStringContainsString(__('homepage.header-language'), $html);
        $this->assertStringContainsString(__('homepage.header-become-guide'), $html);
        $this->assertStringContainsString('info.catchaguide@gmail.com', $html);
        $this->assertStringContainsString('facebook.com/CatchAGuide', $html);
        $this->assertStringContainsString('instagram.com/catchaguide_official', $html);
        $this->assertStringContainsString('fa-book-open', $html);
        $this->assertStringContainsString('cag-site-mobile-menu__group', $html);
        $this->assertStringContainsString('cag-site-mobile-menu__langs', $html);
        $this->assertStringContainsString('data-bs-target="#registerModal"', $html);
        $this->assertStringContainsString('data-bs-target="#loginModal"', $html);
        $this->assertStringNotContainsString('cag-site-mobile-menu__eyebrow', $html);
    }

    public function test_mobile_cta_is_compact_on_small_screens(): void
    {
        $html = View::make('layouts.partials.site-nav', [
            'overlay' => true,
            'idPrefix' => 'site',
        ])->render();
        $scss = (string) file_get_contents(resource_path('sass/components/_site-nav.scss'));

        $this->assertStringContainsString('cag-site-nav__cta-short', $html);
        $this->assertStringContainsString(__('homepage.header-become-guide-short'), $html);
        $this->assertMatchesRegularExpression(
            '/\.cag-site-nav__cta[\s\S]*@media \(max-width: 767\.98px\)[\s\S]*font-size: 0\.68rem/',
            $scss
        );
        $this->assertMatchesRegularExpression(
            '/\.cag-site-nav__cta[\s\S]*@media \(max-width: 767\.98px\)[\s\S]*border: 0/',
            $scss
        );
    }

    public function test_become_guide_cta_is_hidden_for_verified_guides(): void
    {
        config(['guide_onboarding.new_onboarding_enabled' => true]);

        $user = User::factory()->create([
            'is_guide' => 1,
            'guide_status' => GuideStatus::VERIFIED,
        ]);

        $this->actingAs($user);
        $html = View::make('layouts.partials.site-nav', [
            'overlay' => true,
            'idPrefix' => 'site',
        ])->render();

        $this->assertStringNotContainsString('cag-site-nav__cta', $html);
        $this->assertStringNotContainsString(__('homepage.header-become-guide'), $html);
        $this->assertStringNotContainsString(__('homepage.header-become-guide-short'), $html);
        $this->assertStringContainsString('fa-bars', $html);
        $this->assertStringNotContainsString('cag-site-nav__avatar-btn', $html);
        $this->assertStringNotContainsString('fa-user-circle', $html);
    }

    public function test_become_guide_cta_is_shown_for_customers(): void
    {
        config(['guide_onboarding.new_onboarding_enabled' => true]);

        $user = User::factory()->create([
            'is_guide' => 0,
            'guide_status' => null,
        ]);

        $this->actingAs($user);
        $html = View::make('layouts.partials.site-nav', [
            'overlay' => true,
            'idPrefix' => 'site',
        ])->render();

        $this->assertStringContainsString('cag-site-nav__cta', $html);
        $this->assertStringContainsString(__('homepage.header-become-guide'), $html);
        $this->assertStringContainsString('fa-bars', $html);
        $this->assertStringNotContainsString('cag-site-nav__avatar-btn', $html);
    }

    public function test_mobile_bottom_nav_uses_burger_catalog_links(): void
    {
        $this->bindNamedRequest('/', 'welcome');

        $html = View::make('pages.home.partials.mobile-bottom-nav')->render();

        $this->assertPrimaryNavOrder($html);
        $this->assertStringContainsString(__('homepage.footer_destinations'), $html);
        $this->assertStringContainsString(__('homepage.header-login'), $html);
        $this->assertStringContainsString('cag-home-bottom-nav__item', $html);
        $this->assertStringContainsString('cag-icon--nav-grid', $html);
        $this->assertStringContainsString('cag-icon--nav-rod', $html);
        $this->assertStringContainsString('cag-icon--nav-camp', $html);
        $this->assertStringContainsString('cag-icon--nav-pin', $html);
        $this->assertStringContainsString('cag-icon--nav-user', $html);
        $this->assertStringContainsString('data-bs-target="#loginModal"', $html);
        $this->assertStringNotContainsString(__('homepage.mobile_nav_explore'), $html);
        $this->assertStringNotContainsString(__('homepage.mobile_nav_bookings'), $html);
        $this->assertStringNotContainsString(__('homepage.mobile_nav_saved'), $html);
        $this->assertStringNotContainsString('aria-current="page"', $html);
    }

    public function test_mobile_bottom_nav_highlights_active_catalog_section(): void
    {
        $this->bindNamedRequest('/guidings', 'guidings.landing');

        $html = View::make('pages.home.partials.mobile-bottom-nav')->render();

        $this->assertNavItemIsActive($html, route('guidings.landing'));
        $this->assertNavItemIsInactive($html, route('offers.index'));
        $this->assertNavItemIsInactive($html, route('vacations.index'));
    }

    public function test_layout_bottom_nav_renders_on_catalog_pages_but_not_product_or_checkout(): void
    {
        $this->bindNamedRequest('/offers', 'offers.index');
        $html = View::make('layouts.partials.site-bottom-nav')->render();
        $this->assertStringContainsString('cag-home-bottom-nav', $html);

        $this->bindNamedRequest('/guidings/offer/sea-trout', 'guidings.show');
        $html = View::make('layouts.partials.site-bottom-nav')->render();
        $this->assertStringNotContainsString('cag-home-bottom-nav', $html);

        $this->bindNamedRequest('/trips/sweden-trip', 'trips.show');
        $html = View::make('layouts.partials.site-bottom-nav')->render();
        $this->assertStringNotContainsString('cag-home-bottom-nav', $html);

        $this->bindNamedRequest('/vacations-v2/12', 'vacations.v2');
        $html = View::make('layouts.partials.site-bottom-nav')->render();
        $this->assertStringNotContainsString('cag-home-bottom-nav', $html);

        $this->bindNamedRequest('/checkout', 'checkout.index');
        $html = View::make('layouts.partials.site-bottom-nav')->render();
        $this->assertStringNotContainsString('cag-home-bottom-nav', $html);
    }

    public function test_overlay_nav_styles_keep_active_link_visible(): void
    {
        $scss = (string) file_get_contents(resource_path('sass/components/_site-nav.scss'));

        $this->assertStringContainsString('&.is-active::after', $scss);
        $this->assertStringContainsString('.cag-site-nav-shell .cag-site-nav:not(.is-solid)', $scss);
        $this->assertStringContainsString('#mobileMenuModal', $scss);
        $this->assertStringContainsString('.has-cag-bottom-nav', $scss);
        $this->assertStringContainsString('appearance: none', $scss);
        $this->assertMatchesRegularExpression(
            '/\.cag-site-nav-shell \.cag-site-nav:not\(\.is-solid\)[\s\S]*&\\.is-active::after/',
            $scss
        );
    }

    private function assertPrimaryNavOrder(string $html): void
    {
        $offersPos = strpos($html, __('offers.nav_label'));
        $toursPos = strpos($html, __('homepage.filter-fishing-near-me'));
        $vacationsPos = strpos($html, __('homepage.header-vacations'));

        $this->assertNotFalse($offersPos);
        $this->assertNotFalse($toursPos);
        $this->assertNotFalse($vacationsPos);
        $this->assertLessThan($toursPos, $offersPos);
        $this->assertLessThan($vacationsPos, $toursPos);
    }

    private function assertNavItemIsActive(string $html, string $url): void
    {
        $this->assertMatchesRegularExpression(
            '/href="'.preg_quote($url, '/').'"[^>]*(?:class="[^"]*is-active[^"]*"|aria-current="page")/',
            $html
        );
        $this->assertStringContainsString('aria-current="page"', $html);
    }

    private function assertNavItemIsInactive(string $html, string $url): void
    {
        $this->assertDoesNotMatchRegularExpression(
            '/href="'.preg_quote($url, '/').'"[^>]*class="[^"]*is-active/',
            $html
        );
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
