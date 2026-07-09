<?php

namespace App\Support;

use Illuminate\Support\Collection;

class AdminListingStats
{
    public static function statusSummary(Collection $items, string $statusField = 'status'): array
    {
        return [
            'total' => $items->count(),
            'active' => $items->where($statusField, 'active')->count(),
            'draft' => $items->where($statusField, 'draft')->count(),
            'inactive' => $items->whereNotIn($statusField, ['active', 'draft'])->count(),
        ];
    }

    public static function withImagesCount(Collection $items): int
    {
        return $items->filter(function ($item) {
            $gallery = $item->gallery_images ?? [];
            if (is_string($gallery)) {
                $gallery = json_decode($gallery, true) ?: [];
            }

            return !empty($item->thumbnail_path) || !empty($gallery);
        })->count();
    }

    /**
     * @return array<int, array{label: string, value: string|int}>
     */
    public static function cardsForStatusListings(Collection $items, string $totalLabel, string $statusField = 'status'): array
    {
        $summary = self::statusSummary($items, $statusField);

        return [
            ['label' => $totalLabel, 'value' => $summary['total']],
            ['label' => 'Active / Draft', 'value' => $summary['active'] . ' / ' . $summary['draft']],
            ['label' => 'With images', 'value' => self::withImagesCount($items)],
            ['label' => 'Inactive', 'value' => $summary['inactive']],
        ];
    }

    /**
     * @return array<int, array{label: string, value: string|int}>
     */
    public static function cardsForRentalBoats(Collection $items): array
    {
        $active = $items->where('status', 'active')->count();
        $inactive = $items->where('status', '!=', 'active')->count();
        $withPrice = $items->filter(fn ($boat) => isset($boat->prices['base_price']))->count();

        return [
            ['label' => 'Total boats', 'value' => $items->count()],
            ['label' => 'Active / Inactive', 'value' => $active . ' / ' . $inactive],
            ['label' => 'With price set', 'value' => $withPrice],
            ['label' => 'With images', 'value' => self::withImagesCount($items)],
        ];
    }

    /**
     * @return array<int, array{label: string, value: string|int}>
     */
    public static function cardsForTrips(Collection $items): array
    {
        $summary = self::statusSummary($items);
        $priced = $items->whereNotNull('price_per_person')->where('price_per_person', '>', 0)->count();
        $avgPrice = $items->whereNotNull('price_per_person')->where('price_per_person', '>', 0)->avg('price_per_person');

        return [
            ['label' => 'Total trips', 'value' => $summary['total']],
            ['label' => 'Active / Draft', 'value' => $summary['active'] . ' / ' . $summary['draft']],
            ['label' => 'With price set', 'value' => $priced],
            [
                'label' => 'Avg. price',
                'value' => $avgPrice ? '€' . number_format($avgPrice, 0) : '–',
            ],
        ];
    }
}
