<?php

namespace App\Services\Checkout\Camp;

use App\Mail\VacationBookingAdminMail;
use App\Mail\VacationBookingCustomerMail;
use App\Models\Camp;
use App\Models\CampVacationBooking;
use App\Models\User;
use App\Presenters\Vacation\CampQuotePresenter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Turns a validated camp checkout into a camp request (CampVacationBooking, handled in the
 * admin's camp request list) with the server-side estimate, and notifies guest and admin with
 * the same mails the camp contact form uses.
 */
class CampBookingSubmissionService
{
    public function __construct(
        private readonly CampQuotePresenter $presenter,
    ) {}

    /**
     * @param  array{first_name: string, last_name: string, email: string, country_code: string, phone: string}  $contact
     */
    public function submit(
        Camp $camp,
        CampCheckoutQuote $quote,
        string $arrivalDate,
        array $contact,
        string $guestMessage,
        ?User $currentUser,
        string $locale,
    ): CampVacationBooking {
        $total = $quote->total();
        $summary = $this->presenter->summary($arrivalDate, $quote->nights, $quote->persons, $quote->lines, $total, $guestMessage);

        $booking = CampVacationBooking::create([
            'source_type' => CampVacationBooking::SOURCE_CAMP,
            'source_id' => $camp->id,
            'preferred_date' => $arrivalDate,
            'nights' => $quote->nights,
            'number_of_persons' => $quote->persons,
            'accommodation_id' => $quote->lineId('accommodation'),
            'rental_boat_id' => $quote->lineId('boat'),
            'guiding_id' => $quote->lineId('tour'),
            'special_offer_id' => $quote->lineId('special'),
            'estimated_total' => $total > 0 ? $total : null,
            'currency' => $quote->currency,
            'price_breakdown' => $quote->lines,
            'name' => trim($contact['first_name'].' '.$contact['last_name']),
            'first_name' => $contact['first_name'],
            'last_name' => $contact['last_name'],
            'email' => $contact['email'],
            'user_id' => $currentUser?->getKey(),
            'language' => $locale,
            'phone_country_code' => $contact['country_code'],
            'phone' => $contact['phone'],
            'message' => $summary,
            'status' => CampVacationBooking::STATUS_OPEN,
        ]);

        if (! app()->environment('local')) {
            $this->notify($booking, $camp, $summary);
        }

        return $booking;
    }

    private function notify(CampVacationBooking $booking, Camp $camp, string $summary): void
    {
        $extra = [
            'contact_message' => $summary,
            'preferred_date' => $booking->preferred_date?->toDateString(),
            'number_of_persons' => $booking->number_of_persons,
            'source_type' => CampVacationBooking::SOURCE_CAMP,
            'source_id' => $camp->id,
            'camp_id' => $camp->id,
            'source_title' => $camp->title,
            'view_requests_url' => route('admin.camp-vacation-bookings.index'),
        ];

        $mails = [
            [$booking->email, new VacationBookingCustomerMail($booking->name, $booking->email, $summary, $booking->phone, $booking->phone_country_code, $extra)],
            [config('mail.admin_email'), new VacationBookingAdminMail($booking->name, $booking->email, $summary, $booking->phone, $booking->phone_country_code, $extra)],
        ];

        foreach ($mails as [$recipient, $mail]) {
            try {
                Mail::to($recipient)->send($mail);
            } catch (Throwable $e) {
                Log::error('Camp checkout mail failed', [
                    'camp_vacation_booking_id' => $booking->id,
                    'mail' => $mail::class,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
