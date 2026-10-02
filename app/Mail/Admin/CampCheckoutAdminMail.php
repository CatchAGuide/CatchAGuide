<?php

namespace App\Mail\Admin;

use App\Mail\Guest\CampCheckoutGuestMail;
use App\Models\Camp;
use App\Models\CampVacationBooking;
use App\Presenters\Vacation\CampQuotePresenter;
use Illuminate\Support\Str;

/**
 * Admin notification for a camp checkout: stay, chosen options with the estimate shown to the
 * guest, and the guest's message. Sent in the booking's customerLocale().
 */
class CampCheckoutAdminMail extends CheckoutRequestAdminMail
{
    /** price_breakdown line type => row label key. */
    private const OPTION_ROWS = [
        'accommodation' => 'accommodation',
        'boat' => 'rental_boat',
        'tour' => 'guided_tour',
        'special' => 'special_offer',
    ];

    // Properties for email logging
    public $type = 'admin_camp_checkout_request';
    public $language;
    public $target;

    public function __construct(
        public CampVacationBooking $booking,
        public Camp $camp,
        public string $guestMessageText = '',
    ) {
        $this->language = $booking->customerLocale();
        $this->target = 'camp_vacation_booking_'.$booking->id;
    }

    /**
     * Unsaved sample request (all options chosen, with a message) for the admin email preview.
     */
    public static function sample(): self
    {
        $guest = CampCheckoutGuestMail::sample();

        $guest->booking->forceFill([
            'last_name' => 'Schmidt',
            'email' => 'lena.schmidt@example.com',
            'phone_country_code' => '+49',
            'phone' => '151 2345678',
            'created_at' => now(),
        ]);

        return new self($guest->booking, $guest->camp, $guest->guestMessage);
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
        return route('admin.camp-vacation-bookings.index');
    }

    protected function productData(): array
    {
        $presenter = app(CampQuotePresenter::class);
        $booking = $this->booking;
        $copy = self::COPY;
        $lines = $booking->price_breakdown ?? [];
        $nights = (int) $booking->nights;
        $total = $booking->estimated_total !== null ? (float) $booking->estimated_total : null;
        $arrival = $booking->preferred_date;

        $rows = [];
        if ($arrival !== null) {
            $rows[] = ['label' => __($copy.'.arrival'), 'value' => $this->dayDate($arrival)];
            if ($nights > 0) {
                $rows[] = ['label' => __($copy.'.departure'), 'value' => $this->dayDate($arrival->copy()->addDays($nights))];
            }
        }
        $rows[] = ['pair' => [
            ['label' => __($copy.'.nights'), 'value' => (string) $nights],
            ['label' => __($copy.'.persons'), 'value' => (string) (int) $booking->number_of_persons],
        ]];
        foreach ($lines as $line) {
            $key = self::OPTION_ROWS[$line['type'] ?? ''] ?? null;
            if ($key !== null && trim((string) ($line['name'] ?? '')) !== '') {
                $rows[] = ['label' => __($copy.'.'.$key), 'value' => (string) $line['name']];
            }
        }

        $title = (string) $this->camp->title;

        return [
            'productType' => 'camp',
            'title' => $title,
            'shortTitle' => $title,
            'location' => implode(' · ', array_map(Str::ucfirst(...), array_filter([(string) $this->camp->city, (string) $this->camp->country]))),
            'url' => $this->camp->slug ? route('vacations.camps.show', $this->camp->slug) : null,
            'image' => $this->camp->thumbnail_path ? media_url($this->camp->thumbnail_path) : null,
            'rows' => $rows,
            'priceLines' => $presenter->lines($lines),
            'total' => $presenter->total($total),
            'subjectDate' => $this->subjectDate($arrival),
            'subjectTotal' => $total !== null && $total > 0 ? $presenter->total($total) : null,
        ];
    }
}
