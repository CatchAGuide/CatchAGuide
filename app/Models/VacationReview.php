<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * A guest's review of a camp or trip stay, submitted from the link in the review request email
 * (see App\Services\Vacation\VacationReviewService).
 */
class VacationReview extends Model
{
    public const SCORES = ['overall_score', 'accommodation_score', 'fishing_score', 'value_score'];

    protected $fillable = [
        'reviewable_type',
        'reviewable_id',
        'listing_type',
        'listing_id',
        'user_id',
        'overall_score',
        'accommodation_score',
        'fishing_score',
        'value_score',
        'grandtotal_score',
        'comment',
        'language',
    ];

    protected $casts = [
        'overall_score' => 'integer',
        'accommodation_score' => 'integer',
        'fishing_score' => 'integer',
        'value_score' => 'integer',
        'grandtotal_score' => 'float',
    ];

    protected static function booted(): void
    {
        static::saving(function (VacationReview $review) {
            $scores = array_map(fn (string $field) => (int) $review->{$field}, self::SCORES);
            $review->grandtotal_score = round(array_sum($scores) / count($scores), 2);
        });
    }

    /**
     * The CampVacationBooking or TripBooking this review belongs to.
     */
    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeNewestFirst($query)
    {
        return $query->orderByDesc('created_at')->orderByDesc('id');
    }
}
