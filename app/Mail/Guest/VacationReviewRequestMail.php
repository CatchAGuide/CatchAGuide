<?php

namespace App\Mail\Guest;

use App\Models\CampVacationBooking;
use App\Models\TripBooking;
use App\Services\Sales\SalesLinks;
use App\Services\Vacation\VacationReviewService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Asks a camp or trip guest to review their stay. Sent by an admin from the camp/trip bookings
 * list (VacationReviewService::sendRequest), in the guest's language.
 */
class VacationReviewRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    // Properties for email logging
    public $type = 'guest_vacation_review_request';
    public $language;
    public $target;

    public function __construct(
        public CampVacationBooking|TripBooking $booking,
        public string $reviewUrl,
    ) {
        $this->language = $booking->customerLocale();
        $this->target = ($booking instanceof TripBooking ? 'trip_booking_' : 'camp_vacation_booking_').$booking->id;
    }

    /**
     * Unsaved sample camp booking for the admin email preview.
     */
    public static function sample(): self
    {
        $booking = (new CampVacationBooking)->forceFill([
            'source_type' => CampVacationBooking::SOURCE_CAMP,
            'source_id' => 0,
            'preferred_date' => now()->subWeeks(2)->startOfWeek()->toDateString(),
            'nights' => 5,
            'number_of_persons' => 2,
            'first_name' => 'Lena',
            'name' => 'Lena Schmidt',
            'language' => app()->getLocale(),
        ]);

        return new self($booking, url('/ratings/camp/sample-token'));
    }

    public function build()
    {
        $data = $this->viewData();

        $mail = $this->view('mails.guest.vacation_review_request')
            ->subject(__('emails.vacation_review_request.subject', ['listing' => $data['listingTitle']]))
            ->with($data);

        if ($data['contactEmail'] !== '') {
            $mail->replyTo($data['contactEmail']);
        }

        return $mail;
    }

    /**
     * Variables for mails.guest.vacation_review_request in the current locale (also used by
     * the admin email preview).
     *
     * @return array<string, mixed>
     */
    public function viewData(): array
    {
        $summary = app(VacationReviewService::class)->summary($this->booking);
        $title = $summary['title'] !== '' ? $summary['title'] : __('emails.vacation_review_request.fallback_title');
        $shell = app(SalesLinks::class)->shell($this->language ?: app()->getLocale());

        return [
            'firstName' => $this->booking->first_name ?: $this->booking->name,
            'listingTitle' => $title,
            'listingLocation' => $summary['location'],
            'dates' => $summary['dates'],
            'persons' => $summary['persons'],
            'reviewUrl' => $this->reviewUrl,
            'contactEmail' => (string) config('mail.admin_email'),
            'site' => $shell['site'],
            'homeUrl' => $shell['homeUrl'],
            'imprintUrl' => $shell['imprintUrl'],
            'privacyUrl' => $shell['privacyUrl'],
        ];
    }
}
