<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\DDoSNotificationService;
use Symfony\Component\HttpFoundation\Response;

class ThrottleSearchRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        $identifier = $this->getIdentifier($request);
        
        if ($this->isBlocked($identifier)) {
            Log::warning('Blocked IP attempted search request', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'identifier' => $identifier,
                'url' => $request->fullUrl()
            ]);
            
            return response()->json([
                'error' => 'Too many search requests. Please try again later.',
                'retry_after' => $this->getRetryAfter($identifier)
            ], 429);
        }

        if (!$this->checkRateLimit($identifier)) {
            $this->recordViolation($identifier);
            
            // Send email alert for rate limit violations
            $notificationService = new DDoSNotificationService();
            $violations = Cache::get("search_violations_{$identifier}", 0);
            $notificationService->sendRateLimitAlert($identifier, $violations, $request->fullUrl());
            
            return response()->json([
                'error' => 'Search rate limit exceeded. Please try again later.',
                'retry_after' => 60
            ], 429);
        }

        if (!$this->validateSearchInput($request)) {
            Log::channel('ddos_attacks')->warning('Invalid search input detected', [
                'ip' => $request->ip(),
                'input' => $request->all(),
                'user_agent' => $request->userAgent(),
                'endpoint' => $request->fullUrl()
            ]);
            
            // Send email alert for suspicious input
            $notificationService = new DDoSNotificationService();
            $input = json_encode($request->all());
            $notificationService->sendSuspiciousInputAlert($identifier, $input, 'Malicious pattern detected');
            
            return response()->json([
                'error' => 'Invalid search parameters provided.'
            ], 400);
        }

        return $next($request);
    }

    private function getIdentifier(Request $request): string
    {
        if (auth()->check()) {
            return 'user_' . auth()->id();
        }
        
        return 'ip_' . $request->ip();
    }

    private function checkRateLimit(string $identifier): bool
    {
        // Check per-minute limit (20 requests)
        $minuteKey = "search_rate_limit_minute_{$identifier}";
        $minuteAttempts = Cache::get($minuteKey, 0);
        if ($minuteAttempts >= 20) {
            return false;
        }

        // Check per-hour limit (200 requests)
        $hourKey = "search_rate_limit_hour_{$identifier}";
        $hourAttempts = Cache::get($hourKey, 0);
        if ($hourAttempts >= 200) {
            return false;
        }

        // Check per-day limit (1000 requests)
        $dayKey = "search_rate_limit_day_{$identifier}";
        $dayAttempts = Cache::get($dayKey, 0);
        if ($dayAttempts >= 1000) {
            return false;
        }

        // Increment counters
        Cache::put($minuteKey, $minuteAttempts + 1, 60);
        Cache::put($hourKey, $hourAttempts + 1, 3600);
        Cache::put($dayKey, $dayAttempts + 1, 86400);

        return true;
    }

    private function validateSearchInput(Request $request): bool
    {
        $input = $request->all();
        
        // Check for suspicious patterns
        $suspiciousPatterns = [
            '/\b(script|javascript|eval|function|alert|prompt|confirm)\b/i',
            '/\b(admin|root|system|config|password|token|key)\b/i',
            '/[<>"\']/', // HTML/JS injection attempts
            '/\b(select|insert|update|delete|drop|create|alter)\b/i', // SQL injection
            '/\b(union|or|and)\s+\d+/i', // SQL injection patterns
        ];

        foreach ($input as $key => $value) {
            if (is_string($value)) {
                // Check length
                if (strlen($value) > 500) {
                    return false;
                }
                
                // Check for suspicious patterns
                foreach ($suspiciousPatterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    private function isBlocked(string $identifier): bool
    {
        $blockKey = "search_blocked_{$identifier}";
        $blockData = Cache::get($blockKey);
        
        if ($blockData && $blockData['expires_at'] > time()) {
            return true;
        }
        
        return false;
    }

    private function recordViolation(string $identifier): void
    {
        $violationKey = "search_violations_{$identifier}";
        $violations = Cache::get($violationKey, 0) + 1;
        
        // Store violations for 24 hours
        Cache::put($violationKey, $violations, 86400);
        
        // Progressive blocking based on violations
        if ($violations >= 5) {
            $blockDuration = min($violations * 60, 3600); // Max 1 hour
            $this->blockIdentifier($identifier, $blockDuration);
        }
    }

    private function blockIdentifier(string $identifier, int $duration): void
    {
        $blockKey = "search_blocked_{$identifier}";
        Cache::put($blockKey, [
            'blocked_at' => time(),
            'expires_at' => time() + $duration
        ], $duration);
        
        Log::channel('ddos_attacks')->warning('IP blocked for search violations', [
            'identifier' => $identifier,
            'duration' => $duration,
            'ip' => $this->extractIpFromIdentifier($identifier),
            'violations' => Cache::get("search_violations_{$identifier}", 0)
        ]);
        
        // Send email alert for IP blocking
        $notificationService = new DDoSNotificationService();
        $violations = Cache::get("search_violations_{$identifier}", 0);
        $notificationService->sendIPBlockAlert($identifier, $duration / 60, $violations);
    }

    private function getRetryAfter(string $identifier): int
    {
        $blockKey = "search_blocked_{$identifier}";
        $blockData = Cache::get($blockKey);
        
        if ($blockData) {
            return max(0, $blockData['expires_at'] - time());
        }
        
        return 60; // Default 1 minute
    }

    private function extractIpFromIdentifier(string $identifier): string
    {
        if (strpos($identifier, 'ip_') === 0) {
            return substr($identifier, 3);
        }
        return $identifier;
    }
}
