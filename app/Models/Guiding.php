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
        'payment_point'
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
            $query->whereHas('columnsWithValueCount', function ($query) {
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

}
