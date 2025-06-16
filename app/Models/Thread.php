<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use App\Traits\Cacheable;

class Thread extends Model
{
    use HasSlug, Cacheable;

    protected $fillable = [
        'title',
        'language',
        'slug',
        'excerpt',
        'body',
        'author',
        'thumbnail_path',
        'category_id',
        'cache'
    ];

    /**
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function getThumbnailPath()
    {
        $thumbnail_path = \Str::replace('public', 'storage', $this->thumbnail_path);

        return '/'.$thumbnail_path;
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }
}
