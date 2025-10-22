<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegionTranslation extends Model
{
    use HasFactory;

    protected $table = 'c_region_translations';

    protected $fillable = [
        'region_id',
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
     * Get the region that owns this translation
     */
    public function region()
    {
        return $this->belongsTo(Region::class);
    }
}
