<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Media extends Model
{
    use HasFactory;
    protected $fillable = [
        'file_path',
        'file_type'
    ];

    /**
     * @return HasMany
     */
    public function guidings(): HasMany
    {
        return $this->hasMany(Guiding::class, 'thumbnail_id');
    }

    /**
     * @return HasMany
     */
    public function guiding_galleries(): HasMany
    {
        return $this->hasMany(GuidingGalleryMedia::class);
    }
}
