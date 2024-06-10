<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SearchRequest extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'fishing_type',
        'name',
        'email',
        'phone',
        'country',
        'region',
        'target_fish',
        'number_of_guest',
        'is_best_fishing_time_recommendation',
        'date_from',
        'date_to',
        'is_guided',
        'days_of_guiding',
        'is_boat_rental',
        'days_of_boat_rental',
        'total_budget_to_spend',
        'comments',
    ];
}
