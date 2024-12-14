<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuidingRecommendations extends Model
{
    protected $fillable = [
        'name',
        'name_en',
    ];

    protected $table = 'guiding_recommendations';

    public function getNameAttribute()
    {        
        return app()->getLocale() == 'en' ? $this->attributes['name_en'] : $this->attributes['name'];
    }
}

