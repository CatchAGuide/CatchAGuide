<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'c_cities';

    protected $fillable = [
        'country_id',
        'region_id',
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
     * Get the country that owns this city
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the region that owns this city
     */
    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Get all translations for this city
     */
    public function translations()
    {
        return $this->hasMany(CityTranslation::class);
    }

    /**
     * Get translation for specific language
     */
    public function translation($language = null)
    {
        $language = $language ?? app()->getLocale();
        return $this->hasOne(CityTranslation::class)->where('language', $language);
    }

    /**
     * Get FAQs for this city
     */
    public function faqs()
    {
        return $this->hasMany(DestinationFaq::class, 'destination_id')->where('destination_type', 'city');
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
