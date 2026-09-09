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

class BookingAcceptSecurityTest extends TestCase
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

    private function createBooking(string $status, array $overrides = []): Booking
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

        $booking = new Booking();
        $booking->forceFill(array_merge([
            'guiding_id' => $guiding->id,
            'blocked_event_id' => $blockedEvent->id,
            'status' => $status,
            'token' => 'test-token-'.uniqid(),
            'is_guest' => true,
            'email' => 'guest@example.com',
            'count_of_users' => 1,
            'price' => 100,
        ], $overrides))->save();

        return $booking;
    }

    public function test_a_pending_booking_can_still_be_accepted(): void
    {
        $booking = $this->createBooking('pending');

        $response = $this->get(route('booking.accept', $booking->token));

        $response->assertOk();
        $this->assertSame('accepted', $booking->fresh()->status);
    }

    public function test_an_already_rejected_booking_cannot_be_flipped_back_to_accepted_via_a_suffixed_token(): void
    {
        $booking = $this->createBooking('rejected');

        // Before the fix, appending `|anything` to the token skipped the already-processed
        // guard entirely and unconditionally set status=accepted. The token is no longer
        // parsed for a suffix at all, so `{token}|1` simply doesn't match any stored token.
        $response = $this->get(route('booking.accept', $booking->token).'|1');

        $response->assertNotFound();
        $this->assertSame('rejected', $booking->fresh()->status, 'status must not have been flipped back to accepted');
    }

    public function test_an_already_accepted_booking_shows_the_status_page_instead_of_reprocessing(): void
    {
        $booking = $this->createBooking('accepted');

        $response = $this->get(route('booking.accept', $booking->token));

        $response->assertOk();
        $response->assertViewIs('pages.additional.mail_redirection.status');
    }

    public function test_an_unknown_token_returns_404(): void
    {
        $response = $this->get(route('booking.accept', 'this-token-does-not-exist'));

        $response->assertNotFound();
    }
}
