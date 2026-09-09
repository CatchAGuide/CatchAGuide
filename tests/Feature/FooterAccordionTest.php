<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class FooterAccordionTest extends TestCase
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

    public function test_footer_renders_accordion_groups_and_contact(): void
    {
        $response = $this->get(route('additional.about_us'));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('cag-footer__accordion', $html);
        $this->assertStringContainsString('cag-footer__group', $html);
        $this->assertStringContainsString('cag-footer__group-title', $html);
        $this->assertStringContainsString('cag-footer__group-panel', $html);
        $this->assertStringContainsString('cag-footer__top-link', $html);
        $this->assertStringContainsString(__('homepage.footer_all_experiences'), $html);
        $this->assertStringContainsString(route('offers.index', [], false), $html);
        $this->assertStringContainsString(__('homepage.footer_group_tours'), $html);
        $this->assertStringContainsString(__('homepage.footer_group_vacations'), $html);
        $this->assertStringContainsString(__('homepage.footer_group_providers'), $html);
        $this->assertStringContainsString(__('homepage.footer_group_about'), $html);
        $this->assertStringContainsString(__('message.legal'), $html);
        $this->assertStringContainsString(__('message.miscellaneous'), $html);
        $this->assertStringContainsString(__('homepage.footer_by_country'), $html);
        $this->assertStringContainsString(__('homepage.offers_camps_title'), $html);
        $this->assertStringContainsString(__('homepage.offers_trips_title'), $html);
        $this->assertStringContainsString(route('targets.index', [], false), $html);
        $this->assertStringContainsString(route('guidings.methods', [], false), $html);
        $this->assertStringContainsString(route('vacations.camps.index', [], false), $html);
        $this->assertStringContainsString(route('vacations.trips.index', [], false), $html);
        $this->assertStringContainsString('data-cag-footer-accordion', $html);
        $this->assertStringContainsString('cag-footer__contact--brand', $html);
        $this->assertStringContainsString('cag-footer__contact--mobile', $html);
        $this->assertSame(1, substr_count($html, 'cag-footer__contact--mobile'));
        $this->assertStringContainsString("classList.contains('is-open')", $html);
        $this->assertStringContainsString('facebook.com/CatchAGuide', $html);
        $this->assertStringContainsString('instagram.com/catchaguide_official', $html);
        $this->assertStringContainsString('wa.me/', $html);
        $this->assertStringContainsString('footer-widget__social', $html);
        $this->assertStringContainsString('info.catchaguide@gmail.com', $html);
        $this->assertStringContainsString('cag-footer__langs', $html);
        $this->assertStringContainsString('cag-footer__lang is-active', $html);
        $this->assertStringContainsString('name="language"', $html);
        $this->assertStringContainsString('value="de"', $html);
        $this->assertStringContainsString('value="en"', $html);
        $this->assertStringContainsString('© '.now()->year.' Catch A Guide', $html);
        $this->assertStringNotContainsString('<details', $html);
    }

    public function test_bottom_nav_keeps_gray_inactive_and_coral_active_styles(): void
    {
        $home = (string) file_get_contents(resource_path('sass/page/home.scss'));
        $mobile = (string) file_get_contents(resource_path('sass/page/_home-mobile.scss'));

        $this->assertMatchesRegularExpression(
            '/\.cag-home-bottom-nav__item\s*\{[\s\S]*color:\s*var\(--cag-gray\)[\s\S]*&\.is-active\s*\{[\s\S]*color:\s*var\(--cag-coral\)/',
            $home
        );
        $this->assertStringNotContainsString('.cag-home-bottom-nav__item', $mobile);
        $this->assertMatchesRegularExpression(
            '/\.cag-home-bottom-nav\s*\{[\s\S]*overflow:\s*visible[\s\S]*&::after\s*\{[\s\S]*top:\s*100%[\s\S]*height:\s*100vh/',
            $home
        );
    }

    public function test_footer_mobile_styles_hide_duplicate_contact_and_scroll_top(): void
    {
        $scss = (string) file_get_contents(resource_path('sass/layout/_footer.scss'));

        $this->assertStringContainsString('.cag-footer__contact--brand', $scss);
        $this->assertStringContainsString('display: none !important', $scss);
        $this->assertStringContainsString('a.scroll-to-top', $scss);
        $this->assertMatchesRegularExpression(
            '/\.cag-footer__contact--mobile,\s*\.cag-footer__langs\s*\{\s*display:\s*none/',
            $scss
        );
        $this->assertStringContainsString('grid-template-rows: 0fr', $scss);
        $this->assertStringContainsString('.cag-footer__group.is-open .cag-footer__group-panel', $scss);
        $this->assertStringContainsString('display: flex !important', $scss);
        $this->assertMatchesRegularExpression(
            '/@include media\(">=tablet"\)\s*\{\s*\.cag-footer__accordion\s*\{\s*display:\s*grid/',
            $scss
        );
    }

    public function test_destination_tiles_keep_flag_styles_on_mobile(): void
    {
        $mobile = (string) file_get_contents(resource_path('sass/page/_home-mobile.scss'));

        $this->assertDoesNotMatchRegularExpression(
            '/\.cag-home-destinations__flag\s*\{\s*display:\s*none/',
            $mobile
        );
        $this->assertStringNotContainsString('.cag-home-destinations__code', $mobile);
    }
}
