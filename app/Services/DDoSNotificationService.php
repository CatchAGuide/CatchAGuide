<?php

namespace App\Services;

use App\Mail\DDoSAlertMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DDoSNotificationService
{
    private $adminEmail;
    private $cooldownMinutes = 15; // Don't send duplicate alerts within 15 minutes
    
    public function __construct()
    {
        $this->adminEmail = config('mail.admin_email', config('mail.from.address'));
    }
    
    /**
     * Send alert for rate limit violations
     */
    public function sendRateLimitAlert($identifier, $violations, $endpoint = null)
    {
        $alertKey = "rate_limit_alert_{$identifier}";
        
        if ($this->shouldSendAlert($alertKey)) {
            $details = [
                'ip' => $this->extractIpFromIdentifier($identifier),
                'violations' => $violations,
                'endpoint' => $endpoint,
                'requests_per_minute' => $violations,
                'user_agent' => request()->userAgent(),
            ];
            
            $this->sendAlert('Rate Limit Violations', $details);
            $this->recordAlertSent($alertKey);
        }
    }
    
    /**
     * Send alert for IP blocking
     */
    public function sendIPBlockAlert($identifier, $blockDuration, $violations)
    {
        $alertKey = "ip_block_alert_{$identifier}";
        
        if ($this->shouldSendAlert($alertKey)) {
            $details = [
                'ip' => $this->extractIpFromIdentifier($identifier),
                'violations' => $violations,
                'block_duration' => $blockDuration,
                'user_agent' => request()->userAgent(),
            ];
            
            $this->sendAlert('IP Address Blocked', $details);
            $this->recordAlertSent($alertKey);
        }
    }
    
    /**
     * Send alert for high Gemini API usage
     */
    public function sendHighUsageAlert($dailyUsage, $estimatedCost = null)
    {
        $alertKey = "high_usage_alert_" . date('Y-m-d');
        
        if ($this->shouldSendAlert($alertKey)) {
            $details = [
                'daily_usage' => $dailyUsage,
                'estimated_cost' => $estimatedCost,
                'threshold' => 1000,
            ];
            
            $this->sendAlert('High Gemini API Usage', $details);
            $this->recordAlertSent($alertKey);
        }
    }
    
    /**
     * Send alert for suspicious input patterns
     */
    public function sendSuspiciousInputAlert($identifier, $input, $pattern)
    {
        $alertKey = "suspicious_input_alert_{$identifier}";
        
        if ($this->shouldSendAlert($alertKey)) {
            $details = [
                'ip' => $this->extractIpFromIdentifier($identifier),
                'suspicious_input' => substr($input, 0, 100),
                'detected_pattern' => $pattern,
                'user_agent' => request()->userAgent(),
            ];
            
            $this->sendAlert('Suspicious Input Detected', $details);
            $this->recordAlertSent($alertKey);
        }
    }
    
    /**
     * Send alert for system overload
     */
    public function sendSystemOverloadAlert($responseTime, $concurrentRequests)
    {
        $alertKey = "system_overload_alert_" . date('Y-m-d-H');
        
        if ($this->shouldSendAlert($alertKey)) {
            $details = [
                'response_time' => $responseTime,
                'concurrent_requests' => $concurrentRequests,
                'timestamp' => now()->toISOString(),
            ];
            
            $this->sendAlert('System Performance Degradation', $details);
            $this->recordAlertSent($alertKey);
        }
    }
    
    /**
     * Send alert for stubborn attackers
     */
    public function sendStubbornAttackerAlert($identifier, $violations, $context)
    {
        $alertKey = "stubborn_attacker_alert_{$identifier}";
        
        if ($this->shouldSendAlert($alertKey)) {
            $details = [
                'ip' => $this->extractIpFromIdentifier($identifier),
                'violations' => $violations,
                'context' => $context,
                'user_agent' => request()->userAgent(),
                'severity' => 'CRITICAL',
                'action_taken' => 'Extended block applied',
                'recommendation' => 'Consider permanent IP ban if pattern continues'
            ];
            
            $this->sendAlert('STUBBORN ATTACKER DETECTED', $details);
            $this->recordAlertSent($alertKey);
        }
    }
    
    /**
     * Send the actual email alert
     */
    private function sendAlert($alertType, $details)
    {
        try {
            Mail::to($this->adminEmail)->send(new DDoSAlertMail($alertType, $details));
            
            Log::channel('ddos_alerts')->info('DDoS alert email sent', [
                'alert_type' => $alertType,
                'admin_email' => $this->adminEmail,
                'details' => $details
            ]);
        } catch (\Exception $e) {
            Log::channel('ddos_alerts')->error('Failed to send DDoS alert email', [
                'alert_type' => $alertType,
                'error' => $e->getMessage(),
                'details' => $details
            ]);
        }
    }
    
    /**
     * Check if we should send an alert (cooldown period)
     */
    private function shouldSendAlert($alertKey)
    {
        $lastSent = Cache::get($alertKey);
        return !$lastSent || (time() - $lastSent) > ($this->cooldownMinutes * 60);
    }
    
    /**
     * Record that an alert was sent
     */
    private function recordAlertSent($alertKey)
    {
        Cache::put($alertKey, time(), $this->cooldownMinutes * 60);
    }
    
    /**
     * Extract IP address from identifier
     */
    private function extractIpFromIdentifier($identifier)
    {
        if (strpos($identifier, 'ip_') === 0) {
            return substr($identifier, 3);
        }
        return $identifier;
    }
    
    /**
     * Test email functionality
     */
    public function sendTestAlert()
    {
        $details = [
            'ip' => '127.0.0.1',
            'violations' => 5,
            'endpoint' => '/guidings',
            'requests_per_minute' => 25,
            'user_agent' => 'Test User Agent',
            'test_mode' => true,
        ];
        
        $this->sendAlert('Test Alert - DDoS Protection System', $details);
        
        return true;
    }
}
