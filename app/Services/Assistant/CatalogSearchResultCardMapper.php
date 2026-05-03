<?php

namespace App\Services\Assistant;

/**
 * Maps raw search_catalog tool rows into UI card payloads.
 */
final class CatalogSearchResultCardMapper
{
    /**
     * @param  array<int, mixed>  $decoded
     * @return list<array<string, mixed>>
     */
    public function mapRows(array $decoded): array
    {
        return array_values(array_filter(array_map(function ($row) {
            if (! is_array($row)) {
                return null;
            }

            $title = isset($row['title']) && is_string($row['title']) ? trim($row['title']) : '';
            $url = isset($row['url']) && is_string($row['url']) ? trim($row['url']) : '';
            if ($title === '' || $url === '') {
                return null;
            }

            $price = null;
            if (isset($row['min_price']) && (is_numeric($row['min_price']) || is_string($row['min_price']))) {
                $price = (float) $row['min_price'];
            }

            $currency = isset($row['currency']) && is_string($row['currency']) && $row['currency'] !== ''
                ? $row['currency']
                : 'EUR';

            return [
                'type' => isset($row['type']) && is_string($row['type']) ? $row['type'] : null,
                'title' => $title,
                'url' => $url,
                'price' => $price,
                'currency' => $currency,
                'snippet' => isset($row['snippet']) && is_string($row['snippet']) ? $row['snippet'] : '',
                'image' => isset($row['image']) && is_string($row['image']) ? $row['image'] : null,
            ];
        }, $decoded)));
    }
}
