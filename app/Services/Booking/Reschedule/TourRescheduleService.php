<?php

namespace App\Services\Booking\Reschedule;

use App\Models\Booking;
use App\Models\User;
use App\Models\UserGuest;
use App\Services\BookingService;
use App\Services\Checkout\TourBookingSubmissionService;
use App\Services\Checkout\TourCheckoutQuote;
use Illuminate\Support\Facades\DB;

/**
 * Sends a declined tour request again for a new date: creates the follow-up booking request
 * and marks the declined one as used, atomically, so a double click or a replayed request
 * can never create two.
 */
class TourRescheduleService
{
    public function __construct(
        private readonly BookingService $bookings,
        private readonly TourBookingSubmissionService $guests,
    ) {}

    /**
     * @param  array{first_name: string, last_name: string, email: string, country_code: string, phone: string}  $contact
     *
     * @throws RescheduleAlreadyUsedException When the link was used in the meantime.
     * @throws \InvalidArgumentException When the date was taken in the meantime.
     */
    public function submit(Booking $original, TourCheckoutQuote $quote, string $selectedDate, array $contact): Booking
    {
        return DB::transaction(function () use ($original, $quote, $selectedDate, $contact) {
            $locked = Booking::whereKey($original->getKey())->lockForUpdate()->first();

            if ($locked === null || $locked->is_rescheduled || $locked->status !== 'rejected') {
                throw new RescheduleAlreadyUsedException();
            }

            $booker = $this->booker($locked, $contact);

            $newBooking = $this->bookings->rescheduleGuidingBooking($locked, [
                'selected_date' => $selectedDate,
                'total_price' => $quote->total(),
                'total_extra_price' => $quote->extrasTotal(),
                'count_of_users' => $quote->persons,
                'extras_serialized' => $quote->serializedExtras(),
                'email' => $contact['email'],
                'phone' => $contact['country_code'].' '.$contact['phone'],
                'phone_country_code' => $contact['country_code'],
                'user_id' => $booker->id,
                'is_guest' => $booker instanceof UserGuest,
            ], sendEmails: ! app()->environment('local'));

            $locked->is_rescheduled = true;
            $locked->save();

            return $newBooking;
        });
    }

    /**
     * The customer the new request is sent as. A guest is matched by the submitted email so a
     * corrected address does not rewrite the declined request. A registered account keeps its
     * login email; the name and phone shown to the guide follow the form.
     *
     * @param  array{first_name: string, last_name: string, email: string, country_code: string, phone: string}  $contact
     */
    private function booker(Booking $original, array $contact): User|UserGuest
    {
        if (! $original->is_guest) {
            $user = $original->registeredUser;
            if ($user !== null) {
                $user->fill([
                    'firstname' => $contact['first_name'],
                    'lastname' => $contact['last_name'],
                    'phone' => $contact['phone'],
                    'phone_country_code' => $contact['country_code'],
                ])->save();

                return $user;
            }
        }

        return $this->guests->syncGuest($contact, (string) ($original->language ?: app()->getLocale()));
    }
}
