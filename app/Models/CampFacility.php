<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CampFacility extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_de',
        'name_en',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function camps(): BelongsToMany
    {
        return $this->belongsToMany(Camp::class, 'camp_facility_camp');
    }
}
