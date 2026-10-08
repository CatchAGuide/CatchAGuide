<?php

namespace Tests\Unit\Support;

use App\Services\Translation\TranslationCircuitBreaker;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Covers the translate() helper's cache and circuit-breaker paths only — the
 * live Google Translate call itself is a real HTTP request to a third-party
 * scraping endpoint and is intentionally never exercised in this suite.
 */
class TranslateHelperCircuitBreakerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_empty_and_null_input_short_circuit_before_any_cache_or_network_work(): void
    {
        $this->assertSame('', translate(''));
        $this->assertSame('', translate(null));
    }

    public function test_returns_the_cached_translation_without_touching_the_network(): void
    {
        $string = 'Ein einzigartiger Teststring '.uniqid();
        Cache::forever(translation_cache_key($string, 'en'), 'A unique test string');

        $this->assertSame('A unique test string', translate($string, 'en'));
    }

    public function test_skips_the_live_call_and_returns_the_original_string_while_the_breaker_is_open(): void
    {
        for ($i = 0; $i < 5; $i++) {
            TranslationCircuitBreaker::recordFailure();
        }
        $this->assertTrue(TranslationCircuitBreaker::isOpen());

        $string = 'Noch nie gesehener Teststring '.uniqid();

        $start = microtime(true);
        $result = translate($string, 'en');
        $elapsed = microtime(true) - $start;

        $this->assertSame($string, $result);
        // A real attempt uses a 10s/5s timeout; finishing near-instantly is the proof
        // the live call was skipped rather than attempted and failed fast.
        $this->assertLessThan(1.0, $elapsed);
    }

    public function test_cache_key_is_deterministic_and_locale_scoped(): void
    {
        $a = translation_cache_key('Hallo Welt', 'en');
        $b = translation_cache_key('Hallo Welt', 'en');
        $c = translation_cache_key('Hallo Welt', 'de');

        $this->assertSame($a, $b);
        $this->assertNotSame($a, $c);
        $this->assertSame('translation_en_'.md5('Hallo Welt'), $a);
    }

    public function test_emoji_review_uses_a_fresh_cache_key_and_strips_symbols_for_the_api(): void
    {
        $string = "Ein toller Tag mit einem super Guide\u{1F44D}";

        $this->assertNotSame(
            'translation_en_'.md5($string),
            translation_cache_key($string, 'en')
        );
        $this->assertSame(
            'translation_v2_en_'.md5($string),
            translation_cache_key($string, 'en')
        );
        $this->assertSame('Ein toller Tag mit einem super Guide', translation_api_text($string));
        $this->assertSame("\u{1F44D}", translation_preserved_symbols($string));
    }

    public function test_emoji_review_does_not_reuse_a_cached_untranslated_original(): void
    {
        $string = "Ein toller Tag mit einem super Guide\u{1F44D}";
        Cache::forever('translation_en_'.md5($string), $string);
        Cache::forever(translation_cache_key($string, 'en'), 'A great day with a super guide '."\u{1F44D}");

        $this->assertSame('A great day with a super guide '."\u{1F44D}", translate($string, 'en'));
    }

    public function test_emoji_only_text_is_returned_without_a_live_call(): void
    {
        $string = "\u{1F44D}";

        $start = microtime(true);
        $result = translate($string, 'en');
        $elapsed = microtime(true) - $start;

        $this->assertSame($string, $result);
        $this->assertLessThan(1.0, $elapsed);
        $this->assertNull(Cache::get(translation_cache_key($string, 'en')));
    }
}
