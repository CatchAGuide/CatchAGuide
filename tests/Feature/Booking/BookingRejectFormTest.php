<?php

namespace Tests\Feature\Booking;

use App\Enums\GuideStatus;
use App\Events\BookingStatusChanged;
use App\Models\BlockedEvent;
use App\Models\Booking;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class BookingRejectFormTest extends TestCase
{
    use DatabaseTransactions;

    private const MESSAGE = 'Leider bin ich an dem Tag schon ausgebucht, die anderen Termine passen aber super.';

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

    private function pendingBooking(array $overrides = [], array $tour = []): Booking
    {
        $guide = User::factory()->create(['language' => 'de', 'is_guide' => 1, 'guide_status' => GuideStatus::VERIFIED]);

        $guiding = new Guiding();
        $guiding->forceFill(array_merge([
            'title' => 'Reject Tour '.uniqid(),
            'slug' => 'reject-tour-'.uniqid(),
            'location' => 'Speyer',
            'status' => 1,
            'max_guests' => 4,
            'duration' => 4,
            'price' => 300,
            'price_type' => 'per_tour',
            'fishing_type_id' => FishingType::query()->value('id'),
            'user_id' => $guide->id,
        ], $tour))->save();

        $event = new BlockedEvent();
        $event->forceFill(['from' => now(), 'due' => now()->addHours(4), 'type' => 'booking', 'user_id' => $guide->id])->save();

        $booking = new Booking();
        $booking->forceFill(array_merge([
            'guiding_id' => $guiding->id,
            'blocked_event_id' => $event->id,
            'status' => 'pending',
            'token' => 'reject-token-'.uniqid(),
            'is_guest' => true,
            'user_id' => 999999,
            'email' => 'guest@example.com',
            'count_of_users' => 2,
            'price' => 300,
            'book_date' => now()->addDays(8)->toDateString(),
        ], $overrides))->save();

        return $booking;
    }

    private function dates(int ...$daysAhead): array
    {
        return array_map(fn (int $days) => now()->addDays($days)->toDateString(), $daysAhead);
    }

    private function reject(Booking $booking, array $payload)
    {
        return $this->postJson(route('booking.rejection', $booking->token), $payload);
    }

    public function test_reject_form_renders_the_checkout_style_page_privately(): void
    {
        $booking = $this->pendingBooking();

        $response = $this->get(route('booking.reject', $booking->token))
            ->assertOk()
            ->assertHeader('Referrer-Policy', 'no-referrer')
            ->assertSee('x-data="bookingReject"', false)
            ->assertSee(__('checkout.reject.title'))
            ->assertSee($booking->guiding->title);

        preg_match('#id="booking-reject-config">(.*?)</script>#s', $response->getContent(), $match);
        $config = json_decode($match[1], true);

        $this->assertSame(5, $config['maxDates']);
        $this->assertSame(50, $config['minMessage']);
        $this->assertSame(route('booking.rejection', $booking->token), $config['submitUrl']);
        $requested = substr((string) $booking->book_date, 0, 10);
        $this->assertNotEmpty(array_filter($config['blocked'], fn ($r) => $requested >= $r['from'] && $requested <= $r['due']),
            'the requested day is blocked as an alternative');
    }

    public function test_decline_saves_sorted_dates_and_message_and_notifies_once(): void
    {
        Event::fake([BookingStatusChanged::class]);
        $booking = $this->pendingBooking();
        $dates = $this->dates(20, 12, 15);

        $this->reject($booking, ['alternative_dates' => $dates, 'reason' => '  '.self::MESSAGE.'  '])
            ->assertOk()
            ->assertJson(['success' => true, 'redirect_url' => route('booking.rejectsuccess')]);

        // A second click is a no-op: no second status change or email.
        $this->reject($booking, ['alternative_dates' => $dates, 'reason' => self::MESSAGE])->assertOk();

        $booking->refresh();
        $this->assertSame('rejected', $booking->status);
        $this->assertSame(self::MESSAGE, $booking->additional_information);
        $this->assertSame($this->dates(12, 15, 20), json_decode($booking->alternative_dates, true));
        Event::assertDispatchedTimes(BookingStatusChanged::class, 1);
    }

    public function test_message_must_have_fifty_characters(): void
    {
        $booking = $this->pendingBooking();

        $this->reject($booking, ['alternative_dates' => $this->dates(12), 'reason' => 'Zu kurz.'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['reason']);

        $this->assertSame('pending', $booking->fresh()->status);
    }

    public function test_between_one_and_five_future_distinct_dates(): void
    {
        $booking = $this->pendingBooking();

        $this->reject($booking, ['alternative_dates' => [], 'reason' => self::MESSAGE])
            ->assertStatus(422)->assertJsonValidationErrors(['alternative_dates']);

        $this->reject($booking, ['alternative_dates' => $this->dates(10, 11, 12, 13, 14, 15), 'reason' => self::MESSAGE])
            ->assertStatus(422)->assertJsonValidationErrors(['alternative_dates']);

        $this->reject($booking, ['alternative_dates' => [now()->subDay()->toDateString()], 'reason' => self::MESSAGE])
            ->assertStatus(422)->assertJsonValidationErrors(['alternative_dates.0']);

        $this->reject($booking, ['alternative_dates' => ['not-a-date'], 'reason' => self::MESSAGE])
            ->assertStatus(422)->assertJsonValidationErrors(['alternative_dates.0']);

        $this->assertSame('pending', $booking->fresh()->status);
    }

    public function test_the_requested_date_and_blocked_dates_cannot_be_offered(): void
    {
        $booking = $this->pendingBooking([], ['allowed_booking_advance' => 'one_week']);

        $this->reject($booking, ['alternative_dates' => [substr((string) $booking->book_date, 0, 10)], 'reason' => self::MESSAGE])
            ->assertStatus(422)->assertJsonValidationErrors(['alternative_dates']);

        // Inside the guide's one-week advance-booking block.
        $this->reject($booking, ['alternative_dates' => $this->dates(3), 'reason' => self::MESSAGE])
            ->assertStatus(422)->assertJsonValidationErrors(['alternative_dates']);
    }

    public function test_older_form_posting_a_json_string_still_works(): void
    {
        $booking = $this->pendingBooking();

        $this->post(route('booking.rejection', $booking->token), [
            'alternative_dates' => json_encode($this->dates(14)),
            'reason' => self::MESSAGE,
        ])->assertRedirect(route('booking.rejectsuccess'));

        $this->assertSame('rejected', $booking->fresh()->status);
    }

    public function test_answered_request_shows_status_page_instead_of_form(): void
    {
        $booking = $this->pendingBooking(['status' => 'accepted']);

        $this->get(route('booking.reject', $booking->token))
            ->assertOk()
            ->assertViewIs('pages.additional.mail_redirection.status')
            ->assertDontSee('x-data="bookingReject"', false);
    }

    public function test_already_rejected_request_uses_the_success_page(): void
    {
        $booking = $this->pendingBooking(['status' => 'rejected']);

        $this->get(route('booking.reject', $booking->token))
            ->assertRedirect(route('booking.rejectsuccess'));
    }

    public function test_guide_profile_reject_uses_the_same_form(): void
    {
        $booking = $this->pendingBooking();

        $this->actingAs($booking->guiding->user)
            ->get(route('profile.guidebookings.reject', $booking))
            ->assertOk()
            ->assertSee('x-data="bookingReject"', false);
    }

    public function test_success_page_uses_the_new_design(): void
    {
        $this->get(route('booking.rejectsuccess'))
            ->assertOk()
            ->assertSee(__('checkout.reject.success.title'));
    }
}
