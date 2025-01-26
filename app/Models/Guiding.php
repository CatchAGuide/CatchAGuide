<?php

namespace App\Models;

use Akuechler\Geoly;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Support\Str;
use App\Traits\MethodTraits;
use App\Traits\ModelImageTrait;

use App\Models\GuidingInclussion;
use App\Models\GuidingExtras;
use App\Models\Levels;
use App\Models\FishingFrom;
use App\Models\FishingType;
use App\Models\EquipmentStatus;
use App\Models\FishingEquipment;
use App\Models\Rating;
use App\Models\GuidingRequirements;
use App\Models\GuidingAdditionalInformation;
use App\Models\GuidingRecommendations;
use App\Models\GuidingBoatType;
use App\Models\GuidingBoatDescription;
use App\Models\GuidingBoatExtras;
use App\Models\BoatExtras;

class Guiding extends Model
{
    use HasFactory, Geoly, ModelImageTrait;

    protected $fillable = [
        'title',
        'slug',
        'location',
        'country',
        'water',
        'water_sonstiges',
        'targets',
        'target_fish_sonstiges',
        'methods',
        'methods_sonstiges',
        'recommended_for_anfaenger',
        'recommended_for_fortgeschrittene',
        'recommended_for_profis',
        'max_guests',
        'duration',
        'required_special_license',
        'fishing_type',
        'fishing_from',
        'description',
        'required_equipment',
        'provided_equipment',
        'additional_information',
        'price',
        'price_two_persons',
        'price_three_persons',
        'price_four_persons',
        'price_five_persons',
        'lat',
        'lng',
        'location',
        'thumbnail_id',
        'user_id',
        'fishing_from_id',
        'fishing_type_id',
        'equipment_status_id',
        'boat_information',
        'rest_method',
        'water_name',
        'catering',
        'thumbnail_path',
        'galleries',
        'needed_equipment',
        'meeting_point',
        'payment_point',
        'is_boat',
        'boat_type',
        'boat_extras',
        'target_fish',
        'fishing_methods',
        'water_types',
        'experience_level',
        'inclusions',
        'requirements',
        'recommendations',
        'other_information',
        'style_of_fishing',
        'tour_type',
        'duration_type',
        'price_type',
        'prices',
        'pricing_extra',
        'months',
        'seasonal_trip',
        'allowed_booking_advance',
        'booking_window',
        'gallery_images',
        'city',
        'desc_course_of_action',
        'desc_meeting_point',
        'desc_starting_time',
        'desc_tour_unique'
    ];

    public const LATITUDE  = 'lat';
    public const LONGITUDE = 'lng';

    protected $columns = [
        'price',
        'price_two_persons',
        'price_three_persons',
        'price_four_persons',
        'price_five_persons',
    ];

    /**
     * @return BelongsTo
     */
    public function thumbnail(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'thumbnail_id');
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany
     */
    public function gallery_media(): HasMany
    {
        return $this->hasMany(GuidingGalleryMedia::class);
    }

    /**
     * @return HasMany
     */
    public function water_types(): HasMany
    {
        return $this->hasMany(GuidingWaterType::class);
    }

    /**
     * @return HasMany
     */
    public function target_fish(): HasMany
    {
        return $this->hasMany(GuidingTargetFish::class);
    }

    /**
     * @return HasMany
     */
    public function methods(): HasMany
    {
        return $this->hasMany(GuidingMethod::class);
    }


    public function fishingFrom()
    {
        return $this->belongsTo(FishingFrom::class,'fishing_from_id','id');
    }

    public function guidingTargets()
    {
        return $this->belongsToMany(Target::class,'guiding_targets')->withTimestamps();
    }

    public function guidingMethods()
    {
        return $this->belongsToMany(Method::class,'guiding_method')->withTimestamps();
    }

    public function guidingWaters()
    {
        return $this->belongsToMany(Water::class,'guiding_waters')->withTimestamps();
    }

    public function fishingTypes()
    {
        return $this->belongsTo(FishingType::class,'fishing_type_id','id');
    }

    public function equipmentStatus(){
        return $this->belongsTo(EquipmentStatus::class,'equipment_status_id','id');
    }


    public function inclussions()
    {
        return $this->belongsToMany(Inclussion::class,'guiding_inclussions')->withTimestamps();
    }

    public function fishing_equipment()
    {
        return $this->belongsToMany(FishingEquipment::class, 'guiding_equipment');
    }

    public function levels()
    {
        return $this->belongsToMany(Levels::class,'guiding_levels')->withTimestamps();
    }

    public function extras()
    {
        return $this->hasMany(GuidingExtras::class,'guiding_id','id');
    }




    /**
     * @return HasMany
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function getGuideSalary(int $guests): float
    {
        $price = $this->price;
        if($guests == 2){
            $price = $this->price_two_persons;
        }
        if($guests == 3){
            $price = $this->price_three_persons;
        }
        if($guests == 4){
            $price = $this->price_four_persons;
        }
        if($guests == 5){
            $price = $this->price_five_persons;
        }
        return $price;
    }


    public function threeTargets()
    {
        $string = "";
        if($this->targets) {
            $targets = unserialize($this->targets);
            foreach($targets as $target) {
                $string .= ucfirst($target) . ", ";
            }
        }
        if($this->target_fish_sonstiges){
            $string .= ucfirst($this->target_fish_sonstiges). ", ";
        }

        return $this->removeLastComma($string);
    }

    public function threeWaters()
    {
        $string = "";
        if($this->water) {
            $waters = unserialize($this->water);
            foreach($waters as $water) {
                $string .= $water . ", ";
            }
        }

        if($this->water_sonstiges){
            $string .= ucfirst($this->water_sonstiges). ", ";
        }

        return $this->removeLastComma($string);
    }

    public function threeMethods()
    {

        $string = "";

        if($this->methods) {
            $methods = unserialize($this->methods);

            foreach($methods as $method) {
                $chk = Method::where('name',$method)->first();

                if($chk){
                    if(app()->getLocale() == "en"){
                        $method = $chk->name_en ? $chk->name_en : $chk->name ;
                    }else{
                        $method = $chk->name;
                    }
                }

                $string .= $method . ", ";
            }
        }

        if($this->methods_sonstiges){
            $string .= ucfirst($this->methods_sonstiges). ", ";
        }



        $string = $this->removeLastComma($string);






        return $string;
    }

    public function aboutme()
    {
        $aboutme = [];
        array_push($aboutme, substr($this->user->information['about_me'], 0, 350));
        array_push($aboutme, substr($this->user->information['about_me'], 350));
        return $aboutme;
    }



/*
    public function advancePayment($price)
    {
        $price = $price - 12;
        if($price > 0 && $price <= 350) {
            $advancePayment = ($price * 0.10) + 12;
        } elseif ($price > 350 && $price <= 1500) {
            $advancePayment = ($price * 0.075) + 12;
        } elseif($price > 1500) {
            $advancePayment = ($price * 0.03) + 12;
        }
        return floatval(two($advancePayment));
    }
    public function advancePaymentString($price)
    {
        $price = $price - 12;
        if($price > 0 && $price <= 350) {
            $advancePayment = ($price * 0.10) + 12;
        } elseif ($price > 350 && $price <= 1500) {
            $advancePayment = ($price * 0.075) + 12;
        } elseif($price > 1500) {
            $advancePayment = ($price * 0.03) + 12;
        }
        return $advancePayment;
    }
*/
    protected function removeLastComma (string $string):string
    {
        $return = substr($string, 0, -2);
        return $return;
    }

    public function galleries()
    {
        return $this->hasMany(Gallery::class);
    }

    public function getExcerptAttribute(){

        $description = (strip_tags(html_entity_decode($this->description,ENT_QUOTES)));
        $excerpt = Str::limit($description, 200);
        return $excerpt;
    }

    public function getColumnsWithValueCountAttribute()
    {
        $count = 0;

        foreach ($this->columns as $column) {
            $value = $this->$column;

            if (!is_null($value) && $value >= 0) {
                $count++;
            }
        }

        return $count;
    }

    public function scopeFilterByRequestValue($query, $requestValue)
    {
        if ($requestValue) {
            $query->whereHas('columnsWithValueCount', function ($query) use ($requestValue) {
                $query->where('columns_with_value_count', $requestValue);
            });
        }

        return $query;
    }

    public function getGuidingPriceByPerson($person)
    {
        $prices = json_decode($this->prices, true);
        foreach($prices as $price){
            if($price['person'] == $person){
                return $price['amount'];
            }

            return $this->price;
        }
    }

    public function ratings(){
        return $this->hasMany(Rating::class,'guide_id','id');
    }

    public function boatType(){
        return $this->hasOne(GuidingBoatType::class,'id','boat_type');
    }

    public function getLowestPrice()
    {
        $prices = json_decode($this->prices, true);
        if (!$prices) {
            return 0;
        }
  
        $singlePrice = collect($prices)->where('person', 1)->first();
        $singlePrice = $singlePrice ? $singlePrice['amount'] : PHP_FLOAT_MAX;
        
        $minPrice = min(array_map(function($price) {
            return $price['person'] > 1 ? round($price['amount'] / $price['person']) : $price['amount'];
        }, $prices));
        
        return min($singlePrice, $minPrice);
        return 0;
    }

    public function getBlockedEvents()
    {
        $blocked_events = collect($this->user->blocked_events)
            ->filter(function($blocked) {
                return $blocked->guiding_id == $this->id || 
                       ($blocked->guiding_id === null && 
                        $this->bookings()
                            ->where('blocked_event_id', $blocked->id)
                            ->exists());
            })
            ->map(function($blocked) {
                return [
                    "from" => date('Y-m-d', strtotime($blocked->from)),
                    "due" => date('Y-m-d', strtotime($blocked->due))
                ];
            })
            ->toArray();

        $today = now();

        // Add booking window restrictions
        if ($this->booking_window !== 'no_limitation') {
            $bookingWindowMonths = [
                'six_months' => 6,
                'nine_months' => 9,
                'twelve_months' => 12
            ];

            if (isset($bookingWindowMonths[$this->booking_window])) {
                $blockFrom = $today->copy()->addMonths($bookingWindowMonths[$this->booking_window])->addDay();
                
                $blocked_events[] = [
                    "from" => $blockFrom->format('Y-m-d'),
                    "due" => $blockFrom->copy()->addYears(10)->format('Y-m-d')
                ];
            }
        }

        // Add advance booking restrictions
        $advanceBookingPeriods = [
            'same_day' => fn($date) => $date->endOfDay(),
            'three_days' => fn($date) => $date->addDays(3), 
            'one_week' => fn($date) => $date->addWeek(),
            'one_month' => fn($date) => $date->addMonth()
        ];

        if (isset($advanceBookingPeriods[$this->allowed_booking_advance])) {
            $blockUntil = $advanceBookingPeriods[$this->allowed_booking_advance]($today->copy());
            
            $blocked_events[] = [
                "from" => $today->format('Y-m-d'),
                "due" => $blockUntil->format('Y-m-d')
            ];
        }

        return $blocked_events;
    }

    /**
     * Filter guidings based on location and radius
     * 
     * @param string $location City or country name
     * @param int|null $radius Search radius in kilometers
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function locationFilter(string $city = null, string $country = null, $region = null, ?int $radius = null, $placeLat = null, $placeLng = null)
    {
        // Get standardized English names using the helper
        if ($city || $country) {
            $searchQuery = array_filter([$city, $country, $region], fn($val) => !empty($val));
            $searchString = implode(', ', $searchQuery);
            
            $translated  = getLocationDetails($searchString);
            if ($translated) {
                $locationParts = ['city_en' => $translated['city'], 'country_en' => $translated['country'], 'region_en' => $translated['region']];
            }
        }

        $locationParts = array_merge(['city' => $city, 'country' => $country, 'region' => $region], $locationParts ?? []);

        $returnData = [
            'message' => '',
            'ids' => []
        ];
        
        // Try direct database match based on standardized names
        $guidings = self::select('id')
            ->where(function($query) use ($locationParts) {
                if ($locationParts['city']) {
                    if (isset($locationParts['city_en'])) {
                        $query->where('city', $locationParts['city'])->orWhere('city', $locationParts['city_en']);
                    } else {
                        $query->where('city', $locationParts['city']);
                    }
                } else if ($locationParts['country']) {
                    if (isset($locationParts['country_en'])) {
                        $query->where('country', $locationParts['country'])->orWhere('country', $locationParts['country_en']);
                    } else {
                        $query->where('country', $locationParts['country']);
                    }
                }
                if ($locationParts['region']) {
                    if (isset($locationParts['region_en'])) {
                        $query->where('region', $locationParts['region'])->orWhere('region', $locationParts['region_en']);
                    } else {
                        $query->where('region', $locationParts['region']);
                    }
                }
            })
            ->where('status', 1)
            ->pluck('id');

        if ($guidings->isNotEmpty()) {
            $returnData['ids'] = $guidings;
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
        
        $guidings = self::select('id')
            ->whereRaw("ST_Distance_Sphere(
                point(lng, lat),
                point(?, ?)
            ) <= ?", [
                $coordinates['lng'],
                $coordinates['lat'],
                $searchRadius * 1000
            ])
            ->where('status', 1)
            ->pluck('id');

        if ($guidings->isNotEmpty()) {
            $returnData['ids'] = $guidings;
            $returnData['message'] = str_replace('#location#', $city . ', ' . $country, __('search-request.searchLevel2'));
            return $returnData;
        }

        // If still no results, find nearest guiding
        $returnData['ids'] = self::select('id')
            ->where('status', 1)
            ->selectRaw("ST_Distance_Sphere(
                point(lng, lat), 
                point(?, ?)
            ) as distance", [
                $coordinates['lng'],
                $coordinates['lat']
            ])
            ->orderBy('distance')
            ->pluck('id');
        $returnData['message'] = str_replace('#location#', $city . ', ' . $country, __('search-request.searchLevel3'));
        return $returnData;
    }

    /**
     * Parse location string into components
     * 
     * @param string $location
     * @return array
     */
    private static function parseLocation(string $location): array
    {
        // Initialize return array
        $result = [
            'original' => $location,
            'city' => null,
            'country' => null
        ];

        // Remove any extra whitespace and split by comma
        $parts = array_map('trim', explode(',', $location));

        if (count($parts) === 1) {
            // Single location provided - need to determine if it's a city or country
            $geocodeResult = getCoordinatesFromLocation($location);
            if ($geocodeResult) {
                $result['city'] = $geocodeResult['city'];
                $result['country'] = $geocodeResult['country'];
            }
        } else {
            // City and country provided
            $result['city'] = $parts[0];
            $result['country'] = $parts[1];
        }

        return $result;
    }

    /**
     * Get coordinates from location using Google Geocoding API
     */
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

    /**
     * Get localized fishing method names
     * @return array
     */
    public function getFishingMethodNames(): array
    {
        $methodIds = json_decode($this->fishing_methods) ?? [];
        
        if (empty($methodIds)) {
            return [];
        }

        return Method::whereIn('id', $methodIds)
            ->select('id', 'name', 'name_en')
            ->get()
            ->map(function($method) {
                return [
                    'id' => $method->id,
                    'name' => $method->name
                ];
            })
            ->toArray();
    }


    /**
     * Get localized target fish names
     * @return array
     */
    public function getTargetFishNames(): array
    {
        $targetIds = json_decode($this->target_fish) ?? [];
        
        if (empty($targetIds)) {
            return [];
        }

        return Target::whereIn('id', $targetIds)
            ->select('id', 'name', 'name_en')
            ->get()
            ->map(function($target) {
                return [
                    'id' => $target->id,
                    'name' => $target->name
                ];
            })
            ->toArray();
    }

    /**
     * Get localized inclusion names
     * @return array
     */
    public function getInclusionNames(): array
    {
        $inclusionIds = json_decode($this->inclusions) ?? [];
        
        if (empty($inclusionIds)) {
            return [];
        }
        
        return Inclussion::whereIn('id', $inclusionIds)
            ->select('id', 'name', 'name_en')
            ->get()
            ->map(function($inclusion) {
                return [
                    'id' => $inclusion->id,
                    'name' => $inclusion->name
                ];
            })
            ->toArray();
    }

    /**
     * Get localized water names
     * @return array
     */
    public function getWaterNames(): array
    {
        $waterIds = json_decode($this->water_types) ?? [];
        
        if (empty($waterIds)) {
            return [];
        }

        return Water::whereIn('id', $waterIds)
            ->select('id', 'name', 'name_en')
            ->get()
            ->map(function($water) {
                return [
                    'id' => $water->id,
                    'name' => $water->name
                ];
            })
            ->toArray();
    }

    public function getBoatExtras(): array
    {
        $boatExtras = json_decode($this->boat_extras, true);
        if (!$boatExtras) {
            return [];
        }

        return BoatExtras::whereIn('id', $boatExtras)
            ->get()
            ->map(function($boatExtra) {
                return [
                    'id' => $boatExtra->id,
                    'name' => $boatExtra->name
                ];
            })
            ->toArray();
    }

    /**
     * Get the requirements associated with the guiding.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRequirementsAttribute()
    {
        if (!$this->attributes['requirements']) {
            return collect();
        }

        $requirementsData = collect(json_decode($this->attributes['requirements'], true));
        return GuidingRequirements::whereIn('id', array_keys($requirementsData->all()))
            ->get()
            ->map(function ($requirement) use ($requirementsData) {
                $data = $requirementsData[$requirement->id];
                
                return [
                    'id' => $requirement->id,
                    'value' => is_array($data) && isset($data['value']) ? $data['value'] : $data,
                    'name' => $requirement->name
                ];
            });
    }

    public function getOtherInformationAttribute()
    {
        if (!$this->attributes['other_information']) {
            return collect();
        }

        $otherInformationData = collect(json_decode($this->attributes['other_information'], true));
        
        return GuidingAdditionalInformation::whereIn('id', array_keys($otherInformationData->all()))
            ->get()
            ->map(function ($otherInformation) use ($otherInformationData) {
                $data = $otherInformationData[$otherInformation->id];
                return [
                    'id' => $otherInformation->id,
                    'value' => isset($data['value']) ? $data['value'] : $data,
                    'name' => $otherInformation->name
                ];
            });
    }

    public function getRecommendationsAttribute()
    {
        if (!$this->attributes['recommendations']) {
            return collect();
        }

        $recommendationsData = collect(json_decode($this->attributes['recommendations'], true));

        return GuidingRecommendations::whereIn('id', collect($recommendationsData->pluck('id')))
            ->get()
            ->map(function ($recommendation) use ($recommendationsData) {
                $data = $recommendationsData->firstWhere('id', $recommendation->id);
                return [
                    'id' => $recommendation->id,
                    'value' => isset($data['value']) ? $data['value'] : $data,
                    'name' => $recommendation->name
                ];
            });
    }

    public function getBoatInformationAttribute()
    {
        if (!$this->attributes['boat_information']) {
            return collect();
        }
        
        $boatInformationData = collect(json_decode($this->attributes['boat_information'], true));
        
        return GuidingBoatDescription::whereIn('id', collect($boatInformationData)->pluck('id'))
            ->get()
            ->map(function ($boatInformation) use ($boatInformationData) {
                $data = collect($boatInformationData)->firstWhere('id', $boatInformation->id);
                return [
                    'id' => $boatInformation->id,
                    'value' => $data['value'],
                    'name' => $boatInformation->name
                ];
            });
    }

    public function getPricingExtraAttribute()
    {
        if (!$this->attributes['pricing_extra']) {
            return collect();
        }

        $maxId = ExtrasPrice::max('id') ?? 10000; // Get highest existing ID or start at 10000
        $counter = $maxId + 1; // Start counter above highest existing ID
        
        return collect(json_decode($this->attributes['pricing_extra'], true))->map(function ($item) use (&$counter) {
            // Check if name is numeric (an ID)
            if (is_numeric($item['name'])) {
                // Try to find matching ExtrasPrice
                $extraPrice = ExtrasPrice::find($item['name']);
                if ($extraPrice) {
                    return [
                        'id' => $extraPrice->id,
                        'name' => $extraPrice->name,
                        'price' => $item['price']
                    ];
                }
            }
            
            // If name is not numeric or ExtrasPrice not found, generate an ID
            $result = [
                'id' => $counter, // Use incrementing counter that's guaranteed to not overlap
                'name' => $item['name'],
                'price' => $item['price']
            ];
            $counter++;
            return $result;
        });
    }
}
