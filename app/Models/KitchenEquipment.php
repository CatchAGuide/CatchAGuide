<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KitchenEquipment extends Model
{
    use HasFactory;

    protected $table = 'kitchen_equipment';

    protected $fillable = [
        'value',
        'value_de',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getNameAttribute(): string
    {
        $locale = app()->getLocale();
        if ($locale === 'en') {
            return (string)($this->attributes['value'] ?? '');
        }

        return (string)($this->attributes['value_de'] ?? $this->attributes['value'] ?? '');
    }

    // Scope for active equipment
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
