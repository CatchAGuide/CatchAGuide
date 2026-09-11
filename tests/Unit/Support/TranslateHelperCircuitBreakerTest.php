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
    }
}
