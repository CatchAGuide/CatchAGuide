<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class CategoryEntity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'parent_id',
        'country_id',
        'region_id',
        'name',
        'slug',
        'countrycode',
        'filters',
        'thumbnail_path',
    ];

    protected $casts = [
        'filters' => 'array',
    ];

    public function scopeCountries($query)
    {
        return $query->where('type', 'country');
    }

    public function scopeRegions($query)
    {
        return $query->where('type', 'region');
    }

    public function scopeCities($query)
    {
        return $query->where('type', 'city');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function country()
    {
        return $this->belongsTo(self::class, 'country_id');
    }

    public function region()
    {
        return $this->belongsTo(self::class, 'region_id');
    }

    public function getThumbnailPath(): string
    {
        return media_url($this->thumbnail_path, 'assets/images/300x300.png');
    }

    /**
     * Parity with the legacy Country/Region/City models' getTitleAttribute(): fall back to the
     * plain entity name when no scoped `languages` content has been overlaid for the requested
     * scope/locale (see CategoryPageContentService::applyScopedContentToModel()).
     */
    public function getTitleAttribute($value)
    {
        return filled($value) ? $value : $this->name;
    }

    private ?Language $scopedCmsLanguage = null;

    private bool $usesScopedCms = false;

    /**
     * Mirrors Country/Region/City's OverlaysScopedCategoryContent trait, minus the legacy
     * per-entity translation table (CategoryEntity has none — content lives only in `languages`).
     * Sets plain attributes too, so callers reading e.g. `$entity->title` directly keep working.
     */
    public function overlayScopedTranslation(?Language $content): void
    {
        $this->usesScopedCms = true;
        $this->scopedCmsLanguage = $content;

        if ($content === null) {
            return;
        }

        foreach ([
            'title', 'sub_title', 'introduction', 'content', 'faq_title',
            'fish_avail_title', 'fish_avail_intro', 'size_limit_title', 'size_limit_intro',
            'time_limit_title', 'time_limit_intro',
        ] as $field) {
            if (filled($content->{$field})) {
                $this->setAttribute($field, $content->{$field});
            }
        }
    }

    public function scopedCmsValue(string $field): ?string
    {
        if (! $this->usesScopedCms || $this->scopedCmsLanguage === null) {
            return null;
        }

        $value = $this->scopedCmsLanguage->{$field} ?? null;

        return filled($value) ? (string) $value : null;
    }

    /**
     * The old c_countries/c_regions/c_cities id this entity was migrated from, if any (see
     * category_entity_migration_map, written by BackfillCategoryEntitiesCommand). Entities
     * created directly against category_entities after Phase 3 (no legacy counterpart) return
     * null. Needed because destination_fish_charts/_size_limits/_time_limits are not yet
     * migrated off the legacy id space — see docs/category-pages-data-consolidation-plan.md §9
     * risk #10.
     */
    public function legacyId(): ?int
    {
        $legacyTable = $this->legacyTable();

        if ($legacyTable === null) {
            return null;
        }

        return CategoryEntityMigrationMap::query()
            ->where('old_table', $legacyTable)
            ->where('new_id', $this->id)
            ->value('old_id');
    }

    private function legacyTable(): ?string
    {
        return match ($this->type) {
            'country' => 'c_countries',
            'region' => 'c_regions',
            'city' => 'c_cities',
            default => null,
        };
    }

    /** @return Collection<int, DestinationFishChart> */
    public function fish_charts(): Collection
    {
        return DestinationFishChart::query()
            ->where('destination_id', $this->legacyId() ?? $this->id)
            ->where('destination_type', $this->type)
            ->get();
    }

    /** @return Collection<int, DestinationFishSizeLimit> */
    public function fish_size_limits(): Collection
    {
        return DestinationFishSizeLimit::query()
            ->where('destination_id', $this->legacyId() ?? $this->id)
            ->where('destination_type', $this->type)
            ->get();
    }

    /** @return Collection<int, DestinationFishTimeLimit> */
    public function fish_time_limits(): Collection
    {
        return DestinationFishTimeLimit::query()
            ->where('destination_id', $this->legacyId() ?? $this->id)
            ->where('destination_type', $this->type)
            ->get();
    }
}
