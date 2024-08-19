<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoryCountryFaq extends Model
{

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'language',
        'title',
        'sub_title',
        'introduction',
        'content',
        'filters',
        'thumbnail_path'
    ];

    /**
     * @return BelongsTo
     */
    public function category_country(): BelongsTo
    {
        return $this->belongsTo(CategoryCountry::class, 'category_country_id');
    }
}
