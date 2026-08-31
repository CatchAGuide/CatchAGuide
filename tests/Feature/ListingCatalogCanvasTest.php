<?php

namespace Tests\Feature;

use Tests\TestCase;

class ListingCatalogCanvasTest extends TestCase
{
    public function test_listing_lists_use_a_white_canvas_instead_of_the_grey_page_fill(): void
    {
        $helpers = (string) file_get_contents(resource_path('sass/settings/_helpers.scss'));
        $offers = (string) file_get_contents(resource_path('sass/page/offers.scss'));

        $this->assertMatchesRegularExpression(
            '/\.offers-catalog-page,\s*\.category-hero-page,\s*\.vacation-country,\s*\.vacation-pillar-index,\s*\.tours-list,\s*#trips-category \{\s*background:\s*#fff;/',
            $helpers
        );

        $this->assertDoesNotMatchRegularExpression(
            '/\.offers-catalog-page\s*\{[^}]*background:\s*\$bg/',
            $offers
        );
        $this->assertDoesNotMatchRegularExpression(
            '/\.offers-catalog-page\s*\{[^}]*background:\s*#f7f9fb/',
            $offers
        );
    }
}
