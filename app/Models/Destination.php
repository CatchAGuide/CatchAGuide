<?php

namespace App\Models;

use App\Models\DestinationFaq;
use App\Models\DestinationFishChart;
use App\Models\DestinationFishSizeLimit;
use App\Models\DestinationFishTimeLimit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Destination extends Model
{

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'country_id',
        'region_id',
        'title',
        'sub_title',
        'introduction',
        'content',
        'filters',
        'thumbnail_path',

        'fish_avail_title',
        'fish_avail_intro',
        'size_limit_title',
        'size_limit_intro',
        'time_limit_title',
        'time_limit_intro',
        'faq_title',
        'slug',
        'language'
    ];

    public function faq()
    {
        return $this->hasMany(DestinationFaq::class);
    }

    public function fish_chart()
    {
        return $this->hasMany(DestinationFishChart::class);
    }

    public function fish_size_limit()
    {
        return $this->hasMany(DestinationFishSizeLimit::class);
    }

    public function fish_time_limit()
    {
        return $this->hasMany(DestinationFishTimeLimit::class);
    }

    /*public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = str_replace(' ', '-', strtolower($value));
    }*/

    public function getThumbnailPath()
    {
        if (empty($this->thumbnail_path)) {
            return 'https://place-hold.it/300x300';
        }

        $thumbnail_path = \Str::replace('public', 'storage', $this->thumbnail_path);

        return '/' . $thumbnail_path;
    }

    public function getCountryNameAttribute()
    {
        $row = self::whereType('country')->whereId($this->country_id)->first();
        if (!empty($row)) {
            return $row->name;
        }

        return 'N/A';
    }

    public function getCountrySlugAttribute()
    {
        $row = self::whereType('country')->whereId($this->country_id)->first();
        if (!empty($row)) {
            return $row->slug;
        }

        return 'N/A';
    }

    public function getRegionNameAttribute()
    {
        $row = self::whereType('region')->whereId($this->region_id)->first();
        if (!empty($row)) {
            return $row->name;
        }

        return 'N/A';
    }

    public function getRegionSlugAttribute()
    {
        $row = self::whereType('region')->whereId($this->region_id)->first();
        if (!empty($row)) {
            return $row->slug;
        }

        return 'N/A';
    }
}