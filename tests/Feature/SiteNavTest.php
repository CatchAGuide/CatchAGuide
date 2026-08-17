<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class SiteNavTest extends TestCase
{
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
    }

    public function test_overlay_nav_styles_keep_active_link_visible(): void
    {
        $scss = (string) file_get_contents(resource_path('sass/components/_site-nav.scss'));

        $this->assertStringContainsString('&.is-active::after', $scss);
        $this->assertStringContainsString('.cag-site-nav-shell .cag-site-nav:not(.is-solid)', $scss);
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
