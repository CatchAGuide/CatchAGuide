<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\View;
use Tests\TestCase;

class VacationsCatalogHeaderTest extends TestCase
{
    public function test_vacations_catalog_header_partial_renders_site_nav_and_country_search(): void
    {
        $html = View::make('pages.vacations.partials.catalog-header', [
            'listingTitle' => 'Book your next fishing holiday',
            'listingSubtitle' => 'Choose between camps and trips',
            'breadcrumbItems' => [
                ['label' => 'Fishing Vacations', 'url' => null],
            ],
        ])->render();

        $this->assertStringContainsString('cag-site-nav', $html);
        $this->assertStringContainsString('cag-site-nav-shell', $html);
        $this->assertStringContainsString('cag-site-nav--overlay', $html);
        $this->assertStringContainsString('vacations-page-header', $html);
        $this->assertStringContainsString('vacations-page-header__band', $html);
        $this->assertStringNotContainsString('vacations-page-header__image', $html);
        $this->assertStringContainsString('data-vacations-header-search', $html);
        $this->assertStringContainsString('name="country"', $html);
        $this->assertStringContainsString('vacationsCatalogCountry', $html);
        $this->assertMatchesRegularExpression(
            '/vacations-page-header__search-btn[\s\S]{0,400}fa-arrow-right/',
            $html
        );
    }

    public function test_app_v2_layout_uses_site_header_for_vacations_listings(): void
    {
        $source = (string) file_get_contents(resource_path('views/layouts/app-v2.blade.php'));

        $this->assertStringContainsString('$useVacationsSiteHeader', $source);
        $this->assertStringContainsString("routeIs('vacations.index')", $source);
        $this->assertStringContainsString("routeIs('vacations.trips.show')", $source);
        $this->assertStringContainsString("routeIs('vacations.camps.show')", $source);
        $this->assertStringContainsString('vacation-page-loading-overlay', $source);
    }

    public function test_vacations_product_header_keeps_page_h1_and_uses_country_search(): void
    {
        $html = View::make('pages.vacations.partials.catalog-header', [
            'listingTitle' => 'Book your next fishing holiday',
            'listingSubtitle' => 'Choose between camps and trips',
            'titleTag' => 'p',
            'currentVacationCountry' => 'germany',
            'breadcrumbItems' => [
                ['label' => 'Fishing Vacations', 'url' => route('vacations.index')],
                ['label' => 'Hausboot Fürstenberg', 'url' => null],
            ],
        ])->render();

        $this->assertStringContainsString('<p class="vacations-page-header__title">', $html);
        $this->assertStringNotContainsString('<h1 class="vacations-page-header__title">', $html);
        $this->assertStringContainsString('vacationsCatalogCountry', $html);
        $this->assertStringContainsString('data-vacations-header-search', $html);
        $this->assertStringContainsString('Hausboot Fürstenberg', $html);
        $this->assertStringNotContainsString('categoryHeroSearchPlace', $html);
        $this->assertStringNotContainsString('data-offers-persons-stepper', $html);
        $this->assertStringNotContainsString('name="num_guests"', $html);
    }

    public function test_trip_and_camp_product_views_use_vacations_catalog_header(): void
    {
        $trip = (string) file_get_contents(resource_path('views/pages/trips/show.blade.php'));
        $camp = (string) file_get_contents(resource_path('views/pages/vacations/v2.blade.php'));
        $legacy = (string) file_get_contents(resource_path('views/pages/vacations/show.blade.php'));
        $layout = (string) file_get_contents(resource_path('views/layouts/app.blade.php'));

        $this->assertStringContainsString('pages.vacations.partials.catalog-header', $trip);
        $this->assertStringContainsString('pages.vacations.partials.catalog-header', $camp);
        $this->assertStringContainsString('pages.vacations.partials.catalog-header', $legacy);
        $this->assertStringNotContainsString('pages.category.partials.product-hero-header', $trip);
        $this->assertStringNotContainsString('pages.category.partials.product-hero-header', $camp);
        $this->assertStringNotContainsString('pages.category.partials.product-hero-header', $legacy);
        $this->assertStringNotContainsString('category-hero-header-script', $trip);
        $this->assertStringNotContainsString('category-hero-header-script', $camp);
        $this->assertStringNotContainsString('category-hero-header-script', $legacy);
        $this->assertStringContainsString('$useProductSiteHeader', $layout);
        $this->assertStringContainsString("'vacations.trips.show'", $layout);
        $this->assertStringContainsString("'vacations.camps.show'", $layout);
        $this->assertStringContainsString('vacation-page-loading-overlay', $layout);
    }

    public function test_catalog_search_bars_use_page_container_width_and_squared_corners(): void
    {
        $helpers = (string) file_get_contents(resource_path('sass/settings/_helpers.scss'));
        $this->assertStringContainsString('@mixin cag-page-container', $helpers);
        $this->assertStringContainsString('max-width: 1440px', $helpers);
        $this->assertStringContainsString('.container-xxl', $helpers);

        foreach ([
            resource_path('sass/page/offers.scss'),
            resource_path('sass/page/_vacations-header.scss'),
            resource_path('sass/page/guiding.scss'),
        ] as $file) {
            $source = (string) file_get_contents($file);
            $this->assertMatchesRegularExpression(
                '/&__inner \{\s*@include cag-page-container;/',
                $source
            );
            $this->assertMatchesRegularExpression(
                '/&__search-box \{[^}]*border-radius:\s*0\.75rem;/',
                $source
            );
            $this->assertDoesNotMatchRegularExpression(
                '/&__search-box \{[^}]*border-radius:\s*999px;/',
                $source
            );
        }

        $nav = (string) file_get_contents(resource_path('sass/components/_site-nav.scss'));
        $home = (string) file_get_contents(resource_path('sass/page/home.scss'));
        $this->assertStringContainsString('@include cag-page-container;', $nav);
        $this->assertStringContainsString('.cag-home-container {', $home);
        $this->assertMatchesRegularExpression(
            '/\.cag-home-container \{\s*@include cag-page-container;/',
            $home
        );
        $this->assertMatchesRegularExpression(
            '/\.cag-home-hero__inner \{\s*@include cag-page-container;/',
            $home
        );
        $this->assertStringNotContainsString('max-width: 1280px', $nav);
        $this->assertStringNotContainsString('max-width: 1280px', $home);
    }
}
