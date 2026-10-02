<?php

namespace App\Mail\Guest;

use App\Models\Trip;
use App\Models\TripBooking;
use App\Presenters\Vacation\TripQuotePresenter;
use App\Services\Checkout\Trip\TripCheckoutOffer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

/**
 * Confirmation to the guest after a trip checkout: the trip with its fixed departure or preferred
 * window, the estimate, their message and the next steps. Rendered in the locale it is sent with
 * (see TripBooking::customerLocale()).
 */
class TripCheckoutGuestMail extends Mailable
{
    use Queueable, SerializesModels;

    // Properties for email logging
    public $type = 'guest_trip_checkout_request';
    public $language;
    public $target;

    public function __construct(
        public TripBooking $booking,
        public Trip $trip,
        public string $guestMessage = '',
    ) {
        $this->language = $booking->customerLocale();
        $this->target = 'trip_booking_'.$booking->id;
    }

    /**
     * Unsaved sample request (fixed departure, with a message) for the admin email preview.
     */
    public static function sample(): self
    {
        $de = app()->getLocale() === 'de';

        $trip = (new Trip)->forceFill([
            'title' => $de
                ? 'Fliegenfischen Spanien: Pyrenäen-Reise auf Trophy-Forellen & Barben'
                : 'Fly fishing Spain: Pyrenees trip for trophy trout & barbel',
            'slug' => 'fliegenfischen-spanien-pyrenaeen',
            'region' => $de ? 'Pyrenäen' : 'Pyrenees',
            'country' => $de ? 'Spanien' : 'Spain',
            'duration_days' => 7,
            'duration_nights' => 6,
            'price_per_person' => 1290,
        ]);

        $booking = (new TripBooking)->forceFill([
            'preferred_date' => now()->addMonth()->startOfWeek()->addDays(5)->toDateString(),
            'preferred_date_to' => null,
            'number_of_persons' => 2,
            'estimated_total' => 2580,
            'first_name' => 'Lena',
            'name' => 'Lena Schmidt',
            'language' => app()->getLocale(),
        ]);

        return new self($booking, $trip, $de
            ? 'Wir sind zu zweit, beide mit etwas Erfahrung im Fliegenfischen. Eigene Ruten bringen wir mit.'
            : 'There are two of us, both with some fly fishing experience. We bring our own rods.');
    }

    public function build()
    {
        $data = $this->viewData();

        $mail = $this->view('mails.guest.trip_checkout_request')
            ->subject(__('emails.trip_checkout_guest.subject'))
            ->with($data);

        if ($data['contactEmail'] !== '') {
            $mail->replyTo($data['contactEmail']);
        }

        return $mail;
    }

    /**
     * Variables for mails.guest.trip_checkout_request in the current locale (also used by the
     * admin email preview).
     *
     * @return array<string, mixed>
     */
    public function viewData(): array
    {
        $presenter = app(TripQuotePresenter::class);
        $offer = TripCheckoutOffer::for($this->trip);
        $booking = $this->booking;
        $copy = 'emails.trip_checkout_guest';

        // Same rule as the thank-you page: a preferred window stores its end date.
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

        $rows = array_values(array_filter([
            $dates !== null ? ['label' => __($copy.($fixedDate ? '.travel_dates' : '.wish_window')), 'value' => $dates] : null,
            $duration !== '' ? ['label' => __($copy.($fixedDate ? '.duration' : '.trip_length')), 'value' => $duration] : null,
            ['label' => __($copy.'.persons'), 'value' => (string) $persons],
        ]));

        $price = null;
        if ($total !== null && $total > 0 && $persons > 0) {
            $unit = $presenter->money(round($total / $persons, 2));
            $subtotal = $presenter->money($total);
            $price = [
                'label' => __($copy.'.price_line', ['persons' => $presenter->persons($persons)]),
                'unit' => $fixedDate ? $unit : __('checkout.trip.from', ['amount' => $unit]),
                'subtotal' => $fixedDate ? $subtotal : __('checkout.trip.from', ['amount' => $subtotal]),
            ];
        }

        $tripTitle = (string) $this->trip->title;
        $mode = $fixedDate ? 'fixed' : 'window';

        return [
            'fixedDate' => $fixedDate,
            'tripTitle' => $tripTitle,
            'tripLocation' => implode(' · ', array_filter([
                Str::ucfirst(trim((string) $this->trip->region)),
                Str::ucfirst(trim((string) $this->trip->country)),
            ])),
            'tripUrl' => route('vacations.trips.show', $this->trip->slug),
            'firstName' => $booking->first_name ?: $booking->name,
            'preheader' => $fixedDate && $dates !== null
                ? __($copy.'.preheader_fixed', ['trip' => $tripTitle, 'dates' => $dates])
                : __($copy.'.preheader_window'),
            'intro' => __($copy.'.intro', ['trip' => $tripTitle]).' '.__($copy.'.intro_'.$mode),
            'rows' => $rows,
            'price' => $price,
            'total' => $presenter->total($total, $fixedDate),
            'note' => __($copy.'.note_'.$mode),
            'steps' => [
                __($copy.'.step_'.$mode.'_1'),
                __($copy.'.step_'.$mode.'_2'),
                __($copy.'.step_3'),
            ],
            'guestNote' => trim($this->guestMessage),
            'contactEmail' => (string) config('mail.admin_email'),
            'contactPhone' => config('cag.contact_num') ? '+49 (0) '.config('cag.contact_num') : null,
            'site' => preg_replace('/^www\./', '', (string) parse_url(url('/'), PHP_URL_HOST)),
        ];
    }
}
