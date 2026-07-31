<?php

namespace App\Services;

use App\Models\Guiding;
use App\Models\Method;
use App\Models\Target;
use App\Models\Water;

/**
 * Builds the "filter option => matching guiding ids" lookup tables used by the
 * guidings listing sidebar.
 */
class GuidingFilterMapBuilder
{
    public const PERSON_RANGE_MAX = 8;

    private const PRICE_BUCKET_SIZE = 50;

    /** Price buckets start at the €50 the UI slider defaults to. */
    private const PRICE_BUCKET_FLOOR = 50;

    private const DURATION_TYPES = ['half_day', 'full_day', 'multi_day'];

    /**
     * Identity of the current guiding data set. Any insert, update, delete or
     * status change moves this value, which is what expires the cached map.
     */
    public function fingerprint(): string
    {
        $state = Guiding::query()
            ->where('status', 1)
            ->selectRaw('COUNT(*) AS total, COALESCE(MAX(updated_at), 0) AS last_touched, COALESCE(MAX(id), 0) AS last_id')
            ->first();

        return md5(($state->total ?? 0) . '|' . ($state->last_touched ?? '') . '|' . ($state->last_id ?? 0));
    }

    public function build(): array
    {
        $guidings = Guiding::query()
            ->where('status', 1)
            ->select(['id', 'target_fish', 'fishing_methods', 'water_types', 'duration_type', 'max_guests', 'price', 'prices'])
            ->get();

        $knownOptions = [
            'targets' => Target::pluck('id')->map(fn ($id) => (int) $id)->all(),
            'methods' => Method::pluck('id')->map(fn ($id) => (int) $id)->all(),
            'water_types' => Water::pluck('id')->map(fn ($id) => (int) $id)->all(),
        ];

        $map = [
            'targets' => array_fill_keys($knownOptions['targets'], []),
            'methods' => array_fill_keys($knownOptions['methods'], []),
            'water_types' => array_fill_keys($knownOptions['water_types'], []),
            'duration_types' => array_fill_keys(self::DURATION_TYPES, []),
            'person_ranges' => array_fill_keys(range(1, self::PERSON_RANGE_MAX), []),
            'price_ranges' => [],
        ];

        $priceBounds = $this->priceBounds($guidings);
        for ($start = self::PRICE_BUCKET_FLOOR; $start <= $priceBounds['max']; $start += self::PRICE_BUCKET_SIZE) {
            $map['price_ranges'][$start . '-' . ($start + self::PRICE_BUCKET_SIZE)] = [];
        }

        $columns = [
            'targets' => 'target_fish',
            'methods' => 'fishing_methods',
            'water_types' => 'water_types',
        ];

        foreach ($guidings as $guiding) {
            $guidingId = (int) $guiding->id;

            foreach ($columns as $facet => $column) {
                foreach ($this->decodeOptionIds($guiding->{$column}, $knownOptions[$facet]) as $optionId) {
                    $map[$facet][$optionId][] = $guidingId;
                }
            }

            if (in_array($guiding->duration_type, self::DURATION_TYPES, true)) {
                $map['duration_types'][$guiding->duration_type][] = $guidingId;
            }

            // "Up to N people" is cumulative: a tour for 4 guests also serves 1, 2 and 3.
            $maxPersons = min(self::PERSON_RANGE_MAX, (int) $guiding->max_guests);
            for ($persons = 1; $persons <= $maxPersons; $persons++) {
                $map['person_ranges'][$persons][] = $guidingId;
            }

            $lowestPrice = $this->resolveLowestPrice($guiding);
            if ($lowestPrice > 0) {
                $bucket = $this->priceBucket($lowestPrice);
                if (isset($map['price_ranges'][$bucket])) {
                    $map['price_ranges'][$bucket][] = $guidingId;
                }
            }
        }

        $map['metadata'] = [
            'generated_at' => now()->toISOString(),
            'total_guidings' => $guidings->count(),
            'minPrice' => $priceBounds['min'],
            'maxPrice' => $priceBounds['max'],
            'counts' => $this->countsFor($map),
        ];

        return $map;
    }

    /**
     * Option ids stored in the JSON columns are not always clean — some listings
     * carry free-text entries such as "Thunfisch" alongside real ids, so anything
     * that is not a known option id is dropped.
     *
     * @param  array<int, int>  $knownOptionIds
     * @return array<int, int>
     */
    private function decodeOptionIds($raw, array $knownOptionIds): array
    {
        $decoded = json_decode((string) $raw, true);

        if (! is_array($decoded)) {
            return [];
        }

        $ids = [];
        foreach ($decoded as $value) {
            if (! is_numeric($value)) {
                continue;
            }

            $id = (int) $value;
            if (in_array($id, $knownOptionIds, true)) {
                $ids[$id] = $id;
            }
        }

        return array_values($ids);
    }

    /**
     * Per-guiding lowest price: cheapest per-person tier when `prices` holds valid
     * JSON, otherwise the flat `price` column.
     */
    private function resolveLowestPrice($guiding): float
    {
        if ($guiding->prices) {
            $prices = json_decode($guiding->prices, true);

            if (is_array($prices) && json_last_error() === JSON_ERROR_NONE) {
                $lowestPrice = null;

                foreach ($prices as $priceData) {
                    if (! is_array($priceData) || ! isset($priceData['person'], $priceData['amount'])) {
                        continue;
                    }

                    $person = (int) $priceData['person'];
                    $amount = (float) $priceData['amount'];
                    $perPerson = $person > 1 ? $amount / $person : $amount;

                    if ($lowestPrice === null || $perPerson < $lowestPrice) {
                        $lowestPrice = $perPerson;
                    }
                }

                if ($lowestPrice !== null) {
                    return $lowestPrice;
                }
            }
        }

        return (float) ($guiding->price ?? 0);
    }

    private function priceBucket(float $price): string
    {
        $start = max(
            self::PRICE_BUCKET_FLOOR,
            (int) (floor($price / self::PRICE_BUCKET_SIZE) * self::PRICE_BUCKET_SIZE)
        );

        return $start . '-' . ($start + self::PRICE_BUCKET_SIZE);
    }

    /**
     * Catalog-wide bounds rounded to €50 steps.
     *
     * @return array{min: int, max: int}
     */
    private function priceBounds($guidings): array
    {
        $minPrice = null;
        $maxPrice = 0;

        foreach ($guidings as $guiding) {
            $lowestPrice = $this->resolveLowestPrice($guiding);

            if ($lowestPrice <= 0) {
                continue;
            }

            if ($minPrice === null || $lowestPrice < $minPrice) {
                $minPrice = $lowestPrice;
            }

            if ($lowestPrice > $maxPrice) {
                $maxPrice = $lowestPrice;
            }
        }

        return [
            'min' => (int) max(self::PRICE_BUCKET_FLOOR, floor(($minPrice ?? self::PRICE_BUCKET_FLOOR) / self::PRICE_BUCKET_SIZE) * self::PRICE_BUCKET_SIZE),
            'max' => (int) ceil(($maxPrice ?: 5000) / self::PRICE_BUCKET_SIZE) * self::PRICE_BUCKET_SIZE,
        ];
    }

    /**
     * @return array<string, array<int|string, int>>
     */
    private function countsFor(array $map): array
    {
        $counts = [];

        foreach (['targets', 'methods', 'water_types', 'duration_types', 'person_ranges', 'price_ranges'] as $facet) {
            $counts[$facet] = array_map('count', $map[$facet]);
        }

        return $counts;
    }
}
