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
        'galery_images',
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

        switch ($person) {
            case 1:
                $price = $this->price;
            break;
            case 2:
                $price = $this->price_two_persons;
            break;
            case 3:
                $price = $this->price_three_persons;
            break;
            case 4:
                $price = $this->price_four_persons;
            break;
            case 5:
                $price = $this->price_five_persons;
            break;
            default:
                $price = 0;
          }

        return $price;
    }

    public function ratings(){
        return $this->hasMany(Rating::class,'guide_id','id');
    }

    public function getLowestPrice()
    {
        if ($this->is_newguiding) {
            if ($this->price_type == 'per_person') {
                $prices = json_decode($this->prices, true);
                return $prices ? min(array_map(function($price) {
                    return $price['person'] > 1 ? round($price['amount'] / $price['person']) : $price['amount'];
                }, $prices)) : 0;
            }
            // Add handling for other price types if needed
            return 0;
        } else {
            $validPrices = array_filter([
                $this->price,
                $this->max_guests >= 2 ? $this->price_two_persons / 2 : null,
                $this->max_guests >= 3 ? $this->price_three_persons / 3 : null,
                $this->max_guests >= 4 ? $this->price_four_persons / 4 : null,
                $this->max_guests >= 5 ? $this->price_five_persons / 5 : null
            ], function($value) { return $value > 0; });

            return !empty($validPrices) ? min(array_map('round', $validPrices)) : 0;
        }
    }

    public function getBlockedEvents()
    {
        $blocked_events = collect($this->user->blocked_events)
            ->where('guiding_id', $this->id)
            ->map(function($blocked) {
                return [
                    "from" => date('Y-m-d', strtotime($blocked->from)), 
                    "due" => date('Y-m-d', strtotime($blocked->due))
                ];
            })->toArray();

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

    public function nearestGuides($latitude, $longitude, $country)
    {
        $latitude = $latitude;
        $longitude = $longitude;
        $country = $country;

        $distances = [50, 100, 200];
        $level = 1;
    
        foreach ($distances as $distance) {
            $guides = $this->select('*')
                ->where('country', $country)
                ->whereRaw("ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) <= ?", [$longitude, $latitude, $distance * 1000])
                ->get();
            
            if ($guides->isNotEmpty()) {
                // Add level identifier to the results
                return response()->json([
                    'note' => "Reached Level {$level} - {$distance} km",
                    'level' => $level,
                    'distance' => $distance,
                    'guides' => $guides
                ]);
            }
    
            $level++; // Increment level if no results found within the current radius
        }
        
        // Global nearest guide if none found within specified distances
        $nearestGuide = $this->select('*')
            ->orderByRaw("ST_Distance_Sphere(point(longitude, latitude), point(?, ?))", [$longitude, $latitude])
            ->first();
    
        return response()->json([
            'note' => 'Reached Global Level - No match within country boundaries',
            'level' => 4,
            'distance' => 0,
            'guides' => $nearestGuide ? [$nearestGuide] : []
        ]);
    }

    public static function nearestGuideIds($latitude, $longitude, $country)
    {
        $latitude = $latitude;
        $longitude = $longitude;
        $country = $country;

        $distances = [100, 200, 300];
        $level = 1;
    
        foreach ($distances as $distance) {
            $ids = [];
            $guides = self::select('*')
                ->where('status', 1)
                ->whereRaw("ST_Distance_Sphere(point(lng, lat), point(?, ?)) <= ?", [$longitude, $latitude, $distance * 1000])
                ->get();

            foreach ($guides as $guide) {
                $ids[] = $guide->id;
            }

            if (count($ids) > 0) {
                return $ids;
            }

            $level++; // Increment level if no results found within the current radius
        }
        
        // Global nearest guide if none found within specified distances
        $nearestGuide = self::select('*')
            ->where('status', 1)
            ->orderByRaw("ST_Distance_Sphere(point(lng, lat), point(?, ?))", [$longitude, $latitude])
            ->first();
    
        return $nearestGuide ? [$nearestGuide->id] : null;
    }

    public static function nearestGuideList($latitude, $longitude, $customRadius = null)
    {
        // If custom radius provided, try that first
        if ($customRadius) {
            $guides = self::select('id')
                ->whereRaw("ST_Distance_Sphere(point(lng, lat), point(?, ?)) <= ?", [
                    $longitude, 
                    $latitude, 
                    $customRadius * 1000
                ])
                ->where('status', 1)
                ->get();

            if ($guides->isNotEmpty()) {
                return $guides;
            }
        }

        $distances = [50, 100, 200];

        foreach ($distances as $distance) {
            $distance = $customRadius ? $customRadius + $distance : $distance;
            $guides = self::select('id')
                ->whereRaw("ST_Distance_Sphere(point(lng, lat), point(?, ?)) <= ?", [
                    $longitude, 
                    $latitude, 
                    $distance * 1000
                ])
                ->where('status', 1)
                ->get();

            if ($guides->isNotEmpty()) {
                return $guides;
            }
        }

        // Level 4: Find nearest guide globally
        return self::select('id')
            ->where('status', 1)
            ->orderByRaw("ST_Distance_Sphere(point(lng, lat), point(?, ?))", [$longitude, $latitude])
            ->limit(1)
            ->get();
    }



}
