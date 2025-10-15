<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DestinationFishSizeLimit extends Model
{

    use HasFactory;

    protected $fillable = [
        'destination_id',
        'destination_type',
        'fish',
        'data',
        'language'
    ];

    /**
     * @return BelongsTo
     */
    public function category_country(): BelongsTo
    {
        return $this->belongsTo(Destination::class, 'destination_id');
    }
}