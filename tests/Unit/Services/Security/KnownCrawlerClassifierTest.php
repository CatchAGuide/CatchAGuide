<?php

namespace Tests\Unit\Services\Security;

use App\Services\Security\CrawlerLane;
use App\Services\Security\KnownCrawlerClassifier;
use Illuminate\Http\Request;
use Tests\TestCase;

class KnownCrawlerClassifierTest extends TestCase
{
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

    public function test_search_engine_lane_uses_higher_limits(): void
    {
        $classification = $this->classifier()->classify($this->request(
            'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'
        ));

        $limits = $this->classifier()->limitsFor($classification, ['minute' => 80, 'hour' => 800, 'day' => 4000]);

        $this->assertSame(180, $limits['minute']);
        $this->assertSame(4000, $limits['hour']);
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
