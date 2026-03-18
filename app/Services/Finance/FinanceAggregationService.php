<?php

namespace App\Services\Finance;

use App\Models\Booking;
use App\Models\Camp;
use App\Models\CampVacationBooking;
use App\Models\Trip;
use App\Models\TripBooking;
use App\Models\Vacation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FinanceAggregationService
{
    public function __construct(
        private readonly ProvisionCalculator $provisionCalculator,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getInvoiceRows(Request $request): array
    {
        [$from, $to] = $this->getReservationDateRange($request);

        $bookingRows = $this->getGuidingBookingRows($from, $to);
        $tripRows = $this->getTripRequestRows($from, $to);
        $campVacationRows = $this->getCampVacationRequestRows($from, $to);

        $rows = array_merge($bookingRows, $tripRows, $campVacationRows);

        usort($rows, function (array $a, array $b) {
            return ($b['booking_date_sort'] ?? 0) <=> ($a['booking_date_sort'] ?? 0);
        });

        return $rows;
    }

    /**
     * Returns [from,to] date range (inclusive) or [null,null].
     *
     * Filters are based on Reservation Date:
     * - Booking: calendar_schedule.date OR blocked_event.from OR bookings.book_date
     * - TripBooking/CampVacationBooking: preferred_date
     *
     * Supported query params:
     * - year=YYYY
     * - month=1..12 (requires year; if omitted we assume current year)
     * - quarter=1..4 (requires year; if omitted we assume current year)
     *
     * @return array{0: Carbon|null, 1: Carbon|null}
     */
    private function getReservationDateRange(Request $request): array
    {
        $year = trim((string) $request->query('year', ''));
        $month = trim((string) $request->query('month', ''));
        $quarter = trim((string) $request->query('quarter', ''));

        $yearInt = $year !== '' && ctype_digit($year) ? (int) $year : null;
        $monthInt = $month !== '' && ctype_digit($month) ? (int) $month : null;
        $quarterInt = $quarter !== '' && ctype_digit($quarter) ? (int) $quarter : null;

        if ($yearInt === null && $monthInt === null && $quarterInt === null) {
            return [null, null];
        }

        $yearInt = $yearInt ?? (int) now()->format('Y');

        if ($quarterInt !== null && $quarterInt >= 1 && $quarterInt <= 4) {
            $startMonth = match ($quarterInt) {
                1 => 1,
                2 => 4,
                3 => 7,
                default => 10,
            };
            $from = Carbon::create($yearInt, $startMonth, 1)->startOfDay();
            $to = $from->copy()->addMonths(2)->endOfMonth()->endOfDay();
            return [$from, $to];
        }

        if ($monthInt !== null && $monthInt >= 1 && $monthInt <= 12) {
            $from = Carbon::create($yearInt, $monthInt, 1)->startOfDay();
            $to = $from->copy()->endOfMonth()->endOfDay();
            return [$from, $to];
        }

        // Year-only filter
        $from = Carbon::create($yearInt, 1, 1)->startOfDay();
        $to = Carbon::create($yearInt, 12, 31)->endOfDay();
        return [$from, $to];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getGuidingBookingRows(?Carbon $from, ?Carbon $to): array
    {
        $bookingsQuery = Booking::query()
            ->with(['guiding.user', 'user', 'financeItem', 'calendar_schedule', 'blocked_event'])
            ->where('status', 'accepted')
            ->latest();

        if ($from && $to) {
            $bookingsQuery->where(function ($q) use ($from, $to) {
                $q->whereHas('calendar_schedule', function ($sq) use ($from, $to) {
                    $sq->whereBetween('date', [$from->toDateString(), $to->toDateString()]);
                })->orWhereHas('blocked_event', function ($sq) use ($from, $to) {
                    $sq->whereBetween('from', [$from, $to]);
                })->orWhereBetween('book_date', [$from, $to]);
            });
        }

        $bookings = $bookingsQuery->get();

        return $bookings->map(function (Booking $booking) {
            $finance = $booking->financeItem;

            $guestName = null;
            $guestEmail = null;
            $guestPhone = trim(($booking->phone_country_code ?? '') . ' ' . ($booking->phone ?? ''));

            if ($booking->user) {
                $guestName = trim(($booking->user->firstname ?? '') . ' ' . ($booking->user->lastname ?? '')) ?: null;
                $guestEmail = $booking->email ?? ($booking->user->email ?? null);
                if (!$guestPhone && method_exists($booking, 'getFullPhoneNumber')) {
                    $guestPhone = $booking->getFullPhoneNumber();
                }
            } else {
                $guestName = 'Guest';
                $guestEmail = $booking->email ?? null;
            }

            $guideName = $booking->guiding && $booking->guiding->user
                ? (string) ($booking->guiding->user->full_name ?? trim(($booking->guiding->user->firstname ?? '') . ' ' . ($booking->guiding->user->lastname ?? '')))
                : null;
            $guideEmail = $booking->guiding && $booking->guiding->user ? ($booking->guiding->user->email ?? null) : null;

            $price = $booking->price !== null ? (float) $booking->price : null;
            $provision = $this->provisionCalculator->getProvisionAmount($price);
            $tax = $this->provisionCalculator->getTaxAmount($provision);

            $reservationDate = $booking->getBookingDate();

            return [
                'source_key' => 'booking',
                'id' => $booking->id,
                'booking_no' => 'B-' . $booking->id,
                'source' => 'Guiding' . ($booking->created_source ? (' (' . $booking->created_source . ')') : ''),
                'booking_date' => optional($booking->created_at)->format('Y-m-d H:i') ?? '—',
                'booking_date_sort' => optional($booking->created_at)->timestamp ?? 0,
                'guest_name' => $guestName ?: '—',
                'guest_email' => $guestEmail ?: '—',
                'guest_phone' => $guestPhone ?: '—',
                'guide_name' => $guideName ?: '—',
                'guide_email' => $guideEmail ?: '—',
                'price' => $price,
                'provision' => $provision,
                'tax' => $tax,
                'acceptance_status' => $booking->status ?? '—',
                'invoice_sent' => (bool) ($finance && $finance->invoice_status === 'sent'),
                'invoice_sent_at' => $finance?->invoice_sent_at,
                'paid_status' => $finance?->paid_status ?? 'unpaid',
                'paid_at' => $finance?->paid_at,
                'reservation_date' => $reservationDate ? $reservationDate->format('Y-m-d') : '—',
            ];
        })->values()->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getTripRequestRows(?Carbon $from, ?Carbon $to): array
    {
        $requestsQuery = TripBooking::query()
            ->with(['financeItem'])
            ->where('status', TripBooking::STATUS_DONE)
            ->latest();

        if ($from && $to) {
            $requestsQuery->whereBetween('preferred_date', [$from->toDateString(), $to->toDateString()]);
        }

        $requests = $requestsQuery->get();

        $tripIds = $requests->pluck('source_id')->filter()->unique()->values();
        $tripsById = Trip::query()
            ->whereIn('id', $tripIds)
            ->get()
            ->keyBy('id');

        return $requests->map(function (TripBooking $req) use ($tripsById) {
            $finance = $req->financeItem;

            $trip = $tripsById->get($req->source_id);
            $price = null;
            if ($trip) {
                $lowest = method_exists($trip, 'getLowestPrice') ? (float) $trip->getLowestPrice() : null;
                $price = ($lowest !== null && $lowest > 0) ? $lowest : null;
            }

            $provision = $this->provisionCalculator->getProvisionAmount($price);
            $tax = $this->provisionCalculator->getTaxAmount($provision);

            $guestPhone = trim(($req->phone_country_code ?? '') . ' ' . ($req->phone ?? ''));

            return [
                'source_key' => 'trip',
                'id' => $req->id,
                'booking_no' => 'T-' . $req->id,
                'source' => 'Trip',
                'booking_date' => optional($req->created_at)->format('Y-m-d H:i') ?? '—',
                'booking_date_sort' => optional($req->created_at)->timestamp ?? 0,
                'guest_name' => $req->name ?: '—',
                'guest_email' => $req->email ?: '—',
                'guest_phone' => $guestPhone ?: '—',
                'guide_name' => '—',
                'guide_email' => '—',
                'price' => $price,
                'provision' => $provision,
                'tax' => $tax,
                'acceptance_status' => $req->status ?? '—',
                'invoice_sent' => (bool) ($finance && $finance->invoice_status === 'sent'),
                'invoice_sent_at' => $finance?->invoice_sent_at,
                'paid_status' => $finance?->paid_status ?? 'unpaid',
                'paid_at' => $finance?->paid_at,
                'reservation_date' => optional($req->preferred_date)->format('Y-m-d') ?: '—',
            ];
        })->values()->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getCampVacationRequestRows(?Carbon $from, ?Carbon $to): array
    {
        $requestsQuery = CampVacationBooking::query()
            ->with(['financeItem'])
            ->where('status', CampVacationBooking::STATUS_DONE)
            ->latest();

        if ($from && $to) {
            $requestsQuery->whereBetween('preferred_date', [$from->toDateString(), $to->toDateString()]);
        }

        $requests = $requestsQuery->get();

        $campIds = $requests->where('source_type', CampVacationBooking::SOURCE_CAMP)->pluck('source_id')->filter()->unique()->values();
        $vacationIds = $requests->where('source_type', CampVacationBooking::SOURCE_VACATION)->pluck('source_id')->filter()->unique()->values();

        $campsById = Camp::query()->whereIn('id', $campIds)->get()->keyBy('id');
        $vacationsById = Vacation::query()->whereIn('id', $vacationIds)->get()->keyBy('id');

        return $requests->map(function (CampVacationBooking $req) use ($campsById, $vacationsById) {
            $finance = $req->financeItem;

            $price = null;
            $sourceType = strtolower((string) $req->source_type);
            if ($sourceType === CampVacationBooking::SOURCE_CAMP) {
                $camp = $campsById->get($req->source_id);
                if ($camp) {
                    $low = method_exists($camp, 'getLowestPrice') ? (float) $camp->getLowestPrice() : null;
                    $price = ($low !== null && $low > 0) ? $low : null;
                }
            } elseif ($sourceType === CampVacationBooking::SOURCE_VACATION) {
                $vac = $vacationsById->get($req->source_id);
                if ($vac) {
                    $p = $vac->package_price_per_person ?? $vac->accommodation_price ?? null;
                    $p = $p !== null ? (float) $p : null;
                    $price = ($p !== null && $p > 0) ? $p : null;
                }
            }

            $provision = $this->provisionCalculator->getProvisionAmount($price);
            $tax = $this->provisionCalculator->getTaxAmount($provision);

            $guestPhone = trim(($req->phone_country_code ?? '') . ' ' . ($req->phone ?? ''));
            $sourceLabel = ucfirst($sourceType ?: 'Camp/Vacation');

            return [
                'source_key' => 'camp_vacation',
                'id' => $req->id,
                'booking_no' => 'CV-' . $req->id,
                'source' => $sourceLabel,
                'booking_date' => optional($req->created_at)->format('Y-m-d H:i') ?? '—',
                'booking_date_sort' => optional($req->created_at)->timestamp ?? 0,
                'guest_name' => $req->name ?: '—',
                'guest_email' => $req->email ?: '—',
                'guest_phone' => $guestPhone ?: '—',
                'guide_name' => '—',
                'guide_email' => '—',
                'price' => $price,
                'provision' => $provision,
                'tax' => $tax,
                'acceptance_status' => $req->status ?? '—',
                'invoice_sent' => (bool) ($finance && $finance->invoice_status === 'sent'),
                'invoice_sent_at' => $finance?->invoice_sent_at,
                'paid_status' => $finance?->paid_status ?? 'unpaid',
                'paid_at' => $finance?->paid_at,
                'reservation_date' => optional($req->preferred_date)->format('Y-m-d') ?: '—',
            ];
        })->values()->all();
    }
}

