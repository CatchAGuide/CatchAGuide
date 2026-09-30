<?php

namespace App\Services\Checkout\Camp;

/**
 * A nightly/daily rate with an optional weekly rate, as camps price accommodations and rental
 * boats. Mirrored by resources/js/checkout/camp-pricing.js (StayRate) for instant totals.
 */
final class StayRate
{
    public function __construct(
        public readonly ?float $daily,
        public readonly ?float $weekly,
    ) {}

    public static function from(mixed $daily, mixed $weekly): self
    {
        $positive = fn (mixed $value): ?float => is_numeric($value) && (float) $value > 0 ? round((float) $value, 2) : null;

        return new self($positive($daily), $positive($weekly));
    }

    public function isPriced(): bool
    {
        return $this->daily !== null || $this->weekly !== null;
    }

    /**
     * Cheapest price for a number of nights (or days): whole weeks at the weekly rate plus the
     * remaining nights at the daily rate, never more than paying every night at the daily rate.
     */
    public function total(int $units): float
    {
        $units = max(0, $units);

        if ($this->daily === null) {
            return $this->weekly === null ? 0.0 : round((int) ceil($units / 7) * $this->weekly, 2);
        }

        $allDaily = $units * $this->daily;
        if ($this->weekly === null || $units < 7) {
            return round($allDaily, 2);
        }

        $mixed = intdiv($units, 7) * $this->weekly + ($units % 7) * $this->daily;

        return round(min($allDaily, $mixed), 2);
    }

    /**
     * @return array{daily: ?float, weekly: ?float}
     */
    public function toArray(): array
    {
        return ['daily' => $this->daily, 'weekly' => $this->weekly];
    }
}
