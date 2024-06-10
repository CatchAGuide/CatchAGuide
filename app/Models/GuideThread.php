<?php

namespace App\Models;

use App\Models\Category;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GuideThread extends Model
{
    use HasFactory, HasSlug;


    protected $fillable = [
        'title',
        'language',
        'slug',
        'excerpt',
        'body',
        'filters',
        'author',
        'thumbnail_path',
        'introduction'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function getThumbnailPath()
    {
        $thumbnail_path = \Str::replace('public', 'storage', $this->thumbnail_path);

        return '/'.$thumbnail_path;
    }

}
