<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuidingAdditionalInformation extends Model
{
    protected $fillable = [
        'name',
        'name_en',
    ];

    protected $table = 'guiding_additional_informations';
}
