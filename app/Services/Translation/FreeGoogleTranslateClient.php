<?php

namespace App\Services\Translation;

use Stichoza\GoogleTranslate\GoogleTranslate;

/**
 * The one live call behind the translate() helper: Google Translate's free endpoint.
 * Kept in its own class so tests can swap it out and never reach Google.
 */
class FreeGoogleTranslateClient
{
    /**
     * @param  array{timeout?: int, connect_timeout?: int}  $httpOptions
     */
    public function translate(string $text, string $locale, array $httpOptions): string
    {
        return (string) GoogleTranslate::trans($text, $locale, null, $httpOptions);
    }
}
