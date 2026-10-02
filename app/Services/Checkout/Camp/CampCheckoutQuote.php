<?php

namespace App\Services\Checkout\Camp;

/**
 * Immutable price estimate for one camp checkout. The camp confirms the final price with its
 * offer, so this is stored as a guide value alongside the request.
 */
final class CampCheckoutQuote
{
    /**
     * @param  list<array{type: string, id: int, name: string, quantity: int, unit_price: ?float, amount: float}>  $lines
     */
    public function __construct(
        public readonly int $nights,
        public readonly int $persons,
        public readonly array $lines,
        public readonly string $currency,
    ) {}

    public function total(): float
    {
        return round(array_sum(array_column($this->lines, 'amount')), 2);
    }

    public function lineId(string $type): ?int
    {
        foreach ($this->lines as $line) {
            if ($line['type'] === $type) {
                return $line['id'];
            }
        }

        return null;
    }
}
