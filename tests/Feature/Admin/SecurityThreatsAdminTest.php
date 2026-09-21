<?php

namespace Tests\Feature\Admin;

use App\Models\Employee;
use App\Models\ThreatIntelligence;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class SecurityThreatsAdminTest extends TestCase
{
    use DatabaseTransactions;

    private const CRAWLER_IP = '203.0.113.50';

    private const ATTACKER_IP = '203.0.113.60';

    protected function setUp(): void
    {
        parent::setUp();

        // A scheme-less APP_URL (e.g. "cag.local") turns "/admin/..." into "http://localhost/cag.local/admin/..." and 404s.
        URL::forceRootUrl('http://localhost');
    }

    private function actingAsEmployee(): void
    {
        $employee = Employee::query()->first();
        if (! $employee) {
            $this->markTestSkipped('No employee available for admin auth.');
        }

        $this->actingAs($employee, 'employees');
    }

    /**
     * @param  array<string, mixed>  $attackData
     * @param  array<string, mixed>  $extra
     */
    private function threat(string $ip, array $attackData, array $extra = [], ?string $context = 'search'): ThreatIntelligence
    {
        return ThreatIntelligence::query()->create([
            'ip' => $ip,
            'context' => $context,
            'threat_score' => 0,
            'threat_data' => array_merge([
                'attack_data' => $attackData,
                'network' => ['reverse_dns' => null],
                'behavior' => ['user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0'],
                'fingerprint' => ['is_robot' => false],
            ], $extra),
        ]);
    }

    public function test_guests_cannot_open_the_threat_monitor(): void
    {
        $this->get('/admin/security/threats')->assertRedirect();
    }

    public function test_it_explains_a_dataforseo_crawler_as_not_an_attack(): void
    {
        $this->actingAsEmployee();
        $this->threat(self::CRAWLER_IP, [
            'type' => 'rate_limit',
            'lane' => 'user',
            'crawler' => null,
            'violations' => 14,
            'url' => 'https://catchaguide.de/guidings/targets/dorsch',
        ], [
            'network' => ['reverse_dns' => 'on-page-usa-gw-5.dataforseo.com'],
            'behavior' => ['user_agent' => 'Mozilla/5.0 (compatible; RSiteAuditor)'],
        ]);

        $response = $this->get('/admin/security/threats?ip='.self::CRAWLER_IP);

        $response->assertOk();
        $response->assertSee(self::CRAWLER_IP);
        $response->assertSee('on-page-usa-gw-5.dataforseo.com');
        $response->assertSee(__('admin.security.verdicts.unlisted_crawler.title'));
        $response->assertDontSee(__('admin.security.verdicts.probable_attack.title'));
    }

    public function test_it_flags_exploit_probes_as_probable_attacks_and_sorts_them_first(): void
    {
        $this->actingAsEmployee();
        $this->threat(self::CRAWLER_IP, ['type' => 'rate_limit', 'lane' => 'user', 'violations' => 2, 'url' => 'https://catchaguide.de/destination']);
        $this->threat(self::ATTACKER_IP, [
            'type' => 'sqli',
            'matched' => "') AND ('a'='a' UNION ALL SELECT NULL--",
            'lane' => 'user',
            'url' => 'https://catchaguide.de/destination/deutschland?sortby=x',
        ]);

        $html = $this->get('/admin/security/threats?ip=203.0.113.')->assertOk()->getContent();

        $this->assertStringContainsString(__('admin.security.verdicts.probable_attack.title'), $html);
        $this->assertLessThan(strpos($html, self::CRAWLER_IP), strpos($html, self::ATTACKER_IP));
    }

    public function test_payloads_from_attackers_are_escaped(): void
    {
        $this->actingAsEmployee();
        $this->threat(self::ATTACKER_IP, [
            'type' => 'xss',
            'matched' => '<script>alert(1)</script>',
            'lane' => 'user',
            'url' => 'https://catchaguide.de/x',
        ]);

        $this->get('/admin/security/threats')
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    public function test_ip_prefix_filter_and_time_window_limit_the_results(): void
    {
        $this->actingAsEmployee();
        $this->threat(self::CRAWLER_IP, ['type' => 'rate_limit', 'lane' => 'user', 'url' => 'https://catchaguide.de/a']);
        $this->threat(self::ATTACKER_IP, ['type' => 'rate_limit', 'lane' => 'user', 'url' => 'https://catchaguide.de/b']);
        $old = $this->threat('198.51.100.7', ['type' => 'rate_limit', 'lane' => 'user', 'url' => 'https://catchaguide.de/c']);
        $old->forceFill(['created_at' => now()->subDays(3)])->save();

        $this->get('/admin/security/threats?ip=203.0.113.5')
            ->assertSee(self::CRAWLER_IP)
            ->assertDontSee(self::ATTACKER_IP);

        // The filter input echoes the searched IP, so assert on the empty state rather than the IP itself.
        $this->get('/admin/security/threats?hours=24&ip=198.51.100.7')->assertSee(__('admin.security.empty'));
        $this->get('/admin/security/threats?hours=168&ip=198.51.100.7')->assertDontSee(__('admin.security.empty'));
    }

    public function test_active_blocks_are_shown(): void
    {
        $this->actingAsEmployee();
        $this->threat(self::ATTACKER_IP, ['type' => 'rate_limit', 'lane' => 'user', 'violations' => 30, 'url' => 'https://catchaguide.de/a']);
        Cache::put('search_blocked_ip_'.self::ATTACKER_IP, ['blocked_at' => time(), 'expires_at' => time() + 600], 600);

        $this->get('/admin/security/threats')->assertSee(__('admin.security.currently_blocked'));
    }

    public function test_invalid_window_is_rejected(): void
    {
        $this->actingAsEmployee();

        $this->get('/admin/security/threats?hours=9999')->assertSessionHasErrors('hours');
    }

    public function test_empty_state_is_shown_without_events(): void
    {
        $this->actingAsEmployee();

        $this->get('/admin/security/threats?ip=192.0.2.250')->assertOk()->assertSee(__('admin.security.empty'));
    }
}
