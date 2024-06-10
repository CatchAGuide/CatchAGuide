<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuidingGalleryMedia extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'media_id',
        'guiding_id'
    ];

    /**
     * @return BelongsTo
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    /**
     * @return BelongsTo
     */
    public function guiding(): BelongsTo
    {
        return $this->belongsTo(Guiding::class);
    }
}
