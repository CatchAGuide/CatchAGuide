<?php

namespace App\Services\Translation\Support;

use Illuminate\Support\Facades\Log;
use Stichoza\GoogleTranslate\GoogleTranslate;

/**
 * The free, unofficial Google Translate engine (no API key, no per-request cost) used as the
 * default translator for listing/guiding/vacation copy, with the domain-specific normalization
 * (Führung(en) -> guiding/Angelguidings, leading capital) that was previously duplicated across
 * ListingTranslationService, GuidingTranslationService and GeminiTranslationService's fallback.
 */
class FishingCopyGoogleTranslator
{
    public function translate(string $text, string $toLanguage, string $fromLanguage = 'auto'): string
    {
        $translated = GoogleTranslate::trans($text, $toLanguage, $fromLanguage);

        return $this->normalize((string) $translated);
    }

    /**
     * @param  array<string, string>  $fields
     * @return array<string, string>
     */
    public function batchTranslate(array $fields, string $toLanguage, string $fromLanguage): array
    {
        $translated = [];

        foreach ($fields as $key => $text) {
            if ($text === '') {
                $translated[$key] = $text;

                continue;
            }

            try {
                $translated[$key] = $this->translate($text, $toLanguage, $fromLanguage);
            } catch (\Throwable $e) {
                Log::error('Google Translate failed for field', [
                    'key' => $key,
                    'error' => $e->getMessage(),
                ]);
                $translated[$key] = $text;
            }
        }

        return $translated;
    }

    private function normalize(string $translated): string
    {
        if (str_contains($translated, 'Führungen')) {
            $translated = str_replace('Führungen', 'Angelguidings', $translated);
        }

        if (str_contains($translated, 'Führung')) {
            $translated = str_replace('Führung', 'guiding', $translated);
        }

        return ucfirst($translated);
    }
}
