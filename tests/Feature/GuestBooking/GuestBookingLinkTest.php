<?php

namespace Tests\Feature\GuestBooking;

use App\Enums\GuideStatus;
use App\Events\BookingStatusChanged;
use App\Mail\Guest\BookingAcceptMail;
use App\Mail\Guest\CampCheckoutGuestMail;
use App\Mail\Guest\GuestBookingLinkMail;
use App\Mail\Guest\VoucherOrderGuestMail;
use App\Models\Booking;
use App\Models\BookingAccessEvent;
use App\Models\BookingAccessToken;
use App\Models\CalendarSchedule;
use App\Models\Camp;
use App\Models\CampVacationBooking;
use App\Models\Employee;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\TripBooking;
use App\Models\User;
use App\Models\UserGuest;
use App\Models\VoucherOrder;
use App\Presenters\Booking\GuestBookingPresenter;
use App\Services\GuestBooking\BookingAccessTokenService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Guest booking access, Phase 1: the per-booking link /b/{token}, its page, the button in the
 * guest emails and the admin "Send booking link".
 */
class GuestBookingLinkTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost', 'sales_documents.language_domains' => false]);
        URL::forceRootUrl('http://localhost');
        Event::fake([BookingStatusChanged::class]);
    }

    private function tokens(): BookingAccessTokenService
    {
        return app(BookingAccessTokenService::class);
    }

    private function guide(): User
    {
        return User::factory()->create([
            'firstname' => 'Lars', 'lastname' => 'Fisk', 'is_guide' => 1, 'guide_status' => GuideStatus::VERIFIED,
            'language' => 'en', 'phone' => '1701234567', 'phone_country_code' => '+49',
        ])->fresh();
    }

    private function tour(?User $guide = null): Guiding
    {
        $guide ??= $this->guide();
        $guiding = new Guiding;
        $guiding->forceFill([
            'title' => 'Zander by boat '.uniqid(), 'slug' => 'zander-'.uniqid(), 'location' => 'Hamburg', 'status' => 1,
            'max_guests' => 4, 'duration' => 6, 'price' => 400, 'price_type' => 'per_tour', 'meeting_point' => 'Harbour gate 3',
            'fishing_type_id' => FishingType::query()->value('id'), 'user_id' => $guide->id,
        ])->save();

        return $guiding;
    }

    /** A guest (no account) tour booking: user_id points at user_guests. */
    private function guestBooking(string $status, string $date, array $extra = [], ?Guiding $tour = null): Booking
    {
        $tour ??= $this->tour();
        $guest = UserGuest::create(['salutation' => 'Frau', 'title' => '', 'firstname' => 'Mia', 'lastname' => 'Berg', 'address' => 'Hafenstr. 1', 'postal' => '20457', 'city' => 'Hamburg', 'country' => 'DE', 'phone' => '1705551234', 'phone_country_code' => '+49', 'email' => 'mia.guest@example.com', 'language' => 'en']);
        $schedule = CalendarSchedule::create(['type' => 'tour_request', 'date' => $date, 'guiding_id' => $tour->id, 'user_id' => $tour->user_id]);
        $booking = new Booking;
        $booking->forceFill(array_merge([
            'guiding_id' => $tour->id, 'blocked_event_id' => $schedule->id, 'status' => $status, 'token' => 'gb-'.uniqid(),
            'is_guest' => true, 'user_id' => $guest->id, 'email' => 'Mia.Guest@Example.com', 'count_of_users' => 2,
            'price' => 400, 'cag_percent' => 40, 'book_date' => $date, 'expires_at' => now()->addDays(2), 'language' => 'en',
        ], $extra))->save();

        return $booking->fresh();
    }

    /** Opens the email link and returns the page URL it redirects to. */
    private function open(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        $response = $this->get($path)->assertStatus(303)->assertHeader('Referrer-Policy', 'no-referrer');

        return $response->headers->get('Location');
    }

    public function test_a_link_is_made_once_and_reused_by_every_email(): void
    {
        $booking = $this->guestBooking('pending', now()->addDays(10)->toDateString());

        $first = $this->tokens()->urlFor($booking);
        $second = $this->tokens()->urlFor($booking->fresh());

        $this->assertNotNull($first);
        $this->assertSame($first, $second);
        $this->assertMatchesRegularExpression('#/b/[A-Za-z0-9]{48}$#', $first);

        $row = BookingAccessToken::query()->where('bookable_type', 'tour')->where('bookable_id', $booking->id)->sole();
        // Only the hash and an encrypted copy are stored, never the token itself.
        $token = substr($first, -48);
        $this->assertSame(hash('sha256', $token), $row->token_hash);
        $this->assertStringNotContainsString($token, $row->token_encrypted);
        $this->assertSame('mia.guest@example.com', $row->email);
        $this->assertSame(1, BookingAccessEvent::query()->where('booking_access_token_id', $row->id)->where('event', 'created')->count());
    }

    public function test_the_link_opens_the_booking_without_the_token_in_the_page_url(): void
    {
        $booking = $this->guestBooking('accepted', now()->addDays(10)->toDateString());

        $page = $this->open($this->tokens()->urlFor($booking));

        $this->assertStringNotContainsString('/b/', $page);
        $this->get($page)
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertSee($booking->guiding->title)
            ->assertSee('#'.$booking->id)
            // Confirmed and still to come: the guest needs the guide's phone and meeting point.
            ->assertSee('Harbour gate 3')
            ->assertSee('+49 1701234567')
            ->assertSee(__('guest_bookings.add_to_calendar'));

        // Opening it again in the same browser isn't logged twice.
        $this->get($page)->assertOk();
        $this->assertSame(1, BookingAccessEvent::query()->where('bookable_type', 'tour')->where('bookable_id', $booking->id)->where('event', 'opened')->count());
    }

    public function test_the_page_needs_the_email_link_in_this_browser(): void
    {
        $booking = $this->guestBooking('accepted', now()->addDays(10)->toDateString());
        $this->tokens()->urlFor($booking);
        $access = $this->tokens()->active($booking);

        // Guessing the sequential page URL without having opened the link shows nothing.
        $this->get(route('guest-booking.show', ['access' => $access->id]))
            ->assertForbidden()
            ->assertDontSee($booking->guiding->title)
            ->assertSee(__('guest_bookings.unavailable.title'));

        $this->get('/b/'.str_repeat('a', 48))->assertNotFound();
    }

    public function test_contact_details_hide_after_the_tour_and_the_view_is_reduced_after_90_days(): void
    {
        $recent = $this->guestBooking('accepted', now()->subDays(3)->toDateString(), ['additional_information' => 'See you']);
        $this->get($this->open($this->tokens()->urlFor($recent)))
            ->assertOk()
            ->assertDontSee('Harbour gate 3')
            ->assertDontSee('+49 1701234567')
            ->assertSee(__('guest_bookings.contact_hidden'))
            ->assertDontSee(__('guest_bookings.redacted_note'))
            ->assertDontSee(__('guest_bookings.add_to_calendar'));

        $old = $this->guestBooking('accepted', now()->subDays(120)->toDateString());
        $this->get($this->open($this->tokens()->urlFor($old)))
            ->assertOk()
            ->assertSee($old->guiding->title)
            ->assertSee(__('guest_bookings.redacted_note'))
            ->assertDontSee('Harbour gate 3');
    }

    public function test_pending_requests_keep_the_guides_contact_hidden(): void
    {
        $booking = $this->guestBooking('pending', now()->addDays(10)->toDateString());

        $this->get($this->open($this->tokens()->urlFor($booking)))
            ->assertOk()
            ->assertDontSee('+49 1701234567')
            ->assertDontSee('Harbour gate 3');
    }

    public function test_a_turned_off_link_stops_working_and_the_next_email_gets_a_new_one(): void
    {
        $booking = $this->guestBooking('accepted', now()->addDays(10)->toDateString());
        $old = $this->tokens()->urlFor($booking);
        $page = $this->open($old);

        $this->assertTrue($this->tokens()->revoke($booking, null));

        $this->get(parse_url($old, PHP_URL_PATH))->assertNotFound();
        // Even a session that opened it before loses the page.
        $this->get($page)->assertForbidden();

        $new = $this->tokens()->urlFor($booking->fresh());
        $this->assertNotSame($old, $new);
        $this->get($this->open($new))->assertOk();
    }

    public function test_the_calendar_file_has_the_tour_day(): void
    {
        $date = now()->addDays(10);
        $booking = $this->guestBooking('accepted', $date->toDateString());
        $page = $this->open($this->tokens()->urlFor($booking));
        $access = $this->tokens()->active($booking);

        $response = $this->get(route('guest-booking.calendar', ['access' => $access->id]))->assertOk();

        $this->assertStringContainsString('text/calendar', $response->headers->get('Content-Type'));
        $body = $response->getContent();
        $this->assertStringContainsString('DTSTART;VALUE=DATE:'.$date->format('Ymd'), $body);
        $this->assertStringContainsString('DTEND;VALUE=DATE:'.$date->copy()->addDay()->format('Ymd'), $body);
        $this->assertStringContainsString('BEGIN:VEVENT', $body);
        $this->assertNotEmpty($page);
    }

    public function test_camp_trip_and_voucher_pages(): void
    {
        $camp = CampVacationBooking::create(['source_type' => 'camp', 'source_id' => 0, 'name' => 'Mia Berg', 'phone' => '1705551234', 'phone_country_code' => '+49', 'message' => 'Two anglers', 'email' => 'mia.guest@example.com', 'number_of_persons' => 3, 'status' => 'open', 'preferred_date' => now()->addMonth()->toDateString(), 'nights' => 4]);
        $trip = TripBooking::create(['source_type' => 'trip', 'source_id' => 0, 'name' => 'Mia Berg', 'phone' => '1705551234', 'phone_country_code' => '+49', 'message' => 'Hello', 'email' => 'mia.guest@example.com', 'number_of_persons' => 2, 'status' => 'in_process', 'preferred_date' => now()->addMonths(2)->toDateString()]);
        $tour = $this->tour();
        $voucher = new VoucherOrder;
        $voucher->forceFill(['kind' => 'order', 'status' => 'open', 'guiding_id' => $tour->id, 'guide_id' => $tour->user_id, 'number_of_guests' => 2, 'price_total' => 400, 'buyer_email' => 'mia.guest@example.com', 'buyer_first_name' => 'Mia', 'buyer_phone' => '1705551234', 'language' => 'en', 'voucher_code' => 'GB-'.uniqid(), 'tour_title_snapshot' => $tour->title])->save();

        $this->get($this->open($this->tokens()->urlFor($camp)))->assertOk()->assertSee(__('guide_area.customer.status.request_open'))->assertSee('Two anglers');
        $this->get($this->open($this->tokens()->urlFor($trip)))->assertOk()->assertSee(__('guide_area.customer.status.request_in_process'));
        $this->get($this->open($this->tokens()->urlFor($voucher)))->assertOk()->assertSee($tour->title)->assertDontSee(__('guest_bookings.download_voucher'));

        // The voucher PDF is the guide's to send after payment: never before.
        $access = $this->tokens()->active($voucher);
        $this->get(route('guest-booking.voucher-pdf', ['access' => $access->id]))->assertNotFound();
    }

    public function test_bookings_without_an_email_or_unsaved_samples_get_no_link(): void
    {
        $this->assertNull($this->tokens()->urlFor(new CampVacationBooking(['email' => 'x@example.com'])));
        $camp = CampVacationBooking::create(['source_type' => 'camp', 'source_id' => 0, 'name' => 'No Mail', 'phone' => '1', 'phone_country_code' => '+49', 'message' => '', 'email' => '', 'number_of_persons' => 1, 'status' => 'open']);
        $this->assertNull($this->tokens()->urlFor($camp));
    }

    public function test_guest_emails_carry_the_view_your_booking_button(): void
    {
        $booking = $this->guestBooking('accepted', now()->addDays(10)->toDateString());
        $html = (new BookingAcceptMail($booking))->locale('en')->render();
        $this->assertStringContainsString(__('guest_bookings.mail.button', [], 'en'), $html);
        $this->assertStringContainsString($this->tokens()->urlFor($booking), $html);

        $camp = CampVacationBooking::create(['source_type' => 'camp', 'source_id' => 0, 'name' => 'Mia Berg', 'first_name' => 'Mia', 'phone' => '1', 'phone_country_code' => '+49', 'message' => '', 'email' => 'mia.guest@example.com', 'number_of_persons' => 2, 'status' => 'open', 'preferred_date' => now()->addMonth()->toDateString(), 'nights' => 3, 'language' => 'de']);
        $campModel = (new Camp)->forceFill(['title' => 'Welscamp', 'slug' => 'welscamp-'.uniqid(), 'city' => 'Mequinenza', 'country' => 'Spain']);
        $html = (new CampCheckoutGuestMail($camp, $campModel))->locale('de')->render();
        $this->assertStringContainsString(__('guest_bookings.mail.button', [], 'de'), $html);
        $this->assertStringContainsString('/b/', $html);

        // Admin previews render unsaved samples: no link, no error.
        $this->assertStringNotContainsString('/b/', VoucherOrderGuestMail::sample()->render());
    }

    public function test_admin_sends_the_link_to_the_bookings_own_email_and_can_rotate_it(): void
    {
        Mail::fake();
        $employee = Employee::query()->first() ?? $this->markTestSkipped('No admin employee in this database.');
        $booking = $this->guestBooking('pending', now()->addDays(10)->toDateString());

        $this->actingAs($employee, 'employees')
            ->post(route('admin.booking-links.send', ['type' => 'tour', 'id' => $booking->id]))
            ->assertRedirect()
            ->assertSessionHas('success');

        Mail::assertQueued(GuestBookingLinkMail::class, fn ($mail) => $mail->hasTo('mia.guest@example.com'));
        $first = $this->tokens()->active($booking);
        $this->assertSame(1, BookingAccessEvent::query()->where('booking_access_token_id', $first->id)->where('event', 'sent')->where('employee_id', $employee->id)->count());

        $this->actingAs($employee, 'employees')
            ->post(route('admin.booking-links.revoke', ['type' => 'tour', 'id' => $booking->id]))
            ->assertSessionHas('success');

        $this->assertNotNull($first->fresh()->revoked_at);
        $this->assertNotSame($first->id, $this->tokens()->active($booking)->id);
        Mail::assertQueued(GuestBookingLinkMail::class, 2);
    }

    public function test_admin_link_routes_need_an_admin(): void
    {
        $booking = $this->guestBooking('pending', now()->addDays(10)->toDateString());

        $this->post(route('admin.booking-links.send', ['type' => 'tour', 'id' => $booking->id]))->assertRedirect();
        $this->assertNull($this->tokens()->active($booking));
    }

    public function test_reduced_view_starts_90_days_after_the_last_day(): void
    {
        $presenter = app(GuestBookingPresenter::class);
        $camp = new CampVacationBooking(['preferred_date' => '2026-01-01', 'nights' => 5]);

        $this->assertFalse($presenter->isRedacted($camp, now()->setDate(2026, 4, 6)));
        $this->assertTrue($presenter->isRedacted($camp, now()->setDate(2026, 4, 8)));
    }
}
