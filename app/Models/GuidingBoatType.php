<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuidingBoatType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'name_en'];
    protected $table = 'guiding_boat_types';

    public function getNameAttribute()
    {
        return app()->getLocale() == 'en' ? $this->attributes['name_en'] : $this->attributes['name'];
    }
}

