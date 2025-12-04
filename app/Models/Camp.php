<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Camp extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description_camp',
        'description_area',
        'description_fishing',
        'location',
        'latitude',
        'longitude',
        'country',
        'city',
        'region',
        'distance_to_store',
        'distance_to_nearest_town',
        'distance_to_airport',
        'distance_to_ferry_port',
        'policies_regulations',
        'target_fish',
        'best_travel_times',
        'travel_information',
        'extras',
        'thumbnail_path',
        'gallery_images',
        'status',
        'user_id',
    ];

    protected $casts = [
        'gallery_images' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'target_fish' => 'array',
        'best_travel_times' => 'array',
    ];

    public function getLowestPrice(): float
    {
        return 0;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(CampFacility::class, 'camp_facility_camp');
    }

    public function accommodations(): BelongsToMany
    {
        return $this->belongsToMany(Accommodation::class, 'camp_accommodation');
    }

    public function rentalBoats(): BelongsToMany
    {
        return $this->belongsToMany(RentalBoat::class, 'camp_rental_boat');
    }

    public function guidings(): BelongsToMany
    {
        return $this->belongsToMany(Guiding::class, 'camp_guiding');
    }
}
