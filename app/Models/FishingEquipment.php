<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class FishingEquipment extends Model
{
    use HasFactory;

    protected $table = 'fishing_equipment';

    protected $fillable = [
        'name',
        'name_en',
    ];

    public function getNameAttribute(): string
    {
        return app()->getLocale() == 'en'
            ? (string)($this->attributes['name_en'] ?? '')
            : (string)($this->attributes['name'] ?? '');
    }

    public function guiding(): BelongsTo
    {
        return $this->belongsTo(Guiding::class);
    }
}
