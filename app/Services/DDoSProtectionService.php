<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\DDoSNotificationService;
use App\Services\ThreatIntelligenceService;
use App\Services\HoneypotService;

class DDoSProtectionService
{
    private DDoSNotificationService $notificationService;
    private ThreatIntelligenceService $threatIntelligence;
    private HoneypotService $honeypotService;
    
    public function __construct(
        DDoSNotificationService $notificationService,
        ThreatIntelligenceService $threatIntelligence,
        HoneypotService $honeypotService
    ) {
        $this->notificationService = $notificationService;
        $this->threatIntelligence = $threatIntelligence;
        $this->honeypotService = $honeypotService;
    }

    /**
     * Check if request should be blocked based on rate limits and violations
     */
    public function shouldBlockRequest(Request $request, array $config): array
    {
        $identifier = $this->getIdentifier($request);
        $context = $config['context'] ?? 'general';
        
        // Enhanced threat intelligence collection
        $threatData = $this->threatIntelligence->collectThreatData($request, $context);
        
        // Check honeypot triggers
        $honeypotTriggers = $this->honeypotService->checkHoneypotTriggers($request);
        if (!empty($honeypotTriggers)) {
            $this->logHoneypotViolation($request, $honeypotTriggers);
            return [
                'blocked' => true,
                'reason' => 'honeypot_triggered',
                'retry_after' => 300, // 5 minutes
                'threat_data' => $threatData,
                'honeypot_triggers' => $honeypotTriggers
            ];
        }
        
        // Check if already blocked
        if ($this->isBlocked($identifier, $context)) {
            $this->logBlockedAttempt($request, $identifier, $context);
            return [
                'blocked' => true,
                'reason' => 'already_blocked',
                'retry_after' => $this->getRetryAfter($identifier, $context),
                'threat_data' => $threatData
            ];
        }

        // Check rate limits
        if (!$this->checkRateLimit($identifier, $context, $config['limits'])) {
            $this->recordViolation($identifier, $context, $config);
            $this->logRateLimitViolation($request, $identifier, $context);
            
            return [
                'blocked' => true,
                'reason' => 'rate_limit_exceeded',
                'retry_after' => 60,
                'threat_data' => $threatData
            ];
        }

        // High threat score blocking - Made less aggressive
        if ($threatData['threat_score'] > 95) { // Increased from 80 to 95
            $this->logHighThreatBlock($request, $threatData);
            $this->blockIdentifier($identifier, $context, 900, 0); // Reduced from 1800 to 900 (15 minutes)
            return [
                'blocked' => true,
                'reason' => 'high_threat_score',
                'retry_after' => 900, // Reduced from 1800 to 900
                'threat_data' => $threatData
            ];
        }

        // Validate input if required
        if (isset($config['validate_input']) && $config['validate_input']) {
            if (!$this->validateInput($request, $config['input_patterns'] ?? [])) {
                $this->logSuspiciousInput($request, $identifier, $context);
                $this->notificationService->sendSuspiciousInputAlert(
                    $identifier, 
                    json_encode($request->all()), 
                    "Malicious {$context} pattern detected"
                );
                
                return [
                    'blocked' => true,
                    'reason' => 'suspicious_input',
                    'retry_after' => 0
                ];
            }
        }

        return ['blocked' => false];
    }

    /**
     * Get identifier for rate limiting (user ID or IP)
     */
    private function getIdentifier(Request $request): string
    {
        if (auth()->check()) {
            return 'user_' . auth()->id();
        }
        
        return 'ip_' . $request->ip();
    }

    /**
     * Check rate limits based on configuration
     */
    private function checkRateLimit(string $identifier, string $context, array $limits): bool
    {
        foreach ($limits as $window => $limit) {
            $key = "{$context}_rate_limit_{$window}_{$identifier}";
            $attempts = Cache::get($key, 0);
            
            if ($attempts >= $limit) {
                return false;
            }
            
            // Increment counter
            $ttl = $this->getWindowTtl($window);
            Cache::put($key, $attempts + 1, $ttl);
        }

        return true;
    }

    /**
     * Get TTL for rate limit window
     */
    private function getWindowTtl(string $window): int
    {
        return match($window) {
            'minute' => 60,
            'hour' => 3600,
            'day' => 86400,
            default => 60
        };
    }

    /**
     * Validate input against suspicious patterns
     */
    private function validateInput(Request $request, array $customPatterns = []): bool
    {
        $defaultPatterns = [
            '/\b(script|javascript|eval|function|alert|prompt|confirm)\b/i',
            '/\b(admin|root|system|config|password|token|key)\b/i',
            '/[<>"\']/', // HTML/JS injection attempts
            '/\b(select|insert|update|delete|drop|create|alter)\b/i', // SQL injection
            '/\b(union|or|and)\s+\d+/i', // SQL injection patterns
        ];

        $patterns = array_merge($defaultPatterns, $customPatterns);
        $input = $request->all();

        return $this->validateArrayInput($input, $patterns);
    }

    /**
     * Recursively validate array input
     */
    private function validateArrayInput($data, array $patterns): bool
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // Check length
                if (strlen($value) > 1000) {
                    return false;
                }
                
                // Check for suspicious patterns
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        return false;
                    }
                }
            } elseif (is_array($value)) {
                if (!$this->validateArrayInput($value, $patterns)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Check if identifier is blocked
     */
    private function isBlocked(string $identifier, string $context): bool
    {
        $blockKey = "{$context}_blocked_{$identifier}";
        $blockData = Cache::get($blockKey);
        
        return $blockData && $blockData['expires_at'] > time();
    }

    /**
     * Record violation and potentially block identifier
     */
    private function recordViolation(string $identifier, string $context, array $config): void
    {
        $violationKey = "{$context}_violations_{$identifier}";
        $violations = Cache::get($violationKey, 0) + 1;
        
        // Store violations for 24 hours
        Cache::put($violationKey, $violations, 86400);
        
        // Enhanced progressive blocking for stubborn attackers
        $blockThreshold = $config['block_threshold'] ?? 10;
        $stubbornThreshold = $config['stubborn_threshold'] ?? 50; // New threshold for stubborn attackers
        
        if ($violations >= $blockThreshold) {
            // Calculate block duration based on violation count
            if ($violations >= $stubbornThreshold) {
                // Stubborn attacker - much longer blocks
                $blockDuration = $this->calculateStubbornBlockDuration($violations, $config);
                $this->logStubbornAttacker($identifier, $context, $violations);
            } else {
                // Regular progressive blocking
                $blockDuration = min($violations * ($config['block_multiplier'] ?? 30), $config['max_block_duration'] ?? 1800);
            }
            
            $this->blockIdentifier($identifier, $context, $blockDuration, $violations);
        }

        // Send notification
        $this->notificationService->sendRateLimitAlert($identifier, $violations, request()->fullUrl());
    }

    /**
     * Block identifier for specified duration
     */
    private function blockIdentifier(string $identifier, string $context, int $duration, int $violations): void
    {
        $blockKey = "{$context}_blocked_{$identifier}";
        Cache::put($blockKey, [
            'blocked_at' => time(),
            'expires_at' => time() + $duration
        ], $duration);
        
        Log::channel('ddos_attacks')->warning("IP blocked for {$context} violations", [
            'identifier' => $identifier,
            'duration' => $duration,
            'ip' => $this->extractIpFromIdentifier($identifier),
            'violations' => $violations,
            'context' => $context
        ]);
        
        $this->notificationService->sendIPBlockAlert($identifier, $duration / 60, $violations);
    }

    /**
     * Get retry after time for blocked identifier
     */
    private function getRetryAfter(string $identifier, string $context): int
    {
        $blockKey = "{$context}_blocked_{$identifier}";
        $blockData = Cache::get($blockKey);
        
        if ($blockData) {
            return max(0, $blockData['expires_at'] - time());
        }
        
        return 60; // Default 1 minute
    }

    /**
     * Extract IP from identifier
     */
    private function extractIpFromIdentifier(string $identifier): string
    {
        if (strpos($identifier, 'ip_') === 0) {
            return substr($identifier, 3);
        }
        return $identifier;
    }

    /**
     * Log blocked attempt
     */
    private function logBlockedAttempt(Request $request, string $identifier, string $context): void
    {
        Log::channel('ddos_attacks')->warning("Blocked IP attempted {$context} request", [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'identifier' => $identifier,
            'url' => $request->fullUrl(),
            'context' => $context
        ]);
    }

    /**
     * Log rate limit violation
     */
    private function logRateLimitViolation(Request $request, string $identifier, string $context): void
    {
        Log::channel('ddos_attacks')->warning("Rate limit exceeded for {$context}", [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'identifier' => $identifier,
            'url' => $request->fullUrl(),
            'context' => $context
        ]);
    }

    /**
     * Log suspicious input
     */
    private function logSuspiciousInput(Request $request, string $identifier, string $context): void
    {
        Log::channel('ddos_attacks')->warning("Invalid {$context} input detected", [
            'ip' => $request->ip(),
            'input' => $request->all(),
            'user_agent' => $request->userAgent(),
            'identifier' => $identifier,
            'endpoint' => $request->fullUrl(),
            'context' => $context
        ]);
    }

    /**
     * Log honeypot violation
     */
    private function logHoneypotViolation(Request $request, array $honeypotTriggers): void
    {
        Log::channel('ddos_attacks')->critical("HONEYPOT TRIGGERED - BOT DETECTED", [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'triggers' => $honeypotTriggers,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Log high threat block
     */
    private function logHighThreatBlock(Request $request, array $threatData): void
    {
        Log::channel('ddos_attacks')->critical("HIGH THREAT BLOCKED", [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'threat_score' => $threatData['threat_score'],
            'threat_data' => $threatData,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Calculate block duration for stubborn attackers
     */
    private function calculateStubbornBlockDuration(int $violations, array $config): int
    {
        $baseDuration = $config['stubborn_base_duration'] ?? 3600; // 1 hour base
        $multiplier = $config['stubborn_multiplier'] ?? 2; // Exponential growth
        $maxDuration = $config['stubborn_max_duration'] ?? 86400; // 24 hours max
        
        // Exponential growth: base * (multiplier ^ (violations - stubborn_threshold))
        $stubbornThreshold = $config['stubborn_threshold'] ?? 50;
        $excessViolations = max(0, $violations - $stubbornThreshold);
        $duration = $baseDuration * pow($multiplier, $excessViolations);
        
        return min($duration, $maxDuration);
    }

    /**
     * Log stubborn attacker detection
     */
    private function logStubbornAttacker(string $identifier, string $context, int $violations): void
    {
        Log::channel('ddos_attacks')->critical("STUBBORN ATTACKER DETECTED", [
            'identifier' => $identifier,
            'context' => $context,
            'violations' => $violations,
            'ip' => $this->extractIpFromIdentifier($identifier),
            'timestamp' => now()->toISOString(),
            'action' => 'extended_block_applied'
        ]);
        
        // Send special notification for stubborn attackers
        $this->notificationService->sendStubbornAttackerAlert($identifier, $violations, $context);
    }

    /**
     * Get usage statistics for an identifier
     */
    public function getUsageStats(string $identifier, string $context): array
    {
        $stats = [];
        $windows = ['minute', 'hour', 'day'];
        
        foreach ($windows as $window) {
            $key = "{$context}_rate_limit_{$window}_{$identifier}";
            $stats["{$window}_usage"] = Cache::get($key, 0);
        }
        
        $stats['violations'] = Cache::get("{$context}_violations_{$identifier}", 0);
        $stats['is_blocked'] = $this->isBlocked($identifier, $context);
        
        if ($stats['is_blocked']) {
            $stats['retry_after'] = $this->getRetryAfter($identifier, $context);
        }
        
        return $stats;
    }

    /**
     * Reset security limits for an identifier
     */
    public function resetSecurityLimits(string $identifier, string $context): void
    {
        $windows = ['minute', 'hour', 'day'];
        
        foreach ($windows as $window) {
            Cache::forget("{$context}_rate_limit_{$window}_{$identifier}");
        }
        
        Cache::forget("{$context}_violations_{$identifier}");
        Cache::forget("{$context}_blocked_{$identifier}");
        
        // Security limits reset - no logging needed for admin actions
    }
}
