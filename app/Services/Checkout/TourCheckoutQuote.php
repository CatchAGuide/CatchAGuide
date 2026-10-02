<?php

namespace App\Services\Checkout;

/**
 * Immutable price breakdown for one tour checkout request.
 */
final class TourCheckoutQuote
{
    /**
     * @param  list<array{index: int, name: string, price: float, quantity: int, total: float}>  $extras
     */
    public function __construct(
        public readonly int $persons,
        public readonly float $basePrice,
        public readonly array $extras,
    ) {}

    public function extrasTotal(): float
    {
        return round(array_sum(array_column($this->extras, 'total')), 2);
    }

    public function total(): float
    {
        return round($this->basePrice + $this->extrasTotal(), 2);
    }

    /**
     * Extras in the serialized shape bookings store (see BookingService / thank-you page).
     */
    public function serializedExtras(): ?string
    {
        if ($this->extras === []) {
            return null;
        }

        return serialize(array_map(fn (array $extra) => [
            'extra_id' => $extra['index'],
            'extra_name' => $extra['name'],
            'extra_price' => $extra['price'],
            'extra_quantity' => $extra['quantity'],
            'extra_total_price' => $extra['total'],
        ], $this->extras));
    }
}
