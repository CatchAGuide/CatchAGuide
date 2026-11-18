<?php

namespace App\Enums;

use App\Models\Booking;
use App\Models\BookingStatusChange;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Guide Area view of a tour booking's bookings.status. Stored values stay as they are
 * (pending / accepted / rejected / cancelled, no data rewrite); the spec's names map onto
 * them: Open, Confirmed, Declined, Cancelled. Completed and Expired are not stored yet (no
 * completion job, and the expiry job writes `cancelled`), so forBooking() derives them:
 * Completed = confirmed with the tour date passed, Expired = cancelled by the expiry job
 * (status history reason `expired`).
 */
enum BookingStatus: string
{
    case Open = 'pending';
    case Confirmed = 'accepted';
    case Declined = 'rejected';
    case Cancelled = 'cancelled';
    case Completed = 'completed';
    case Expired = 'expired';

    /** Old rows store the German word for cancelled. */
    public const CANCELLED_STORED = ['cancelled', 'storniert'];

    public static function fromStored(?string $value): ?self
    {
        $value = strtolower(trim((string) $value));

        return $value === 'storniert' ? self::Cancelled : self::tryFrom($value);
    }

    /**
     * What the guide sees for this booking. Expects `expired_by_job` (withExists) when the
     * caller wants Expired told apart from Cancelled; without it, cancelled stays Cancelled.
     */
    public static function forBooking(Booking $booking, ?CarbonInterface $today = null): ?self
    {
        $stored = self::fromStored($booking->status);
        $today ??= Carbon::today();

        return match ($stored) {
            self::Open => $booking->expires_at && Carbon::parse($booking->expires_at)->isPast() ? self::Expired : self::Open,
            self::Confirmed => ($date = $booking->getBookingDate()) && $date->copy()->startOfDay()->lt($today) ? self::Completed : self::Confirmed,
            self::Cancelled => ! $booking->cancelled_by && $booking->expired_by_job ? self::Expired : self::Cancelled,
            default => $stored,
        };
    }

    /** History reason the expiry job writes (see GuidingBookingStatusLogger). */
    public static function expiryReason(): string
    {
        return BookingStatusChange::REASON_EXPIRED;
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

    /** Badge colour in the Guide Area (ga-status modifier). */
    public function tone(): string
    {
        return match ($this) {
            self::Open => 'warning',
            self::Confirmed => 'success',
            self::Completed => 'neutral',
            default => 'danger',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Declined, self::Cancelled, self::Completed, self::Expired], true);
    }

    /** The guest's phone and email are shown only once the guide has accepted (spec BK-08). */
    public function showsGuestContact(): bool
    {
        return in_array($this, [self::Confirmed, self::Completed, self::Cancelled], true);
    }
}
