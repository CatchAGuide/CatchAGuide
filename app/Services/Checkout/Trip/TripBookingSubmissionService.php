<?php

namespace App\Services\Checkout\Trip;

use App\Mail\VacationBookingAdminMail;
use App\Mail\VacationBookingCustomerMail;
use App\Models\TripBooking;
use App\Models\User;
use App\Presenters\Vacation\TripQuotePresenter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Turns a validated trip checkout into a trip request (TripBooking, handled in the admin's trip
 * request list) with the server-side estimate, and notifies guest and admin with the same mails
 * the trip contact form uses.
 */
class TripBookingSubmissionService
{
    public function __construct(
        private readonly TripQuotePresenter $presenter,
    ) {}

    /**
     * Either $departureDate (a fixed departure) or $wishStart/$wishEnd (a preferred window) is set.
     *
     * @param  array{first_name: string, last_name: string, email: string, country_code: string, phone: string}  $contact
     */
    public function submit(
        TripCheckoutOffer $offer,
        ?string $departureDate,
        ?string $wishStart,
        ?string $wishEnd,
        int $persons,
        array $contact,
        string $guestMessage,
        ?User $currentUser,
        string $locale,
    ): TripBooking {
        $trip = $offer->trip();
        $total = $offer->estimate($persons);
        $summary = $this->presenter->summary(
            $departureDate,
            $offer->departure($departureDate)['end'] ?? null,
            $departureDate === null ? $wishStart : null,
            $departureDate === null ? $wishEnd : null,
            $persons,
            $offer->pricePerPerson(),
            $total,
            $guestMessage,
        );

        $booking = TripBooking::create([
            'source_type' => TripBooking::SOURCE_TRIP,
            'source_id' => $trip->id,
            'preferred_date' => $departureDate ?? $wishStart,
            'preferred_date_to' => $departureDate === null ? $wishEnd : null,
            'number_of_persons' => $persons,
            'estimated_total' => $total,
            'currency' => TripCheckoutOffer::CURRENCY,
            'name' => trim($contact['first_name'].' '.$contact['last_name']),
            'first_name' => $contact['first_name'],
            'last_name' => $contact['last_name'],
            'email' => $contact['email'],
            'user_id' => $currentUser?->getKey(),
            'language' => $locale,
            'phone_country_code' => $contact['country_code'],
            'phone' => $contact['phone'],
            'message' => $summary,
            'status' => TripBooking::STATUS_OPEN,
        ]);

        if (! app()->environment('local')) {
            $this->notify($booking, $summary, (string) $trip->title);
        }

        return $booking;
    }

    private function notify(TripBooking $booking, string $summary, string $tripTitle): void
    {
        $extra = [
            'contact_message' => $summary,
            'preferred_date' => $booking->preferred_date?->toDateString(),
            'number_of_persons' => $booking->number_of_persons,
            'source_type' => TripBooking::SOURCE_TRIP,
            'source_id' => $booking->source_id,
            'source_title' => $tripTitle,
            'view_requests_url' => route('admin.trip-bookings.index'),
        ];

        $mails = [
            [$booking->email, new VacationBookingCustomerMail($booking->name, $booking->email, $summary, $booking->phone, $booking->phone_country_code, $extra)],
            [config('mail.admin_email'), new VacationBookingAdminMail($booking->name, $booking->email, $summary, $booking->phone, $booking->phone_country_code, $extra)],
        ];

        foreach ($mails as [$recipient, $mail]) {
            try {
                Mail::to($recipient)->send($mail);
            } catch (Throwable $e) {
                Log::error('Trip checkout mail failed', [
                    'trip_booking_id' => $booking->id,
                    'mail' => $mail::class,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
