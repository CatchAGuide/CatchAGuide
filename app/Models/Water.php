<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Cacheable;

class Water extends Model
{
    use HasFactory, Cacheable;

    public function guidings()
    {
        return $this->belongsToMany(Guiding::class, 'guiding_waters')->withTimestamps();;
    }

    public function getNameAttribute()
    {        
        return app()->getLocale() == 'en' ? $this->attributes['name_en'] : $this->attributes['name'];
    }
}
