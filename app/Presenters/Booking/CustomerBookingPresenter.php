<?php

namespace App\Presenters\Booking;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\CampVacationBooking;
use App\Models\TripBooking;
use App\Models\VoucherOrder;
use Illuminate\Support\Carbon;

/**
 * Bookings as the guest sees them on /profile/bookings and the customer Overview: tour
 * bookings, camp and trip requests, and bought tour vouchers. Dates only, no times. The
 * guide's email and phone appear once the guide has accepted, the same rule the guide side
 * uses for the guest's details.
 */
class CustomerBookingPresenter
{
    public function __construct(private readonly GuideBookingPresenter $guidePresenter) {}

    /** @return array<string, mixed> */
    public function tour(Booking $booking): array
    {
        $status = BookingStatus::forBooking($booking) ?? BookingStatus::Cancelled;
        $guiding = $booking->guiding;
        $guide = $guiding?->user;
        $date = $booking->getBookingDate();
        $showContact = in_array($status, [BookingStatus::Confirmed, BookingStatus::Completed], true);
        $alternatives = $status === BookingStatus::Declined ? $this->alternativeDates($booking) : [];
        $extras = collect($booking->extrasList())
            ->filter(fn ($extra) => is_array($extra))
            ->map(fn (array $extra) => [
                'name' => (string) ($extra['extra_name'] ?? __('guide_area.customer.bookings.extra')),
                'price_label' => $this->money((float) ($extra['extra_total_price'] ?? 0)),
            ])->values()->all();

        return [
            'type' => 'tour',
            'id' => (int) $booking->id,
            'status' => $status->key(),
            'status_label' => __('guide_area.customer.status.'.$status->key()),
            'status_tone' => $status->tone(),
            'title' => (string) ($guiding?->title ?? __('guide_area.bookings.unknown_tour')),
            'url' => $guiding && filled($guiding->slug) && (int) $guiding->status === 1 ? $guiding->publicShowUrl() : null,
            'location' => (string) ($guiding?->location ?? ''),
            'meeting_point' => $showContact ? (string) ($guiding?->meeting_point ?? '') : '',
            'duration' => $guiding && (int) $guiding->duration > 0
                ? $guiding->duration.' '.($guiding->duration_type === 'multi_day' ? __('guidings.days') : __('guidings.hours'))
                : null,
            'guide' => [
                'name' => (string) ($guide?->full_name ?: __('guide_area.customer.bookings.your_guide')),
                'email' => $showContact ? ($guide?->email ?: null) : null,
                'phone' => $showContact ? ($this->guidePhone($guide) ?: null) : null,
            ],
            'contact_after_accept' => $status === BookingStatus::Open,
            'date_label' => $date ? $this->day($date) : '—',
            'date_short' => $date ? ['weekday' => $date->translatedFormat('D'), 'day' => $date->format('j'), 'month' => $date->translatedFormat('M')] : null,
            'requested_label' => $booking->created_at ? $this->day($booking->created_at) : null,
            'guests' => max(1, (int) $booking->count_of_users),
            'price_label' => $this->money($booking->getGrossAmount()),
            'extras' => $extras,
            'answer_by' => $status === BookingStatus::Open && $booking->expires_at ? $this->day(Carbon::parse($booking->expires_at)) : null,
            'guide_message' => $status === BookingStatus::Declined ? trim((string) $booking->additional_information) : '',
            'alternative_labels' => array_map(fn (string $d) => $this->day(Carbon::parse($d)), $alternatives),
            'reschedule_url' => $alternatives !== [] && filled($booking->token) ? route('booking.reschedule', ['token' => $booking->token]) : null,
            'cancel_reason_label' => $status === BookingStatus::Cancelled && $booking->cancel_reason
                ? __('guide_area.bookings.cancel_reasons.'.$booking->cancel_reason)
                : null,
            'cancelled_by_guide' => $status === BookingStatus::Cancelled && $booking->cancelled_by === 'guide',
            'rate_url' => $status === BookingStatus::Completed && filled($booking->token) && $booking->canBeReviewed()
                ? route('ratings.show', ['token' => $booking->token])
                : null,
        ];
    }

    /** @return array<string, mixed> */
    public function camp(CampVacationBooking $request): array
    {
        $listing = $request->camp;
        $parts = array_values(array_filter([
            $request->accommodation?->title,
            $request->rentalBoat?->title,
            $request->guiding?->title,
        ]));

        return $this->vacation($request, 'camp', [
            'title' => (string) ($listing?->title ?? __('guide_area.customer.bookings.unknown_camp')),
            'url' => $listing && filled($listing->slug) ? route('vacations.camps.show', ['slug' => $listing->slug]) : null,
            'location' => $this->place($listing),
            'date_label' => $request->preferred_date ? $this->day($request->preferred_date) : '—',
            'nights' => (int) $request->nights ?: null,
            'includes' => $parts,
        ]);
    }

    /** @return array<string, mixed> */
    public function trip(TripBooking $request): array
    {
        $listing = $request->trip;
        $from = $request->preferred_date;
        $to = $request->preferred_date_to;

        return $this->vacation($request, 'trip', [
            'title' => (string) ($listing?->title ?? __('guide_area.customer.bookings.unknown_trip')),
            'url' => $listing && filled($listing->slug) ? route('vacations.trips.show', ['slug' => $listing->slug]) : null,
            'location' => $this->place($listing),
            'date_label' => $from
                ? ($to && ! $to->isSameDay($from) ? $this->day($from).' – '.$this->day($to) : $this->day($from))
                : '—',
            'nights' => null,
            'includes' => [],
        ]);
    }

    /** @return array<string, mixed> */
    public function voucher(VoucherOrder $order): array
    {
        $status = (string) ($order->status ?: VoucherOrder::STATUS_OPEN);
        [$label, $tone] = match (true) {
            (bool) $order->is_sent => [__('guide_area.customer.status.voucher_sent'), 'success'],
            (bool) $order->is_paid => [__('guide_area.customer.status.voucher_paid'), 'success'],
            $status === VoucherOrder::STATUS_DONE => [__('guide_area.customer.status.request_done'), 'neutral'],
            default => [__('guide_area.customer.status.voucher_open'), 'warning'],
        };
        $guide = $order->guide;

        return [
            'type' => 'voucher',
            'id' => (int) $order->id,
            'reference' => $order->public_id ?: '#'.$order->id,
            'status_label' => $label,
            'status_tone' => $tone,
            'title' => (string) ($order->guiding?->title ?: $order->tour_title_snapshot ?: __('guide_area.bookings.unknown_tour')),
            'url' => $order->guiding && filled($order->guiding->slug) && (int) $order->guiding->status === 1 ? $order->guiding->publicShowUrl() : null,
            'guide_name' => (string) ($guide?->full_name ?: __('guide_area.customer.bookings.your_guide')),
            'guests' => max(1, (int) $order->number_of_guests),
            'price_label' => $this->money((float) $order->price_total),
            'recipients' => trim((string) $order->recipient_names),
            'ordered_label' => $order->created_at ? $this->day($order->created_at) : null,
            'valid_until_label' => $order->valid_until ? $this->day($order->valid_until) : null,
            'code' => $order->is_sent ? ($order->voucher_code ?: null) : null,
        ];
    }

    public function money(float $amount): string
    {
        return $this->guidePresenter->money($amount);
    }

    /**
     * Camp and trip requests share one shape: the CaG team handles them (open → in process →
     * done), so there is no accept step and no guide contact to show.
     *
     * @param  array<string, mixed>  $fields
     * @return array<string, mixed>
     */
    private function vacation(CampVacationBooking|TripBooking $request, string $type, array $fields): array
    {
        $status = (string) ($request->status ?: CampVacationBooking::STATUS_OPEN);
        $tone = match ($status) {
            CampVacationBooking::STATUS_DONE => 'neutral',
            CampVacationBooking::STATUS_IN_PROCESS => 'info',
            default => 'warning',
        };

        return $fields + [
            'type' => $type,
            'id' => (int) $request->id,
            'status' => $status,
            'status_label' => __('guide_area.customer.status.request_'.(in_array($status, ['open', 'in_process', 'done'], true) ? $status : 'open')),
            'status_tone' => $tone,
            'guests' => max(1, (int) $request->number_of_persons),
            'price_label' => (float) $request->estimated_total > 0 ? $this->money((float) $request->estimated_total) : null,
            'requested_label' => $request->created_at ? $this->day($request->created_at) : null,
            'message' => trim((string) $request->message),
            'rate_url' => $status === CampVacationBooking::STATUS_DONE && filled($request->review_token) && $request->review_requested_at
                ? route('ratings.vacation.show', ['type' => $type, 'token' => $request->review_token])
                : null,
        ];
    }

    private function place(mixed $listing): string
    {
        return trim(implode(', ', array_filter([$listing?->city ?? null, $listing?->country ?? null])));
    }

    private function guidePhone(mixed $guide): string
    {
        $number = trim((string) ($guide?->phone ?? ''));
        if ($number === '' || str_starts_with($number, '+') || str_starts_with($number, '00')) {
            return $number;
        }

        return trim(trim((string) ($guide?->phone_country_code ?? '')).' '.$number);
    }

    /** "Sat, 14 Nov 2026" (spec 2.2). */
    private function day(Carbon|\DateTimeInterface $date): string
    {
        return Carbon::instance($date)->translatedFormat('D, j M Y');
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
