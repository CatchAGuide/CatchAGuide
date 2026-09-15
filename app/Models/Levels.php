<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Levels extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_en',
    ];

    public function guidings()
    {
        return $this->belongsToMany(Guiding::class, 'guiding_levels')->withTimestamps();;
    }
}
