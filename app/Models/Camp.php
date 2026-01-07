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
        $prices = [];

        // Get lowest price from accommodations - check per_person_pricing field
        $accommodations = $this->accommodations()->where('status', 'active')->get();
        foreach ($accommodations as $accommodation) {
            $perPersonPricing = $accommodation->per_person_pricing;
            
            // Handle string JSON or already decoded array
            if (is_string($perPersonPricing)) {
                $perPersonPricing = json_decode($perPersonPricing, true);
            }
            
            if (is_array($perPersonPricing) && !empty($perPersonPricing)) {
                foreach ($perPersonPricing as $tier) {
                    if (!is_array($tier)) {
                        continue;
                    }
                    
                    // Check price_per_night
                    if (isset($tier['price_per_night']) && $tier['price_per_night'] > 0) {
                        $prices[] = (float) $tier['price_per_night'];
                    }
                    
                    // Check price_per_week and convert to per-night
                    if (isset($tier['price_per_week']) && $tier['price_per_week'] > 0) {
                        $prices[] = (float) $tier['price_per_week'];
                    }
                }
            }
        }

        // Get lowest price from rental boats - check prices field
        $rentalBoats = $this->rentalBoats()->where('status', 'active')->get();
        foreach ($rentalBoats as $rentalBoat) {
            $boatPrices = $rentalBoat->prices;
            
            // Handle string JSON or already decoded array (since it's cast as array)
            if (is_string($boatPrices)) {
                $boatPrices = json_decode($boatPrices, true);
            }
            
            if (is_array($boatPrices) && !empty($boatPrices)) {
                // Handle indexed array format [0 => ['amount' => ...]]
                if (isset($boatPrices[0]) && is_array($boatPrices[0])) {
                    foreach ($boatPrices as $price) {
                        if (isset($price['amount']) && $price['amount'] > 0) {
                            $prices[] = (float) $price['amount'];
                        }
                    }
                } 
                // Handle associative array format ['per_day' => amount, ...]
                else {
                    foreach ($boatPrices as $key => $value) {
                        if (is_array($value) && isset($value['amount'])) {
                            if ($value['amount'] > 0) {
                                $prices[] = (float) $value['amount'];
                            }
                        } elseif (is_numeric($value) && $value > 0) {
                            $prices[] = (float) $value;
                        }
                    }
                }
            }
        }

        // Get lowest price from guidings - check prices field directly
        // $guidings = $this->guidings()->where('status', 1)->get();
        // foreach ($guidings as $guiding) {
        //     $guidingPrices = decode_if_json($guiding->prices, true);
            
        //     if (is_array($guidingPrices) && !empty($guidingPrices)) {
        //         foreach ($guidingPrices as $price) {
        //             if (is_array($price) && isset($price['amount']) && $price['amount'] > 0) {
        //                 $amount = (float) $price['amount'];
        //                 // Calculate per-person price if person count is specified
        //                 if (isset($price['person']) && $price['person'] > 1) {
        //                     $amount = $amount / $price['person'];
        //                 }
        //                 $prices[] = $amount;
        //             }
        //         }
        //     }
        // }

        // Return the minimum price, or 0 if no prices found
        return !empty($prices) ? (float) min($prices) : 0.0;
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

    public function specialOffers(): BelongsToMany
    {
        return $this->belongsToMany(SpecialOffer::class, 'camp_special_offer');
    }
}
