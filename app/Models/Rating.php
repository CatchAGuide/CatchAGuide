<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Rating extends Model
{
    use HasFactory;
    protected $fillable = [
        'description',
        'rating',
        'rating_guide',
        'rating_region',
        'user_id',
        'guide_id',
        'booking_id'
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo
     */
    public function guide(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guide_id');
    }

    /**
     * @return HasOne
     */
    public function booking(): HasOne
    {
        return $this->hasOne(Booking::class);
    }
}
