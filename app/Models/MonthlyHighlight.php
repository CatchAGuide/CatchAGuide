<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class MonthlyHighlight extends Model
{
    public const ITEM_TYPE_COUNTRY = 'country';

    public const ITEM_TYPE_TARGET = 'target';

    public const ITEM_TYPE_PAIR = 'pair';

    public const MAX_ITEMS = 3;

    protected $fillable = [
        'month',
        'title_en',
        'title_de',
        'subtitle_en',
        'subtitle_de',
        'items',
        'is_active',
    ];

    protected $casts = [
        'month' => 'integer',
        'items' => 'array',
        'is_active' => 'boolean',
    ];

    public static function forMonth(int $month): ?self
    {
        return static::query()
            ->where('month', $month)
            ->where('is_active', true)
            ->first();
    }

    public function localizedTitle(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        return $locale === 'de'
            ? (string) $this->title_de
            : (string) $this->title_en;
    }

    public function localizedSubtitle(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        $value = $locale === 'de' ? $this->subtitle_de : $this->subtitle_en;

        return filled($value) ? (string) $value : null;
    }

    /**
     * Build stored items from admin card rows (country + target pairs).
     *
     * @param  array<int, mixed>  $cards
     * @return array<int, array{type: string, country_id: int, target_id: int}>
     */
    public static function itemsFromCardInput(array $cards): array
    {
        return collect($cards)
            ->map(function ($card) {
                if (! is_array($card)) {
                    return null;
                }

                $countryId = (int) ($card['country_id'] ?? 0);
                $targetId = (int) ($card['target_id'] ?? 0);

                if ($countryId < 1 || $targetId < 1) {
                    return null;
                }

                return [
                    'type' => self::ITEM_TYPE_PAIR,
                    'country_id' => $countryId,
                    'target_id' => $targetId,
                ];
            })
            ->filter()
            ->take(self::MAX_ITEMS)
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, array{type: string, id?: int, country_id?: int, target_id?: int}>
     */
    public function normalizedItems(): Collection
    {
        return collect($this->items ?? [])
            ->map(function ($item) {
                if (! is_array($item)) {
                    return null;
                }

                $type = $item['type'] ?? null;

                if ($type === self::ITEM_TYPE_PAIR) {
                    $countryId = (int) ($item['country_id'] ?? 0);
                    $targetId = (int) ($item['target_id'] ?? 0);

                    if ($countryId < 1 || $targetId < 1) {
                        return null;
                    }

                    return [
                        'type' => self::ITEM_TYPE_PAIR,
                        'country_id' => $countryId,
                        'target_id' => $targetId,
                    ];
                }

                if (in_array($type, [self::ITEM_TYPE_COUNTRY, self::ITEM_TYPE_TARGET], true)
                    && filled($item['id'] ?? null)) {
                    return [
                        'type' => (string) $type,
                        'id' => (int) $item['id'],
                    ];
                }

                return null;
            })
            ->filter()
            ->take(self::MAX_ITEMS)
            ->values();
    }
}
