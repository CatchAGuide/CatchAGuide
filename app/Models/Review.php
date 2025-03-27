<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'comment',
        'overall_score',
        'guide_score', 
        'region_water_score',
        'grandtotal_score',
        'user_id',
        'guide_id',
        'booking_id',
        'guiding_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($review) {
            $review->grandtotal_score = ($review->overall_score + $review->guide_score + $review->region_water_score) / 3;
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function guide()
    {
        return $this->belongsTo(User::class)->where('is_guide', true);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function guiding()
    {
        return $this->belongsTo(Guiding::class);
    }
}
