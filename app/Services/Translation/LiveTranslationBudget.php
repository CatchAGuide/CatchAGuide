<?php

namespace App\Services\Translation;

/**
 * Caps how many live translate() calls one web request may make, and how long they may take
 * in total. Without it a page with many uncached strings waited on each one (up to 10s) and
 * crawlers got 15–50s responses or 5xx timeouts. Once the budget is spent, translate() returns
 * the original string and queues TranslateStringJob instead.
 *
 * Console commands and queued jobs (translations:warm, TranslateStringJob) are not limited.
 * Bound as scoped, so every request and every queued job starts with a fresh budget.
 */
class LiveTranslationBudget
{
    private int $calls = 0;

    private float $spentMs = 0.0;

    private ?bool $enforced = null;

    public function enforce(bool $enforced = true): static
    {
        $this->enforced = $enforced;

        return $this;
    }

    public function isEnforced(): bool
    {
        return $this->enforced ?? ! app()->runningInConsole();
    }

    public function allowsLiveCall(): bool
    {
        if (! $this->isEnforced()) {
            return true;
        }

        return $this->calls < (int) config('services.translation.live_budget.max_calls', 3)
            && $this->spentMs < (int) config('services.translation.live_budget.max_ms', 2000);
    }

    public function record(float $milliseconds): void
    {
        $this->calls++;
        $this->spentMs += $milliseconds;
    }

    /**
     * @return array{timeout: int, connect_timeout: int}
     */
    public function httpOptions(): array
    {
        if (! $this->isEnforced()) {
            return ['timeout' => 10, 'connect_timeout' => 5];
        }

        return [
            'timeout' => (int) config('services.translation.live_budget.timeout', 3),
            'connect_timeout' => (int) config('services.translation.live_budget.connect_timeout', 2),
        ];
    }
}
