<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoatExtra extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_en',
    ];

    // Get name based on current locale
    public function getNameAttribute()
    {
        return app()->getLocale() == 'en' ? $this->attributes['name_en'] : $this->attributes['name'];
    }
}
