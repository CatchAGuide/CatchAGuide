<?php

namespace App\Services\Translation;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Guards the live translate() helper against a degraded/blocked Google Translate
 * endpoint. Without this, every uncached string on a page (dozens on a listing
 * page) attempts its own ~10s live call, so a single blocked/slow window turns
 * into a full-page hang or timeout instead of one fast, logged failure.
 *
 * State is cache-backed (not per-process) so every request — and every worker —
 * shares the same open/closed state.
 */
class TranslationCircuitBreaker
{
    private const FAILURE_KEY = 'translate_circuit:failures';

    private const OPEN_UNTIL_KEY = 'translate_circuit:open_until';

    /** Consecutive failures within the window below that trip the breaker. */
    private const FAILURE_THRESHOLD = 5;

    /** Failure counter resets if this many seconds pass without another failure. */
    private const FAILURE_WINDOW_SECONDS = 60;

    /** How long to stop attempting live calls once tripped. */
    private const COOLDOWN_SECONDS = 300;

    public static function isOpen(): bool
    {
        $openUntil = Cache::get(self::OPEN_UNTIL_KEY);

        return $openUntil !== null && now()->timestamp < $openUntil;
    }

    public static function recordSuccess(): void
    {
        Cache::forget(self::FAILURE_KEY);
    }

    public static function recordFailure(): void
    {
        $failures = (int) Cache::get(self::FAILURE_KEY, 0) + 1;
        Cache::put(self::FAILURE_KEY, $failures, self::FAILURE_WINDOW_SECONDS);

        if ($failures >= self::FAILURE_THRESHOLD && ! self::isOpen()) {
            Cache::put(self::OPEN_UNTIL_KEY, now()->addSeconds(self::COOLDOWN_SECONDS)->timestamp, self::COOLDOWN_SECONDS);

            Log::warning('Translation circuit breaker opened after repeated Google Translate failures.', [
                'failures' => $failures,
                'cooldown_seconds' => self::COOLDOWN_SECONDS,
            ]);
        }
    }
}
