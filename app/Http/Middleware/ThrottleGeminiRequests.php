<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ThrottleSearchRequests
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $identifier = $this->getIdentifier($request);
        
        // Check if IP is blocked
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

        // Check rate limits
        if (!$this->checkRateLimit($identifier)) {
            $this->recordViolation($identifier);
            return response()->json([
                'error' => 'Search rate limit exceeded. Please try again later.',
                'retry_after' => 60
            ], 429);
        }

        // Validate search input
        if (!$this->validateSearchInput($request)) {
            Log::warning('Invalid search input detected', [
                'ip' => $request->ip(),
                'input' => $request->all()
            ]);
            
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
        $limits = [
            'minute' => ['key' => "search_throttle_minute_{$identifier}", 'limit' => 20, 'window' => 60],
            'hour' => ['key' => "search_throttle_hour_{$identifier}", 'limit' => 200, 'window' => 3600],
            'day' => ['key' => "search_throttle_day_{$identifier}", 'limit' => 1000, 'window' => 86400]
        ];

        foreach ($limits as $period => $config) {
            $current = Cache::get($config['key'], 0);
            if ($current >= $config['limit']) {
                Log::warning("Search throttle limit exceeded - {$period}", [
                    'identifier' => $identifier,
                    'current' => $current,
                    'limit' => $config['limit']
                ]);
                return false;
            }
            
            Cache::put($config['key'], $current + 1, $config['window']);
        }

        return true;
    }

    private function validateSearchInput(Request $request): bool
    {
        // Check for search parameters
        $searchParams = ['place', 'city', 'country', 'region', 'target_fish', 'num_guests'];
        $hasSearchParam = false;
        
        foreach ($searchParams as $param) {
            if ($request->has($param) && !empty($request->get($param))) {
                $hasSearchParam = true;
                break;
            }
        }
        
        if (!$hasSearchParam) {
            return true; // No search params, allow request
        }

        // Validate place input
        if ($request->has('place')) {
            $place = $request->get('place');
            if (strlen($place) > 200) {
                return false;
            }
            
            // Check for suspicious patterns
            $suspiciousPatterns = [
                '/\b(script|javascript|eval|function|alert|prompt|confirm)\b/i',
                '/\b(admin|root|system|config|password|token|key)\b/i',
                '/[<>"\']/', // HTML/JS injection attempts
                '/\b(select|insert|update|delete|drop|create|alter)\b/i', // SQL injection
            ];

            foreach ($suspiciousPatterns as $pattern) {
                if (preg_match($pattern, $place)) {
                    return false;
                }
            }
        }

        // Validate num_guests
        if ($request->has('num_guests')) {
            $numGuests = $request->get('num_guests');
            if (!is_numeric($numGuests) || $numGuests < 1 || $numGuests > 20) {
                return false;
            }
        }

        // Validate target_fish
        if ($request->has('target_fish')) {
            $targetFish = $request->get('target_fish');
            if (is_array($targetFish)) {
                foreach ($targetFish as $fish) {
                    if (!is_numeric($fish) || $fish < 1) {
                        return false;
                    }
                }
            } elseif (!is_numeric($targetFish) || $targetFish < 1) {
                return false;
            }
        }

        return true;
    }

    private function isBlocked(string $identifier): bool
    {
        return Cache::has("search_blocked_{$identifier}");
    }

    private function recordViolation(string $identifier): void
    {
        $violationKey = "search_violations_{$identifier}";
        $violations = Cache::get($violationKey, 0) + 1;
        
        Cache::put($violationKey, $violations, 3600); // 1 hour
        
        // Block after 5 violations (more lenient than Gemini)
        if ($violations >= 5) {
            Cache::put("search_blocked_{$identifier}", true, 3600); // Block for 1 hour
            Log::critical('IP blocked for search abuse', [
                'identifier' => $identifier,
                'violations' => $violations
            ]);
        }
    }

    private function getRetryAfter(string $identifier): int
    {
        $violationKey = "search_violations_{$identifier}";
        $violations = Cache::get($violationKey, 0);
        
        // Progressive backoff: 1 hour, 4 hours, 24 hours
        $backoffHours = min(pow(2, $violations - 1), 24);
        return $backoffHours * 3600;
    }
}
