<?php

namespace App\Services;

use App\Jobs\SendCheckoutEmail;
use App\Events\BookingCreated;
use App\Models\Booking;
use App\Models\CalendarSchedule;
use App\Models\Guiding;
use App\Models\User;
use App\Models\UserGuest;
use Carbon\Carbon;
use InvalidArgumentException;

class BookingService
{
    /**
     * Create a guiding booking using the shared domain workflow.
     *
     * This method is intentionally generic so it can be used from:
     * - Livewire checkout
     * - Modern API checkout
     * - Admin/manual booking creation
     *
     * The caller is responsible for resolving the user / guest and
     * computing any frontend-specific payload structures.
     *
     * @param  array               $data
     * @param  Guiding             $guiding
     * @param  User|UserGuest|null $user
     * @param  bool                $isGuest
     * @param  bool                $sendEmails
     * @param  int|null            $createdById   (reserved for admin usage)
     * @param  string              $createdSource (e.g. 'frontend', 'admin', 'system')
     * @return Booking
     */
    public function createGuidingBooking(
        array $data,
        Guiding $guiding,
        $user,
        bool $isGuest,
        bool $sendEmails = true,
        ?int $createdById = null,
        string $createdSource = 'frontend'
    ): Booking {
        $this->assertRequiredKeys($data, [
            'persons',
            'selected_date',
            'total_price',
            'phone_full',
            'phone_country_code',
            'email',
        ]);

        $selectedDate = $data['selected_date'];
        $selectedTime = $data['selected_time'] ?? '00:00';
        $persons = (int) $data['persons'];
        $totalPrice = (float) $data['total_price'];
        $totalExtraPrice = (float) ($data['total_extra_price'] ?? 0);
        $extrasSerialized = $data['extras_serialized'] ?? null;
        $locale = $data['language'] ?? app()->getLocale();

        $eventService = app(EventService::class);
        $helperService = app(HelperService::class);

        // Create blocked event for this booking
        $blockedEvent = $eventService->createBlockedEvent(
            $selectedTime,
            $selectedDate,
            $guiding,
            'tour_request',
            $user
        );

        // Fee is calculated on the guiding base price; allow caller to override
        $feeBase = (float) ($data['guiding_price_for_fee'] ?? $totalPrice);
        $fee = $helperService->calculateRates($feeBase);

        // Expiration time can be provided or derived using the existing rule
        $expiresAt = $data['expires_at'] ?? $this->calculateExpirationTime($selectedDate);

        $bookingAttributes = [
            'user_id' => $user ? $user->id : null,
            'is_guest' => $isGuest,
            'guiding_id' => $guiding->id,
            'blocked_event_id' => $blockedEvent->id,
            'is_paid' => false,
            'extras' => $extrasSerialized,
            'total_extra_price' => $totalExtraPrice,
            'count_of_users' => $persons,
            'price' => $totalPrice,
            'cag_percent' => $fee,
            'status' => $data['status'] ?? 'pending',
            'book_date' => $selectedDate,
            'expires_at' => $expiresAt,
            'phone' => $data['phone_full'],
            'phone_country_code' => $data['phone_country_code'],
            'language' => $locale,
            'email' => $data['email'],
            'token' => $this->generateBookingToken($blockedEvent->id),
        ];

        if (isset($data['parent_id'])) {
            $bookingAttributes['parent_id'] = $data['parent_id'];
        }

        if ($createdById !== null) {
            $bookingAttributes['created_by_id'] = $createdById;
        }

        if (!empty($createdSource)) {
            $bookingAttributes['created_source'] = $createdSource;
        }

        $booking = Booking::create($bookingAttributes);

        // Link to calendar schedule (new calendar system)
        $calendarSchedule = CalendarSchedule::find($blockedEvent->id);
        if ($calendarSchedule) {
            $calendarSchedule->booking_id = $booking->id;
            $calendarSchedule->save();
        }

        if ($sendEmails && !app()->environment('local')) {
            SendCheckoutEmail::dispatch($booking, $user, $guiding, $guiding->user);
        }

        event(new BookingCreated($booking, $sendEmails, $createdSource));

        return $booking;
    }

    /**
     * Reschedule a guiding booking by creating a new Booking instance
     * linked back to the original.
     *
     * @param  Booking             $original
     * @param  array               $data
     * @param  bool                $sendEmails
     * @param  int|null            $createdById
     * @param  string              $createdSource
     * @return Booking
     */
    public function rescheduleGuidingBooking(
        Booking $original,
        array $data,
        bool $sendEmails = true,
        ?int $createdById = null,
        string $createdSource = 'frontend'
    ): Booking {
        $this->assertRequiredKeys($data, [
            'selected_date',
            'total_price',
            'count_of_users',
        ]);

        $guiding = $original->guiding;
        $user = $original->user;

        $selectedDate = $data['selected_date'];
        $persons = (int) $data['count_of_users'];
        $totalPrice = (float) $data['total_price'];
        $extrasSerialized = $data['extras_serialized'] ?? null;

        $eventService = app(EventService::class);
        $helperService = app(HelperService::class);

        $blockedEvent = $eventService->createBlockedEvent(
            '00:00',
            $selectedDate,
            $guiding,
            'tour_request',
            $user
        );

        $fee = $helperService->calculateRates($totalPrice);
        $expiresAt = $data['expires_at'] ?? $this->calculateExpirationTime($selectedDate);

        $totalExtraPrice = $data['total_extra_price'] ?? $this->calculateTotalExtraPrice($extrasSerialized);

        $bookingAttributes = [
            'user_id' => $original->user_id,
            'is_guest' => $original->is_guest,
            'guiding_id' => $guiding->id,
            'blocked_event_id' => $blockedEvent->id,
            'is_paid' => false,
            'extras' => $extrasSerialized,
            'total_extra_price' => $totalExtraPrice,
            'count_of_users' => $persons,
            'price' => $totalPrice,
            'cag_percent' => $fee,
            'status' => 'pending',
            'book_date' => $selectedDate,
            'expires_at' => $expiresAt,
            'phone' => $original->phone,
            'email' => $original->email,
            'token' => $this->generateBookingToken($blockedEvent->id),
            'parent_id' => $original->id,
        ];

        $newBooking = Booking::create($bookingAttributes);

        $calendarSchedule = CalendarSchedule::find($blockedEvent->id);
        if ($calendarSchedule) {
            $calendarSchedule->booking_id = $newBooking->id;
            $calendarSchedule->save();
        }

        if ($sendEmails && !app()->environment('local')) {
            SendCheckoutEmail::dispatch($newBooking, $user, $guiding, $guiding->user);
        }

        event(new BookingCreated($newBooking, $sendEmails, $createdSource));

        return $newBooking;
    }

    private function assertRequiredKeys(array $data, array $keys): void
    {
        $missing = [];
        foreach ($keys as $key) {
            if (!array_key_exists($key, $data)) {
                $missing[] = $key;
            }
        }

        if (!empty($missing)) {
            throw new InvalidArgumentException('Missing required booking data keys: ' . implode(', ', $missing));
        }
    }

    private function calculateExpirationTime(string $selectedDate): Carbon
    {
        $expiresAt = Carbon::now()->addHours(24);

        $dateDifference = Carbon::parse($selectedDate)->diffInDays(Carbon::now());

        if ($dateDifference > 3) {
            $expiresAt = Carbon::now()->addHours(48);
        }

        return $expiresAt;
    }

    private function calculateTotalExtraPrice(?string $serializedExtras): float
    {
        if (!$serializedExtras) {
            return 0.0;
        }

        $extras = @unserialize($serializedExtras);
        if (!is_array($extras)) {
            return 0.0;
        }

        $total = 0.0;
        foreach ($extras as $extra) {
            if (isset($extra['extra_total_price'])) {
                $total += (float) $extra['extra_total_price'];
            }
        }

        return $total;
    }

    private function generateBookingToken($eventId): string
    {
        $timestamp = time();
        $combinedString = $eventId . '-' . $timestamp;

        return hash('sha256', $combinedString);
    }
}

