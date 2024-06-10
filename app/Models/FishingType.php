<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\FishingType;

class FishingType extends Model
{
    use HasFactory;
    
    public function guidings()
    {
        return $this->belongsToMany(FishingType::class,'guiding_fishing_types')->withTimestamps();
    }


}
