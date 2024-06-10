<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\Camper
 *
 * @property int $id
 * @property string $name
 * @property string $manufacturer
 * @property string $model
 * @property string $description
 * @property int $max_person
 * @property int $seats
 * @property string $price
 * @property int $mileage
 * @property int $power
 * @property string $fuel_type
 * @property string $gearbox
 * @property int $emission_class
 * @property int $eco_badge
 * @property \Illuminate\Support\Carbon $first_registration
 * @property int $vehicle_owners
 * @property int $total_weight
 * @property \Illuminate\Support\Carbon $main_exam
 * @property int $sleeping_places
 * @property int $fixed_bed
 * @property int $bunk_bed
 * @property int $bed_alcove
 * @property int $rear_sleeping_places
 * @property int $dinette_sleeping_places
 * @property int $lift_bed
 * @property string $heating
 * @property string $fresh_water_tank
 * @property string $waste_water_tank
 * @property string $rear_garage
 * @property int $length
 * @property int $width
 * @property int $heigth
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ContactRequest[] $contact_requests
 * @property-read int|null $contact_requests_count
 * @property-read \App\Models\Equipment|null $equipment
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CamperImage[] $images
 * @property-read int|null $images_count
 * @method static \Database\Factories\CamperFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Camper newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Camper query()
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereBedAlcove($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereBunkBed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereDinetteSleepingPlaces($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereEcoBadge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereEmissionClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereFirstRegistration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereFixedBed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereFreshWaterTank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereFuelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereGearbox($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereHeating($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereHeigth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereLength($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereLiftBed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereMainExam($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereManufacturer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereMaxPerson($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereMileage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper wherePower($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereRearGarage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereRearSleepingPlaces($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereSeats($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereSleepingPlaces($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereTotalWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereVehicleOwners($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereWasteWaterTank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereWidth($value)
 * @mixin \Eloquent
 */
class Camper extends Model
{
    use HasFactory;

    protected $casts = [
        'main_exam' => 'datetime',
        'first_registration' => 'datetime',
    ];
    protected $fillable =
        [
            'name',
            'manufacturer',
            'model',
            'description',
            'max_person',
            'price',
            'mileage',
            'power',
            'fuel_type',
            'gearbox',
            'emission_class',
            'eco_badge',
            'first_registration',
            'vehicle_owners',
            'total_weight',
            'main_exam',
            'sleeping_places',
            'length',
            'width',
            'heigth',
            'seats',
            'bed_alcove',
            'rear_sleeping_places',
            'dinette_sleeping_places',
            'lift_bed',
            'heating',
            'fresh_water_tank',
            'waste_water_tank',
            'rear_garage',
            'lend'
        ];

    /**
     * @return HasMany
     */
    public function images(): HasMany
    {
        return $this->hasMany(CamperImage::class);
    }

    /**
     * @return HasMany
     */
    public function contact_requests(): HasMany
    {
        return $this->hasMany(ContactRequest::class);
    }

    /**
     * @return HasOne
     */
    public function equipment(): HasOne
    {
        return $this->hasOne(Equipment::class);
    }

    /**
     * @return mixed
     */
    public function getFirstImage()
    {
        return $this->images->first()->getImage();
    }
}
