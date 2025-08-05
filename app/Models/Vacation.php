<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

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
        'status',
        'language',
        'content_updated_at'
    ];

    protected $casts = [
        'gallery' => 'array',
        'best_travel_times' => 'array',
        'target_fish' => 'array',
        'travel_options' => 'array',
        'included_services' => 'array',
        'additional_services' => 'array',
        'amenities' => 'array',
        'equipment' => 'array',
        'content_updated_at' => 'datetime',
        'has_boat' => 'boolean',
        'has_guiding' => 'boolean',
        'pets_allowed' => 'boolean',
        'smoking_allowed' => 'boolean',
        'disability_friendly' => 'boolean'
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

    /**
     * Get translations for this vacation
     */
    public function translations(): HasMany
    {
        return $this->hasMany(Language::class, 'source_id')->where('type', 'vacations');
    }

    /**
     * Get translation for specific language
     */
    public function getTranslation(string $language): ?Language
    {
        return $this->translations()->where('language', $language)->first();
    }

    /**
     * Get translated field value
     */
    public function getTranslatedField(string $field, string $language = null): string
    {
        $language = $language ?: app()->getLocale();
        
        // Return original if same language
        if ($this->language === $language) {
            $value = $this->$field;
            
            // Handle array fields
            if (is_array($value)) {
                return implode(', ', $value);
            }
            
            return $value ?? '';
        }
        
        // Get translation
        $translation = $this->getTranslation($language);
        if (!$translation || !$translation->json_data) {
            return $this->$field ?? '';
        }
        
        $translatedData = json_decode($translation->json_data, true);
        $translatedValue = $translatedData[$field] ?? $this->$field;
        
        // Handle array fields
        if (is_array($translatedValue)) {
            return implode(', ', $translatedValue);
        }
        
        return $translatedValue ?? '';
    }

    /**
     * Check if vacation has translation for language
     */
    public function hasTranslation(string $language): bool
    {
        if ($this->language === $language) {
            return true;
        }
        
        return $this->translations()->where('language', $language)->exists();
    }

    /**
     * Get all translated data for the vacation in a specific language
     * Returns an object with both original and translated values
     */
    public function getTranslatedData(string $language = null): object
    {
        $language = $language ?: app()->getLocale();
        
        // If same language, return original data
        if ($this->language === $language) {
            return (object) $this->toArray();
        }
        
        $cacheKey = 'vacation_full_translation_' . $this->id . '_' . $language;
        
        return Cache::remember($cacheKey, 3600, function() use ($language) {
            $translation = $this->getTranslation($language);
            $originalData = $this->toArray();
            
            if (!$translation || !$translation->json_data) {
                return (object) $originalData;
            }
            
            $translatedData = json_decode($translation->json_data, true);
            
            // Merge original data with translated data
            $result = $originalData;
            foreach ($translatedData as $field => $value) {
                if (isset($result[$field])) {
                    $result[$field] = $value;
                }
            }
            
            return (object) $result;
        });
    }

    /**
     * Get translated accommodations
     */
    public function getTranslatedAccommodations(string $language = null): \Illuminate\Support\Collection
    {
        $language = $language ?: app()->getLocale();
        
        return $this->accommodations->map(function($accommodation) use ($language) {
            return $this->getTranslatedRelationItem($accommodation, 'accommodation', $language);
        });
    }

    /**
     * Get translated boats
     */
    public function getTranslatedBoats(string $language = null): \Illuminate\Support\Collection
    {
        $language = $language ?: app()->getLocale();
        
        return $this->boats->map(function($boat) use ($language) {
            return $this->getTranslatedRelationItem($boat, 'boat', $language);
        });
    }

    /**
     * Get translated packages
     */
    public function getTranslatedPackages(string $language = null): \Illuminate\Support\Collection
    {
        $language = $language ?: app()->getLocale();
        
        return $this->packages->map(function($package) use ($language) {
            return $this->getTranslatedRelationItem($package, 'package', $language);
        });
    }

    /**
     * Get translated guidings
     */
    public function getTranslatedGuidings(string $language = null): \Illuminate\Support\Collection
    {
        $language = $language ?: app()->getLocale();
        
        return $this->guidings->map(function($guiding) use ($language) {
            return $this->getTranslatedRelationItem($guiding, 'guiding', $language);
        });
    }

    /**
     * Get translated extras
     */
    public function getTranslatedExtras(string $language = null): \Illuminate\Support\Collection
    {
        $language = $language ?: app()->getLocale();
        
        return $this->extras->map(function($extra) use ($language) {
            return $this->getTranslatedRelationItem($extra, 'extra', $language);
        });
    }

    /**
     * Helper method to get translated relation item
     */
    private function getTranslatedRelationItem($item, string $relationType, string $language): object
    {
        // Skip translation if source language matches target language
        if ($this->language === $language) {
            return $item;
        }
        
        $cacheKey = 'vacation_relation_translation_' . $item->id . '_' . $relationType . '_' . $language;
        
        return Cache::remember($cacheKey, 3600, function() use ($item, $relationType, $language) {
            $translation = Language::where([
                'source_id' => $item->id,
                'type' => 'vacation_' . $relationType,
                'language' => $language
            ])->first();
            
            if (!$translation || !$translation->json_data) {
                return $item;
            }
            
            $translatedData = json_decode($translation->json_data, true);
            $result = $item->toArray();
            
            // Handle different relation types
            switch ($relationType) {
                case 'accommodation':
                case 'boat':
                case 'package':
                case 'guiding':
                    // These models have: title, description, dynamic_fields
                    if (isset($translatedData['title'])) {
                        $result['title'] = $translatedData['title'];
                    }
                    if (isset($translatedData['description'])) {
                        $result['description'] = $translatedData['description'];
                    }
                    
                    // Update dynamic fields
                    $this->updateDynamicFields($result, $translatedData);
                    break;
                    
                case 'extra':
                    // VacationExtra has: type, description, price
                    if (isset($translatedData['description'])) {
                        $result['description'] = $translatedData['description'];
                    }
                    if (isset($translatedData['type'])) {
                        $result['type'] = $translatedData['type'];
                    }
                    break;
                    
                default:
                    // Generic fallback
                    if (isset($translatedData['title'])) {
                        $result['title'] = $translatedData['title'];
                    }
                    if (isset($translatedData['description'])) {
                        $result['description'] = $translatedData['description'];
                    }
                    break;
            }
            
            return (object) $result;
        });
    }

    /**
     * Update dynamic fields with translated values
     */
    private function updateDynamicFields(array &$result, array $translatedData): void
    {
        foreach ($translatedData as $key => $value) {
            if (strpos($key, 'dynamic_') === 0) {
                $fieldName = substr($key, 8); // Remove 'dynamic_' prefix
                if (isset($result['dynamic_fields'])) {
                    $dynamicFields = is_string($result['dynamic_fields']) ? 
                        json_decode($result['dynamic_fields'], true) : 
                        $result['dynamic_fields'];
                        
                    if (is_array($dynamicFields)) {
                        $dynamicFields[$fieldName] = $value;
                        // Keep as array for consistent handling
                        $result['dynamic_fields'] = $dynamicFields;
                    }
                }
            }
        }
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