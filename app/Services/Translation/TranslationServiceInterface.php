<?php

namespace App\Services\Translation;

interface TranslationServiceInterface
{
    /**
     * Translate text using the translation service
     *
     * @param string $text The text to translate
     * @return string The translated text
     * @throws TranslationException
     */
    public function translate(string $text): string;

    /**
     * Batch translate multiple texts
     *
     * @param array $texts Array of texts to translate (key => value pairs)
     * @param string $toLanguage Target language code (e.g., 'en', 'de')
     * @param string $fromLanguage Source language code (e.g., 'en', 'de')
     * @return array Translated texts with same keys
     * @throws TranslationException
     */
    public function batchTranslate(array $texts, string $toLanguage, string $fromLanguage = 'auto'): array;

    /**
     * Detect the language of given text
     *
     * @param string $text The text to analyze
     * @return string The detected language code (e.g., 'en', 'de')
     * @throws TranslationException
     */
    public function detectLanguage(string $text): string;
}

