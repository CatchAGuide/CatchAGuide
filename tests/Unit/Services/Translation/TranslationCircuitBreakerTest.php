<?php

namespace Tests\Unit\Services\Translation;

use App\Services\Translation\TranslationCircuitBreaker;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TranslationCircuitBreakerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_is_closed_by_default(): void
    {
        $this->assertFalse(TranslationCircuitBreaker::isOpen());
    }

    public function test_stays_closed_below_the_failure_threshold(): void
    {
        for ($i = 0; $i < 4; $i++) {
            TranslationCircuitBreaker::recordFailure();
        }

        $this->assertFalse(TranslationCircuitBreaker::isOpen());
    }

    public function test_opens_after_reaching_the_failure_threshold(): void
    {
        for ($i = 0; $i < 5; $i++) {
            TranslationCircuitBreaker::recordFailure();
        }

        $this->assertTrue(TranslationCircuitBreaker::isOpen());
    }

    public function test_success_resets_the_failure_count(): void
    {
        for ($i = 0; $i < 4; $i++) {
            TranslationCircuitBreaker::recordFailure();
        }

        TranslationCircuitBreaker::recordSuccess();

        // Would trip at 5-in-a-row; after the reset these 4 alone must not open it.
        for ($i = 0; $i < 4; $i++) {
            TranslationCircuitBreaker::recordFailure();
        }

        $this->assertFalse(TranslationCircuitBreaker::isOpen());
    }
}
