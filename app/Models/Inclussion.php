<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inclussion extends Model
{
    use HasFactory;

    public function guidings()
    {
        return $this->belongsToMany(Guiding::class, 'guiding_inclussions')->withTimestamps();;
    }

    public function getNameAttribute()
    {        
        return app()->getLocale() == 'en' ? $this->attributes['name_en'] : $this->attributes['name'];
    }
}
