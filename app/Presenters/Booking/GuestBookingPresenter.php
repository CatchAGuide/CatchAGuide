<?php

namespace App\Presenters\Booking;

use App\Enums\GuestBookingType;
use App\Models\Booking;
use App\Models\CampVacationBooking;
use App\Models\TripBooking;
use App\Models\VoucherOrder;
use App\Services\Customer\CustomerBookingService;
use Illuminate\Database\Eloquent\Model;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * One booking as the per-booking guest link shows it (/your-booking/{id}): the customer area's
 * card, with the guest link's own rules on top.
 *
 * - The guide's phone, email and meeting point show from the guide's confirmation until the
 *   tour day, then hide again (Q3). The card is the same one logged-in customers see.
 * - From 90 days after the trip the view is reduced (F4): tour, date, guide name, people, price,
 *   status and review link stay; contact details, meeting point, messages and the voucher PDF go.
 */
class GuestBookingPresenter
{
    public const REDACT_AFTER_DAYS = 90;

    public function __construct(
        private readonly CustomerBookingPresenter $cards,
        private readonly CustomerBookingService $bookings,
    ) {}

    /** The model with what its card needs loaded; null when it no longer exists. */
    public function load(GuestBookingType $type, int $id): ?Model
    {
        return match ($type) {
            GuestBookingType::Tour => $this->bookings->tourForCard($id),
            GuestBookingType::Camp => CampVacationBooking::query()->with(['camp', 'accommodation', 'rentalBoat', 'guiding'])->find($id),
            GuestBookingType::Trip => TripBooking::query()->with('trip')->find($id),
            GuestBookingType::Voucher => VoucherOrder::query()->with(['guiding', 'guide'])->find($id),
        };
    }

    /**
     * @return array{type: string, item: array<string, mixed>, redacted: bool, calendar: bool, voucher_pdf: bool}
     */
    public function present(Model $bookable, ?CarbonInterface $today = null): array
    {
        $type = GuestBookingType::forModel($bookable);
        $redacted = $this->isRedacted($bookable, $today);

        return [
            'type' => $type->value,
            'item' => $this->card($bookable, $today),
            'redacted' => $redacted,
            'calendar' => $this->calendarDate($bookable) !== null,
            'voucher_pdf' => ! $redacted && $bookable instanceof VoucherOrder && $this->voucherPdfAvailable($bookable),
        ];
    }

    /**
     * The card alone, with the guest rules applied (also the "Find my booking" list).
     *
     * @return array<string, mixed>
     */
    public function card(Model $bookable, ?CarbonInterface $today = null): array
    {
        $item = match (true) {
            $bookable instanceof Booking => $this->tour($bookable),
            $bookable instanceof CampVacationBooking => $this->cards->camp($bookable),
            $bookable instanceof TripBooking => $this->cards->trip($bookable),
            $bookable instanceof VoucherOrder => $this->cards->voucher($bookable),
        };

        return $this->isRedacted($bookable, $today) ? $this->redact($item) : $item;
    }

    public function isRedacted(Model $bookable, ?CarbonInterface $today = null): bool
    {
        $last = GuestBookingType::forModel($bookable)->lastDay($bookable);

        return $last !== null
            && $last->copy()->addDays(self::REDACT_AFTER_DAYS)->lt(($today ?? Carbon::today())->copy()->startOfDay());
    }

    /**
     * The day to put in the guest's calendar: a confirmed tour still to come, or the wish date
     * of a camp or trip request the team hasn't closed. Null when there's nothing to add.
     *
     * @return array{from: CarbonInterface, to: CarbonInterface}|null  `to` is the last day (inclusive)
     */
    public function calendarDate(Model $bookable, ?CarbonInterface $today = null): ?array
    {
        $today = ($today ?? Carbon::today())->copy()->startOfDay();
        $type = GuestBookingType::forModel($bookable);

        if ($bookable instanceof Booking) {
            $date = $bookable->getBookingDate();
            if ($date === null || $bookable->status !== 'accepted' || $date->copy()->startOfDay()->lt($today)) {
                return null;
            }
            $days = $bookable->guiding?->duration_type === 'multi_day' ? max(1, (int) $bookable->guiding->duration) : 1;

            return ['from' => $date->copy()->startOfDay(), 'to' => $date->copy()->startOfDay()->addDays($days - 1)];
        }

        if ($bookable instanceof CampVacationBooking || $bookable instanceof TripBooking) {
            $from = $bookable->preferred_date?->copy()->startOfDay();
            $to = $type->lastDay($bookable);
            if ($from === null || $to === null || $to->lt($today) || $bookable->status === CampVacationBooking::STATUS_DONE) {
                return null;
            }

            // A camp's last day is the departure morning; the stay covers the nights before it.
            return ['from' => $from, 'to' => $to->copy()->startOfDay()];
        }

        return null;
    }

    /** The guide sends the voucher once the buyer paid; before that there's nothing to download. */
    public function voucherPdfAvailable(VoucherOrder $order): bool
    {
        return (bool) $order->is_sent && $order->hasPdf();
    }

    /** @return array<string, mixed> */
    private function tour(Booking $booking): array
    {
        $item = $this->cards->tour($booking);

        // Q3: contact details are for the day itself, not for after the tour.
        if ($item['status'] === 'completed') {
            $item = $this->hideContact($item);
        }

        return $item;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function redact(array $item): array
    {
        if (($item['type'] ?? null) === 'tour') {
            $item = $this->hideContact($item);
            $item['guide_message'] = '';
            $item['extras'] = [];
        }
        if (array_key_exists('message', $item)) {
            $item['message'] = '';
        }
        if (array_key_exists('recipients', $item)) {
            $item['recipients'] = '';
        }
        if (array_key_exists('code', $item)) {
            $item['code'] = null;
        }

        return $item;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function hideContact(array $item): array
    {
        $item['meeting_point'] = '';
        $item['guide']['email'] = null;
        $item['guide']['phone'] = null;
        $item['contact_after_accept'] = false;
        $item['contact_placeholder'] = __('guest_bookings.contact_hidden');

        return $item;
    }
}
