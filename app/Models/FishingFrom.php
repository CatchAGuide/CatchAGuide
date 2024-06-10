<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\FishingFrom;

class FishingFrom extends Model
{
    use HasFactory;

    public function guidings()
    {
        return $this->belongsToMany(FishingFrom::class,'guiding_fishing_froms')->withTimestamps();
    }

}
