<?php

namespace App\Mail\Admin;

use App\Mail\Guest\TripCheckoutGuestMail;
use App\Models\Trip;
use App\Models\TripAvailabilityDate;
use App\Models\TripBooking;
use App\Presenters\Vacation\TripQuotePresenter;
use App\Services\Checkout\Trip\TripCheckoutOffer;
use Illuminate\Support\Str;

/**
 * Admin notification for a trip checkout: the fixed departure (with its free places) or the
 * preferred travel window, the estimate shown to the guest and the guest's message. Sent in the
 * booking's customerLocale().
 */
class TripCheckoutAdminMail extends CheckoutRequestAdminMail
{
    // Properties for email logging
    public $type = 'admin_trip_checkout_request';
    public $language;
    public $target;

    public function __construct(
        public TripBooking $booking,
        public Trip $trip,
        public string $guestMessageText = '',
    ) {
        $this->language = $booking->customerLocale();
        $this->target = 'trip_booking_'.$booking->id;
    }

    /**
     * Unsaved sample request (fixed departure, with a message) for the admin email preview.
     */
    public static function sample(): self
    {
        $guest = TripCheckoutGuestMail::sample();

        $guest->trip->forceFill(['group_size_max' => 8]);
        $guest->trip->setRelation('availabilityDates', collect([
            (new TripAvailabilityDate)->forceFill([
                'departure_date' => $guest->booking->preferred_date,
                'spots_available' => 2,
            ]),
        ]));

        $guest->booking->forceFill([
            'last_name' => 'Schmidt',
            'email' => 'lena.schmidt@example.com',
            'phone_country_code' => '+49',
            'phone' => '151 2345678',
            'created_at' => now(),
        ]);

        return new self($guest->booking, $guest->trip, $guest->guestMessage);
    }

    protected function request(): object
    {
        return $this->booking;
    }

    protected function guestMessage(): string
    {
        return $this->guestMessageText;
    }

    protected function requestsUrl(): string
    {
        return route('admin.trip-bookings.index');
    }

    protected function productData(): array
    {
        $presenter = app(TripQuotePresenter::class);
        $offer = TripCheckoutOffer::for($this->trip);
        $booking = $this->booking;
        $copy = self::COPY;

        // Same rule as the guest mail: a preferred window stores its end date.
        $fixedDate = $booking->preferred_date_to === null;
        $start = $booking->preferred_date?->toDateString();
        $persons = (int) $booking->number_of_persons;
        $total = $booking->estimated_total !== null ? (float) $booking->estimated_total : null;

        $dates = null;
        if ($start !== null) {
            $dates = $fixedDate
                ? $presenter->dayRange($start, $offer->returnDate($start), true)
                : $presenter->dayRange($start, $booking->preferred_date_to->toDateString(), false);
        }

        $duration = $fixedDate
            ? $presenter->duration($offer->durationDays(), $offer->durationNights())
            : $presenter->duration($offer->durationDays(), 0);

        $rows = [
            ['label' => __($copy.'.date_type'), 'value' => __($copy.($fixedDate ? '.date_fixed' : '.date_window'))],
        ];
        if ($dates !== null) {
            $rows[] = ['label' => __($copy.'.date'), 'value' => $dates];
        }
        if ($duration !== '') {
            $rows[] = ['label' => __($copy.($fixedDate ? '.duration' : '.trip_length')), 'value' => $duration];
        }
        $rows[] = ['label' => __($copy.'.persons'), 'value' => (string) $persons];

        $spots = $fixedDate ? ($offer->departure($start)['spots'] ?? null) : null;
        if ($spots !== null) {
            $capacity = (int) $this->trip->group_size_max;
            $rows[] = [
                'label' => __($copy.'.free_places'),
                'value' => $capacity >= $spots && $capacity > 0
                    ? __($copy.'.free_places_of', ['free' => $spots, 'total' => $capacity])
                    : (string) $spots,
            ];
        }

        $priceLines = [];
        if ($total !== null && $total > 0 && $persons > 0) {
            $unit = $presenter->money(round($total / $persons, 2));
            $subtotal = $presenter->money($total);
            $priceLines[] = [
                'label' => __($copy.'.price_line_trip', [
                    'persons' => $presenter->persons($persons),
                    'price' => $fixedDate ? $unit : __('checkout.trip.from', ['amount' => $unit]),
                ]),
                'amount' => $fixedDate ? $subtotal : __('checkout.trip.from', ['amount' => $subtotal]),
            ];
        }

        $title = (string) $this->trip->title;

        return [
            'productType' => 'trip',
            'title' => $title,
            // "Fliegenfischen Spanien: Pyrenäen-Reise auf …" → "Fliegenfischen Spanien"
            'shortTitle' => trim(Str::before($title, ':')) ?: $title,
            'location' => implode(' · ', array_filter([
                Str::ucfirst(trim((string) $this->trip->region)),
                Str::ucfirst(trim((string) $this->trip->country)),
            ])),
            'url' => $this->trip->slug ? route('vacations.trips.show', $this->trip->slug) : null,
            'image' => $this->trip->thumbnail_path ? media_url($this->trip->thumbnail_path) : null,
            'rows' => $rows,
            'priceLines' => $priceLines,
            'total' => $presenter->total($total, $fixedDate),
            'subjectDate' => $this->subjectDate($booking->preferred_date),
            'subjectTotal' => $total !== null && $total > 0 ? $presenter->total($total, $fixedDate) : null,
        ];
    }
}
