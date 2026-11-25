<?php

namespace App\Services\Guide\Area;

use RuntimeException;

/**
 * A Guide Area booking action that can't be done: the request was already answered or has
 * expired, the day is already booked, or a confirmed booking can no longer be cancelled.
 */
class GuideBookingActionException extends RuntimeException
{
    /** HTTP status for the API (spec 5.6: 409 day taken, 410 expired). */
    private const STATUS = ['day_taken' => 409, 'expired' => 410];

    public function __construct(public readonly string $reason)
    {
        parent::__construct(__('guide_area.bookings.errors.'.$reason));
    }

    public function httpStatus(): int
    {
        return self::STATUS[$this->reason] ?? 422;
    }
}
