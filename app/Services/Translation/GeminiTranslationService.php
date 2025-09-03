<?php

namespace App\Services\Translation;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use App\Services\DDoSProtectionService;

class GeminiTranslationService
{
    private string $baseUrl;
    private string $apiKey;
    private Request $request;

    // Rate limiting constants
    private const MAX_REQUESTS_PER_MINUTE = 10;
    private const MAX_REQUESTS_PER_HOUR = 100;
    private const MAX_REQUESTS_PER_DAY = 500;
    
    // Input validation constants
    private const MAX_INPUT_LENGTH = 500;
    private const MIN_INPUT_LENGTH = 2;
    


    public function __construct()
    {
        $this->baseUrl = config('services.gemini.base_url') . '/' . config('services.gemini.model') . ':generateContent';
        $this->apiKey = config('services.gemini.key');
        $this->request = request();
    }

    public function translate(string $text): string
    {
        // DDoS Protection: Check rate limits and validate input
        $protectionService = app(DDoSProtectionService::class);
        $config = [
            'context' => 'gemini',
            'limits' => [
                'minute' => 10,
                'hour' => 100,
                'day' => 500
            ],
            'validate_input' => true,
            'block_threshold' => 10,
            'block_multiplier' => 30,
            'max_block_duration' => 1800
        ];
        
        $result = $protectionService->shouldBlockRequest(request(), $config);
        if ($result['blocked']) {
            throw new TranslationException('Rate limit exceeded. Please try again later.');
        }

        try {
            $response = $this->makeTranslationRequest($text);
            
            if ($response->failed()) {
                throw new TranslationException('Gemini API error: ' . $response->body());
            }

            $result = $this->parseResponse($response);
            
            // Cache the successful result
            $this->cacheTranslation($text, $result);
            
            // Log successful usage
            $this->logApiUsage($text, true);
            
            return $result;
        } catch (\Exception $e) {
            $this->logApiUsage($text, false);
            throw new TranslationException("Translation failed: {$e->getMessage()}");
        }
    }

    private function validateInput(string $text): bool
    {
        // Check length
        if (strlen($text) < self::MIN_INPUT_LENGTH || strlen($text) > self::MAX_INPUT_LENGTH) {
            Log::channel('gemini_usage')->warning('Invalid input length for translation', [
                'length' => strlen($text),
                'text' => substr($text, 0, 100)
            ]);
            return false;
        }

        // Check for suspicious patterns
        $suspiciousPatterns = [
            '/\b(script|javascript|eval|function|alert|prompt|confirm)\b/i',
            '/\b(admin|root|system|config|password|token|key)\b/i',
            '/[<>"\']/', // HTML/JS injection attempts
            '/\b(select|insert|update|delete|drop|create|alter)\b/i', // SQL injection
            '/\b(union|or|and)\s+\d+/i', // SQL injection patterns
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $text)) {
                Log::channel('gemini_usage')->warning('Suspicious input detected for translation', [
                    'pattern' => $pattern,
                    'text' => substr($text, 0, 100)
                ]);
                return false;
            }
        }

        // Check for excessive repetition (potential spam)
        $words = str_word_count($text, 1);
        if (count($words) > 0) {
            $wordCounts = array_count_values($words);
            $maxRepetition = max($wordCounts);
            if ($maxRepetition > count($words) * 0.5) { // More than 50% repetition
                Log::channel('gemini_usage')->warning('Excessive word repetition detected', [
                    'text' => substr($text, 0, 100),
                    'max_repetition' => $maxRepetition
                ]);
                return false;
            }
        }

        return true;
    }

    private function isEnglish(string $text): bool
    {
        // Simple English detection based on common English words
        $englishWords = [
            'the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by',
            'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had',
            'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might',
            'can', 'this', 'that', 'these', 'those', 'a', 'an', 'as', 'if', 'when',
            'where', 'why', 'how', 'what', 'who', 'which', 'all', 'any', 'some', 'no'
        ];

        $words = str_word_count(strtolower($text), 1);
        if (count($words) === 0) return true;

        $englishCount = 0;
        foreach ($words as $word) {
            if (in_array($word, $englishWords)) {
                $englishCount++;
            }
        }

        $englishRatio = $englishCount / count($words);
        return $englishRatio > 0.3; // 30% English words threshold
    }

    private function checkRateLimit(): bool
    {
        $identifier = $this->getRateLimitIdentifier();
        
        // Check per-minute limit
        $minuteKey = "gemini_rate_limit_minute_{$identifier}";
        $minuteAttempts = Cache::get($minuteKey, 0);
        if ($minuteAttempts >= self::MAX_REQUESTS_PER_MINUTE) {
            Log::channel('gemini_usage')->warning('Rate limit exceeded - per minute', ['identifier' => $identifier]);
            return false;
        }

        // Check per-hour limit
        $hourKey = "gemini_rate_limit_hour_{$identifier}";
        $hourAttempts = Cache::get($hourKey, 0);
        if ($hourAttempts >= self::MAX_REQUESTS_PER_HOUR) {
            Log::channel('gemini_usage')->warning('Rate limit exceeded - per hour', ['identifier' => $identifier]);
            return false;
        }

        // Check per-day limit
        $dayKey = "gemini_rate_limit_day_{$identifier}";
        $dayAttempts = Cache::get($dayKey, 0);
        if ($dayAttempts >= self::MAX_REQUESTS_PER_DAY) {
            Log::channel('gemini_usage')->warning('Rate limit exceeded - per day', ['identifier' => $identifier]);
            return false;
        }

        // Increment counters
        Cache::put($minuteKey, $minuteAttempts + 1, 60);
        Cache::put($hourKey, $hourAttempts + 1, 3600);
        Cache::put($dayKey, $dayAttempts + 1, 86400);

        return true;
    }

    private function getRateLimitIdentifier(): string
    {
        // Use user ID if authenticated, otherwise IP address
        if (auth()->check()) {
            return 'user_' . auth()->id();
        }
        
        return 'ip_' . $this->request->ip();
    }



    private function getCachedTranslation(string $text): ?string
    {
        $cacheKey = 'gemini_translation_' . md5($text);
        return Cache::get($cacheKey);
    }

    private function cacheTranslation(string $text, string $result): void
    {
        $cacheKey = 'gemini_translation_' . md5($text);
        // Cache for 24 hours
        Cache::put($cacheKey, $result, 86400);
    }

    private function makeTranslationRequest(string $text): Response
    {
        // Optimize prompt to reduce token usage
        $optimizedText = $this->optimizePrompt($text);
        
        return Http::timeout(30)->post("{$this->baseUrl}?key={$this->apiKey}", [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $optimizedText]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'topP' => 0.8,
                'topK' => 40,
                'maxOutputTokens' => 1024 // Reduced from 8192 to save costs
            ]
        ]);
    }

    private function optimizePrompt(string $text): string
    {
        // Truncate very long inputs to save on token costs
        if (strlen($text) > 200) {
            $text = substr($text, 0, 200) . '...';
        }

        // Add specific prompt for location translation
        return "Translate the following location name to English. Return only the English name, no explanations: {$text}";
    }

    private function parseResponse(Response $response): string
    {
        $data = $response->json();
        
        if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            throw new TranslationException('Unexpected response format from Gemini API');
        }

        return trim($data['candidates'][0]['content']['parts'][0]['text']);
    }

    private function logApiUsage(string $text, bool $success): void
    {
        $logData = [
            'timestamp' => now()->toISOString(),
            'ip' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
            'user_id' => auth()->id(),
            'text_length' => strlen($text),
            'text_preview' => substr($text, 0, 50),
            'success' => $success,
            'cost_estimate' => $this->estimateCost($text)
        ];

        Log::channel('gemini_usage')->info('Gemini API Usage', $logData);

        // Track daily usage for monitoring
        $dailyUsageKey = 'gemini_daily_usage_' . date('Y-m-d');
        $dailyUsage = Cache::get($dailyUsageKey, 0) + 1;
        Cache::put($dailyUsageKey, $dailyUsage, 86400);

        // Alert if usage exceeds threshold
        if ($dailyUsage > 1000) { // 1000 requests per day threshold
            $this->sendUsageAlert($dailyUsage);
            
            // Send email alert for high usage
            $notificationService = new DDoSNotificationService();
            $estimatedCost = $this->estimateCost($text) * $dailyUsage;
            $notificationService->sendHighUsageAlert($dailyUsage, $estimatedCost);
        }
    }

    private function estimateCost(string $text): float
    {
        // Rough estimate: Gemini charges per token
        // Input tokens: ~4 characters per token
        // Output tokens: ~4 characters per token
        $inputTokens = strlen($text) / 4;
        $outputTokens = strlen($text) / 4; // Assume similar output length
        $totalTokens = $inputTokens + $outputTokens;
        
        // Gemini pricing (approximate): $0.0005 per 1K tokens
        return ($totalTokens / 1000) * 0.0005;
    }

    private function sendUsageAlert(int $dailyUsage): void
    {
        // Log critical usage alert
        Log::channel('gemini_usage')->critical('High Gemini API usage detected', [
            'daily_usage' => $dailyUsage,
            'date' => date('Y-m-d'),
            'threshold' => 1000
        ]);

        // You can add email/Slack notifications here
        // Mail::to('admin@catchaguide.com')->send(new HighApiUsageAlert($dailyUsage));
    }

    /**
     * Get current usage statistics
     */
    public function getUsageStats(): array
    {
        $identifier = $this->getRateLimitIdentifier();
        
        return [
            'minute_usage' => Cache::get("gemini_rate_limit_minute_{$identifier}", 0),
            'hour_usage' => Cache::get("gemini_rate_limit_hour_{$identifier}", 0),
            'day_usage' => Cache::get("gemini_rate_limit_day_{$identifier}", 0),
            'daily_total' => Cache::get('gemini_daily_usage_' . date('Y-m-d'), 0)
        ];
    }

    /**
     * Clear all rate limiting data (admin function)
     */
    public function resetSecurityLimits(): void
    {
        $identifier = $this->getRateLimitIdentifier();
        
        Cache::forget("gemini_rate_limit_minute_{$identifier}");
        Cache::forget("gemini_rate_limit_hour_{$identifier}");
        Cache::forget("gemini_rate_limit_day_{$identifier}");
        
        Log::channel('gemini_usage')->info('Gemini security limits reset', ['identifier' => $identifier]);
    }
} 