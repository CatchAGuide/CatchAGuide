<?php

namespace App\Services\Sales;

/**
 * Priced result of a sales document's product cards: one group per card with its lines,
 * the grand total, the travel period and the card-level warnings/errors.
 *
 * A group: key, type, title, location, url, inclusions, partner, lines, subtotal, warnings,
 * errors. A line: key, item_type, extra, title, detail, formula, calculated, total, adjusted,
 * from, to plus the stored fields (listing_type, listing_id, persons, days, quantity, ...).
 */
final class SalesQuote
{
    /**
     * @param  list<array<string, mixed>>  $groups
     */
    public function __construct(
        public readonly array $groups,
        public readonly float $total,
        public readonly ?string $travelFrom,
        public readonly ?string $travelTo,
    ) {}

    public function lineCount(): int
    {
        return array_sum(array_map(fn (array $group) => count($group['lines']), $this->groups));
    }

    /**
     * @return list<string>
     */
    public function warnings(): array
    {
        return array_merge(...array_map(fn (array $group) => $group['warnings'], $this->groups ?: [['warnings' => []]]));
    }

    /**
     * @return list<string>
     */
    public function errors(): array
    {
        return array_merge(...array_map(fn (array $group) => $group['errors'], $this->groups ?: [['errors' => []]]));
    }

    /**
     * Guides and hosts of the products, once each (confirmation CC and host block).
     *
     * @return list<array{id: int, name: string, email: string, phone: string}>
     */
    public function partners(): array
    {
        $partners = [];
        foreach ($this->groups as $group) {
            $partner = $group['partner'] ?? null;
            if (is_array($partner) && ! isset($partners[$partner['id']])) {
                $partners[$partner['id']] = $partner;
            }
        }

        return array_values($partners);
    }

    /**
     * Product locations, once each (confirmation subject).
     *
     * @return list<string>
     */
    public function destinations(): array
    {
        return array_values(array_unique(array_filter(array_map(fn (array $group) => (string) ($group['location'] ?? ''), $this->groups))));
    }
}
