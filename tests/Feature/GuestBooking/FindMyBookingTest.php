<?php

namespace Tests\Feature\GuestBooking;

use App\Enums\GuideStatus;
use App\Events\BookingStatusChanged;
use App\Http\Livewire\Customer\GuestBookings;
use App\Mail\Guest\GuestFindBookingsMail;
use App\Models\Booking;
use App\Models\BookingAccessEvent;
use App\Models\CalendarSchedule;
use App\Models\CampVacationBooking;
use App\Models\FishingType;
use App\Models\GuestLoginLink;
use App\Models\Guiding;
use App\Models\User;
use App\Models\UserGuest;
use App\Models\VoucherOrder;
use App\Services\GuestBooking\GuestBookingEmailSession;
use App\Services\GuestBooking\GuestLoginLinkService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Guest booking access, Phase 2: "Find my booking". Email form, single-use sign-in link, the
 * 7-day sign-in and the list of every booking under that email.
 */
class FindMyBookingTest extends TestCase
{
    use DatabaseTransactions;

    private string $email;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost', 'sales_documents.language_domains' => false]);
        URL::forceRootUrl('http://localhost');
        Event::fake([BookingStatusChanged::class]);
        $this->email = 'find-'.uniqid().'@example.com';
    }

    private function tour(): Guiding
    {
        $guide = User::factory()->create(['firstname' => 'Lars', 'is_guide' => 1, 'guide_status' => GuideStatus::VERIFIED, 'language' => 'en', 'phone' => '1701234567', 'phone_country_code' => '+49']);
        $guiding = new Guiding;
        $guiding->forceFill([
            'title' => 'Pike by kayak '.uniqid(), 'slug' => 'pike-'.uniqid(), 'location' => 'Kiel', 'status' => 1,
            'max_guests' => 4, 'duration' => 6, 'price' => 300, 'price_type' => 'per_tour', 'meeting_point' => 'Pier 7',
            'fishing_type_id' => FishingType::query()->value('id'), 'user_id' => $guide->id,
        ])->save();

        return $guiding;
    }

    private function guest(string $email, array $extra = []): UserGuest
    {
        $guest = new UserGuest;
        $guest->forceFill(array_merge(['salutation' => 'Frau', 'title' => '', 'firstname' => 'Mia', 'lastname' => 'Berg', 'address' => 'Hafenstr. 1', 'postal' => '20457', 'city' => 'Hamburg', 'country' => 'DE', 'phone' => '1705551234', 'phone_country_code' => '+49', 'email' => $email, 'language' => 'en'], $extra))->save();

        return $guest;
    }

    private function booking(Guiding $tour, int $userId, bool $isGuest, ?string $bookingEmail, string $status = 'accepted'): Booking
    {
        $date = now()->addDays(12)->toDateString();
        $schedule = CalendarSchedule::create(['type' => 'tour_request', 'date' => $date, 'guiding_id' => $tour->id, 'user_id' => $tour->user_id]);
        $booking = new Booking;
        $booking->forceFill([
            'guiding_id' => $tour->id, 'blocked_event_id' => $schedule->id, 'status' => $status, 'token' => 'fb-'.uniqid(),
            'is_guest' => $isGuest, 'user_id' => $userId, 'email' => $bookingEmail, 'count_of_users' => 2,
            'price' => 300, 'cag_percent' => 30, 'book_date' => $date, 'expires_at' => now()->addDays(2), 'language' => 'en',
        ])->save();

        return $booking->fresh();
    }

    private function guestTour(?string $email = null): Booking
    {
        $email ??= $this->email;

        return $this->booking($this->tour(), $this->guest($email)->id, true, $email);
    }

    /** Requests a link and returns its plain token (taken from the queued mail). */
    private function requestLink(): string
    {
        Mail::fake();
        $this->post(route('guest-bookings.send'), ['email' => strtoupper($this->email)])->assertRedirect(route('guest-bookings.find'));

        $url = null;
        Mail::assertQueued(GuestFindBookingsMail::class, function (GuestFindBookingsMail $mail) use (&$url) {
            $url = $mail->url;

            return $mail->hasTo($this->email);
        });

        return substr((string) parse_url($url, PHP_URL_PATH), -48);
    }

    /** Full sign-in: email link → confirm page → button. Returns the used link row. */
    private function signIn(): GuestLoginLink
    {
        $token = $this->requestLink();
        $this->get(route('guest-bookings.enter', ['token' => $token]))->assertStatus(303);
        $this->post(route('guest-bookings.start'))->assertRedirect(route('guest-bookings.list'));

        return GuestLoginLink::query()->where('token_hash', hash('sha256', $token))->sole();
    }

    private function withSignIn(GuestLoginLink $link): static
    {
        return $this->withCookie(GuestBookingEmailSession::COOKIE, (string) $link->id);
    }

    public function test_the_answer_never_tells_whether_an_email_has_bookings(): void
    {
        Mail::fake();

        $this->post(route('guest-bookings.send'), ['email' => 'nobody-'.uniqid().'@example.com'])
            ->assertRedirect(route('guest-bookings.find'))->assertSessionHas('find_booking_sent', true);
        Mail::assertNothingQueued();
        $this->get(route('guest-bookings.find'))->assertOk()->assertSee(__('guest_bookings.find.sent'));

        $this->guestTour();
        $this->post(route('guest-bookings.send'), ['email' => '  '.strtoupper($this->email).' '])
            ->assertRedirect(route('guest-bookings.find'))->assertSessionHas('find_booking_sent', true);
        Mail::assertQueued(GuestFindBookingsMail::class, fn ($mail) => $mail->hasTo($this->email));

        $link = GuestLoginLink::query()->where('email', $this->email)->sole();
        $this->assertTrue($link->expires_at->between(now()->addMinutes(29), now()->addMinutes(31)));
        $this->assertSame(1, BookingAccessEvent::query()->where('email', $this->email)->where('event', 'login_sent')->count());
    }

    public function test_the_email_link_keeps_its_token_out_of_the_url_and_only_the_button_uses_it(): void
    {
        $booking = $this->guestTour();
        $token = $this->requestLink();

        $response = $this->get(route('guest-bookings.enter', ['token' => $token]))
            ->assertStatus(303)->assertHeader('Referrer-Policy', 'no-referrer');
        $this->assertStringNotContainsString($token, (string) $response->headers->get('Location'));

        // Opening the page (or a mail scanner following the link) doesn't spend it.
        $this->get(route('guest-bookings.confirm'))->assertOk()->assertSee(__('guest_bookings.find.confirm_button'));
        $this->assertNull(GuestLoginLink::query()->where('token_hash', hash('sha256', $token))->value('used_at'));

        $this->post(route('guest-bookings.start'))
            ->assertRedirect(route('guest-bookings.list'))
            ->assertCookie(GuestBookingEmailSession::COOKIE);

        $link = GuestLoginLink::query()->where('token_hash', hash('sha256', $token))->sole();
        $this->assertNotNull($link->used_at);
        $this->assertTrue($link->session_expires_at->between(now()->addDays(7)->subMinute(), now()->addDays(7)->addMinute()));

        $this->withSignIn($link)->get(route('guest-bookings.list'))
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertSee($booking->guiding->title)
            ->assertSee(__('guest_bookings.find.sign_out'))
            ->assertDontSee(__('homepage.header-profile'));

        // Single use: the same link can't start a second sign-in.
        $this->post(route('guest-bookings.start'))->assertStatus(410)->assertSee(__('guest_bookings.find.link_expired_title'));
    }

    public function test_an_expired_link_asks_for_a_new_one(): void
    {
        $this->guestTour();
        $token = $this->requestLink();
        GuestLoginLink::query()->where('token_hash', hash('sha256', $token))->update(['expires_at' => now()->subMinute()]);

        $this->get(route('guest-bookings.enter', ['token' => $token]));
        $this->get(route('guest-bookings.confirm'))->assertStatus(410)->assertSee(__('guest_bookings.find.new_link'));
        $this->post(route('guest-bookings.start'))->assertStatus(410);
    }

    public function test_the_list_shows_every_booking_under_the_email_and_nothing_else(): void
    {
        $guestTour = $this->guestTour();

        // Q9: a registered user with the same email sees their account bookings here too.
        $account = User::factory()->create(['email' => strtoupper($this->email)]);
        $accountTour = $this->booking($this->tour(), $account->id, false, null);

        $camp = CampVacationBooking::create(['source_type' => 'camp', 'source_id' => 0, 'name' => 'Mia Berg', 'phone' => '1705551234', 'phone_country_code' => '+49', 'message' => 'Camp for two', 'email' => $this->email, 'number_of_persons' => 2, 'status' => 'open', 'preferred_date' => now()->addMonth()->toDateString(), 'nights' => 3]);
        $voucherTour = $this->tour();
        $voucher = new VoucherOrder;
        $voucher->forceFill(['kind' => 'order', 'status' => 'open', 'guiding_id' => $voucherTour->id, 'guide_id' => $voucherTour->user_id, 'number_of_guests' => 2, 'price_total' => 300, 'buyer_email' => $this->email, 'buyer_first_name' => 'Mia', 'buyer_phone' => '1705551234', 'language' => 'en', 'voucher_code' => 'FB-'.uniqid(), 'tour_title_snapshot' => $voucherTour->title])->save();

        // Someone else's booking, and a voucher where this email is only the recipient (Q5).
        $other = $this->guestTour('other-'.uniqid().'@example.com');

        $link = $this->signIn();

        $this->withSignIn($link)->get(route('guest-bookings.list'))
            ->assertOk()
            ->assertSee($guestTour->guiding->title)
            ->assertSee($accountTour->guiding->title)
            ->assertDontSee($other->guiding->title);
        $this->withSignIn($link)->get(route('guest-bookings.list', ['tab' => 'camps']))->assertOk()->assertSee('Camp for two');
        $this->withSignIn($link)->get(route('guest-bookings.list', ['tab' => 'vouchers']))->assertOk()->assertSee($voucherTour->title);
    }

    public function test_a_guest_id_equal_to_a_user_id_never_mixes_bookings(): void
    {
        // bookings.user_id is a user_guests id on guest rows and a users id otherwise.
        $id = max((int) User::query()->max('id'), (int) UserGuest::query()->max('id')) + 1000;
        $stranger = User::factory()->create(['id' => $id, 'email' => 'stranger-'.uniqid().'@example.com']);
        $guest = $this->guest($this->email, ['id' => $id]);

        $mine = $this->booking($this->tour(), $guest->id, true, null);
        $theirs = $this->booking($this->tour(), $stranger->id, false, null);

        $link = $this->signIn();

        $this->withSignIn($link)->get(route('guest-bookings.list'))
            ->assertOk()
            ->assertSee($mine->guiding->title)
            ->assertDontSee($theirs->guiding->title);
    }

    public function test_switching_tabs_keeps_the_email_scope_and_needs_the_sign_in(): void
    {
        $this->guestTour();
        CampVacationBooking::create(['source_type' => 'camp', 'source_id' => 0, 'name' => 'Mia Berg', 'phone' => '1705551234', 'phone_country_code' => '+49', 'message' => 'Lake camp please', 'email' => $this->email, 'number_of_persons' => 2, 'status' => 'open', 'preferred_date' => now()->addMonth()->toDateString(), 'nights' => 3]);
        CampVacationBooking::create(['source_type' => 'camp', 'source_id' => 0, 'name' => 'Ola', 'phone' => '1705551234', 'phone_country_code' => '+49', 'message' => 'Someone elses camp', 'email' => 'other-'.uniqid().'@example.com', 'number_of_persons' => 2, 'status' => 'open', 'preferred_date' => now()->addMonth()->toDateString(), 'nights' => 3]);
        $link = $this->signIn();

        // Livewire's own update requests carry the cookie, never the email.
        Livewire::withCookies([GuestBookingEmailSession::COOKIE => (string) $link->id])
            ->test(GuestBookings::class)
            ->call('setTab', 'camps')
            ->assertSee('Lake camp please')
            ->assertDontSee('Someone elses camp')
            ->assertSee(__('guest_bookings.find.open_booking'));

        $link->forceFill(['signed_out_at' => now()])->save();
        Livewire::withCookies([GuestBookingEmailSession::COOKIE => (string) $link->id])
            ->test(GuestBookings::class)
            ->assertForbidden();
    }

    public function test_rate_limits_per_email_and_per_ip(): void
    {
        $this->guestTour();
        Mail::fake();

        for ($i = 0; $i < 4; $i++) {
            $this->post(route('guest-bookings.send'), ['email' => $this->email])->assertSessionHas('find_booking_sent', true);
        }
        // The fourth request answers the same, but sends nothing.
        Mail::assertQueuedCount(3);

        for ($i = 0; $i < 6; $i++) {
            $this->post(route('guest-bookings.send'), ['email' => 'x'.$i.'-'.uniqid().'@example.com']);
        }
        $this->post(route('guest-bookings.send'), ['email' => $this->email])
            ->assertSessionHasErrors(['email' => __('guest_bookings.find.throttled')]);
    }

    public function test_sign_out_ends_the_sign_in_for_good(): void
    {
        $this->guestTour();
        $link = $this->signIn();

        $this->withSignIn($link)->post(route('guest-bookings.sign-out'))
            ->assertRedirect(route('guest-bookings.find'));
        $this->assertNotNull($link->fresh()->signed_out_at);

        // The old cookie no longer counts.
        $this->withSignIn($link)->get(route('guest-bookings.list'))->assertRedirect(route('guest-bookings.find'));
    }

    public function test_the_list_needs_a_sign_in_and_it_lasts_7_days(): void
    {
        $this->get(route('guest-bookings.list'))->assertRedirect(route('guest-bookings.find'));

        $this->guestTour();
        $link = $this->signIn();
        $link->forceFill(['session_expires_at' => now()->subMinute()])->save();

        $this->withSignIn($link)->get(route('guest-bookings.list'))->assertRedirect(route('guest-bookings.find'));
    }

    public function test_a_card_opens_its_own_booking_page_only_for_the_signed_in_email(): void
    {
        $mine = $this->guestTour();
        $other = $this->guestTour('other-'.uniqid().'@example.com');
        $link = $this->signIn();

        $page = $this->withSignIn($link)->get(route('guest-bookings.open', ['type' => 'tour', 'id' => $mine->id]))
            ->assertRedirect()->headers->get('Location');
        $this->assertStringContainsString('/your-booking/', $page);
        $this->get($page)->assertOk()->assertSee($mine->guiding->title);

        $this->withSignIn($link)->get(route('guest-bookings.open', ['type' => 'tour', 'id' => $other->id]))->assertNotFound();
    }

    public function test_the_sign_in_mail_carries_the_button(): void
    {
        $html = (new GuestFindBookingsMail('http://localhost/my-bookings/sign-in/'.str_repeat('a', 48), 'en'))->render();

        $this->assertStringContainsString(__('guest_bookings.find.mail.button'), $html);
        $this->assertStringContainsString('/my-bookings/sign-in/'.str_repeat('a', 48), $html);
        $this->assertStringContainsString(__('guest_bookings.find.mail.hint'), $html);
    }

    public function test_a_used_up_or_unknown_token_finds_nothing(): void
    {
        $service = app(GuestLoginLinkService::class);

        $this->assertNull($service->findUsable('short'));
        $this->assertNull($service->findUsable(str_repeat('z', 48)));
    }
}
