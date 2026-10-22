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
        'guest_name',
        'overall_score',
        'accommodation_score',
        'fishing_score',
        'value_score',
        'grandtotal_score',
        'comment',
        'language',
        'is_manual',
    ];

    protected $casts = [
        'overall_score' => 'integer',
        'accommodation_score' => 'integer',
        'fishing_score' => 'integer',
        'value_score' => 'integer',
        'grandtotal_score' => 'float',
        'is_manual' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (VacationReview $review) {
            $scores = array_map(fn (string $field) => (int) $review->{$field}, self::SCORES);
            $review->grandtotal_score = round(array_sum($scores) / count($scores), 2);
        });
    }

    /**
     * The CampVacationBooking or TripBooking this review belongs to (null for a manual review
     * without a booking).
     */
    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeNewestFirst($query)
    {
        return $query->orderByDesc('created_at')->orderByDesc('id');
    }

    public function scopeForListing($query, string $listingType, int $listingId)
    {
        return $query->where('listing_type', $listingType)->where('listing_id', $listingId);
    }

    /**
     * First name shown on the public page: the stored name of a manual review, else the
     * booking's first name (or the first word of its full name).
     */
    public function displayName(): ?string
    {
        $name = trim((string) ($this->guest_name ?: ($this->reviewable?->first_name ?: $this->reviewable?->name)));
        if ($name === '') {
            return null;
        }

        return $this->guest_name ? $name : strtok($name, ' ');
    }
}
