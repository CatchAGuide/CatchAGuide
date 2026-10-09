<?php

namespace Tests\Feature\Seo;

use Tests\TestCase;

class TagManagerHeadTest extends TestCase
{
    /**
     * GA4 and Clarity come from the GTM container (one property/project for both domains, tagged with
     * site_language), so the page itself must only ship consent defaults + GTM — inline gtag.js double-counted.
     */
    public function test_layout_loads_only_gtm_with_consent_defaults_on_both_domains(): void
    {
        config(['services.google_tag_manager.container_id' => 'GTM-TEST123']);

        foreach (['http://catchaguide.com/about-us', 'http://catchaguide.de/about-us'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $consent = strpos($html, "gtag('consent', 'default'");
            $gtm = strpos($html, '"GTM-TEST123"');
            $this->assertNotFalse($consent, $url);
            $this->assertNotFalse($gtm, $url);
            $this->assertLessThan($gtm, $consent, "Consent defaults must be set before GTM loads ({$url}).");
            $this->assertStringContainsString("clarity('consentv2'", $html);

            $this->assertStringNotContainsString('googletagmanager.com/gtag/js', $html);
            $this->assertStringNotContainsString('clarity.ms/tag/', $html);
            $this->assertStringNotContainsString('i9xet5addk', $html);
        }
    }

    public function test_gtm_still_loads_when_a_stale_config_cache_lacks_the_key(): void
    {
        config(['services.google_tag_manager' => []]);

        $html = $this->get('http://catchaguide.de/about-us')->assertOk()->getContent();

        $this->assertStringContainsString('"GTM-K6VGF9NQ"', $html);
    }

    public function test_nothing_is_loaded_without_a_container_id(): void
    {
        config(['services.google_tag_manager.container_id' => null]);

        $html = $this->get('http://catchaguide.com/about-us')->assertOk()->getContent();

        $this->assertStringNotContainsString('googletagmanager.com/gtm.js', $html);
        $this->assertStringNotContainsString("gtag('consent'", $html);
    }
}
