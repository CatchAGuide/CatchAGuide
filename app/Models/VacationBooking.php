<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VacationBooking extends Model
{
    protected $fillable = [
        'vacation_id',
        'start_date',
        'end_date',
        'duration',
        'number_of_persons',
        'booking_type',
        'package_id',
        'accommodation_id',
        'boat_id',
        'guiding_id',
        'title',
        'name',
        'surname',
        'street',
        'post_code',
        'city',
        'country',
        'phone_country_code',
        'phone',
        'email',
        'comments',
        'has_pets',
        'extra_offers',
        'total_price',
        'status'
    ];

    protected $casts = [
        'extra_offers' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'has_pets' => 'boolean',
    ];

    public function vacation()
    {
        return $this->belongsTo(Vacation::class);
    }

    public function package()
    {
        return $this->belongsTo(VacationPackage::class);
    }

    public function accommodation()
    {
        return $this->belongsTo(VacationAccommodation::class);
    }

    public function boat()
    {
        return $this->belongsTo(VacationBoat::class);
    }

    public function guiding()
    {
        return $this->belongsTo(VacationGuiding::class);
    }

    public function extras()
    {
        return $this->hasMany(VacationExtra::class);
    }
}
