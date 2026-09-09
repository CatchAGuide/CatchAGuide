<?php

namespace Tests\Unit\Translation;

use App\Services\Translation\Support\FishingCopyGoogleTranslator;
use ReflectionMethod;
use Tests\TestCase;

/**
 * The Führung(en) -> guiding/Angelguidings + leading-capital normalization used to be copy-pasted
 * across ListingTranslationService, GuidingTranslationService and
 * GeminiTranslationService::googleTranslateFallback. It now lives in one place. These tests cover
 * the pure normalization logic without calling the real Google Translate engine.
 */
class FishingCopyGoogleTranslatorTest extends TestCase
{
    private function normalize(string $text): string
    {
        $translator = new FishingCopyGoogleTranslator();
        $method = new ReflectionMethod($translator, 'normalize');
        $method->setAccessible(true);

        return $method->invoke($translator, $text);
    }

    public function test_normalize_replaces_fuehrungen_with_angelguidings(): void
    {
        $this->assertSame('Book Angelguidings today', $this->normalize('book Führungen today'));
    }

    public function test_normalize_replaces_fuehrung_with_guiding(): void
    {
        $this->assertSame('This guiding is great', $this->normalize('this Führung is great'));
    }

    public function test_normalize_capitalizes_the_result(): void
    {
        $this->assertSame('Hello world', $this->normalize('hello world'));
    }

    public function test_batch_translate_passes_empty_fields_through_without_calling_the_engine(): void
    {
        $translator = new FishingCopyGoogleTranslator();

        $result = $translator->batchTranslate(['title' => ''], 'en', 'de');

        $this->assertSame(['title' => ''], $result);
    }
}
