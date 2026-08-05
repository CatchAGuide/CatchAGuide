<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class MonthlyHighlight extends Model
{
    public const ITEM_TYPE_COUNTRY = 'country';

    public const ITEM_TYPE_TARGET = 'target';

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
     * @return Collection<int, array{type: string, id: int}>
     */
    public function normalizedItems(): Collection
    {
        return collect($this->items ?? [])
            ->filter(fn ($item) => is_array($item)
                && in_array($item['type'] ?? null, [self::ITEM_TYPE_COUNTRY, self::ITEM_TYPE_TARGET], true)
                && filled($item['id'] ?? null))
            ->map(fn (array $item) => [
                'type' => (string) $item['type'],
                'id' => (int) $item['id'],
            ])
            ->take(self::MAX_ITEMS)
            ->values();
    }
}
