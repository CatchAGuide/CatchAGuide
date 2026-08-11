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

    public function test_guidings_catalog_header_partial_renders_site_nav_and_search_fields(): void
    {
        $html = View::make('pages.guidings.partials.catalog-header', [
            'listingTitle' => 'All Fishing Tours',
            'listingSubtitle' => '',
            'place' => null,
        ])->render();

        $this->assertStringContainsString('cag-site-nav', $html);
        $this->assertStringContainsString('cag-site-nav--solid', $html);
        $this->assertStringContainsString('guidings-page-header', $html);
        $this->assertStringContainsString('guidings-page-header__band', $html);
        $this->assertStringNotContainsString('guidings-page-header__image', $html);
        $this->assertStringContainsString('data-guidings-header-search', $html);
        $this->assertStringContainsString('guidingsCatalogSearchPlace', $html);
        $this->assertStringContainsString('name="num_guests"', $html);
        $this->assertStringContainsString('tagify-fish-guidings-catalog', $html);
        $this->assertStringContainsString('guidings-page-header__segment--fish', $html);
        $this->assertStringContainsString('name="place"', $html);
        $this->assertStringContainsString(route('guidings.index', [], false), $html);
    }

    public function test_places_deferred_inputs_include_guidings_catalog_search(): void
    {
        $source = (string) file_get_contents(resource_path('js/maps/places-entry.js'));

        $this->assertStringContainsString("'guidingsCatalogSearchPlace'", $source);
    }

    public function test_app_v2_1_layout_uses_site_header_for_guidings_index(): void
    {
        $source = (string) file_get_contents(resource_path('views/layouts/app-v2-1.blade.php'));

        $this->assertStringContainsString("routeIs('guidings.index')", $source);
        $this->assertStringContainsString('layouts.partials.site-mobile-menu', $source);
        $this->assertStringContainsString('layouts.partials.newheader-short', $source);
    }
}
