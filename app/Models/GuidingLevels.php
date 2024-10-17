<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuidingLevels extends Model
{
    use HasFactory;
    
    public function guides()
    {
        return $this->belongsToMany(Guiding::class,'guiding_levels');
    }
}
