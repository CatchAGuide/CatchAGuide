<?php

namespace App\Services\Translation;

use Stichoza\GoogleTranslate\GoogleTranslate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GoogleTranslationService implements TranslationServiceInterface
{
    private GoogleTranslate $translator;
    
    public function __construct()
    {
        $this->translator = new GoogleTranslate();
    }

    /**
     * Translate text using Google Translate
     *
     * @param string $text The text to translate
     * @return string The translated text
     * @throws TranslationException
     */
    public function translate(string $text): string
    {
        try {
            // Check cache first
            $cached = $this->getCachedTranslation($text);
            if ($cached !== null) {
                return $cached;
            }

            // Perform translation
            $result = $this->translator->translate($text);
            
            if ($result === false || empty($result)) {
                throw new TranslationException('Google Translate returned empty result');
            }

            // Cache the result
            $this->cacheTranslation($text, $result);
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Google Translation failed', [
                'text' => substr($text, 0, 100),
                'error' => $e->getMessage()
            ]);
            throw new TranslationException("Translation failed: {$e->getMessage()}");
        }
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
            $this->translator->setSource($fromLanguage);
            $this->translator->setTarget($toLanguage);
            
            $translated = [];
            
            foreach ($texts as $key => $text) {
                if (empty($text) || !is_string($text)) {
                    $translated[$key] = $text;
                    continue;
                }

                // Check cache first
                $cacheKey = $this->getBatchCacheKey($text, $toLanguage, $fromLanguage);
                $cachedResult = Cache::get($cacheKey);
                
                if ($cachedResult !== null) {
                    $translated[$key] = $cachedResult;
                    continue;
                }

                // Translate
                $result = $this->translator->translate($text);
                
                if ($result === false || empty($result)) {
                    Log::warning('Google Translate returned empty result for key', ['key' => $key]);
                    $translated[$key] = $text; // Fallback to original
                    continue;
                }

                $translated[$key] = $result;
                
                // Cache individual translation
                Cache::put($cacheKey, $result, 86400); // 24 hours
                
                // Small delay to avoid rate limits
                usleep(100000); // 0.1 second
            }

            return $translated;
        } catch (\Exception $e) {
            Log::error('Google Batch Translation failed', [
                'to_language' => $toLanguage,
                'from_language' => $fromLanguage,
                'error' => $e->getMessage()
            ]);
            throw new TranslationException("Batch translation failed: {$e->getMessage()}");
        }
    }

    /**
     * Detect the language of given text
     *
     * @param string $text The text to analyze
     * @return string The detected language code (e.g., 'en', 'de')
     * @throws TranslationException
     */
    public function detectLanguage(string $text): string
    {
        try {
            // Use Google Translate's ability to detect language
            $this->translator->setSource('auto');
            $this->translator->setTarget('en');
            
            // Make a translation to trigger language detection
            $this->translator->translate($text);
            
            // Get the detected source language
            $detectedLanguage = $this->translator->getLastDetectedSource();
            
            if (empty($detectedLanguage)) {
                Log::warning('Language detection returned empty, defaulting to "de"');
                return 'de'; // Default fallback
            }

            // Validate the detected language is a 2-letter code
            if (preg_match('/^[a-z]{2}$/', strtolower($detectedLanguage))) {
                return strtolower($detectedLanguage);
            }

            return 'de'; // Default fallback
        } catch (\Exception $e) {
            Log::error('Language detection failed', [
                'text' => substr($text, 0, 100),
                'error' => $e->getMessage()
            ]);
            return 'de'; // Default fallback
        }
    }

    /**
     * Get cached translation
     */
    private function getCachedTranslation(string $text): ?string
    {
        $cacheKey = 'google_translation_' . md5($text);
        return Cache::get($cacheKey);
    }

    /**
     * Cache translation result
     */
    private function cacheTranslation(string $text, string $result): void
    {
        $cacheKey = 'google_translation_' . md5($text);
        Cache::put($cacheKey, $result, 86400); // 24 hours
    }

    /**
     * Get batch cache key
     */
    private function getBatchCacheKey(string $text, string $toLanguage, string $fromLanguage): string
    {
        return 'google_translation_' . md5($text . '_' . $fromLanguage . '_' . $toLanguage);
    }
}

