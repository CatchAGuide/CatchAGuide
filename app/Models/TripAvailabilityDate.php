<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripAvailabilityDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'departure_date',
        'spots_available',
        'status',
    ];

    protected $casts = [
        'departure_date'  => 'date',
        'spots_available' => 'integer',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }
}

