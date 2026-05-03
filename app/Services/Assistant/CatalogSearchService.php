<?php

namespace App\Services\Assistant;

use App\Services\Catalog\TripCatalogService;

/**
 * Server-side search over cached catalog slices (guidings, vacations, camps).
 */
final class CatalogSearchService
{
    public function __construct(
        private TripCatalogService $tripCatalog,
    ) {
    }

    /**
     * @param  array<int, string>  $types  guiding, vacation, camp (empty = all)
     * @return array<int, array<string, mixed>>
     */
    public function search(string $query, array $types, int $limit, ?float $maxPrice = null, bool $maxPriceStrict = false): array
    {
        $limit = max(1, min($limit, (int) config('booking_assistant.max_tool_rows', 8)));
        $types = array_values(array_filter(array_map('strtolower', $types)));

        $pool = [];
        if ($types === [] || in_array('guiding', $types, true)) {
            $pool = array_merge($pool, $this->tripCatalog->getGuidingTrips());
        }
        if ($types === [] || in_array('vacation', $types, true)) {
            $pool = array_merge($pool, $this->tripCatalog->getVacationTrips());
        }
        if ($types === [] || in_array('camp', $types, true)) {
            $pool = array_merge($pool, $this->tripCatalog->getCampTrips());
        }

        $needle = mb_strtolower(trim($query));
        if ($needle === '') {
            return [];
        }

        $scored = [];
        foreach ($pool as $row) {
            $score = $this->scoreRow($row, $needle);
            if ($score > 0) {
                $scored[] = ['score' => $score, 'row' => $row];
            }
        }

        if ($maxPrice !== null && $maxPrice > 0) {
            $scored = array_values(array_filter($scored, function (array $item) use ($maxPrice, $maxPriceStrict): bool {
                $row = $item['row'];
                if (! isset($row['min_price']) || ! is_numeric($row['min_price'])) {
                    return false;
                }
                $p = (float) $row['min_price'];

                return $maxPriceStrict ? ($p < $maxPrice) : ($p <= $maxPrice);
            }));
        }

        usort($scored, static fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        $out = [];
        foreach (array_slice($scored, 0, $limit) as $item) {
            $row = $item['row'];
            $image = null;
            if (isset($row['images']) && is_array($row['images']) && isset($row['images'][0]) && is_string($row['images'][0])) {
                $image = $row['images'][0];
            }
            $out[] = [
                'type' => $row['type'] ?? null,
                'title' => $row['title'] ?? '',
                'url' => $row['url'] ?? '',
                'min_price' => $row['min_price'] ?? null,
                'currency' => $row['currency'] ?? null,
                'image' => $image,
                'snippet' => $this->snippet((string) ($row['short_description'] ?? ''), 200),
            ];
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function scoreRow(array $row, string $needle): int
    {
        $hay = mb_strtolower(implode(' ', array_filter([
            $row['title'] ?? '',
            $row['slug'] ?? '',
            $row['short_description'] ?? '',
            $row['country'] ?? '',
            $row['region'] ?? '',
            $row['city'] ?? '',
            is_array($row['categories'] ?? null) ? implode(' ', $row['categories']) : '',
        ])));

        if ($hay === '') {
            return 0;
        }

        $score = 0;
        if (str_contains($hay, $needle)) {
            $score += 10;
        }

        foreach (preg_split('/\s+/', $needle, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $token) {
            if (mb_strlen($token) < 2) {
                continue;
            }
            if (str_contains($hay, $token)) {
                $score += 3;
            }
        }

        $score += $this->productTypeIntentBonus($needle, (string) ($row['type'] ?? ''));

        return $score;
    }

    /**
     * When the user names a product category, up-rank matching rows so vacations/camps are not drowned out by generic text matches.
     */
    private function productTypeIntentBonus(string $needle, string $rowType): int
    {
        $rowType = strtolower(trim($rowType));
        if ($rowType === '') {
            return 0;
        }

        $locale = app()->getLocale();

        if ($rowType === 'camp' && (
            (bool) preg_match('/\bcamps?\b/u', $needle)
            || str_contains($needle, 'fishing camp')
            || ($locale === 'de' && (bool) preg_match('/\b(angelcamp|fischercamp)\b/u', $needle))
        )) {
            return 8;
        }

        if ($rowType === 'vacation' && (
            (bool) preg_match('/\b(vacations?|packages?|package\s+deal)\b/u', $needle)
            || str_contains($needle, 'fishing holiday')
            || str_contains($needle, 'multi-day package')
            || ($locale === 'de' && (bool) preg_match('/\b(urlaubs?|anglerurlaub|ferien|pauschal\w*|schnupper\w*reise)\b/u', $needle))
        )) {
            return 8;
        }

        if ($rowType === 'guiding' && (
            (bool) preg_match('/\bguidings?\b/u', $needle)
            || (bool) preg_match('/\b(day\s+)?trip\s+with\s+(a\s+)?guide\b/u', $needle)
            || ($locale === 'de' && (bool) preg_match('/\b(guidings?|tagesguiding|einzelguiding)\b/u', $needle))
        )) {
            return 8;
        }

        return 0;
    }

    private function snippet(string $text, int $max): string
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags($text)) ?? '');

        return mb_strlen($text) <= $max ? $text : mb_substr($text, 0, $max) . '…';
    }
}
