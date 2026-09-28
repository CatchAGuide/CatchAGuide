<?php

namespace Tests\Unit\Checkout;

use App\Models\Booking;
use App\Models\Guiding;
use App\Models\User;
use App\Models\UserGuest;
use App\Services\BookingService;
use App\Services\Checkout\TourBookingSubmissionService;
use App\Services\Checkout\TourCheckoutQuote;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\TestCase;

class TourBookingSubmissionServiceTest extends TestCase
{
    use DatabaseTransactions;

    private function contact(string $email): array
    {
        return [
            'first_name' => 'Jonas',
            'last_name' => 'Keller',
            'email' => $email,
            'country_code' => '+49',
            'phone' => '151 23456789',
        ];
    }

    private function quote(): TourCheckoutQuote
    {
        return new TourCheckoutQuote(2, 898.0, [
            ['index' => 1, 'name' => 'Catering', 'price' => 35.0, 'quantity' => 2, 'total' => 70.0],
        ]);
    }

    public function test_guest_booking_creates_a_guest_and_passes_server_prices(): void
    {
        $email = 'guest-'.uniqid().'@example.com';
        $guiding = new Guiding();

        $bookings = Mockery::mock(BookingService::class);
        $bookings->shouldReceive('createGuidingBooking')
            ->once()
            ->withArgs(function (array $data, Guiding $g, $booker, bool $isGuest) use ($email, $guiding) {
                return $g === $guiding
                    && $isGuest
                    && $booker instanceof UserGuest
                    && $booker->email === $email
                    && $data['total_price'] === 968.0
                    && $data['total_extra_price'] === 70.0
                    && $data['guiding_price_for_fee'] === 898.0
                    && $data['phone_full'] === '+49 151 23456789'
                    && unserialize($data['extras_serialized'])[0]['extra_total_price'] === 70.0;
            })
            ->andReturn(new Booking());

        (new TourBookingSubmissionService($bookings))
            ->submit($guiding, $this->quote(), '2026-11-04', $this->contact($email), null, 'de');

        $this->assertDatabaseHas('user_guests', ['email' => $email, 'firstname' => 'Jonas', 'language' => 'de']);
    }

    public function test_repeat_guest_updates_the_existing_guest_record(): void
    {
        $email = 'guest-'.uniqid().'@example.com';
        $existing = UserGuest::create(array_merge($this->contact($email), [
            'salutation' => 'male', 'title' => '', 'firstname' => 'Old', 'lastname' => 'Name',
            'address' => '', 'postal' => '', 'city' => '', 'country' => 'Deutschland', 'phone_country_code' => '+43', 'language' => 'en',
        ]));

        $bookings = Mockery::mock(BookingService::class);
        $bookings->shouldReceive('createGuidingBooking')
            ->once()
            ->withArgs(fn (array $data, Guiding $g, $booker) => $booker->is($existing))
            ->andReturn(new Booking());

        (new TourBookingSubmissionService($bookings))
            ->submit(new Guiding(), $this->quote(), '2026-11-04', $this->contact($email), null, 'de');

        $existing->refresh();
        $this->assertSame('Jonas', $existing->firstname);
        $this->assertSame('+49', $existing->phone_country_code);
    }

    public function test_signed_in_user_books_as_themselves(): void
    {
        $user = User::factory()->create();

        $bookings = Mockery::mock(BookingService::class);
        $bookings->shouldReceive('createGuidingBooking')
            ->once()
            ->withArgs(fn (array $data, Guiding $g, $booker, bool $isGuest) => $booker === $user && ! $isGuest)
            ->andReturn(new Booking());

        (new TourBookingSubmissionService($bookings))
            ->submit(new Guiding(), $this->quote(), '2026-11-04', $this->contact($user->email), $user, 'en');

        $this->assertDatabaseMissing('user_guests', ['email' => $user->email, 'firstname' => 'Jonas']);
    }
}
