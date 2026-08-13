<?php

namespace Tests\Feature\Destination;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class DestinationHubHeaderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');
    }

    public function test_category_hero_header_partial_renders_overlay_nav_and_search(): void
    {
        $html = View::make('pages.category.partials.hero-header', [
            'listingTitle' => 'Fishing tours all over Europe',
            'listingSubtitle' => 'All destinations for your next fishing tour in Europe',
            'breadcrumbItems' => [
                ['label' => __('destination.breadcrumb'), 'url' => null],
            ],
        ])->render();

        $this->assertStringContainsString('cag-site-nav', $html);
        $this->assertStringContainsString('cag-site-nav-shell', $html);
        $this->assertStringContainsString('cag-site-nav--overlay', $html);
        $this->assertStringContainsString('data-category-header-shell', $html);
        $this->assertStringContainsString('offers-page-header__hero', $html);
        $this->assertStringContainsString('data-category-header-search', $html);
        $this->assertStringContainsString('categoryHeroSearchPlace', $html);
        $this->assertStringContainsString('name="num_guests"', $html);
        $this->assertStringContainsString(route('offers.index', [], false), $html);
        $this->assertStringContainsString(__('destination.breadcrumb'), $html);
    }

    public function test_app_v2_layout_uses_site_header_for_destination_and_targets(): void
    {
        $source = (string) file_get_contents(resource_path('views/layouts/app-v2.blade.php'));

        $this->assertStringContainsString('$useCategorySiteHeader', $source);
        $this->assertStringContainsString("'destination'", $source);
        $this->assertStringContainsString("'destination.country'", $source);
        $this->assertStringContainsString("'targets.index'", $source);
        $this->assertStringContainsString("'targets.show'", $source);
    }

    public function test_places_deferred_inputs_include_category_hero_search(): void
    {
        $source = (string) file_get_contents(resource_path('js/maps/places-entry.js'));

        $this->assertStringContainsString("'categoryHeroSearchPlace'", $source);
    }
}
