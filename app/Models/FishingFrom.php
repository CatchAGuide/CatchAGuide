<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FishingFrom extends Model
{
    use HasFactory;

    public function getNameAttribute(): string
    {
        if (app()->getLocale() === 'en' && ! empty($this->attributes['name_en'])) {
            return (string) $this->attributes['name_en'];
        }

        return (string) ($this->attributes['name'] ?? '');
    }

    public function guidings()
    {
        return $this->belongsToMany(FishingFrom::class, 'guiding_fishing_froms')->withTimestamps();
    }
}
