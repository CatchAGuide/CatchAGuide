<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Equipment
 *
 * @property int $id
 * @property int $esp
 * @property int $central_locking
 * @property int $abs
 * @property int $wc
 * @property int $radio_with_cd
 * @property int $radio_without_cd
 * @property int $navigation
 * @property int $cruise_control
 * @property int $power_steering
 * @property int $seperate_shower
 * @property int $checkbook_maintained
 * @property int $awning
 * @property int $air_condition
 * @property int $parking_assist
 * @property int $camper_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Camper $equipment
 * @method static \Database\Factories\EquipmentFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment query()
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereAbs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereAirCondition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereAwning($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereCamperId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereCentralLocking($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereCheckbookMaintained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereCruiseControl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereEsp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereNavigation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereParkingAssist($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment wherePowerSteering($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereRadioWithCd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereRadioWithoutCd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereSeperateShower($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereWc($value)
 * @mixin \Eloquent
 */
class Equipment extends Model
{

    use HasFactory;

    protected $fillable = [
            'esp',
            'central_locking',
            'abs',
            'wc',
            'radio_with_cd',
            'radio_without_cd',
            'navigation',
            'cruise_control',
            'power_steering',
            'seperate_shower',
            'checkbook_maintained',
            'awning',
            'air_condition',
            'parking_assist',
            'camper_id'
    ];

    /**
     * @return BelongsTo
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Camper::class);
    }

    /**
     * @return array
     */
    public static function getAttributesList(): array
    {
        $list = (new Equipment)->getFillable();
        unset($list[14]);

        return $list;
    }

}
