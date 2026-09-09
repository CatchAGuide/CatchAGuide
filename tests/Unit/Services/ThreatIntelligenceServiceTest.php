<?php

namespace Tests\Unit\Services;

use App\Services\ThreatIntelligenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * The behavioral/pattern side of the threat score (request frequency,
 * automation timing, concurrent requests) reads Cache keys
 * (threat_activity_{ip}, recent_requests_{ip}) that nothing ever wrote —
 * so real attack traffic always looked like a first-ever request and
 * scored 0. This is why 245 of 288 threat_intelligence rows have
 * threat_score = 0 (score only ever came from the one non-stub check,
 * the "high last octet" IP pattern, +15). collectThreatData() now records
 * each request into those caches before analyzing them.
 */
class ThreatIntelligenceServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        config(['ddos.threat_intelligence.enabled' => false]);
    }

    private function request(string $ip = '203.0.113.5'): Request
    {
        return Request::create('/destination/spanien', 'GET', ['page' => 1], [], [], [
            'REMOTE_ADDR' => $ip,
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        ]);
    }

    public function test_repeated_requests_from_same_ip_raise_the_recorded_request_count(): void
    {
        $service = new ThreatIntelligenceService();
        $ip = '203.0.113.5';

        $first = $service->collectThreatData($this->request($ip), 'search');
        $this->assertSame(1, $first['behavior']['request_count_last_hour'], 'The current request is recorded before it is analyzed.');

        $second = $service->collectThreatData($this->request($ip), 'search');
        $this->assertSame(2, $second['behavior']['request_count_last_hour'], 'The prior request should now be in the recorded history too.');

        $third = $service->collectThreatData($this->request($ip), 'search');
        $this->assertSame(3, $third['behavior']['request_count_last_hour']);
    }

    public function test_user_agent_is_captured_on_the_behavior_payload(): void
    {
        $service = new ThreatIntelligenceService();

        $data = $service->collectThreatData($this->request(), 'search');

        $this->assertSame('Mozilla/5.0 (Windows NT 10.0; Win64; x64)', $data['behavior']['user_agent']);
    }

    public function test_different_ips_do_not_share_recorded_history(): void
    {
        $service = new ThreatIntelligenceService();

        $service->collectThreatData($this->request('203.0.113.5'), 'search');
        $data = $service->collectThreatData($this->request('198.51.100.9'), 'search');

        $this->assertSame(1, $data['behavior']['request_count_last_hour'], 'Only its own request, not the other IP\'s history.');
    }
}
