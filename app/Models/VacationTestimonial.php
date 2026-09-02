<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class VacationTestimonial extends Model
{
    protected $fillable = [
        'quote',
        'author',
        'rating',
        'reviewed_on',
        'listing_title',
        'listing_url',
        'is_published',
        'sort_order',
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'reviewed_on' => 'date',
        'is_published' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * @param  Builder<VacationTestimonial>  $query
     * @return Builder<VacationTestimonial>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
