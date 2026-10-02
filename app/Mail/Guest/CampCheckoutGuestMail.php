<?php

namespace App\Mail\Guest;

use App\Models\Camp;
use App\Models\CampVacationBooking;
use App\Presenters\Vacation\CampQuotePresenter;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Confirmation to the guest after a camp checkout: stay, selection with the estimate, their
 * message and the next steps. Rendered in the locale it is sent with (see
 * CampVacationBooking::customerLocale()).
 */
class CampCheckoutGuestMail extends Mailable
{
    use Queueable, SerializesModels;

    // Properties for email logging
    public $type = 'guest_camp_checkout_request';
    public $language;
    public $target;

    public function __construct(
        public CampVacationBooking $booking,
        public Camp $camp,
        public string $guestMessage = '',
    ) {
        $this->language = $booking->customerLocale();
        $this->target = 'camp_vacation_booking_'.$booking->id;
    }

    /**
     * Unsaved sample request (all options chosen, with a message) for the admin email preview.
     */
    public static function sample(): self
    {
        $camp = (new Camp)->forceFill([
            'title' => 'Welscamp Riba-Roja',
            'slug' => 'welscamp-riba-roja',
            'city' => "Riba-Roja d'Ebre",
            'country' => app()->getLocale() === 'de' ? 'Spanien' : 'Spain',
        ]);

        $booking = (new CampVacationBooking)->forceFill([
            'preferred_date' => now()->addMonth()->startOfWeek()->toDateString(),
            'nights' => 3,
            'number_of_persons' => 2,
            'estimated_total' => 525,
            'price_breakdown' => [
                ['type' => 'accommodation', 'id' => 0, 'name' => 'Bungalow', 'quantity' => 3, 'unit_price' => 55.0, 'amount' => 165.0],
                ['type' => 'boat', 'id' => 0, 'name' => 'Boat', 'quantity' => 3, 'unit_price' => 60.0, 'amount' => 180.0],
                ['type' => 'tour', 'id' => 0, 'name' => 'Wels', 'quantity' => 1, 'unit_price' => 180.0, 'amount' => 180.0],
            ],
            'first_name' => 'Lena',
            'name' => 'Lena Schmidt',
            'language' => app()->getLocale(),
        ]);

        return new self($booking, $camp, app()->getLocale() === 'de'
            ? 'Wir sind zwei erfahrene Welsangler und bringen eigenes Tackle mit.'
            : 'We are two experienced catfish anglers and bring our own tackle.');
    }

    public function build()
    {
        $data = $this->viewData();

        $mail = $this->view('mails.guest.camp_checkout_request')
            ->subject(__('emails.camp_checkout_guest.subject', ['camp' => $data['campTitle']]))
            ->with($data);

        if ($data['contactEmail'] !== '') {
            $mail->replyTo($data['contactEmail']);
        }

        return $mail;
    }

    /**
     * Variables for mails.guest.camp_checkout_request in the current locale (also used by the
     * admin email preview).
     *
     * @return array<string, mixed>
     */
    public function viewData(): array
    {
        $presenter = app(CampQuotePresenter::class);
        $booking = $this->booking;

        return [
            'campTitle' => (string) $this->camp->title,
            'campLocation' => implode(' · ', array_map(Str::ucfirst(...), array_filter([(string) $this->camp->city, (string) $this->camp->country]))),
            'campUrl' => route('vacations.camps.show', $this->camp->slug),
            'firstName' => $booking->first_name ?: $booking->name,
            'arrival' => $this->arrival($booking->preferred_date),
            'nights' => (int) $booking->nights,
            'persons' => (int) $booking->number_of_persons,
            'lines' => $presenter->lines($booking->price_breakdown ?? []),
            'total' => $presenter->total($booking->estimated_total !== null ? (float) $booking->estimated_total : null),
            'guestNote' => trim($this->guestMessage),
            'contactEmail' => (string) config('mail.admin_email'),
            'contactPhone' => config('cag.contact_num') ? '+49 (0) '.config('cag.contact_num') : null,
            'site' => preg_replace('/^www\./', '', (string) parse_url(url('/'), PHP_URL_HOST)),
        ];
    }

    /**
     * "Mo., 12.10.2026" / "Mon, Oct 12, 2026".
     */
    private function arrival(?Carbon $date): ?string
    {
        if ($date === null) {
            return null;
        }

        $locale = app()->getLocale();

        return $date->copy()->locale($locale)->translatedFormat($locale === 'de' ? 'D, d.m.Y' : 'D, M j, Y');
    }
}
