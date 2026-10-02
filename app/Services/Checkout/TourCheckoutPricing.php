<?php

namespace App\Services\Checkout;

use App\Models\Guiding;
use Illuminate\Support\Collection;

/**
 * Server-authoritative pricing for one tour (guiding) checkout.
 *
 * Built once per request via for(): the guiding's extras accessor runs lookups, so the
 * normalized extras list is resolved here once and reused for the price table, quotes
 * and the booking payload.
 */
final class TourCheckoutPricing
{
    private const DEFAULT_MAX_GUESTS = 10;

    /**
     * @param  list<array{index: int, name: string, price: float}>  $extras
     */
    private function __construct(
        private readonly Guiding $guiding,
        private readonly array $extras,
    ) {}

    public static function for(Guiding $guiding): self
    {
        $raw = $guiding->pricing_extra;
        $items = $raw instanceof Collection ? $raw->values()->all() : array_values(decode_if_json($raw, true) ?: []);

        $extras = [];
        foreach ($items as $index => $item) {
            if (! is_array($item)) {
                continue;
            }

            $extras[] = [
                'index' => $index,
                'name' => (string) ($item['name'] ?? ''),
                'price' => round(max(0.0, (float) ($item['price'] ?? 0)), 2),
            ];
        }

        return new self($guiding, $extras);
    }

    public function maxGuests(): int
    {
        return max(1, (int) ($this->guiding->max_guests ?: self::DEFAULT_MAX_GUESTS));
    }

    public function isPerPerson(): bool
    {
        return $this->guiding->price_type === 'per_person';
    }

    /**
     * @return list<array{index: int, name: string, price: float}>
     */
    public function extras(): array
    {
        return $this->extras;
    }

    /**
     * Tour price (without extras) for a guest count. Per-person tiers store the tour total
     * for each guest count; fixed pricing is the tour total regardless of guests.
     */
    public function basePrice(int $persons): float
    {
        $persons = max(1, $persons);

        if (! $this->isPerPerson()) {
            return (float) $this->guiding->price;
        }

        $tiers = array_values(array_filter(
            decode_if_json($this->guiding->prices, true) ?: [],
            fn ($tier) => is_array($tier) && isset($tier['person'], $tier['amount'])
        ));

        if ($tiers === []) {
            return (float) $this->guiding->price_per_person * $persons;
        }

        foreach ($tiers as $tier) {
            if ((int) $tier['person'] === $persons && (float) $tier['amount'] > 0) {
                return (float) $tier['amount'];
            }
        }

        return (float) end($tiers)['amount'] * $persons;
    }

    /**
     * Base price for every bookable guest count, so the checkout page can update totals
     * without a server round trip. Submission still re-quotes on the server.
     *
     * @return array<int, float>
     */
    public function priceTable(): array
    {
        $table = [];
        for ($persons = 1; $persons <= $this->maxGuests(); $persons++) {
            $table[$persons] = round($this->basePrice($persons), 2);
        }

        return $table;
    }

    /**
     * @param  list<int>  $extraIndexes  Positions in extras(); unknown positions are ignored.
     */
    public function quote(int $persons, array $extraIndexes): TourCheckoutQuote
    {
        $persons = max(1, min($this->maxGuests(), $persons));
        $selected = array_flip(array_map('intval', $extraIndexes));

        $lines = [];
        foreach ($this->extras as $extra) {
            if (! isset($selected[$extra['index']])) {
                continue;
            }

            $lines[] = [
                'index' => $extra['index'],
                'name' => $extra['name'],
                'price' => $extra['price'],
                'quantity' => $persons,
                'total' => round($extra['price'] * $persons, 2),
            ];
        }

        return new TourCheckoutQuote($persons, round($this->basePrice($persons), 2), $lines);
    }
}
