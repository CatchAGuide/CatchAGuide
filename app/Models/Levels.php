<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Levels;

class Levels extends Model
{
    use HasFactory;

    public function guidings()
    {
        return $this->belongsToMany(Guiding::class, 'guiding_levels')->withTimestamps();;
    }
}
