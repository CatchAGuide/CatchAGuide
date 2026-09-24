<?php

namespace Tests\Feature;

use App\Support\SitePrimaryNav;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
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
        $layout = (string) file_get_contents(resource_path('views/layouts/app-v2.blade.php'));
        $chrome = (string) file_get_contents(resource_path('views/layouts/partials/site-chrome.blade.php'));

        $this->assertStringContainsString('layouts.partials.site-chrome', $layout);
        $this->assertStringNotContainsString('layouts.partials.newheader', $layout);
        $this->assertStringContainsString('layouts.partials.site-nav', $chrome);
        $this->assertTrue(SitePrimaryNav::usesOverlayHeader(
            $this->namedRequest('/guidings', 'guidings.landing')
        ));
        $this->assertTrue(SitePrimaryNav::usesOverlayHeader(
            $this->namedRequest('/guidings/alloffers', 'guidings.index')
        ));
        $this->assertTrue(SitePrimaryNav::usesOverlayHeader(
            $this->namedRequest('/guidings/offer/sea-trout', 'guidings.show')
        ));
    }

    public function test_guidings_product_page_uses_offers_style_header(): void
    {
        $source = (string) file_get_contents(resource_path('views/pages/guidings/newIndex.blade.php'));

        $this->assertStringContainsString("@extends('layouts.app-v2')", $source);
        $this->assertStringContainsString('pages.category.partials.product-hero-header', $source);
        $this->assertStringContainsString('data-category-hero-page', $source);
        $this->assertStringContainsString("'listingTitle' => \$guiding->title", $source);
        $this->assertStringContainsString("'hubTitle' => __('homepage.filter-fishing-near-me')", $source);
        // The hero header owns the page's only <h1>; the body repeats the title visually only.
        $this->assertStringNotContainsString('<h1>{{ $guiding->title }}</h1>', $source);
        $this->assertStringContainsString('<p class="h1">{{ $guiding->title }}</p>', $source);
        $this->assertStringNotContainsString('navbar-custom', $source);
    }

    public function test_product_hero_header_uses_listing_title_as_h1_with_compact_search(): void
    {
        $html = View::make('pages.category.partials.product-hero-header', [
            'listingTitle' => 'Pike guiding on Kummerow Lake',
            'searchAction' => route('guidings.index'),
            'breadcrumbItems' => [
                ['label' => 'Fishing Tours', 'url' => route('guidings.index')],
            ],
            'locationLabel' => 'Kummerow, Mecklenburgische Seenplatte',
            'mapHref' => '#map',
            'ratingScore' => 10,
            'reviewsCount' => 5,
        ])->render();

        $this->assertStringContainsString('cag-site-nav--overlay', $html);
        $this->assertStringContainsString('offers-page-header--product', $html);
        $this->assertStringContainsString('data-product-hero-header', $html);
        $this->assertStringContainsString('data-category-header-search', $html);
        $this->assertStringContainsString('<h1 class="offers-page-header__title', $html);
        $this->assertStringContainsString('offers-page-header__title--product', $html);
        $this->assertStringContainsString('offers-page-header__title--hub', $html);
        $this->assertStringContainsString('Pike guiding on Kummerow Lake', $html);
        $this->assertStringContainsString(__('homepage.filter-fishing-near-me'), $html);
        $this->assertStringContainsString('offers-page-header__breadcrumbs', $html);
        $this->assertStringContainsString('offers-page-header__breadcrumbs--below', $html);
        $this->assertStringNotContainsString('offers-page-header__breadcrumbs--hero', $html);
        $this->assertSame(1, substr_count($html, 'aria-label="Breadcrumb"'));
        $this->assertStringNotContainsString('<p class="offers-page-header__title offers-page-header__anim"', $html);
        $this->assertStringContainsString('Kummerow, Mecklenburgische Seenplatte', $html);
        $this->assertStringContainsString('#map', $html);
        $this->assertStringContainsString(__('guidings.show_on_map'), $html);
        $this->assertStringContainsString('offers-page-header__rating', $html);
        $this->assertStringContainsString('id="rating-score-link"', $html);
        $this->assertStringContainsString(trans_choice('offers.reviews_count', 5, ['count' => 5]), $html);
        $this->assertStringContainsString(__('offers.search_field'), $html);
        $this->assertStringContainsString(__('offers.search_change'), $html);
        $this->assertStringContainsString(__('offers.search_submit'), $html);
        $this->assertStringContainsString('fa-arrow-right', $html);
        $this->assertStringContainsString('offers-page-header__search-btn-label--compact', $html);
        $this->assertStringContainsString('offers-page-header__search-btn-label--listing', $html);
        $this->assertStringContainsString(route('guidings.index', [], false), $html);
        $this->assertStringContainsString('name="place"', $html);
        $this->assertStringContainsString('name="num_guests"', $html);
        $this->assertStringContainsString('offers-persons-stepper--catalog', $html);
        $this->assertStringContainsString('offers-page-header__search-row', $html);
        $this->assertStringContainsString('data-offers-persons-delta="-1"', $html);
        $this->assertStringContainsString('data-offers-persons-delta="1"', $html);
        $this->assertStringNotContainsString('data-offers-persons-popup', $html);
        $this->assertMatchesRegularExpression(
            '/offers-page-header__search-row[\s\S]*offers-page-header__search-btn[\s\S]*<\/div>\s*(?:<div class="mobile-search-sheet__chips">[\s\S]*?<\/div>\s*)?<\/div>/',
            $html
        );
    }

    public function test_product_hero_search_stays_a_single_row_when_opened(): void
    {
        $source = (string) file_get_contents(resource_path('sass/page/offers.scss'));

        $this->assertStringContainsString('&--product {', $source);
        $this->assertStringContainsString('.offers-page-header__title--hub', $source);
        $this->assertStringContainsString('.offers-page-header__title--product', $source);
        $this->assertMatchesRegularExpression(
            '/&__breadcrumbs \{[\s\S]*?@media \(max-width: 767\.98px\) \{[\s\S]*?order:\s*-1;[\s\S]*?padding-top:\s*0\.35rem;[\s\S]*?background:\s*#fff;/',
            $source
        );
        $this->assertDoesNotMatchRegularExpression(
            '/\.offers-page-header__breadcrumbs--below \{[\s\S]*?display:\s*none;/',
            $source
        );
        $this->assertStringNotContainsString('offers-page-header__breadcrumbs--hero', $source);
        $this->assertMatchesRegularExpression(
            '/@media \(max-width: 767\.98px\) \{[\s\S]*?grid-template-areas:[\s\S]*?"title"[\s\S]*?"place"[\s\S]*?"rating"/',
            $source
        );
        $this->assertDoesNotMatchRegularExpression(
            '/grid-template-areas:[\s\S]*?"crumbs"/',
            $source
        );
        $this->assertStringContainsString('[data-mobile-search-sheet].is-open', $source);
        $this->assertStringContainsString('flex-direction: column', $source);
        $this->assertStringContainsString('.mobile-search-sheet__chips', $source);
        $this->assertStringContainsString('.offers-page-header__search-row', $source);
        $this->assertMatchesRegularExpression(
            '/\[data-mobile-search-sheet\]\.is-open \{[\s\S]*?\.offers-page-header__segment \{[\s\S]*?border:\s*1\.5px solid[\s\S]*?border-radius:\s*999px;/',
            $source
        );
        $this->assertDoesNotMatchRegularExpression(
            '/\[data-mobile-search-sheet\]\.is-open \{[\s\S]*?\.offers-page-header__segment \{[\s\S]*?&--where,[\s\S]*?&--who \{[\s\S]*?border-right:\s*0;/',
            $source
        );
        $this->assertMatchesRegularExpression(
            '/\.offers-page-header__segment-control > i\.offers-page-header__where-icon--listing \{[\s\S]*?display:\s*none;/',
            $source
        );
        $this->assertMatchesRegularExpression(
            '/&--product \{[\s\S]*?\.mobile-search-sheet__chips,[\s\S]*?\.offers-page-header__search-btn-label--sheet,[\s\S]*?\.offers-page-header__search-btn-label--compact \{[\s\S]*?display:\s*none;/',
            $source
        );
        $this->assertMatchesRegularExpression(
            '/@media \(min-width: 768px\) \{[\s\S]*?\.offers-page-header__search-row \{[\s\S]*?grid-template-columns:[\s\S]*?auto;/',
            $source
        );
    }

    public function test_product_hero_header_renders_mobile_search_sheet_with_summary(): void
    {
        $html = View::make('pages.category.partials.product-hero-header', [
            'listingTitle' => 'Pike guiding on Kummerow Lake',
            'searchAction' => route('guidings.index'),
            'breadcrumbItems' => [],
            'placeValue' => 'Kummerow',
        ])->render();

        $this->assertStringContainsString('data-mobile-search-sheet-open', $html);
        $this->assertStringContainsString('mobile-search-sheet__trigger-summary', $html);
        $this->assertStringContainsString('Kummerow', $html);
        $this->assertStringContainsString(trans_choice('offers.persons_count', 1, ['count' => 1]), $html);
        $this->assertStringContainsString('data-mobile-search-sheet', $html);
        $this->assertStringContainsString(__('offers.search_mobile_sheet_title_tours'), $html);
        $this->assertStringContainsString(__('offers.search_where'), $html);
        $this->assertStringContainsString(__('offers.search_who'), $html);
        $this->assertStringContainsString(__('offers.search_submit'), $html);
        $this->assertStringNotContainsString('mobile-search-sheet__chips', $html);
        $this->assertStringNotContainsString('class="mobile-search-sheet__chip"', $html);
        $this->assertStringContainsString('fa-map-marker-alt', $html);
        $this->assertStringContainsString('id="categoryHeroSearchPlace"', $html);
        $placesEntry = (string) file_get_contents(resource_path('js/maps/places-entry.js'));
        $this->assertStringContainsString("'categoryHeroSearchPlace'", $placesEntry);
        // The sheet wraps the same form/fields -- no duplicate place input or guest field.
        $this->assertSame(1, substr_count($html, 'id="categoryHeroSearchPlace"'));
        $this->assertSame(1, substr_count($html, '<input type="hidden" name="num_guests"'));
    }

    public function test_guidings_listing_does_not_cap_bootstrap_container_at_1200px(): void
    {
        $source = (string) file_get_contents(resource_path('views/pages/guidings/index.blade.php'));

        $this->assertStringNotContainsString('max-width: 1200px', $source);
        $this->assertStringContainsString('class="container"', $source);
    }

    private function namedRequest(string $uri, string $routeName): Request
    {
        $request = Request::create($uri, 'GET');
        $route = new Route(['GET'], ltrim($uri, '/'), static fn () => null);
        $route->name($routeName);
        $request->setRouteResolver(static fn () => $route);

        return $request;
    }

}
