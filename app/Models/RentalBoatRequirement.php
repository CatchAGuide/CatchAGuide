<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalBoatRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_en',
        'input_type',
        'placeholder',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Get name based on current locale
    public function getNameAttribute()
    {
        return app()->getLocale() == 'en' ? $this->attributes['name_en'] : $this->attributes['name'];
    }

    // Get placeholder based on current locale
    public function getPlaceholderAttribute()
    {
        return app()->getLocale() == 'en' ? $this->attributes['placeholder_en'] : $this->attributes['placeholder'];
    }

    // Scope for active requirements
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for ordering
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
