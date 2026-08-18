<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class SiteChromePagesTest extends TestCase
{
    use DatabaseTransactions;

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

    public function test_public_layouts_no_longer_include_legacy_navbar(): void
    {
        foreach ([
            resource_path('views/layouts/app.blade.php'),
            resource_path('views/layouts/app-v2.blade.php'),
            resource_path('views/layouts/app-v2-1.blade.php'),
        ] as $layout) {
            $source = (string) file_get_contents($layout);
            $this->assertStringContainsString('layouts.partials.site-chrome', $source);
            $this->assertStringNotContainsString('layouts.partials.newheader', $source);
            $this->assertStringNotContainsString('layouts.partials.newheader-short', $source);
        }
    }

    public function test_about_us_uses_page_header_band_instead_of_legacy_header(): void
    {
        $this->assertInnerPageHeader($this->get(route('additional.about_us')));
    }

    public function test_contact_uses_page_header_band_instead_of_legacy_header(): void
    {
        $this->assertInnerPageHeader($this->get(route('additional.contact')));
    }

    public function test_imprint_uses_page_header_band_instead_of_legacy_header(): void
    {
        $this->assertInnerPageHeader($this->get(route('law.imprint')));
    }

    public function test_profile_uses_page_header_band_instead_of_legacy_header(): void
    {
        $user = User::factory()->create([
            'is_guide' => false,
        ]);

        $response = $this->actingAs($user)->get(route('profile.index'));

        $this->assertInnerPageHeader($response);
        $response->assertSee('cag-site-nav__profile', false);
        $response->assertSee(route('profile.index'), false);
        $response->assertSee('offers-page-header__hero--compact', false);
    }

    public function test_destination_hub_keeps_a_single_overlay_nav(): void
    {
        $response = $this->get(route('destination'));

        $response->assertOk();
        $html = $response->getContent();
        $this->assertSame(1, substr_count($html, 'cag-site-nav--overlay'));
        $this->assertStringNotContainsString('cag-site-nav--solid', $html);
        $this->assertStringNotContainsString('navbar-custom short-header', $html);
        $this->assertStringContainsString('cag-home-bottom-nav', $html);
        $this->assertStringContainsString('has-cag-bottom-nav', $html);
    }

    public function test_inner_page_header_partial_renders_overlay_nav_and_search(): void
    {
        $html = View::make('layouts.partials.site-page-header')->render();

        $this->assertStringContainsString('data-site-page-header-shell', $html);
        $this->assertStringContainsString('cag-site-nav--overlay', $html);
        $this->assertStringContainsString('sitePageSearchPlace', $html);
        $this->assertStringContainsString('offers-page-header__hero--compact', $html);
        $this->assertStringContainsString(listing_search_action(), $html);
        $this->assertStringNotContainsString('navbar-custom short-header', $html);
    }

    private function assertInnerPageHeader($response): void
    {
        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('data-site-page-header-shell', $html);
        $this->assertStringContainsString('cag-site-nav--overlay', $html);
        $this->assertStringContainsString('sitePageSearchPlace', $html);
        $this->assertStringNotContainsString('cag-site-nav--solid', $html);
        $this->assertStringNotContainsString('navbar-custom short-header', $html);
        $this->assertStringContainsString('cag-home-bottom-nav', $html);
        $this->assertStringContainsString('has-cag-bottom-nav', $html);
        $this->assertSame(1, substr_count($html, 'cag-home-bottom-nav d-md-none'));
        $this->assertSame(1, substr_count($html, 'cag-site-nav--overlay'));
    }
}
