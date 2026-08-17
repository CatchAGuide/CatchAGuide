<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class GuidingsCatalogHeaderTest extends TestCase
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

    public function test_guidings_listing_uses_offers_style_hero_header_without_target_fish(): void
    {
        $html = View::make('pages.category.partials.hero-header', [
            'listingTitle' => 'All Fishing Tours',
            'listingSubtitle' => '',
            'searchAction' => route('guidings.index'),
            'breadcrumbItems' => [
                ['label' => __('homepage.filter-fishing-near-me'), 'url' => null],
            ],
        ])->render();

        $this->assertStringContainsString('cag-site-nav', $html);
        $this->assertStringContainsString('cag-site-nav-shell', $html);
        $this->assertStringContainsString('cag-site-nav--overlay', $html);
        $this->assertStringContainsString('offers-page-header__hero', $html);
        $this->assertStringNotContainsString('offers-page-header__image', $html);
        $this->assertStringNotContainsString('hero-tour.webp', $html);
        $this->assertStringContainsString('data-category-header-search', $html);
        $this->assertStringContainsString('categoryHeroSearchPlace', $html);
        $this->assertStringContainsString('data-offers-persons-stepper', $html);
        $this->assertStringContainsString('name="num_guests"', $html);
        $this->assertMatchesRegularExpression(
            '/offers-page-header__search-btn[\s\S]{0,400}fa-arrow-right/',
            $html
        );
        $this->assertStringContainsString(route('guidings.index', [], false), $html);
        $this->assertStringNotContainsString('guidings-page-header__band', $html);
        $this->assertStringNotContainsString('tagify-fish-guidings-catalog', $html);
        $this->assertStringNotContainsString('guidings-page-header__segment--fish', $html);
    }

    public function test_shared_catalog_header_uses_gray_background_without_hero_image(): void
    {
        $source = (string) file_get_contents(resource_path('sass/page/offers.scss'));

        $this->assertMatchesRegularExpression(
            '/&__hero \{[^}]*background:\s*\$slate;/',
            $source
        );
        $this->assertDoesNotMatchRegularExpression(
            '/&__hero \{[^}]*background-image:/',
            $source
        );
    }

    public function test_app_v2_layout_uses_site_header_for_guidings_listings(): void
    {
        $source = (string) file_get_contents(resource_path('views/layouts/app-v2.blade.php'));

        $this->assertStringContainsString('$useCategorySiteHeader', $source);
        $this->assertStringContainsString("'guidings.landing'", $source);
        $this->assertStringContainsString("'guidings.index'", $source);
        $this->assertStringContainsString("'guidings.destination'", $source);
        $this->assertStringContainsString("'guidings.countries'", $source);
        $this->assertStringContainsString("'guidings.methods'", $source);
        $this->assertStringContainsString("'guidings.targets'", $source);
        $this->assertStringContainsString("'guidings.show'", $source);
    }

    public function test_guidings_product_page_uses_offers_style_header(): void
    {
        $source = (string) file_get_contents(resource_path('views/pages/guidings/newIndex.blade.php'));

        $this->assertStringContainsString("@extends('layouts.app-v2')", $source);
        $this->assertStringContainsString('pages.category.partials.product-hero-header', $source);
        $this->assertStringContainsString('data-category-hero-page', $source);
        $this->assertStringNotContainsString('navbar-custom', $source);
    }

    public function test_product_hero_header_keeps_page_h1_and_submits_to_guidings_catalog(): void
    {
        $html = View::make('pages.category.partials.product-hero-header', [
            'listingTitle' => 'Fishing Tours',
            'searchAction' => route('guidings.index'),
            'breadcrumbItems' => [
                ['label' => 'Fishing Tours', 'url' => route('guidings.index')],
                ['label' => 'Brown trout in Spain', 'url' => null],
            ],
        ])->render();

        $this->assertStringContainsString('cag-site-nav--overlay', $html);
        $this->assertStringContainsString('data-category-header-search', $html);
        $this->assertStringContainsString('<p class="offers-page-header__title', $html);
        $this->assertStringNotContainsString('<h1 class="offers-page-header__title', $html);
        $this->assertStringContainsString(route('guidings.index', [], false), $html);
        $this->assertStringContainsString('Brown trout in Spain', $html);
    }

    public function test_guidings_listing_does_not_cap_bootstrap_container_at_1200px(): void
    {
        $source = (string) file_get_contents(resource_path('views/pages/guidings/index.blade.php'));

        $this->assertStringNotContainsString('max-width: 1200px', $source);
        $this->assertStringContainsString('class="container"', $source);
    }

}
