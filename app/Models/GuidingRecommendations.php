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
}

