<?php

namespace Tests\Feature\Booking;

use App\Enums\GuideStatus;
use App\Models\BlockedEvent;
use App\Models\Booking;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\User;
use App\Models\UserGuest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class BookingRescheduleSecurityTest extends TestCase
{
    use DatabaseTransactions;

    private const SESSION_KEY = 'booking_reschedule';

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

    private function createRejectedBooking(array $overrides = [], array $tour = []): Booking
    {
        $guide = User::factory()->create(['language' => 'en', 'is_guide' => 1, 'guide_status' => GuideStatus::VERIFIED]);

        $guiding = new Guiding();
        $guiding->forceFill(array_merge([
            'title' => 'Test Tour '.uniqid(),
            'slug' => 'test-tour-'.uniqid(),
            'location' => 'Somewhere',
            'status' => 1,
            'max_guests' => 4,
            'duration' => 4,
            'price' => 150,
            'price_type' => 'per_tour',
            'pricing_extra' => json_encode([['name' => 'Lunch', 'price' => 20]]),
            'fishing_type_id' => FishingType::query()->value('id'),
            'user_id' => $guide->id,
        ], $tour))->save();

        $blockedEvent = new BlockedEvent();
        $blockedEvent->forceFill([
            'from' => now(),
            'due' => now()->addHours(4),
            'type' => 'booking',
            'user_id' => $guide->id,
        ])->save();

        $guest = UserGuest::create([
            'salutation' => 'male', 'title' => '', 'firstname' => 'Jonas', 'lastname' => 'Keller',
            'address' => '', 'postal' => '', 'city' => '', 'country' => 'Deutschland',
            'phone' => '15123456789', 'phone_country_code' => '+49',
            'email' => 'jonas.keller@example.com', 'language' => 'en',
        ]);

        $booking = new Booking();
        $booking->forceFill(array_merge([
            'guiding_id' => $guiding->id,
            'blocked_event_id' => $blockedEvent->id,
            'status' => 'rejected',
            'is_rescheduled' => false,
            'token' => 'test-token-'.uniqid(),
            'alternative_dates' => json_encode([
                now()->addDays(10)->toDateString(),
                now()->addDays(12)->toDateString(),
            ]),
            'additional_information' => 'River is flooded that weekend.',
            'is_guest' => true,
            'user_id' => $guest->id,
            'email' => 'jonas.keller@example.com',
            'phone' => '+49 15123456789',
            'count_of_users' => 2,
            'price' => 150,
            'book_date' => now()->addDays(5)->toDateString(),
        ], $overrides))->save();

        return $booking;
    }

    private function alternative(Booking $booking, int $index = 0): string
    {
        return json_decode($booking->alternative_dates, true)[$index];
    }

    private function inSession(Booking $booking): self
    {
        return $this->withSession([self::SESSION_KEY => ['token' => $booking->token, 'date' => null]]);
    }

    // Opening the emailed link

    public function test_emailed_link_moves_the_token_into_the_session_and_off_the_url(): void
    {
        $booking = $this->createRejectedBooking();
        $date = $this->alternative($booking, 1);

        $this->get(route('booking.reschedule', ['token' => $booking->token]).'?date='.$date)
            ->assertRedirect(route('booking.reschedule.show'))
            ->assertStatus(303)
            ->assertHeader('Referrer-Policy', 'no-referrer')
            ->assertSessionHas(self::SESSION_KEY, ['token' => $booking->token, 'date' => $date]);
    }

    public function test_reschedule_page_preselects_the_emailed_date_and_shows_editable_contact_details(): void
    {
        $booking = $this->createRejectedBooking();
        $date = $this->alternative($booking, 1);

        $response = $this->withSession([self::SESSION_KEY => ['token' => $booking->token, 'date' => $date]])
            ->get(route('booking.reschedule.show'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertSee('River is flooded that weekend.')
            ->assertSee('name="first_name"', false)
            ->assertSee('name="last_name"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="phone"', false)
            ->assertDontSee('Jonas K.')
            ->assertDontSee('j•')
            ->assertDontSee(__('checkout.reschedule.contact_note'))
            ->assertDontSee($booking->token);

        preg_match('#<script type="application/json" id="tour-checkout-config">(.*?)</script>#s', $response->getContent(), $match);
        $config = json_decode($match[1], true);

        $this->assertSame($date, $config['selectedDate']);
        $this->assertSame([$this->alternative($booking, 0), $date], $config['allowedDates']);
        $this->assertTrue($config['reschedule']);
        $this->assertSame([
            'firstName' => 'Jonas',
            'lastName' => 'Keller',
            'email' => 'jonas.keller@example.com',
            'countryCode' => '+49',
            'phone' => '15123456789',
        ], $config['contact']);
        $this->assertSame(2, $config['persons']);
        $this->assertSame(route('booking.reschedule.store'), $config['submitUrl']);
    }

    public function test_a_date_that_was_not_suggested_falls_back_to_the_first_suggestion(): void
    {
        $booking = $this->createRejectedBooking();

        $response = $this->withSession([self::SESSION_KEY => ['token' => $booking->token, 'date' => now()->addDays(40)->toDateString()]])
            ->get(route('booking.reschedule.show'))
            ->assertOk();

        preg_match('#id="tour-checkout-config">(.*?)</script>#s', $response->getContent(), $match);
        $this->assertSame($this->alternative($booking), json_decode($match[1], true)['selectedDate']);
    }

    // Friendly fallbacks instead of errors

    public function test_unknown_link_shows_a_helpful_page(): void
    {
        $this->get(route('booking.reschedule', ['token' => 'does-not-exist']))
            ->assertRedirect(route('booking.reschedule.show'));

        $this->get(route('booking.reschedule.show'))
            ->assertNotFound()
            ->assertSee(__('checkout.reschedule.states.invalid.title'))
            ->assertSee(route('guidings.index'));
    }

    public function test_used_link_explains_the_request_was_already_sent(): void
    {
        $booking = $this->createRejectedBooking(['is_rescheduled' => true]);

        $this->inSession($booking)->get(route('booking.reschedule.show'))
            ->assertOk()
            ->assertSee(__('checkout.reschedule.states.used.title'));
    }

    public function test_past_suggestions_offer_to_book_the_tour_for_another_date(): void
    {
        $booking = $this->createRejectedBooking(['alternative_dates' => json_encode([now()->subDays(2)->toDateString()])]);

        $this->inSession($booking)->get(route('booking.reschedule.show'))
            ->assertOk()
            ->assertSee(__('checkout.reschedule.states.expired.title'))
            ->assertSee($booking->guiding->publicShowUrl(), false);
    }

    public function test_unpublished_tour_cannot_be_rescheduled(): void
    {
        $booking = $this->createRejectedBooking([], ['status' => 2]);

        $this->inSession($booking)->get(route('booking.reschedule.show'))
            ->assertOk()
            ->assertSee(__('checkout.reschedule.states.unavailable.title'))
            ->assertDontSee($booking->guiding->publicShowUrl(), false);

        $this->inSession($booking)->postJson(route('booking.reschedule.store'), $this->payload($booking))->assertStatus(409);

        $this->assertFalse((bool) $booking->fresh()->is_rescheduled);
    }

    // Submitting

    public function test_store_requires_the_session_from_the_emailed_link(): void
    {
        $booking = $this->createRejectedBooking();

        // A token in the body is ignored: only the session from the link counts.
        $this->postJson(route('booking.reschedule.store'), $this->payload($booking, [
            'token' => $booking->token,
        ]))->assertStatus(409)->assertJson(['success' => false]);

        $this->assertFalse((bool) $booking->fresh()->is_rescheduled);
    }

    public function test_store_rejects_a_booking_that_is_not_rejected(): void
    {
        $booking = $this->createRejectedBooking(['status' => 'pending']);

        $this->inSession($booking)->postJson(route('booking.reschedule.store'), $this->payload($booking))->assertStatus(409);
    }

    public function test_store_rejects_a_date_outside_the_offered_alternatives(): void
    {
        $booking = $this->createRejectedBooking();

        $this->inSession($booking)->postJson(route('booking.reschedule.store'), $this->payload($booking, [
            'selected_date' => now()->addDays(99)->toDateString(),
        ]))->assertStatus(422)->assertJsonValidationErrors(['selected_date']);

        $this->assertFalse((bool) $booking->fresh()->is_rescheduled);
    }

    public function test_store_rejects_too_many_guests_and_unknown_extras(): void
    {
        $booking = $this->createRejectedBooking();

        $this->inSession($booking)->postJson(route('booking.reschedule.store'), $this->payload($booking, [
            'persons' => 9,
            'extras' => [4],
        ]))->assertStatus(422)->assertJsonValidationErrors(['persons']);

        $this->inSession($booking)->postJson(route('booking.reschedule.store'), $this->payload($booking, [
            'persons' => 2,
            'extras' => [4],
        ]))->assertStatus(422)->assertJsonValidationErrors(['extras']);
    }

    public function test_store_ignores_client_submitted_price_and_recomputes_it_server_side(): void
    {
        $booking = $this->createRejectedBooking();
        $date = $this->alternative($booking);

        $response = $this->inSession($booking)->postJson(route('booking.reschedule.store'), $this->payload($booking, [
            'selected_date' => $date,
            'persons' => 2,
            'extras' => [0],
            'total_price' => 0.01, // attacker-supplied — must be ignored
        ]));

        $response->assertOk()->assertJson(['success' => true])->assertSessionMissing(self::SESSION_KEY);

        $this->assertTrue((bool) $booking->fresh()->is_rescheduled);

        $newBooking = Booking::where('parent_id', $booking->id)->first();
        $this->assertNotNull($newBooking);
        $this->assertEquals(190.0, (float) $newBooking->price, '150 tour + lunch 20 × 2, from the guiding record');
        $this->assertSame($date, substr((string) $newBooking->book_date, 0, 10));
        $this->assertSame(route('checkout.thank-you', [$newBooking]), $response->json('redirect_url'));

        // The confirmation page of the new request is open to this session only.
        $this->get($response->json('redirect_url'))->assertOk();
    }

    public function test_a_link_can_only_be_used_once(): void
    {
        $booking = $this->createRejectedBooking();
        $payload = $this->payload($booking);

        $this->inSession($booking)->postJson(route('booking.reschedule.store'), $payload)->assertOk();
        $this->inSession($booking)->postJson(route('booking.reschedule.store'), $payload)
            ->assertStatus(409)
            ->assertJson(['message' => __('checkout.reschedule.errors.used')]);

        $this->assertSame(1, Booking::where('parent_id', $booking->id)->count());
    }

    public function test_store_saves_an_edited_contact_on_the_new_request(): void
    {
        $booking = $this->createRejectedBooking();

        $this->inSession($booking)->postJson(route('booking.reschedule.store'), $this->payload($booking, [
            'persons' => 2,
            'first_name' => 'Mia',
            'last_name' => 'Berg',
            'email' => 'mia.berg@example.com',
            'phone' => '1701234567',
        ]))->assertOk();

        $newBooking = Booking::where('parent_id', $booking->id)->first();
        $guest = UserGuest::where('email', 'mia.berg@example.com')->first();

        $this->assertNotNull($newBooking);
        $this->assertNotNull($guest);
        $this->assertSame($guest->id, $newBooking->user_id);
        $this->assertSame('Mia', $guest->firstname);
        $this->assertSame('Berg', $guest->lastname);
        $this->assertSame('1701234567', $guest->phone);
        $this->assertSame('+49', $guest->phone_country_code);
        $this->assertSame('mia.berg@example.com', $newBooking->email);
        $this->assertSame('+49 1701234567', $newBooking->phone);
        $this->assertSame('+49', $newBooking->phone_country_code);

        $originalGuest = UserGuest::where('email', 'jonas.keller@example.com')->first();
        $this->assertSame('Jonas', $originalGuest->firstname);
        $this->assertSame('Keller', $originalGuest->lastname);
        $this->assertSame('jonas.keller@example.com', $booking->fresh()->email);
    }

    public function test_store_updates_the_same_guest_when_only_the_name_changes(): void
    {
        $booking = $this->createRejectedBooking();

        $this->inSession($booking)->postJson(route('booking.reschedule.store'), $this->payload($booking, [
            'last_name' => 'Berg',
            'phone' => '1701234567',
        ]))->assertOk();

        $guest = UserGuest::find($booking->user_id);
        $newBooking = Booking::where('parent_id', $booking->id)->first();

        $this->assertSame('Jonas', $guest->firstname);
        $this->assertSame('Berg', $guest->lastname);
        $this->assertSame('1701234567', $guest->phone);
        $this->assertSame($booking->user_id, $newBooking->user_id);
        $this->assertSame('+49 1701234567', $newBooking->phone);
    }

    public function test_store_rejects_an_incomplete_contact_and_keeps_the_link(): void
    {
        $booking = $this->createRejectedBooking();

        $this->inSession($booking)->postJson(route('booking.reschedule.store'), $this->payload($booking, [
            'first_name' => '',
            'email' => 'not-an-email',
            'phone' => '12',
        ]))->assertStatus(422)->assertJsonValidationErrors(['first_name', 'email', 'phone']);

        $this->assertFalse((bool) $booking->fresh()->is_rescheduled);
        $this->assertSame(0, Booking::where('parent_id', $booking->id)->count());
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(Booking $booking, array $overrides = []): array
    {
        return array_merge([
            'selected_date' => $this->alternative($booking),
            'persons' => 1,
            'first_name' => 'Jonas',
            'last_name' => 'Keller',
            'email' => 'jonas.keller@example.com',
            'country_code' => '+49',
            'phone' => '15123456789',
        ], $overrides);
    }
}
