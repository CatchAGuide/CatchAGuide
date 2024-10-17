<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuidingRequest extends Model
{
    use HasFactory;

    protected $table = 'guiding_requests';
    protected $fillable = [
        'name',
        'phone',
        'email',
        'country',
        'city',
        'days_of_tour',
        'specific_number_of_days',
        'accomodation',
        'targets',
        'methods',
        'fishing_from',
        'boat_info',
        'guiding_equipment',
        'number_of_guest',
        'date_of_tour',
        'rentaboat',
        'guide_type',
        'fishing_duration',
        'days_of_fishing'
    ];
}
