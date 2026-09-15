<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuidingBoatDescription extends Model
{
    use HasFactory;

    protected $table = 'guiding_boat_descriptions';

    protected $fillable = [
        'name',
        'name_en',
    ];

    public function getNameAttribute()
    {        
        return app()->getLocale() == 'en' ? $this->attributes['name_en'] : $this->attributes['name'];
    }
}
