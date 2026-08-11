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
        $this->assertStringContainsString('cag-site-nav--solid', $html);
        $this->assertStringContainsString('vacations-page-header', $html);
        $this->assertStringContainsString('vacations-page-header__band', $html);
        $this->assertStringNotContainsString('vacations-page-header__image', $html);
        $this->assertStringContainsString('data-vacations-header-search', $html);
        $this->assertStringContainsString('name="country"', $html);
        $this->assertStringContainsString('vacationsCatalogCountry', $html);
    }

    public function test_app_v2_layout_uses_site_header_for_vacations_listings(): void
    {
        $source = (string) file_get_contents(resource_path('views/layouts/app-v2.blade.php'));

        $this->assertStringContainsString('$useVacationsSiteHeader', $source);
        $this->assertStringContainsString("routeIs('vacations.index')", $source);
        $this->assertStringContainsString('vacation-page-loading-overlay', $source);
    }
}
