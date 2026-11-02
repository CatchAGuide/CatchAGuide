<?php

namespace Tests\Feature\Seo;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CookieConsentBannerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * The popup only offers Accept all, Accept necessary only and a link to the settings page, in the page's
     * language; the switches live on /cookie-settings and in the profile.
     */
    public function test_popup_offers_the_three_choices_in_the_page_language(): void
    {
        $cases = [
            'en' => ['Accept all', 'Accept necessary only', 'Go to settings', 'Cookie settings'],
            'de' => ['Alle akzeptieren', 'Nur notwendige akzeptieren', 'Zu den Einstellungen', 'Cookie-Einstellungen'],
        ];

        // The domain picks the locale only in production; locally the session override does.
        foreach ($cases as $locale => [$acceptAll, $necessary, $settings, $footerLink]) {
            $html = $this->withSession(['locale' => $locale])->get('http://catchaguide.com/about-us')->assertOk()->getContent();
            $settingsUrl = 'http://catchaguide.com/cookie-settings';

            $this->assertSame(1, substr_count($html, 'id="cookie-consent-banner"'), $locale);
            $this->assertMatchesRegularExpression('/id="cookie-accept"[^>]*>'.preg_quote($acceptAll, '/').'</', $html);
            $this->assertMatchesRegularExpression('/id="cookie-decline"[^>]*>'.preg_quote($necessary, '/').'</', $html);
            $this->assertMatchesRegularExpression('/id="cookie-adjust-preferences"[^>]*href="'.preg_quote($settingsUrl, '/').'">'.preg_quote($settings, '/').'</', $html);
            $this->assertMatchesRegularExpression('/href="'.preg_quote($settingsUrl, '/').'"\s*>'.preg_quote($footerLink, '/').'<\/a>/', $html);
            $this->assertStringNotContainsString('<div class="cag-cookie-panel" data-cookie-settings-panel>', $html, 'The popup must not carry the switches.');
        }
    }

    /**
     * GA4 is listed under Necessary (always on); Clarity (Analytics) and Google Ads (Marketing) are opt-in switches.
     */
    public function test_settings_page_shows_the_switches(): void
    {
        $html = $this->withSession(['locale' => 'en'])->get('http://catchaguide.com/cookie-settings')->assertOk()->getContent();

        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $html);
        $this->assertStringContainsString('<div class="cag-cookie-panel" data-cookie-settings-panel>', $html);
        $this->assertStringContainsString('id="cookie-panel-page-necessary" checked disabled', $html);
        $this->assertStringContainsString('data-cookie-consent="analytics"', $html);
        $this->assertStringContainsString('data-cookie-consent="marketing"', $html);
        $this->assertMatchesRegularExpression('/cookie-panel-page-necessary.*?<li>Google Analytics 4<\/li>.*?cookie-panel-page-analytics.*?<li>Microsoft Clarity<\/li>.*?<li>Google Ads<\/li>/s', $html);
    }

    public function test_profile_settings_show_the_switches(): void
    {
        $user = User::factory()->create();

        $html = $this->actingAs($user)->get('http://catchaguide.com/profile/settings')->assertOk()->getContent();

        $this->assertStringContainsString('<div class="cag-cookie-panel" data-cookie-settings-panel>', $html);
        $this->assertStringContainsString('id="cookie-panel-profile-necessary" checked disabled', $html);
    }

    /**
     * The privacy policy names the tools that really run, says GA4 runs for everyone, and links the cookie settings.
     */
    public function test_privacy_policy_describes_the_tracking_tools(): void
    {
        $cases = [
            'en' => ['Analytics tools and advertising', 'including when you choose "Accept necessary only"', 'Google Ads conversion tracking'],
            'de' => ['Analyse-Tools und Werbung', 'auch wenn Sie „Nur notwendige akzeptieren“ wählen', 'Google Ads Conversion-Tracking'],
        ];

        foreach ($cases as $locale => [$title, $ga4AlwaysOn, $ads]) {
            $html = html_entity_decode(
                $this->withSession(['locale' => $locale])->get('http://catchaguide.com/data-protection')->assertOk()->getContent()
            );

            $this->assertStringContainsString('<h2>6. '.$title.'</h2>', $html, $locale);
            $this->assertStringContainsString('<h3>Google Analytics 4</h3>', $html);
            $this->assertStringContainsString($ga4AlwaysOn, $html);
            $this->assertStringContainsString('<h3>Microsoft Clarity</h3>', $html);
            $this->assertStringContainsString('<h3>'.$ads.'</h3>', $html);
            $this->assertStringContainsString('<h3>Google Tag Manager</h3>', $html);
            $this->assertStringContainsString('href="http://catchaguide.com/cookie-settings"', $html);
            $this->assertStringNotContainsString('Matomo', $html, 'Matomo is not used on the site.');
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
