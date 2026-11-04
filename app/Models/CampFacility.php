<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CampFacility extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_de',
        'name_en',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getNameAttribute(): string
    {
        $locale = app()->getLocale();

        if ($locale === 'en' && ! empty($this->attributes['name_en'])) {
            return (string) $this->attributes['name_en'];
        }

        if ($locale === 'de' && ! empty($this->attributes['name_de'])) {
            return (string) $this->attributes['name_de'];
        }

        return (string) ($this->attributes['name'] ?? '');
    }

    /**
     * The admin form lists facilities by the localized name (name_de on .de, name_en on .com),
     * so a lookup on the raw `name` column alone matched only names spelled alike in both.
     *
     * @param  array<int, string>  $names
     */
    public function scopeWhereAnyName(Builder $query, array $names): Builder
    {
        return $query->where(function (Builder $query) use ($names) {
            $query->whereIn('name', $names)
                ->orWhereIn('name_de', $names)
                ->orWhereIn('name_en', $names);
        });
    }

    public function camps(): BelongsToMany
    {
        return $this->belongsToMany(Camp::class, 'camp_facility_camp');
    }
}
