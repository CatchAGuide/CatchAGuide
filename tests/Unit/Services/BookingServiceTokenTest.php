<?php

namespace Tests\Unit\Services;

use App\Services\BookingService;
use ReflectionMethod;
use Tests\TestCase;

class BookingServiceTokenTest extends TestCase
{
    private function generateToken(int $eventId): string
    {
        $method = new ReflectionMethod(BookingService::class, 'generateBookingToken');
        $method->setAccessible(true);

        return $method->invoke(new BookingService(), $eventId);
    }

    public function test_token_is_long_and_not_derived_from_the_event_id_alone(): void
    {
        $token = $this->generateToken(1);

        $this->assertGreaterThanOrEqual(40, strlen($token));
        // The old implementation was hash('sha256', $eventId.'-'.time()) — always exactly
        // 64 lowercase hex characters. Assert the new token isn't that predictable shape,
        // as a guard against silently reverting to a hash-of-known-inputs scheme.
        $this->assertDoesNotMatchRegularExpression('/^[0-9a-f]{64}$/', $token);
    }

    public function test_tokens_for_the_same_event_id_are_not_repeatable(): void
    {
        $first = $this->generateToken(42);
        $second = $this->generateToken(42);

        // Same input, generated back-to-back — the old sha256(eventId.'-'.time()) scheme
        // would very plausibly produce the same value here (time() resolution is seconds).
        $this->assertNotSame($first, $second);
    }
}
