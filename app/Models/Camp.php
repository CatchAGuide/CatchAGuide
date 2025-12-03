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
        $lowestPackagePrice = $this->packages->map(function ($package) {
            $dynamicFields = is_string($package->dynamic_fields) ? json_decode($package->dynamic_fields, true) : $package->dynamic_fields;
            if (!isset($dynamicFields['prices']) || empty($dynamicFields['prices'])) {
                return PHP_FLOAT_MAX;
            }

            // Calculate price per person for each capacity and round to whole numbers
            $pricesPerPerson = collect($dynamicFields['prices'])->map(function ($price, $index) {
                $personCount = $index + 1; // Index 0 = 1 person, 1 = 2 persons, etc.
                return round((float)$price / $personCount); // Round to whole number
            });

            return $pricesPerPerson->min();
        })->filter(function($price) {
            return $price !== PHP_FLOAT_MAX;
        })->min();

        $lowestAccommodationPrice = $this->accommodations->map(function ($accommodation) {
            $dynamicFields = is_string($accommodation->dynamic_fields) ? json_decode($accommodation->dynamic_fields, true) : $accommodation->dynamic_fields;
            if (!isset($dynamicFields['prices']) || empty($dynamicFields['prices'])) {
                return PHP_FLOAT_MAX;
            }

            // Calculate price per person for each capacity and round to whole numbers
            $pricesPerPerson = collect($dynamicFields['prices'])->map(function ($price, $index) {
                $personCount = $index + 1;
                return round((float)$price / $personCount); // Round to whole number
            });

            return $pricesPerPerson->min();
        })->filter(function($price) {
            return $price !== PHP_FLOAT_MAX;
        })->min();

        // Return the lower of the two prices, defaulting to the non-PHP_FLOAT_MAX value if one exists
        $lowestPrice = $lowestPackagePrice && $lowestAccommodationPrice 
            ? min($lowestPackagePrice, $lowestAccommodationPrice)
            : ($lowestPackagePrice ?: $lowestAccommodationPrice ?: 0);

        return round((float)$lowestPrice); // Round the final result to whole number
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
