<?php

namespace Tests\Unit\Services;

use App\Services\DDoSProtectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DDoSProtectionServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Mail::fake();
        config(['ddos.threat_intelligence.enabled' => false]);
        config(['ddos.notifications.send_on_rate_limit' => true]);
        config(['ddos.notifications.send_on_exploit' => true]);
        config(['ddos.notifications.min_rate_limit_violations_for_alert' => 5]);
    }

    public function test_googlebot_is_allowed_on_catalog_pages(): void
    {
        $result = $this->service()->shouldBlockRequest(
            $this->request('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'),
            $this->searchConfig()
        );

        $this->assertFalse($result['blocked']);
    }

    public function test_googlebot_is_not_blocked_after_user_rate_limit(): void
    {
        $config = $this->searchConfig();
        $config['limits'] = ['minute' => 3, 'hour' => 100, 'day' => 100];
        $service = $this->service();
        $bot = $this->request('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');

        for ($i = 0; $i < 4; $i++) {
            $result = $service->shouldBlockRequest($bot, $config);
        }

        $this->assertFalse($result['blocked']);
    }

    public function test_user_is_rate_limited_without_email_on_first_violation(): void
    {
        $config = $this->searchConfig();
        $config['limits'] = ['minute' => 2, 'hour' => 100, 'day' => 100];
        $service = $this->service();
        $user = $this->request('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Safari/605.1.15');

        $this->assertFalse($service->shouldBlockRequest($user, $config)['blocked']);
        $this->assertFalse($service->shouldBlockRequest($user, $config)['blocked']);
        $blocked = $service->shouldBlockRequest($user, $config);

        $this->assertTrue($blocked['blocked']);
        $this->assertSame('rate_limit_exceeded', $blocked['reason']);
        Mail::assertNothingSent();
    }

    public function test_sqli_sortby_is_blocked_and_emails_once(): void
    {
        $request = Request::create('/destination/deutschland/sachsen-anhalt', 'GET', [
            'sortby' => "') AND ('sjceqt'='sjceqt' UNION ALL SELECT NULL,NULL-- ",
        ]);
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Safari/605.1.15');
        $request->server->set('REMOTE_ADDR', '45.86.202.194');

        $result = $this->service()->shouldBlockRequest($request, $this->searchConfig());

        $this->assertTrue($result['blocked']);
        $this->assertSame('suspicious_input', $result['reason']);
        $this->assertSame('sqli', $result['exploit_type']);
        Mail::assertSent(\App\Mail\DDoSAlertMail::class, function ($mail) {
            return $mail->alertType === 'SQL Injection Probe';
        });
    }

    public function test_normal_sortby_is_allowed(): void
    {
        $request = Request::create('/destination/deutschland', 'GET', ['sortby' => 'price-asc']);
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        $request->server->set('REMOTE_ADDR', '84.132.124.162');

        $result = $this->service()->shouldBlockRequest($request, $this->searchConfig());

        $this->assertFalse($result['blocked']);
        Mail::assertNothingSent();
    }

    public function test_rate_limit_email_requires_several_violations(): void
    {
        config(['ddos.notifications.min_rate_limit_violations_for_alert' => 2]);
        $config = $this->searchConfig();
        $config['limits'] = ['minute' => 1, 'hour' => 100, 'day' => 100];
        $service = $this->service();
        $user = $this->request('Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0', '91.62.99.148');

        $service->shouldBlockRequest($user, $config);
        $service->shouldBlockRequest($user, $config);
        Mail::assertNothingSent();

        $service->shouldBlockRequest($user, $config);
        Mail::assertSent(\App\Mail\DDoSAlertMail::class, function ($mail) {
            return $mail->alertType === 'Rate Limit Violations';
        });
    }

    private function service(): DDoSProtectionService
    {
        return app(DDoSProtectionService::class);
    }

    private function searchConfig(): array
    {
        return array_merge(config('ddos.contexts.search'), ['context' => 'search']);
    }

    private function request(string $userAgent, string $ip = '127.0.0.1'): Request
    {
        $request = Request::create('/destination/deutschland', 'GET', ['sortby' => 'newest']);
        $request->headers->set('User-Agent', $userAgent);
        $request->server->set('REMOTE_ADDR', $ip);

        return $request;
    }
}
