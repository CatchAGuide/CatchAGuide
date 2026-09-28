<?php

namespace Tests\Feature\Checkout;

use App\Enums\GuideStatus;
use App\Models\Booking;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\User;
use App\Services\Checkout\BookingConfirmationAccess;
use App\Services\Checkout\TourBookingSubmissionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class BookingConfirmationAccessTest extends TestCase
{
    use DatabaseTransactions;

    private const SESSION_KEY = 'checkout.confirmed_booking_ids';

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');

        $this->withoutMiddleware([
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \App\Http\Middleware\DDoSProtectionMiddleware::class,
        ]);
    }

    private function booking(array $overrides = []): Booking
    {
        $guide = User::factory()->create(['language' => 'en', 'is_guide' => 1, 'guide_status' => GuideStatus::VERIFIED]);

        $guiding = new Guiding();
        $guiding->forceFill([
            'title' => 'Confirmation Tour '.uniqid(),
            'slug' => 'confirmation-tour-'.uniqid(),
            'location' => 'Speyer',
            'status' => 1,
            'max_guests' => 3,
            'duration' => 4,
            'price' => 300,
            'price_type' => 'per_tour',
            'fishing_type_id' => FishingType::query()->value('id'),
            'user_id' => $guide->id,
        ])->save();

        $booking = new Booking();
        $booking->forceFill(array_merge([
            'guiding_id' => $guiding->id,
            'status' => 'pending',
            'token' => 'token-'.uniqid(),
            'is_guest' => true,
            'user_id' => 999999,
            'email' => 'guest@example.com',
            'count_of_users' => 2,
            'price' => 300,
            'book_date' => now()->addDays(10)->toDateString(),
        ], $overrides))->save();

        return $booking;
    }

    public static function confirmationRoutes(): array
    {
        return [
            'tour checkout' => ['checkout.thank-you'],
            'legacy checkout' => ['thank-you'],
        ];
    }

    /**
     * @dataProvider confirmationRoutes
     */
    public function test_a_guessed_booking_id_is_not_found(string $route): void
    {
        $booking = $this->booking();

        $this->get(route($route, [$booking->id]))->assertNotFound();
    }

    /**
     * @dataProvider confirmationRoutes
     */
    public function test_the_session_that_booked_can_see_its_confirmation(string $route): void
    {
        $booking = $this->booking();

        $this->withSession([self::SESSION_KEY => [$booking->id]])
            ->get(route($route, [$booking->id]))
            ->assertOk();
    }

    public function test_a_signed_in_customer_can_see_their_own_booking_but_not_others(): void
    {
        $customer = User::factory()->create();
        $own = $this->booking(['is_guest' => false, 'user_id' => $customer->id]);
        $other = $this->booking(['is_guest' => false, 'user_id' => User::factory()->create()->id]);

        $this->actingAs($customer)->get(route('checkout.thank-you', [$own->id]))->assertOk();
        $this->actingAs($customer)->get(route('checkout.thank-you', [$other->id]))->assertNotFound();
    }

    public function test_a_guest_booking_is_not_opened_by_a_user_with_the_same_numeric_id(): void
    {
        $customer = User::factory()->create();
        $booking = $this->booking(['is_guest' => true, 'user_id' => $customer->id]);

        $this->actingAs($customer)->get(route('checkout.thank-you', [$booking->id]))->assertNotFound();
    }

    public function test_missing_and_non_numeric_ids_are_not_found(): void
    {
        $this->get('/checkout/thank-you/999999999')->assertNotFound();
        $this->get('/checkout/thank-you/abc')->assertNotFound();
    }

    public function test_submitting_the_checkout_grants_access_to_its_confirmation(): void
    {
        $booking = $this->booking();
        $guiding = $booking->guiding;

        $this->mock(TourBookingSubmissionService::class)
            ->shouldReceive('submit')->once()->andReturn($booking);

        $this->postJson(route('checkout.store'), [
            'guiding_id' => $guiding->id,
            'persons' => 2,
            'selected_date' => now()->addDays(20)->toDateString(),
            'first_name' => 'Jonas',
            'last_name' => 'Keller',
            'email' => 'jonas@example.com',
            'country_code' => '+49',
            'phone' => '151 23456789',
        ])->assertOk()->assertSessionHas(self::SESSION_KEY, [$booking->id]);

        $this->get(route('checkout.thank-you', [$booking->id]))->assertOk();
    }

    public function test_only_the_most_recent_bookings_are_remembered(): void
    {
        $access = app(BookingConfirmationAccess::class);

        foreach (range(1, 12) as $id) {
            $booking = new Booking();
            $booking->id = $id;
            $access->grant($booking);
        }

        $this->assertSame(range(3, 12), session(self::SESSION_KEY));
    }
}
