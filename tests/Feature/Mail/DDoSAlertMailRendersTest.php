<?php

namespace Tests\Feature\Mail;

use App\Mail\DDoSAlertMail;
use Tests\TestCase;

/**
 * resources/views/emails/ddos-alert.blade.php had a stray @endif around the
 * "Endpoint" row, so every DDoSNotificationService alert (rate limit,
 * exploit probe, IP block, high usage, suspicious input, system overload,
 * stubborn attacker) failed to render with a Blade compile error and no
 * threat/DDoS alert email was ever actually delivered — confirmed by
 * "Failed to send DDoS alert email" entries in storage/logs/ddos-alerts-*.log.
 * DDoSProtectionServiceTest uses Mail::fake(), which never compiles the view
 * and so never caught this — these tests render the mailable for real.
 */
class DDoSAlertMailRendersTest extends TestCase
{
    public function test_renders_with_endpoint_present(): void
    {
        $html = (new DDoSAlertMail('SQL Injection Probe', [
            'ip' => '38.224.228.154',
            'violations' => 1,
            'endpoint' => 'https://catchaguide.de/destination/spanien?page=1',
            'detected_pattern' => 'sqli',
            'classification' => 'sqli',
            'user_agent' => 'Mozilla/5.0',
        ]))->render();

        $this->assertStringContainsString('SQL Injection Probe', $html);
        $this->assertStringContainsString('38.224.228.154', $html);
        $this->assertStringContainsString('destination/spanien', $html);
    }

    /**
     * sendHighUsageAlert()/sendSystemOverloadAlert() never set `endpoint` —
     * the unconditional (unwrapped) Endpoint block would throw "Undefined
     * array key" for these alert types even once the stray @endif is fixed.
     */
    public function test_renders_without_endpoint_present(): void
    {
        $html = (new DDoSAlertMail('High Gemini API Usage', [
            'daily_usage' => 1200,
            'estimated_cost' => 4.5,
            'threshold' => 1000,
        ]))->render();

        $this->assertStringContainsString('High Gemini API Usage', $html);
        $this->assertStringNotContainsString('Endpoint:', $html);
    }

    public function test_renders_test_alert(): void
    {
        $html = (new DDoSAlertMail('Test Alert - DDoS Protection System', [
            'ip' => '127.0.0.1',
            'violations' => 5,
            'endpoint' => '/guidings',
            'requests_per_minute' => 25,
            'user_agent' => 'Test User Agent',
            'test_mode' => true,
        ]))->render();

        $this->assertStringContainsString('Test Alert - DDoS Protection System', $html);
    }
}
