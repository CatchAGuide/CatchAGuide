<?php

namespace Tests\Feature\Seo;

use Tests\TestCase;

class CookieConsentBannerTest extends TestCase
{
    /**
     * GA4 only counts visitors who accept (Consent Mode defaults to denied), so the banner must offer
     * Accept and Reject on its first screen, in the page's language, and stay reachable from the footer.
     */
    public function test_banner_offers_accept_and_reject_in_the_page_language(): void
    {
        $cases = [
            'en' => ['Accept all', 'Reject all', 'Cookie settings'],
            'de' => ['Alle akzeptieren', 'Alle ablehnen', 'Cookie-Einstellungen'],
        ];

        // The domain picks the locale only in production; locally the session override does.
        foreach ($cases as $locale => [$accept, $reject, $settings]) {
            $html = $this->withSession(['locale' => $locale])->get('http://catchaguide.com/about-us')->assertOk()->getContent();

            $this->assertSame(1, substr_count($html, 'id="cookie-consent-banner"'), $locale);
            $this->assertStringContainsString('id="cookie-accept" class="btn theme-primary rounded-0">'.$accept.'</button>', $html);
            $this->assertStringContainsString('id="cookie-decline" class="btn btn-outline-dark rounded-0">'.$reject.'</button>', $html);
            $this->assertStringContainsString('href="#cookie-settings"', $html);
            $this->assertStringContainsString('>'.$settings.'</a>', $html);
        }
    }

    public function test_choices_are_remembered_for_a_year(): void
    {
        $html = $this->get('http://catchaguide.com/about-us')->assertOk()->getContent();

        $this->assertStringContainsString("setCookie('default_cookie', ONE_YEAR)", $html);
        $this->assertStringNotContainsString("setCookie('default_cookie', 'true');", $html);
        $this->assertStringNotContainsString('fadeIn(2000)', $html);
    }
}
