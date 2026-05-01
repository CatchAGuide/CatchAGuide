<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoatExtras extends Model
{
    use HasFactory;

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
}
