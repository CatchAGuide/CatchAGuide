<?php

namespace App\Services\Booking\Reschedule;

use App\Models\Booking;

/**
 * What a customer's reschedule link currently allows. Every state other than AVAILABLE is
 * shown as a friendly page with a way forward, never as an error.
 */
final class RescheduleOffer
{
    public const AVAILABLE = 'available';
    public const INVALID = 'invalid';        // unknown token / no session
    public const USED = 'used';              // a new date was already requested
    public const EXPIRED = 'expired';        // every suggested date has passed or is taken
    public const UNAVAILABLE = 'unavailable'; // tour is no longer publicly bookable

    /**
     * @param  list<string>|null  $dates  Bookable suggested dates (Y-m-d); null = guide suggested none, any free date.
     */
    public function __construct(
        public readonly string $status,
        public readonly ?Booking $booking = null,
        public readonly ?array $dates = null,
    ) {}

    public static function invalid(): self
    {
        return new self(self::INVALID);
    }

    public function isAvailable(): bool
    {
        return $this->status === self::AVAILABLE;
    }

    public function allowsDate(string $date): bool
    {
        return $this->isAvailable() && ($this->dates === null || in_array($date, $this->dates, true));
    }
}
