<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VacationInterestSignup extends Model
{
    protected $fillable = [
        'email',
        'country',
        'pillar',
        'locale',
    ];
}
