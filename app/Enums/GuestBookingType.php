<?php

namespace App\Enums;

use App\Models\Booking;
use App\Models\CampVacationBooking;
use App\Models\TripBooking;
use App\Models\VoucherOrder;
use Illuminate\Database\Eloquent\Model;
use Carbon\CarbonInterface;
use InvalidArgumentException;

/**
 * The four things a guest can book, as stored in booking_access_tokens.bookable_type. Short
 * keys instead of class names, so a model rename never orphans a link.
 */
enum GuestBookingType: string
{
    case Tour = 'tour';
    case Camp = 'camp';
    case Trip = 'trip';
    case Voucher = 'voucher';

    /** @return class-string<Model> */
    public function modelClass(): string
    {
        return match ($this) {
            self::Tour => Booking::class,
            self::Camp => CampVacationBooking::class,
            self::Trip => TripBooking::class,
            self::Voucher => VoucherOrder::class,
        };
    }

    public static function forModel(Model $model): self
    {
        return match (true) {
            $model instanceof Booking => self::Tour,
            $model instanceof CampVacationBooking => self::Camp,
            $model instanceof TripBooking => self::Trip,
            $model instanceof VoucherOrder => self::Voucher,
            default => throw new InvalidArgumentException('No guest booking type for '.$model::class),
        };
    }

    public static function supports(mixed $model): bool
    {
        return $model instanceof Booking
            || $model instanceof CampVacationBooking
            || $model instanceof TripBooking
            || $model instanceof VoucherOrder;
    }

    /** The booking's own email, trimmed and lowercased (links only ever go there). */
    public function email(Model $model): ?string
    {
        $email = match ($this) {
            self::Tour => $model->customerEmail(),
            self::Camp, self::Trip => $model->email,
            self::Voucher => $model->buyer_email,
        };
        $email = mb_strtolower(trim((string) $email));

        return $email !== '' ? $email : null;
    }

    public function locale(Model $model): string
    {
        return match ($this) {
            self::Voucher => (string) ($model->language ?: app()->getLocale()),
            default => $model->customerLocale(),
        };
    }

    /**
     * Last day of the trip (vouchers: the last day they can be used). The reduced view starts
     * 90 days after it (follow-up F4). Null when unknown.
     */
    public function lastDay(Model $model): ?CarbonInterface
    {
        return match ($this) {
            self::Tour => $model->getBookingDate()?->copy()->startOfDay(),
            self::Camp => $model->preferred_date?->copy()->addDays(max(0, (int) $model->nights)),
            // A fixed-date trip stores no end date; it lasts the trip's duration.
            self::Trip => $model->preferred_date_to?->copy()
                ?? $model->preferred_date?->copy()->addDays(max(0, (int) ($model->trip?->duration_days ?? 1) - 1)),
            self::Voucher => $model->valid_until?->copy(),
        };
    }

    public function translationKey(): string
    {
        return 'guest_bookings.types.'.$this->value;
    }
}
