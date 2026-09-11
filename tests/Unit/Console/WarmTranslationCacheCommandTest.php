<?php

namespace Tests\Unit\Console;

use App\Services\Translation\TranslationCircuitBreaker;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Only covers the guard rails (circuit breaker open, zero limit) that must
 * never touch the database or the network — actually warming a real record
 * would call the live translate() path, which is out of scope for this suite.
 */
class WarmTranslationCacheCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_skips_entirely_while_the_circuit_breaker_is_open(): void
    {
        for ($i = 0; $i < 5; $i++) {
            TranslationCircuitBreaker::recordFailure();
        }

        // A live attempt would hang for ~10s per string; finishing fast is part of the
        // proof this bailed out before ever querying a listing table.
        $start = microtime(true);
        $this->artisan('translations:warm')->assertSuccessful();
        $elapsed = microtime(true) - $start;

        $this->assertLessThan(1.0, $elapsed);
    }

    public function test_zero_limit_is_a_no_op(): void
    {
        $this->artisan('translations:warm --limit=0')->assertSuccessful();
    }
}
