<?php

namespace App\Models;

use App\Traits\Cacheable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TermsSection extends Model
{
    use Cacheable;

    protected $fillable = [
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(TermsSectionTranslation::class);
    }

    public function translationFor(string $locale): ?TermsSectionTranslation
    {
        return $this->translations->firstWhere('language', $locale)
            ?? $this->translations()->where('language', $locale)->first();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
