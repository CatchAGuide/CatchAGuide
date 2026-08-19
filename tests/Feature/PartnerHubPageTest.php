<?php

namespace Tests\Feature;

use App\Support\SitePrimaryNav;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PartnerHubPageTest extends TestCase
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

    public function test_partner_hub_uses_overlay_nav_and_site_footer(): void
    {
        $request = Request::create('/partner', 'GET');
        $route = new Route(['GET'], 'partner', static fn () => null);
        $route->name('additional.partner');
        $request->setRouteResolver(static fn () => $route);
        $this->app->instance('request', $request);

        $this->assertTrue(SitePrimaryNav::usesOverlayHeader());
        $this->assertFalse(SitePrimaryNav::usesLayoutNav());
        $this->assertFalse(SitePrimaryNav::usesLayoutPageHeader());
        $this->assertTrue(SitePrimaryNav::usesLayoutBottomNav());

        $response = $this->get(route('additional.partner'));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('cag-partner-hub', $html);
        $this->assertStringContainsString('cag-site-nav--overlay', $html);
        $this->assertStringNotContainsString('cag-site-nav--solid', $html);
        $this->assertStringNotContainsString('data-site-page-header-shell', $html);
        $this->assertStringNotContainsString('navbar-custom short-header', $html);
        $this->assertStringContainsString('cag-footer', $html);
        $this->assertStringContainsString('cag-home-bottom-nav', $html);
        $this->assertSame(1, substr_count($html, 'cag-site-nav--overlay'));
        $this->assertStringContainsString(__('partner.hero_title'), $html);
        $this->assertStringContainsString(__('partner.cta_become'), $html);
        $this->assertStringContainsString(__('partner.flow_title'), $html);
        $this->assertStringContainsString(__('partner.price_title'), $html);
        $this->assertStringContainsString('cag-partner-hub__faq-toggle', $html);
        $this->assertStringContainsString('cag-partner-hub__faq-panel', $html);
        $this->assertStringContainsString("classList.contains('is-open')", $html);
        $this->assertStringContainsString(route('additional.contact', [], false), $html);
        $this->assertStringContainsString(route('additional.partner', [], false), $html);
        $this->assertStringContainsString('id="guideApplicationModal"', $html);
        $this->assertMatchesRegularExpression(
            '/cag-partner-hub__inner[\s\S]*cag-partner-hub__quotes[\s\S]*cag-partner-hub__quote/',
            $html
        );
    }

    public function test_desktop_styles_share_the_site_container_and_keep_mobile_stack(): void
    {
        $scss = (string) file_get_contents(resource_path('sass/page/_partner-hub.scss'));

        $this->assertStringContainsString('@include cag-page-container', $scss);
        $this->assertStringNotContainsString('max-width: 1120px', $scss);
        $this->assertStringContainsString('flex-direction: column', $scss);
        $this->assertStringContainsString('grid-template-columns: repeat(3, 1fr)', $scss);
        $this->assertStringContainsString('overflow-x: auto', $scss);
        $this->assertStringContainsString('@include media(">=tablet")', $scss);
        $this->assertStringContainsString('grid-template-rows: 0fr', $scss);
        $this->assertStringContainsString('transition: grid-template-rows 0.32s ease', $scss);
        $this->assertStringContainsString('.cag-partner-hub__faq-item.is-open .cag-partner-hub__faq-panel', $scss);
        $this->assertStringNotContainsString('max-width: 22rem', $scss);
    }

    public function test_footer_become_partner_points_to_partner_hub(): void
    {
        $response = $this->get(route('additional.about_us'));

        $response->assertOk();
        $html = $response->getContent();
        $partnerPath = route('additional.partner', [], false);

        $this->assertStringContainsString(__('homepage.footer_become_partner'), $html);
        $this->assertGreaterThanOrEqual(2, substr_count($html, $partnerPath));
        $this->assertStringNotContainsString(
            'href="'.e(route('additional.contact')).'">'.__('homepage.footer_become_partner'),
            $html
        );
    }
}
