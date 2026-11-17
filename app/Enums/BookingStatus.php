<?php

namespace App\Enums;

/**
 * Guide Area view of a tour booking's bookings.status. Stored values stay as they are
 * (pending / accepted / rejected / cancelled, no data rewrite); the spec's names map onto
 * them: Open, Confirmed, Declined, Cancelled. Completed and Expired are written only once the
 * booking state machine (plan Phase 1) exists.
 */
enum BookingStatus: string
{
    case Open = 'pending';
    case Confirmed = 'accepted';
    case Declined = 'rejected';
    case Cancelled = 'cancelled';
    case Completed = 'completed';
    case Expired = 'expired';

    public static function fromStored(?string $value): ?self
    {
        return self::tryFrom(strtolower(trim((string) $value)));
    }

    /** Stable string used in URLs and the API (`open`, `confirmed`, …). */
    public function key(): string
    {
        return strtolower($this->name);
    }

    public function label(): string
    {
        return __('guide_area.bookings.status.'.$this->key());
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Declined, self::Cancelled, self::Completed, self::Expired], true);
    }
}
