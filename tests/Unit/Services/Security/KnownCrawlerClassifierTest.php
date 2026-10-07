<?php

namespace Tests\Unit\Services\Security;

use App\Services\Security\CrawlerClassification;
use App\Services\Security\CrawlerLane;
use App\Services\Security\KnownCrawlerClassifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class KnownCrawlerClassifierTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_googlebot_on_private_ip_is_trusted_search_engine(): void
    {
        $result = $this->classifier()->classify($this->request(
            'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            '127.0.0.1'
        ));

        $this->assertTrue($result->isTrusted());
        $this->assertSame(CrawlerLane::SearchEngine, $result->lane);
        $this->assertSame('Googlebot', $result->name);
    }

    public function test_ahrefs_is_seo_crawler(): void
    {
        $result = $this->classifier()->classify($this->request(
            'Mozilla/5.0 (compatible; AhrefsBot/7.0; +http://ahrefs.com/robot/)',
            '127.0.0.1'
        ));

        $this->assertTrue($result->isTrusted());
        $this->assertSame(CrawlerLane::SeoCrawler, $result->lane);
        $this->assertSame('Ahrefs', $result->name);
    }

    public function test_dataforseo_rsiteauditor_is_seo_crawler(): void
    {
        $result = $this->classifier()->classify($this->request(
            'Mozilla/5.0 (compatible; RSiteAuditor)',
            '68.183.49.222'
        ));

        $this->assertTrue($result->isTrusted());
        $this->assertSame(CrawlerLane::SeoCrawler, $result->lane);
        $this->assertSame('DataForSEO', $result->name);
    }

    public function test_browser_user_is_not_a_crawler(): void
    {
        $result = $this->classifier()->classify($this->request(
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Safari/605.1.15',
            '45.86.202.194'
        ));

        $this->assertFalse($result->isTrusted());
        $this->assertSame(CrawlerLane::User, $result->lane);
    }

    public function test_spoofed_googlebot_without_ptr_gets_tight_lane(): void
    {
        config(['ddos.crawlers.verify_dns' => true]);

        $classifier = new KnownCrawlerClassifier(
            reverseLookup: fn (string $ip): string => $ip,
            forwardLookup: fn (string $host): string => '',
        );

        $result = $classifier->classify($this->request(
            'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            '8.8.8.8'
        ));

        $this->assertFalse($result->isTrusted());
        $this->assertSame(CrawlerLane::SpoofedCrawler, $result->lane);
        $this->assertSame('Googlebot', $result->name);
    }

    public function test_verified_googlebot_ptr_is_search_engine(): void
    {
        config(['ddos.crawlers.verify_dns' => true]);

        $classifier = new KnownCrawlerClassifier(
            reverseLookup: fn (string $ip): string => 'crawl-66-249-73-97.googlebot.com',
            forwardLookup: fn (string $host): string => '66.249.73.97',
        );

        $result = $classifier->classify($this->request(
            'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            '66.249.73.97'
        ));

        $this->assertTrue($result->isTrusted());
        $this->assertSame(CrawlerLane::SearchEngine, $result->lane);
        $this->assertTrue($result->verified);
    }

    public function test_verified_search_engine_is_not_rate_limited(): void
    {
        $classification = $this->classifier()->classify($this->request(
            'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'
        ));

        $this->assertNull($this->classifier()->limitsFor($classification, ['minute' => 80, 'hour' => 800, 'day' => 4000]));
    }

    public function test_spoofed_crawler_keeps_tight_limits(): void
    {
        $classification = new CrawlerClassification(CrawlerLane::SpoofedCrawler, 'Googlebot');

        $limits = $this->classifier()->limitsFor($classification, ['minute' => 80, 'hour' => 800, 'day' => 4000]);

        $this->assertSame(15, $limits['minute']);
    }

    public function test_facebook_link_preview_is_trusted_social_lane_without_dns(): void
    {
        config(['ddos.crawlers.verify_dns' => true]);

        $classifier = new KnownCrawlerClassifier(
            reverseLookup: fn (string $ip): string => throw new \RuntimeException('no DNS for social bots'),
        );

        $result = $classifier->classify($this->request(
            'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)',
            '31.13.103.1'
        ));

        $this->assertTrue($result->isTrusted());
        $this->assertSame(CrawlerLane::Social, $result->lane);
        $this->assertSame('Facebook', $result->name);
        $this->assertSame(60, $classifier->limitsFor($result, [])['minute']);
    }

    public function test_training_crawler_is_treated_as_a_normal_client(): void
    {
        $result = $this->classifier()->classify($this->request(
            'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; GPTBot/1.2; +https://openai.com/gptbot)',
            '20.171.207.1'
        ));

        $this->assertFalse($result->isTrusted());
        $this->assertSame(CrawlerLane::User, $result->lane);
    }

    public function test_failed_dns_lookup_is_retried_soon_instead_of_cached_for_a_day(): void
    {
        config(['ddos.crawlers.verify_dns' => true]);
        $googlebot = $this->request('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', '66.249.73.97');

        $timedOut = new KnownCrawlerClassifier(reverseLookup: fn (string $ip): string => $ip);
        $this->assertSame(CrawlerLane::SpoofedCrawler, $timedOut->classify($googlebot)->lane);

        $this->travel(16)->minutes();

        $this->assertSame(CrawlerLane::SearchEngine, $this->verifiedGoogleClassifier()->classify($googlebot)->lane);
    }

    public function test_foreign_ptr_host_stays_spoofed_for_the_cache_period(): void
    {
        config(['ddos.crawlers.verify_dns' => true]);
        $fake = $this->request('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', '66.249.73.97');

        $foreign = new KnownCrawlerClassifier(reverseLookup: fn (string $ip): string => 'vps-1.example-hosting.net');
        $this->assertSame(CrawlerLane::SpoofedCrawler, $foreign->classify($fake)->lane);

        $this->travel(16)->minutes();

        $this->assertSame(CrawlerLane::SpoofedCrawler, $this->verifiedGoogleClassifier()->classify($fake)->lane);
    }

    public function test_forward_lookup_matches_any_returned_address_including_ipv6(): void
    {
        config(['ddos.crawlers.verify_dns' => true]);

        $classifier = new KnownCrawlerClassifier(
            reverseLookup: fn (string $ip): string => 'crawl-2001-4860-4801-1.googlebot.com',
            forwardLookup: fn (string $host): array => ['66.249.66.1', '2001:4860:4801:0:0:0:0:1'],
        );

        $result = $classifier->classify($this->request(
            'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            '2001:4860:4801::1'
        ));

        $this->assertSame(CrawlerLane::SearchEngine, $result->lane);
    }

    private function verifiedGoogleClassifier(): KnownCrawlerClassifier
    {
        return new KnownCrawlerClassifier(
            reverseLookup: fn (string $ip): string => 'crawl-66-249-73-97.googlebot.com',
            forwardLookup: fn (string $host): string => '66.249.73.97',
        );
    }

    private function classifier(): KnownCrawlerClassifier
    {
        return new KnownCrawlerClassifier;
    }

    private function request(string $userAgent, string $ip = '127.0.0.1'): Request
    {
        $request = Request::create('https://catchaguide.de/destination/deutschland', 'GET');
        $request->headers->set('User-Agent', $userAgent);
        $request->server->set('REMOTE_ADDR', $ip);

        return $request;
    }
}
