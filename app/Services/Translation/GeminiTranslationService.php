<?php

namespace App\Services\Translation;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use App\Services\DDoSProtectionService;
use App\Services\DDoSNotificationService;
use Stichoza\GoogleTranslate\GoogleTranslate;

class GeminiTranslationService implements TranslationServiceInterface
{
    private string $endpoint;
    private string $apiKey;

    // Rate limiting constants
    private const MAX_REQUESTS_PER_MINUTE = 10;
    private const MAX_REQUESTS_PER_HOUR = 100;
    private const MAX_REQUESTS_PER_DAY = 500;
    
    // Input validation constants
    private const MAX_INPUT_LENGTH = 500;
    private const MIN_INPUT_LENGTH = 2;

    public function __construct()
    {
        $base  = rtrim(config('services.gemini.base_url'), '/');
        $model = trim(config('services.gemini.model'));
        $this->endpoint = "{$base}/{$model}:generateContent";
        $this->apiKey   = (string) config('services.gemini.key');
    }

    public function translate(string $text, string $targetLanguage = 'en'): string
    {
        // IMPORTANT: Check cache FIRST to avoid unnecessary API calls
        $cached = $this->getCachedTranslation($text);
        if ($cached !== null) {
            return $cached;
        }

        // Skip DDoS protection in console context (artisan commands)
        if (app()->runningInConsole()) {
            // Use simple rate limiting for console commands
            if (!$this->checkRateLimit()) {
                throw new TranslationException('Rate limit exceeded. Please try again later.');
            }
        } else {
            // DDoS Protection for web requests only
            try {
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
            } catch (\Exception $e) {
                // If DDoS protection fails (e.g., no session), just continue
                Log::warning('DDoS protection skipped: ' . $e->getMessage());
            }
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
            
            // Fallback to Google Translate
            Log::channel('gemini_usage')->warning('Gemini translation failed, falling back to Google Translate', [
                'error' => $e->getMessage(),
                'text' => substr($text, 0, 100)
            ]);
            
            try {
                return $this->googleTranslateFallback($text, $targetLanguage);
            } catch (\Exception $fallbackError) {
                Log::error('Both Gemini and Google Translate failed', [
                    'gemini_error' => $e->getMessage(),
                    'google_error' => $fallbackError->getMessage(),
                    'text' => substr($text, 0, 100)
                ]);
                
                // Return original text as last resort
                return $text;
            }
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
        // Use user ID if authenticated
        if (auth()->check()) {
            return 'user_' . auth()->id();
        }
        
        // If running in console, use 'console' as identifier
        if (app()->runningInConsole()) {
            return 'console';
        }
        
        // Otherwise use IP address (for web requests)
        try {
            return 'ip_' . request()->ip();
        } catch (\Exception $e) {
            return 'unknown';
        }
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
        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $text]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'topP' => 0.8,
                'topK' => 40,
                'maxOutputTokens' => 1024 // Reduced from 8192 to save costs
            ],
        ];
    
        // IMPORTANT: NO RETRY to prevent excessive API usage and costs
        // Each retry = another API call = more money spent
        $resp = Http::timeout(20)
            ->withHeaders([
                'Content-Type'   => 'application/json',
                'x-goog-api-key' => $this->apiKey,
            ])
            ->post($this->endpoint, $payload);
    
        // Helpful message for referrer-locked keys
        if ($resp->status() === 403 && (
            str_contains($resp->body(), 'API_KEY_HTTP_REFERRER_BLOCKED') ||
            str_contains($resp->body(), 'referer') ||
            str_contains($resp->body(), 'referrer')
        )) {
            throw new TranslationException(
                "Your Gemini API key is restricted to HTTP referrers. " .
                "Set Application restriction to 'None' (keep API restriction = Generative Language API)."
            );
        }
    
        // Helpful message for rate limit errors
        if ($resp->status() === 429) {
            throw new TranslationException(
                "Gemini API rate limit exceeded. Free tier limits: 15 requests/minute, 1,500 requests/day. " .
                "Please wait a moment before trying again or consider upgrading your API plan."
            );
        }
    
        return $resp;
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
            'user_id' => auth()->id(),
            'text_length' => strlen($text),
            'text_preview' => substr($text, 0, 50),
            'success' => $success,
            'cost_estimate' => $this->estimateCost($text),
            'context' => app()->runningInConsole() ? 'console' : 'web'
        ];

        // Add request info only if available (web context)
        if (!app()->runningInConsole()) {
            try {
                $logData['ip'] = request()->ip();
                $logData['user_agent'] = request()->userAgent();
            } catch (\Exception $e) {
                $logData['ip'] = 'N/A';
                $logData['user_agent'] = 'N/A';
            }
        }

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

    /**
     * Batch translate multiple texts
     *
     * @param array $texts Array of texts to translate (key => value pairs)
     * @param string $toLanguage Target language code (e.g., 'en', 'de')
     * @param string $fromLanguage Source language code (e.g., 'en', 'de')
     * @return array Translated texts with same keys
     * @throws TranslationException
     */
    public function batchTranslate(array $texts, string $toLanguage, string $fromLanguage = 'auto'): array
    {
        try {
            $languageNames = [
                'de' => 'German', 
                'en' => 'English', 
                'es' => 'Spanish', 
                'fr' => 'French', 
                'it' => 'Italian', 
                'ja' => 'Japanese', 
                'ko' => 'Korean', 
                'pt' => 'Portuguese', 
                'ru' => 'Russian', 
                'zh' => 'Chinese'
            ];

            $fromLanguageName = $languageNames[$fromLanguage] ?? $fromLanguage;
            $toLanguageName = $languageNames[$toLanguage] ?? $toLanguage;

            // Format texts for translation
            $forTranslate = json_encode($texts, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

            $prompt = "Translate the following JSON object from {$fromLanguageName} to {$toLanguageName}. " .
                      "Keep the JSON structure and keys exactly as they are. Only translate the values. " .
                      "Return only valid JSON without any markdown formatting or code blocks.\n\n" .
                      "{$forTranslate}";

            $translatedJson = $this->translate($prompt);

            // Remove markdown code blocks if present
            $translatedJson = preg_replace('/^```json\s*|\s*```\s*$/m', '', $translatedJson);
            $translatedJson = trim($translatedJson);

            // Decode the result
            $translated = json_decode($translatedJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Failed to decode Gemini batch translation response', [
                    'json_error' => json_last_error_msg(),
                    'response' => substr($translatedJson, 0, 500)
                ]);
                // Fallback to original texts
                return $texts;
            }

            return $translated;
        } catch (\Exception $e) {
            Log::error('Gemini Batch Translation failed', [
                'to_language' => $toLanguage,
                'from_language' => $fromLanguage,
                'error' => $e->getMessage()
            ]);
            throw new TranslationException("Batch translation failed: {$e->getMessage()}");
        }
    }

    /**
     * Detect the language of given text using Gemini
     *
     * @param string $text The text to analyze
     * @return string The detected language code (e.g., 'en', 'de')
     * @throws TranslationException
     */
    public function detectLanguage(string $text): string
    {
        try {
            $prompt = "Analyze the following text and determine what language it is written in. " .
                      "Respond with only the 2-letter ISO language code (e.g., 'de' for German, 'en' for English, 'es' for Spanish, etc.).\n\n" .
                      "Text: {$text}";

            $detectedLanguage = $this->translate($prompt);
            $detectedLanguage = strtolower(trim($detectedLanguage));

            // Validate the detected language is a 2-letter code
            if (preg_match('/^[a-z]{2}$/', $detectedLanguage)) {
                return $detectedLanguage;
            }

            return 'de'; // Default fallback
        } catch (\Exception $e) {
            Log::error('Gemini language detection failed', [
                'text' => substr($text, 0, 100),
                'error' => $e->getMessage()
            ]);
            return 'de'; // Default fallback
        }
    }


    /**
     * Fallback to Google Translate when Gemini fails
     * 
     * @param string $text Text to translate
     * @param string $targetLanguage Target language code (default: 'en')
     * @return string Translated text
     */
    private function googleTranslateFallback(string $text, string $targetLanguage = 'en'): string
    {
        try {
            $translate = GoogleTranslate::trans($text, $targetLanguage);

            // Apply the same custom replacements as in the translate helper
            if (strpos($translate, 'Führungen')) {
                $translate = str_replace('Führungen', 'Angelguidings', $translate);
            }

            if (strpos($translate, 'Führung')) {
                $translate = str_replace('Führung', 'guiding', $translate);
            }

            Log::channel('gemini_usage')->info('Google Translate fallback succeeded', [
                'text' => substr($text, 0, 50),
                'result' => substr($translate, 0, 50)
            ]);

            return ucfirst($translate);
        } catch (\Exception $e) {
            Log::error('Google Translate fallback failed', [
                'error' => $e->getMessage(),
                'text' => substr($text, 0, 100)
            ]);
            throw $e;
        }
    }
} 