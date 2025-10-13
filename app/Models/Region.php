<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\DestinationFaq;
use App\Models\DestinationFishChart;
use App\Models\DestinationFishSizeLimit;
use App\Models\DestinationFishTimeLimit;

class Region extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'c_regions';

    protected $fillable = [
        'country_id',
        'name',
        'slug',
        'filters',
        'thumbnail_path',
    ];

    protected $casts = [
        'filters' => 'array',
    ];

    /**
     * Cache for the current locale translation
     */
    protected $currentTranslation = null;

    /**
     * Get the country that owns this region
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get all translations for this region
     */
    public function translations()
    {
        return $this->hasMany(RegionTranslation::class);
    }

    /**
     * Get translation for specific language
     */
    public function translation($language = null)
    {
        $language = $language ?? app()->getLocale();
        return $this->hasOne(RegionTranslation::class)->where('language', $language);
    }

    /**
     * Get all cities in this region
     */
    public function cities()
    {
        return $this->hasMany(City::class);
    }

    /**
     * Get FAQs for this region
     */
    public function faqs()
    {
        return $this->hasMany(DestinationFaq::class, 'destination_id')
            ->where('destination_type', 'region');
    }

    /**
     * Get fish charts for this region
     */
    public function fish_charts()
    {
        return $this->hasMany(DestinationFishChart::class, 'destination_id')
            ->where('destination_type', 'region');
    }

    /**
     * Get fish size limits for this region
     */
    public function fish_size_limits()
    {
        return $this->hasMany(DestinationFishSizeLimit::class, 'destination_id')
            ->where('destination_type', 'region');
    }

    /**
     * Get fish time limits for this region
     */
    public function fish_time_limits()
    {
        return $this->hasMany(DestinationFishTimeLimit::class, 'destination_id')
            ->where('destination_type', 'region');
    }

    /**
     * Get thumbnail path with fallback
     */
    public function getThumbnailPath()
    {
        if (empty($this->thumbnail_path)) {
            return asset('assets/images/300x300.png');
        }

        $thumbnail_path = \Str::replace('public', 'storage', $this->thumbnail_path);
        return '/' . $thumbnail_path;
    }

    /**
     * Get cached current translation to avoid N+1 queries
     */
    protected function getCurrentTranslation()
    {
        if ($this->currentTranslation === null) {
            $this->currentTranslation = $this->translation()->first();
        }
        return $this->currentTranslation;
    }

    /**
     * Accessor: Get translated title based on current locale
     */
    public function getTitleAttribute()
    {
        $translation = $this->getCurrentTranslation();
        return $translation ? $translation->title : $this->name;
    }

    /**
     * Accessor: Get translated subtitle based on current locale
     */
    public function getSubTitleAttribute()
    {
        $translation = $this->getCurrentTranslation();
        return $translation ? $translation->sub_title : null;
    }

    /**
     * Accessor: Get translated introduction based on current locale
     */
    public function getIntroductionAttribute()
    {
        $translation = $this->getCurrentTranslation();
        return $translation ? $translation->introduction : null;
    }

    /**
     * Accessor: Get translated content based on current locale
     */
    public function getContentAttribute()
    {
        $translation = $this->getCurrentTranslation();
        return $translation ? $translation->content : null;
    }

    /**
     * Accessor: Get translated fish_avail_title based on current locale
     */
    public function getFishAvailTitleAttribute()
    {
        $translation = $this->getCurrentTranslation();
        return $translation ? $translation->fish_avail_title : null;
    }

    /**
     * Accessor: Get translated fish_avail_intro based on current locale
     */
    public function getFishAvailIntroAttribute()
    {
        $translation = $this->getCurrentTranslation();
        return $translation ? $translation->fish_avail_intro : null;
    }

    /**
     * Accessor: Get translated size_limit_title based on current locale
     */
    public function getSizeLimitTitleAttribute()
    {
        $translation = $this->getCurrentTranslation();
        return $translation ? $translation->size_limit_title : null;
    }

    /**
     * Accessor: Get translated size_limit_intro based on current locale
     */
    public function getSizeLimitIntroAttribute()
    {
        $translation = $this->getCurrentTranslation();
        return $translation ? $translation->size_limit_intro : null;
    }

    /**
     * Accessor: Get translated time_limit_title based on current locale
     */
    public function getTimeLimitTitleAttribute()
    {
        $translation = $this->getCurrentTranslation();
        return $translation ? $translation->time_limit_title : null;
    }

    /**
     * Accessor: Get translated time_limit_intro based on current locale
     */
    public function getTimeLimitIntroAttribute()
    {
        $translation = $this->getCurrentTranslation();
        return $translation ? $translation->time_limit_intro : null;
    }

    /**
     * Accessor: Get translated faq_title based on current locale
     */
    public function getFaqTitleAttribute()
    {
        $translation = $this->getCurrentTranslation();
        return $translation ? $translation->faq_title : null;
    }
}
