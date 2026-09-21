<?php

namespace Tests\Unit\Services\Security;

use App\Services\Security\ThreatVerdictService;
use Tests\TestCase;

class ThreatVerdictServiceTest extends TestCase
{
    private const BROWSER_UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    public function test_exploit_payloads_are_a_high_severity_probable_attack(): void
    {
        $verdict = $this->assess([
            'types' => ['sqli' => 2],
            'events' => 2,
            'exploit_matches' => ["' UNION ALL SELECT NULL--"],
            'user_agents' => [self::BROWSER_UA],
        ]);

        $this->assertSame('probable_attack', $verdict->key);
        $this->assertSame('high', $verdict->severity);
        $this->assertTrue($verdict->isProbableAttack());
        $this->assertContains('exploit_sample', array_column($verdict->evidence, 'key'));
    }

    public function test_exploit_payload_outranks_crawler_like_identity(): void
    {
        $verdict = $this->assess([
            'types' => ['rate_limit' => 5, 'xss' => 1],
            'user_agents' => ['Mozilla/5.0 (compatible; RSiteAuditor)'],
            'reverse_dns' => 'on-page-usa-gw-5.dataforseo.com',
        ]);

        $this->assertSame('probable_attack', $verdict->key);
    }

    public function test_spoofed_crawler_lane_is_flagged_as_impersonation(): void
    {
        $verdict = $this->assess([
            'types' => ['rate_limit' => 3],
            'lanes' => ['spoofed_crawler' => 3],
            'crawlers' => ['Googlebot'],
            'reverse_dns' => null,
        ]);

        $this->assertSame('spoofed_crawler', $verdict->key);
        $this->assertTrue($verdict->isProbableAttack());
    }

    public function test_honeypot_hit_is_bot_form_abuse(): void
    {
        $verdict = $this->assess(['types' => ['honeypot' => 1]]);

        $this->assertSame('bot_form_abuse', $verdict->key);
        $this->assertSame('medium', $verdict->severity);
    }

    public function test_dataforseo_rsiteauditor_is_an_unlisted_crawler_not_an_attack(): void
    {
        $verdict = $this->assess([
            'types' => ['rate_limit' => 14],
            'lanes' => ['user' => 14],
            'max_violations' => 14,
            'reverse_dns' => 'on-page-usa-gw-5.dataforseo.com',
            'user_agents' => ['Mozilla/5.0 (compatible; RSiteAuditor)'],
        ]);

        $this->assertSame('unlisted_crawler', $verdict->key);
        $this->assertSame('info', $verdict->severity);
        $this->assertFalse($verdict->isProbableAttack());
        $this->assertContains('crawler_rdns', array_column($verdict->evidence, 'key'));
        $this->assertContains('crawler_ua', array_column($verdict->evidence, 'key'));
    }

    public function test_browser_ua_with_many_violations_is_an_aggressive_scraper(): void
    {
        $verdict = $this->assess([
            'types' => ['rate_limit' => 25],
            'max_violations' => 25,
            'user_agents' => [self::BROWSER_UA],
        ]);

        $this->assertSame('aggressive_scraper', $verdict->key);
        $this->assertSame('medium', $verdict->severity);
    }

    public function test_browser_ua_with_few_violations_is_a_likely_false_positive(): void
    {
        $verdict = $this->assess([
            'types' => ['rate_limit' => 2],
            'max_violations' => 2,
            'max_score' => 10,
            'user_agents' => [self::BROWSER_UA],
        ]);

        $this->assertSame('likely_false_positive', $verdict->key);
        $this->assertSame('low', $verdict->severity);
    }

    public function test_unrecognised_event_types_need_review(): void
    {
        $verdict = $this->assess(['types' => ['unknown' => 1]]);

        $this->assertSame('needs_review', $verdict->key);
    }

    public function test_every_verdict_and_evidence_key_has_en_and_de_copy(): void
    {
        foreach (['en', 'de'] as $locale) {
            foreach (['probable_attack', 'spoofed_crawler', 'bot_form_abuse', 'unlisted_crawler', 'aggressive_scraper', 'likely_false_positive', 'needs_review'] as $key) {
                foreach (['title', 'summary', 'action'] as $part) {
                    $path = "admin.security.verdicts.{$key}.{$part}";
                    $this->assertNotSame($path, trans($path, [], $locale), "Missing {$locale} copy for {$path}");
                }
            }

            foreach (['exploit_type', 'exploit_sample', 'repeated_probes', 'claims_crawler', 'rdns_failed', 'honeypot_hit', 'rate_limit_hits', 'crawler_rdns', 'crawler_ua', 'crawler_robot_flag', 'many_violations', 'automation_signs', 'high_score', 'unknown_type'] as $key) {
                $path = "admin.security.evidence.{$key}";
                $this->assertNotSame($path, trans($path, [], $locale), "Missing {$locale} copy for {$path}");
            }
        }
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function assess(array $overrides): \App\Services\Security\ThreatVerdict
    {
        $profile = array_merge([
            'events' => 1,
            'types' => [],
            'lanes' => [],
            'crawlers' => [],
            'user_agents' => [],
            'exploit_matches' => [],
            'max_violations' => 0,
            'max_score' => 0,
            'reverse_dns' => null,
            'is_robot' => false,
            'automation_score' => 0,
        ], $overrides);

        return app(ThreatVerdictService::class)->assess($profile);
    }
}
