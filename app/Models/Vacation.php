<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Akuechler\Geoly;
use App\Traits\ModelImageTrait;

class Vacation extends Model
{
    use HasFactory, Geoly, ModelImageTrait;
    protected $fillable = [
        'title',
        'slug', 
        'location',
        'city',
        'country',
        'latitude',
        'longitude',
        'region',
        'gallery',
        'best_travel_times',
        'surroundings_description',
        'target_fish',
        'airport_distance',
        'water_distance', 
        'shopping_distance',
        'travel_included',
        'travel_options',
        'pets_allowed',
        'smoking_allowed',
        'disability_friendly',
        'has_boat',
        'has_guiding',
        'accommodation_description',
        'living_area',
        'bedroom_count',
        'bed_count',
        'max_persons',
        'min_rental_days',
        'amenities',
        'boat_description',
        'equipment',
        'basic_fishing_description',
        'catering_info',
        'package_price_per_person',
        'accommodation_price',
        'boat_rental_price',
        'guiding_price',
        'additional_services',
        'included_services',
        'status'
    ];

    public function accommodations(): HasMany
    {
        return $this->hasMany(VacationAccommodation::class);
    }

    public function boats(): HasMany
    {
        return $this->hasMany(VacationBoat::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(VacationPackage::class);
    }

    public function guidings(): HasMany
    {
        return $this->hasMany(VacationGuiding::class);
    }

    public function extras(): HasMany
    {
        return $this->hasMany(VacationExtra::class);
    }

    /**
     * Get the bookings for the vacation.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(VacationBooking::class);
    }

    public static function locationFilter(string $city = null, string $country = null, ?int $radius = null, $placeLat = null, $placeLng = null )
    {
        // Get standardized English names using the helper
        if ($city || $country) {
            $searchQuery = array_filter([$city, $country], fn($val) => !empty($val));
            $searchString = implode(', ', $searchQuery);
            
            $translated  = getLocationDetails($searchString);
            if ($translated) {
                $locationParts = ['city_en' => $translated['city'], 'country_en' => $translated['country']];
            }
        }

        $locationParts = array_merge(['city' => $city, 'country' => $country], $locationParts ?? []);

        $returnData = [
            'message' => '',
            'ids' => []
        ];
        
        // First try direct database match based on parsed location
        $vacations = self::select('id')
            ->where(function($query) use ($locationParts) {
                // City conditions
                if ($locationParts['city']) {
                    $query->where(function($q) use ($locationParts) {
                        $q->where('city', $locationParts['city']);
                        if (isset($locationParts['city_en'])) {
                            $q->orWhere('city', $locationParts['city_en']);
                        }
                    });
                }
                
                // Country conditions
                if ($locationParts['country']) {
                    $query->where(function($q) use ($locationParts) {
                        $q->where('country', $locationParts['country']);
                        if (isset($locationParts['country_en'])) {
                            $q->orWhere('country', $locationParts['country_en']);
                        }
                    });
                }
            })
            ->where('status', 1)
            ->pluck('id');

        if ($vacations->isNotEmpty()) {
            $returnData['ids'] = $vacations;
            $returnData['message'] = str_replace('#location#', $city . ', ' . $country, __('search-request.searchLevel1'));;
            return $returnData;
        }

        // If no direct matches, use geocoding
        if ($placeLat && $placeLng) {
            $coordinates = ['lat' => $placeLat, 'lng' => $placeLng];
        } else {
            // $coordinates = self::getCoordinatesFromLocation($locationParts['original']);
            $coordinates = ['lat' => 48.1373, 'lng' => 11.5755];
        }
        
        if (!$coordinates) {
            return collect();
        }

        // Try radius search
        $searchRadius = $radius ?? 200;
        $vacations = self::select('id')
            ->selectRaw("ST_Distance_Sphere(
                point(longitude, latitude),
                point(?, ?)
            ) as distance", [
                $coordinates['lng'],
                $coordinates['lat']
            ])
            ->whereRaw("ST_Distance_Sphere(
                point(longitude, latitude),
                point(?, ?)
            ) <= ?", [
                $coordinates['lng'],
                $coordinates['lat'],
                $searchRadius * 1000
            ])
            ->where('status', 1)
            ->orderBy('distance')  // Sort by distance ascending
            ->pluck('id');

        if ($vacations->isNotEmpty()) {
            $returnData['ids'] = $vacations;
            $returnData['message'] = str_replace('#location#', $city . ', ' . $country, __('search-request.searchLevel2'));
            return $returnData;
        }

        // If still no results, find nearest vacation
        $returnData['ids'] = self::select('id')
            ->selectRaw("ST_Distance_Sphere(
                point(longitude, latitude),
                point(?, ?)
            ) as distance", [
                $coordinates['lng'],
                $coordinates['lat']
            ])
            ->where('status', 1)
            ->orderBy('distance')
            ->pluck('id');
        $returnData['message'] = str_replace('#location#', $city . ', ' . $country, __('search-request.searchLevel3'));
        return $returnData;
    }
    
    private static function getCoordinatesFromLocation(string $location): ?array
    {
        $geocodeResult = getCoordinatesFromLocation($location, true);
        if (!$geocodeResult) {
            return null;
        }

        return [
            'lat' => $geocodeResult['lat'],
            'lng' => $geocodeResult['lng'],
            'type' => $geocodeResult['types']
        ];
    }

    public function getLowestPrice(): float
    {
        $lowestPackagePrice = $this->packages->map(function ($package) {
            $dynamicFields = json_decode($package->dynamic_fields, true);
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
            $dynamicFields = json_decode($accommodation->dynamic_fields, true);
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

    /**
     * Get the total capacity across all accommodations
     *
     * @return int
     */
    public function getTotalCapacity(): int
    {
        return $this->accommodations->sum('capacity');

    }
}