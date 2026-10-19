<?php

namespace App\Traits;

use App\Models\VacationReview;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Review request + review for a camp or trip booking (CampVacationBooking, TripBooking).
 * The using model provides `email`, `status` and the STATUS_DONE constant.
 */
trait HasVacationReview
{
    public function initializeHasVacationReview(): void
    {
        $this->mergeCasts(['review_requested_at' => 'datetime']);
        $this->makeHidden('review_token');
    }

    public function review(): MorphOne
    {
        return $this->morphOne(VacationReview::class, 'reviewable');
    }

    /**
     * An admin can ask for a review once the request is done and the guest has an email
     * address, until the guest has reviewed.
     */
    public function canRequestReview(): bool
    {
        return $this->status === static::STATUS_DONE
            && ! empty($this->email)
            && $this->review === null;
    }
}
