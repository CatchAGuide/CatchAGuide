<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuidingWaterType extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'name',
        'guiding_id'
    ];

    /**
     * @return BelongsTo
     */
    public function guiding(): BelongsTo
    {
        return $this->belongsTo(Guiding::class);
    }
}
