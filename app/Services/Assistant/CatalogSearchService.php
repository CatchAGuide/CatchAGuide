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
    public function search(string $query, array $types, int $limit): array
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

        return $score;
    }

    private function snippet(string $text, int $max): string
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags($text)) ?? '');

        return mb_strlen($text) <= $max ? $text : mb_substr($text, 0, $max) . '…';
    }
}
