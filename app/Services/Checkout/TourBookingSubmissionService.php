<?php

namespace App\Services\Checkout;

use App\Models\Booking;
use App\Models\Guiding;
use App\Models\User;
use App\Models\UserGuest;
use App\Services\BookingService;

/**
 * Turns a validated tour checkout into a booking request: resolves the booker (the signed-in
 * user or a guest record keyed by email) and hands a server-side quote to BookingService.
 */
class TourBookingSubmissionService
{
    public function __construct(
        private readonly BookingService $bookings,
    ) {}

    /**
     * @param  array{first_name: string, last_name: string, email: string, country_code: string, phone: string}  $contact
     */
    public function submit(
        Guiding $guiding,
        TourCheckoutQuote $quote,
        string $selectedDate,
        array $contact,
        ?User $currentUser,
        string $locale,
    ): Booking {
        $booker = $currentUser
            ? $this->updateSignedInUser($currentUser, $contact)
            : $this->resolveGuest($contact, $locale);

        return $this->bookings->createGuidingBooking(
            [
                'persons' => $quote->persons,
                'selected_date' => $selectedDate,
                'total_price' => $quote->total(),
                'total_extra_price' => $quote->extrasTotal(),
                'extras_serialized' => $quote->serializedExtras(),
                'phone_full' => $contact['country_code'].' '.$contact['phone'],
                'phone_country_code' => $contact['country_code'],
                'email' => $contact['email'],
                'language' => $locale,
                'guiding_price_for_fee' => $quote->basePrice,
            ],
            $guiding,
            $booker,
            $booker instanceof UserGuest,
            sendEmails: ! app()->environment('local'),
        );
    }

    /**
     * @param  array{country_code: string, phone: string}  $contact
     */
    private function updateSignedInUser(User $user, array $contact): User
    {
        if ($user->hasInactiveGuideFlag()) {
            $user->phone = $contact['phone'];
            $user->phone_country_code = $contact['country_code'];
            $user->save();
        }

        if ($user->information) {
            $user->information->phone = $contact['phone'];
            $user->information->phone_country_code = $contact['country_code'];
            $user->information->save();
        }

        return $user;
    }

    /**
     * @param  array{first_name: string, last_name: string, email: string, country_code: string, phone: string}  $contact
     */
    private function resolveGuest(array $contact, string $locale): UserGuest
    {
        $guest = UserGuest::where('email', $contact['email'])->first();

        if ($guest === null) {
            return UserGuest::create([
                'salutation' => 'male',
                'title' => '',
                'firstname' => $contact['first_name'],
                'lastname' => $contact['last_name'],
                'address' => '',
                'postal' => '',
                'city' => '',
                'country' => 'Deutschland',
                'phone' => $contact['phone'],
                'phone_country_code' => $contact['country_code'],
                'email' => $contact['email'],
                'language' => $locale,
            ]);
        }

        $guest->fill([
            'firstname' => $contact['first_name'],
            'lastname' => $contact['last_name'],
            'phone' => $contact['phone'],
            'phone_country_code' => $contact['country_code'],
            'language' => $locale,
        ])->save();

        return $guest;
    }
}
