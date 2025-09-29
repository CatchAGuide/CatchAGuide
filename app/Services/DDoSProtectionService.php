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
        
        // Advanced security checks
        $advancedSecurity = $this->checkAdvancedSecurity($request, $identifier, $context);
        if ($advancedSecurity['blocked']) {
            return $advancedSecurity;
        }
        
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

        // High threat score blocking
        if ($threatData['threat_score'] > 80) {
            $this->logHighThreatBlock($request, $threatData);
            $this->blockIdentifier($identifier, $context, 1800, 0); // Block for 30 minutes
            return [
                'blocked' => true,
                'reason' => 'high_threat_score',
                'retry_after' => 1800,
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

    /**
     * Check advanced security features
     */
    private function checkAdvancedSecurity(Request $request, string $identifier, string $context): array
    {
        $advancedConfig = config('ddos.advanced_security', []);
        
        if (!$advancedConfig['enabled'] ?? false) {
            return ['blocked' => false];
        }

        // Check IP whitelist
        if ($this->isWhitelisted($request)) {
            return ['blocked' => false];
        }

        // Check geolocation blocking
        if ($this->isBlockedByGeolocation($request)) {
            $this->logGeolocationBlock($request, $identifier);
            return [
                'blocked' => true,
                'reason' => 'geolocation_blocked',
                'retry_after' => 0
            ];
        }

        // Check behavioral analysis
        if ($advancedConfig['enable_behavioral_analysis'] ?? false) {
            $behavioralResult = $this->analyzeBehavior($request, $identifier, $context);
            if ($behavioralResult['blocked']) {
                return $behavioralResult;
            }
        }

        // Check bot detection
        if ($this->isBot($request)) {
            $this->logBotDetection($request, $identifier);
            return [
                'blocked' => true,
                'reason' => 'bot_detected',
                'retry_after' => 300 // 5 minutes
            ];
        }

        return ['blocked' => false];
    }

    /**
     * Check if IP is whitelisted
     */
    private function isWhitelisted(Request $request): bool
    {
        $advancedConfig = config('ddos.advanced_security', []);
        
        if (!($advancedConfig['enable_ip_whitelist'] ?? false)) {
            return false;
        }

        $whitelistedIPs = $advancedConfig['whitelisted_ips'] ?? [];
        $clientIP = $request->ip();
        
        return in_array($clientIP, $whitelistedIPs);
    }

    /**
     * Check if request is blocked by geolocation
     */
    private function isBlockedByGeolocation(Request $request): bool
    {
        $advancedConfig = config('ddos.advanced_security', []);
        
        if (!($advancedConfig['enable_geolocation_blocking'] ?? false)) {
            return false;
        }

        $blockedCountries = $advancedConfig['blocked_countries'] ?? [];
        
        // Simple country detection based on IP (you might want to use a proper GeoIP service)
        $country = $this->getCountryFromIP($request->ip());
        
        return in_array($country, $blockedCountries);
    }

    /**
     * Analyze user behavior for suspicious patterns
     */
    private function analyzeBehavior(Request $request, string $identifier, string $context): array
    {
        $advancedConfig = config('ddos.advanced_security', []);
        $patterns = $advancedConfig['suspicious_behavior_patterns'] ?? [];
        
        $suspiciousScore = 0;
        $reasons = [];

        // Check rapid page navigation
        if ($patterns['rapid_page_navigation'] ?? false) {
            if ($this->hasRapidPageNavigation($identifier, $context)) {
                $suspiciousScore += 30;
                $reasons[] = 'rapid_page_navigation';
            }
        }

        // Check missing referrer
        if ($patterns['missing_referrer'] ?? false) {
            if ($this->hasMissingReferrer($request)) {
                $suspiciousScore += 20;
                $reasons[] = 'missing_referrer';
            }
        }

        // Check suspicious request sequences
        if ($patterns['suspicious_request_sequences'] ?? false) {
            if ($this->hasSuspiciousRequestSequence($identifier, $context)) {
                $suspiciousScore += 40;
                $reasons[] = 'suspicious_request_sequences';
            }
        }

        // Check high error rate
        if ($patterns['high_error_rate'] ?? false) {
            if ($this->hasHighErrorRate($identifier, $context)) {
                $suspiciousScore += 25;
                $reasons[] = 'high_error_rate';
            }
        }

        // Block if suspicious score is high enough
        if ($suspiciousScore >= 50) {
            $this->logBehavioralBlock($request, $identifier, $suspiciousScore, $reasons);
            return [
                'blocked' => true,
                'reason' => 'suspicious_behavior',
                'retry_after' => 600, // 10 minutes
                'suspicious_score' => $suspiciousScore,
                'reasons' => $reasons
            ];
        }

        return ['blocked' => false];
    }

    /**
     * Check if request is from a bot
     */
    private function isBot(Request $request): bool
    {
        $userAgent = $request->userAgent() ?? '';
        $legitimateUserAgents = config('ddos.advanced.legitimate_user_agents', []);
        
        // Check if user agent contains legitimate patterns
        foreach ($legitimateUserAgents as $pattern) {
            if (stripos($userAgent, $pattern) !== false) {
                return false; // Legitimate user agent
            }
        }

        // Check for bot patterns
        $botPatterns = [
            'bot', 'crawler', 'spider', 'scraper', 'curl', 'wget', 'python-requests',
            'java/', 'go-http', 'okhttp', 'libwww', 'lwp-trivial', 'wget', 'python-urllib'
        ];

        foreach ($botPatterns as $pattern) {
            if (stripos($userAgent, $pattern) !== false) {
                return true; // Bot detected
            }
        }

        // Check for missing or suspicious user agent
        if (empty($userAgent) || strlen($userAgent) < 10) {
            return true; // Suspicious user agent
        }

        return false;
    }

    /**
     * Check for rapid page navigation
     */
    private function hasRapidPageNavigation(string $identifier, string $context): bool
    {
        $key = "{$context}_page_navigation_{$identifier}";
        $navigation = Cache::get($key, []);
        $now = time();
        
        // Keep only last 60 seconds
        $navigation = array_filter($navigation, fn($timestamp) => $now - $timestamp < 60);
        
        // Add current request
        $navigation[] = $now;
        Cache::put($key, $navigation, 60);
        
        // Check if more than 10 page changes in 60 seconds
        return count($navigation) > 10;
    }

    /**
     * Check for missing referrer
     */
    private function hasMissingReferrer(Request $request): bool
    {
        $referrer = $request->header('referer');
        $path = $request->path();
        
        // Allow direct access to main pages
        $allowedPaths = ['', '/', '/guidings', '/vacations', '/about', '/contact'];
        if (in_array($path, $allowedPaths)) {
            return false;
        }
        
        // Check if referrer is missing for internal navigation
        return empty($referrer) && !$request->is('api/*');
    }

    /**
     * Check for suspicious request sequences
     */
    private function hasSuspiciousRequestSequence(string $identifier, string $context): bool
    {
        $key = "{$context}_request_sequence_{$identifier}";
        $sequence = Cache::get($key, []);
        $now = time();
        
        // Keep only last 300 seconds (5 minutes)
        $sequence = array_filter($sequence, fn($timestamp) => $now - $timestamp < 300);
        
        // Add current request
        $sequence[] = $now;
        Cache::put($key, $sequence, 300);
        
        // Check for burst pattern (many requests in short time)
        $recentRequests = array_filter($sequence, fn($timestamp) => $now - $timestamp < 30);
        return count($recentRequests) > 20;
    }

    /**
     * Check for high error rate
     */
    private function hasHighErrorRate(string $identifier, string $context): bool
    {
        $errorKey = "{$context}_errors_{$identifier}";
        $totalKey = "{$context}_total_{$identifier}";
        
        $errors = Cache::get($errorKey, 0);
        $total = Cache::get($totalKey, 0);
        
        // Increment total requests
        Cache::put($totalKey, $total + 1, 3600); // 1 hour
        
        // Check if error rate is above 50%
        return $total > 10 && ($errors / $total) > 0.5;
    }

    /**
     * Get country from IP (simplified implementation)
     */
    private function getCountryFromIP(string $ip): string
    {
        // This is a simplified implementation
        // In production, you should use a proper GeoIP service
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            // For now, return 'unknown' - implement proper GeoIP detection
            return 'unknown';
        }
        
        return 'private';
    }

    /**
     * Log behavioral block
     */
    private function logBehavioralBlock(Request $request, string $identifier, int $score, array $reasons): void
    {
        Log::channel('ddos_attacks')->warning('Behavioral block triggered', [
            'ip' => $request->ip(),
            'identifier' => $identifier,
            'user_agent' => $request->userAgent(),
            'suspicious_score' => $score,
            'reasons' => $reasons,
            'url' => $request->fullUrl()
        ]);
    }

    /**
     * Log bot detection
     */
    private function logBotDetection(Request $request, string $identifier): void
    {
        Log::channel('ddos_attacks')->warning('Bot detected and blocked', [
            'ip' => $request->ip(),
            'identifier' => $identifier,
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl()
        ]);
    }

    /**
     * Log geolocation block
     */
    private function logGeolocationBlock(Request $request, string $identifier): void
    {
        Log::channel('ddos_attacks')->warning('Request blocked by geolocation', [
            'ip' => $request->ip(),
            'identifier' => $identifier,
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl()
        ]);
    }
}
