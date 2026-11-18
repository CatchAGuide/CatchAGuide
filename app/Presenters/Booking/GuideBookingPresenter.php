<?php

namespace App\Presenters\Booking;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Support\Carbon;

/**
 * One tour booking as the guide sees it (Guide Area › Bookings, Overview and
 * /api/guide/bookings). Dates only, no times (spec 2.2). The guest's phone and email appear
 * only once the guide has accepted (spec BK-08).
 */
class GuideBookingPresenter
{
    /** @return array<string, mixed> */
    public function present(Booking $booking): array
    {
        $status = BookingStatus::forBooking($booking) ?? BookingStatus::Cancelled;
        $guiding = $booking->guiding;
        $date = $booking->getBookingDate();
        $price = (float) $booking->getGrossAmount();
        $commission = (float) ($booking->cag_percent ?? 0);
        $customer = $booking->user;
        $showContact = $status->showsGuestContact();
        $phone = $showContact ? $this->phone($booking) : '';
        $email = $showContact ? $booking->customerEmail() : null;
        $alternatives = $this->alternativeDates($booking);

        return [
            'id' => (int) $booking->id,
            'status' => $status->key(),
            'status_label' => $status->label(),
            'status_tone' => $status->tone(),
            'tour' => [
                'id' => $guiding?->id ? (int) $guiding->id : null,
                'title' => (string) ($guiding?->title ?? __('guide_area.bookings.unknown_tour')),
                'url' => $guiding && filled($guiding->slug) && (int) $guiding->status === 1 ? $guiding->publicShowUrl() : null,
                'location' => (string) ($guiding?->location ?? ''),
                'meeting_point' => (string) ($guiding?->meeting_point ?? ''),
                'duration' => $guiding && (int) $guiding->duration > 0
                    ? $guiding->duration.' '.($guiding->duration_type === 'multi_day' ? __('guidings.days') : __('guidings.hours'))
                    : null,
                'type' => $guiding ? $guiding->shoreOrBoatLabel() : null,
            ],
            'date' => $date?->toDateString(),
            'requested_at' => $booking->created_at?->toIso8601String(),
            'expires_at' => $booking->expires_at ? Carbon::parse($booking->expires_at)->toIso8601String() : null,
            'guests' => (int) $booking->count_of_users,
            'price' => round($price, 2),
            'commission' => round($commission, 2),
            'guide_amount' => round(max(0, $price - $commission), 2),
            'commission_percent' => $price > 0 ? (int) round($commission / $price * 100) : null,
            'guest' => [
                'name' => $this->guestName($customer, $showContact),
                'phone' => $phone !== '' ? $phone : null,
                'email' => $email,
            ],
            'alternative_dates' => $alternatives,
            'cancel_reason' => $booking->cancel_reason,
            'can_accept' => $status === BookingStatus::Open,
            'can_decline' => $status === BookingStatus::Open,
            'can_cancel' => $status === BookingStatus::Confirmed,
            // Display strings in the current locale (page only).
            'date_label' => $date ? $this->day($date) : '—',
            'date_short' => $date ? ['weekday' => $date->translatedFormat('D'), 'day' => $date->format('j'), 'month' => $date->translatedFormat('M')] : null,
            'requested_label' => $booking->created_at ? $this->day($booking->created_at) : null,
            'reply_label' => $status === BookingStatus::Open ? $this->replyLabel($booking) : null,
            'reply_urgent' => $status === BookingStatus::Open && $booking->expires_at && Carbon::parse($booking->expires_at)->lt(now()->addDay()),
            'price_label' => $this->money($price),
            'commission_label' => $this->money($commission),
            'guide_amount_label' => $this->money(max(0, $price - $commission)),
            'alternative_labels' => array_map(fn (string $d) => $this->day(Carbon::parse($d)), $alternatives),
            'cancel_reason_label' => $booking->cancel_reason ? __('guide_area.bookings.cancel_reasons.'.$booking->cancel_reason) : null,
        ];
    }

    public function money(float $amount): string
    {
        $de = app()->getLocale() === 'de';

        return $de
            ? number_format($amount, 2, ',', '.').' €'
            : '€'.number_format($amount, 2, '.', ',');
    }

    /** Some rows store the number with its country code already (+31 …); don't repeat it. */
    private function phone(Booking $booking): string
    {
        $number = trim((string) $booking->phone);

        return str_starts_with($number, '+') || str_starts_with($number, '00') ? $number : trim($booking->getFullPhoneNumber());
    }

    /** "Sat, 14 Nov 2026" (spec 2.2). */
    private function day(Carbon|\DateTimeInterface $date): string
    {
        return Carbon::instance($date)->translatedFormat('D, j M Y');
    }

    private function replyLabel(Booking $booking): ?string
    {
        if (! $booking->expires_at) {
            return null;
        }

        $expires = Carbon::parse($booking->expires_at);
        $hours = (int) floor(now()->diffInHours($expires, false));

        return $hours < 48
            ? trans_choice('guide_area.bookings.reply_hours', max(1, $hours), ['count' => max(1, $hours)])
            : trans_choice('guide_area.bookings.reply_days', intdiv($hours, 24), ['count' => intdiv($hours, 24)]);
    }

    /** Full name once confirmed; before that first name + last initial ("Markus B."). */
    private function guestName(mixed $customer, bool $full): string
    {
        $first = trim((string) ($customer?->firstname ?? ''));
        $last = trim((string) ($customer?->lastname ?? ''));

        if ($first === '' && $last === '') {
            return __('guide_area.bookings.guest');
        }

        return $full || $last === '' ? trim($first.' '.$last) : $first.' '.mb_substr($last, 0, 1).'.';
    }

    /** @return list<string> */
    private function alternativeDates(Booking $booking): array
    {
        $raw = $booking->alternative_dates;
        $dates = is_array($raw) ? $raw : (json_decode((string) $raw, true) ?: []);

        return array_values(array_filter(array_map(
            fn ($d) => is_string($d) && preg_match('/^\d{4}-\d{2}-\d{2}/', $d) ? substr($d, 0, 10) : null,
            (array) $dates
        )));
    }
}
