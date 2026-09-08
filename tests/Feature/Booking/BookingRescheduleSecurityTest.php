<?php

namespace Tests\Feature\Booking;

use App\Models\BlockedEvent;
use App\Models\Booking;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class BookingRescheduleSecurityTest extends TestCase
{
    use DatabaseTransactions;

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

    private function createRejectedBooking(array $overrides = []): Booking
    {
        $guide = User::factory()->create(['language' => 'en']);

        $guiding = new Guiding();
        $guiding->forceFill([
            'title' => 'Test Tour '.uniqid(),
            'slug' => 'test-tour-'.uniqid(),
            'location' => 'Somewhere',
            'status' => 1,
            'max_guests' => 4,
            'duration' => 4,
            'price' => 150,
            'price_type' => 'per_tour',
            'fishing_type_id' => FishingType::query()->value('id'),
            'user_id' => $guide->id,
        ])->save();

        $blockedEvent = new BlockedEvent();
        $blockedEvent->forceFill([
            'from' => now(),
            'due' => now()->addHours(4),
            'type' => 'booking',
            'user_id' => $guide->id,
        ])->save();

        $alternativeDate = now()->addDays(10)->toDateString();

        $booking = new Booking();
        $booking->forceFill(array_merge([
            'guiding_id' => $guiding->id,
            'blocked_event_id' => $blockedEvent->id,
            'status' => 'rejected',
            'is_rescheduled' => false,
            'token' => 'test-token-'.uniqid(),
            'alternative_dates' => json_encode([$alternativeDate]),
            'is_guest' => true,
            'email' => 'guest@example.com',
            'count_of_users' => 1,
            'price' => 150,
        ], $overrides))->save();

        return $booking;
    }

    public function test_reschedule_store_requires_a_token(): void
    {
        $booking = $this->createRejectedBooking();
        $alternativeDate = json_decode($booking->alternative_dates, true)[0];

        $response = $this->postJson(route('booking.reschedule.store'), [
            'selectedDate' => $alternativeDate,
            'count_of_users' => 1,
            'terms_accepted' => 1,
            'total_price' => 1, // attacker-supplied bargain price — must be ignored even if accepted
        ]);

        $response->assertStatus(422);
        $this->assertFalse((bool) $booking->fresh()->is_rescheduled);
    }

    public function test_reschedule_store_rejects_an_unknown_token(): void
    {
        $response = $this->postJson(route('booking.reschedule.store'), [
            'token' => 'this-token-does-not-exist',
            'selectedDate' => now()->addDays(10)->toDateString(),
            'count_of_users' => 1,
            'terms_accepted' => 1,
        ]);

        $response->assertStatus(404);
        $response->assertJson(['success' => false]);
    }

    public function test_reschedule_store_rejects_a_booking_that_is_not_rejected(): void
    {
        // Simulates the old vulnerable flow: guessing/enumerating a booking_id (here, a
        // token) for a booking that was never rejected — must not be reschedulable.
        $booking = $this->createRejectedBooking(['status' => 'pending']);
        $alternativeDate = json_decode($booking->alternative_dates, true)[0];

        $response = $this->postJson(route('booking.reschedule.store'), [
            'token' => $booking->token,
            'selectedDate' => $alternativeDate,
            'count_of_users' => 1,
            'terms_accepted' => 1,
        ]);

        $response->assertStatus(404);
    }

    public function test_reschedule_store_rejects_a_date_outside_the_offered_alternatives(): void
    {
        $booking = $this->createRejectedBooking();

        $response = $this->postJson(route('booking.reschedule.store'), [
            'token' => $booking->token,
            'selectedDate' => now()->addDays(999)->toDateString(),
            'count_of_users' => 1,
            'terms_accepted' => 1,
        ]);

        $response->assertStatus(422);
        $this->assertFalse((bool) $booking->fresh()->is_rescheduled);
    }

    public function test_reschedule_store_ignores_client_submitted_price_and_recomputes_it_server_side(): void
    {
        $booking = $this->createRejectedBooking();
        $alternativeDate = json_decode($booking->alternative_dates, true)[0];

        $response = $this->postJson(route('booking.reschedule.store'), [
            'token' => $booking->token,
            'selectedDate' => $alternativeDate,
            'count_of_users' => 1,
            'terms_accepted' => 1,
            'total_price' => 0.01, // attacker-supplied — the guiding's real per-tour price is 150
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $booking->refresh();
        $this->assertTrue((bool) $booking->is_rescheduled);

        $newBooking = Booking::find($response->json('booking_id'));
        $this->assertNotNull($newBooking);
        $this->assertEquals(150.0, (float) $newBooking->price, 'price must come from the guiding record, not the client');
    }
}
