<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CityTranslation extends Model
{
    use HasFactory;

    protected $table = 'c_city_translations';

    protected $fillable = [
        'city_id',
        'language',
        'title',
        'sub_title',
        'introduction',
        'content',
        'fish_avail_title',
        'fish_avail_intro',
        'size_limit_title',
        'size_limit_intro',
        'time_limit_title',
        'time_limit_intro',
        'faq_title',
    ];

    /**
     * Get the city that owns this translation
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
