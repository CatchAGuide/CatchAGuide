<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuidingRequirements extends Model
{
    protected $fillable = [
        'name',
        'name_en',
    ];
    
    protected $table = 'guiding_requirements';
}

