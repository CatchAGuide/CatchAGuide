<?php

namespace App\Services\Translation;

class TranslationEngineFactory
{
    /**
     * Resolve a translation engine by driver name, defaulting to the configured
     * services.translation.driver ('gemini' or 'google').
     */
    public static function make(?string $driver = null): TranslationServiceInterface
    {
        $driver ??= (string) config('services.translation.driver', 'gemini');

        return match ($driver) {
            'google' => new GoogleTranslationService(),
            'gemini' => new GeminiTranslationService(),
            default => new GeminiTranslationService(),
        };
    }
}
