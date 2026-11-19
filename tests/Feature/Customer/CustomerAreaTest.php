<?php

namespace Tests\Feature\Customer;

use App\Enums\GuideStatus;
use App\Events\BookingStatusChanged;
use App\Http\Livewire\Customer\Account;
use App\Http\Livewire\Customer\Bookings;
use App\Http\Livewire\Customer\Favourites;
use App\Models\Booking;
use App\Models\CalendarSchedule;
use App\Models\CampVacationBooking;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\TripBooking;
use App\Models\User;
use App\Models\VoucherOrder;
use App\Models\WishlistItem;
use App\Support\CustomerAreaNav;
use App\Support\SitePrimaryNav;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Customer pages of /profile/* in the Guide Area design: only what a non-guide can use, the
 * same pages inside the guide sidebar for Guide Area guides, and the kill switch.
 */
class CustomerAreaTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost', 'guide_area.enabled' => true, 'guide_area.customer_enabled' => true]);
        URL::forceRootUrl('http://localhost');
        Event::fake([BookingStatusChanged::class]);
    }

    private function customer(array $extra = []): User
    {
        return User::factory()->create(array_merge([
            'firstname' => 'Mia', 'lastname' => 'Berg', 'is_guide' => 0, 'guide_status' => null, 'language' => 'en',
            'password' => Hash::make('old-secret-1'),
        ], $extra))->fresh();
    }

    private function guide(): User
    {
        return User::factory()->create(['firstname' => 'Lars', 'is_guide' => 1, 'guide_status' => GuideStatus::VERIFIED, 'language' => 'en'])->fresh();
    }

    private function tour(User $guide, array $extra = []): Guiding
    {
        $guiding = new Guiding;
        $guiding->forceFill(array_merge([
            'title' => 'Zander by boat '.uniqid(), 'slug' => 'zander-'.uniqid(), 'location' => 'Hamburg', 'status' => 1,
            'max_guests' => 4, 'duration' => 6, 'price' => 400, 'price_type' => 'per_tour', 'meeting_point' => 'Harbour gate 3',
            'fishing_type_id' => FishingType::query()->value('id'), 'user_id' => $guide->id,
        ], $extra))->save();

        return $guiding;
    }

    private function booking(Guiding $tour, User $customer, string $status, string $date, array $extra = []): Booking
    {
        $schedule = CalendarSchedule::create(['type' => 'tour_request', 'date' => $date, 'guiding_id' => $tour->id, 'user_id' => $tour->user_id]);
        $booking = new Booking;
        $booking->forceFill(array_merge([
            'guiding_id' => $tour->id, 'blocked_event_id' => $schedule->id, 'status' => $status, 'token' => 'cust-'.uniqid(),
            'is_guest' => false, 'user_id' => $customer->id, 'email' => $customer->email, 'count_of_users' => 2,
            'price' => 400, 'cag_percent' => 40, 'book_date' => $date, 'expires_at' => now()->addDays(2),
        ], $extra))->save();

        return $booking;
    }

    public function test_customer_pages_render_in_the_new_design_with_only_customer_items(): void
    {
        $customer = $this->customer();
        $this->actingAs($customer);

        foreach (['profile.index', 'profile.bookings', 'profile.favoriteguides', 'profile.settings', 'profile.password'] as $route) {
            $this->get(route($route))
                ->assertOk()
                ->assertSee('ga-shell', false)
                ->assertSee(route('profile.favoriteguides'), false)
                // No guide tools in a customer's sidebar.
                ->assertDontSee(route('guide.area.calendar'), false)
                ->assertDontSee(route('profile.myguidings'), false)
                ->assertDontSee(__('guide_area.nav.invoices'));
        }

        $keys = collect(CustomerAreaNav::sections())->flatMap(fn ($s) => collect($s['items'])->pluck('key'))->all();
        $this->assertSame(['overview', 'bookings', 'favourites', 'details', 'password'], $keys);
    }

    public function test_customer_sidebar_offers_become_a_guide_only_to_people_who_can_apply(): void
    {
        $this->actingAs($this->customer())->get(route('profile.index'))->assertSee(__('guide_area.customer.become_guide.cta'));

        $this->assertNull(CustomerAreaNav::becomeGuideUrl($this->guide()));
    }

    public function test_my_bookings_shows_only_the_users_own_bookings_of_every_type(): void
    {
        $customer = $this->customer();
        $other = $this->customer(['firstname' => 'Other']);
        $tour = $this->tour($this->guide());
        $mine = $this->booking($tour, $customer, 'accepted', now()->addDays(10)->toDateString());
        $notMine = $this->booking($tour, $other, 'accepted', now()->addDays(11)->toDateString());
        // A guest booking whose user_guests id equals this user's id is not theirs.
        $guestCollision = $this->booking($tour, $customer, 'pending', now()->addDays(12)->toDateString(), ['is_guest' => true]);

        CampVacationBooking::create(['source_type' => 'camp', 'source_id' => 0, 'user_id' => $customer->id, 'name' => 'Mia Berg', 'phone' => '1705551234', 'phone_country_code' => '+49', 'message' => 'Hello', 'email' => $customer->email, 'number_of_persons' => 4, 'status' => 'open', 'preferred_date' => now()->addMonth()->toDateString()]);
        TripBooking::create(['source_type' => 'trip', 'source_id' => 0, 'user_id' => $customer->id, 'name' => 'Mia Berg', 'phone' => '1705551234', 'phone_country_code' => '+49', 'message' => 'Hello', 'email' => $customer->email, 'number_of_persons' => 2, 'status' => 'in_process', 'preferred_date' => now()->addMonths(2)->toDateString()]);
        $voucher = new VoucherOrder;
        $voucher->forceFill(['kind' => 'order', 'status' => 'open', 'guiding_id' => $tour->id, 'guide_id' => $tour->user_id, 'user_id' => $customer->id, 'number_of_guests' => 2, 'price_total' => 400, 'buyer_email' => $customer->email, 'buyer_first_name' => 'Mia', 'buyer_phone' => '1705551234', 'language' => 'en', 'voucher_code' => 'CUST-'.uniqid(), 'tour_title_snapshot' => $tour->title])->save();

        $this->actingAs($customer);

        Livewire::test(Bookings::class)
            ->assertSee('#'.$mine->id)
            ->assertDontSee('#'.$notMine->id)
            ->assertDontSee('#'.$guestCollision->id)
            ->assertViewHas('tabCounts', fn (array $c) => $c === ['tours' => 1, 'camps' => 1, 'trips' => 1, 'vouchers' => 1])
            ->call('setTab', 'camps')->assertSee(__('guide_area.customer.status.request_open'))
            ->call('setTab', 'trips')->assertSee(__('guide_area.customer.status.request_in_process'))
            ->call('setTab', 'vouchers')->assertSee($tour->title)->assertSee(__('guide_area.customer.status.voucher_open'));
    }

    public function test_guide_contact_and_meeting_point_appear_only_after_the_guide_confirms(): void
    {
        $customer = $this->customer();
        $guide = $this->guide();
        $guide->forceFill(['phone' => '1701234567', 'phone_country_code' => '+49'])->save();
        $tour = $this->tour($guide);
        $this->booking($tour, $customer, 'pending', now()->addDays(5)->toDateString());

        $this->actingAs($customer);
        Livewire::test(Bookings::class)
            ->assertSee(__('guide_area.customer.status.open'))
            ->assertDontSee($guide->email)
            ->assertDontSee('Harbour gate 3');

        Booking::query()->where('user_id', $customer->id)->update(['status' => 'accepted']);
        Livewire::test(Bookings::class)
            ->assertSee($guide->email)
            ->assertSee('+49 1701234567')
            ->assertSee('Harbour gate 3');
    }

    public function test_declined_request_shows_suggested_dates_and_the_reschedule_link(): void
    {
        $customer = $this->customer();
        $tour = $this->tour($this->guide());
        $booking = $this->booking($tour, $customer, 'rejected', now()->addDays(5)->toDateString(), [
            'alternative_dates' => json_encode([now()->addDays(9)->toDateString()]),
            'additional_information' => 'Sorry, the boat is in the yard that week.',
        ]);

        $this->actingAs($customer);
        Livewire::test(Bookings::class, ['status' => 'closed'])
            ->assertSee(__('guide_area.customer.status.declined'))
            ->assertSee('the boat is in the yard')
            ->assertSee(route('booking.reschedule', ['token' => $booking->token]), false);
    }

    public function test_status_tiles_filter_tour_bookings(): void
    {
        $customer = $this->customer();
        $tour = $this->tour($this->guide());
        $open = $this->booking($tour, $customer, 'pending', now()->addDays(3)->toDateString());
        $done = $this->booking($tour, $customer, 'accepted', now()->subDays(3)->toDateString());

        $this->actingAs($customer);
        Livewire::test(Bookings::class)
            ->assertViewHas('counts', fn (array $c) => $c['all'] === 2 && $c['open'] === 1 && $c['completed'] === 1)
            ->call('setStatus', 'completed')
            ->assertSee('#'.$done->id)
            ->assertDontSee('#'.$open->id);
    }

    public function test_favourites_list_and_remove_only_the_users_own_saved_tours(): void
    {
        $customer = $this->customer();
        $tour = $this->tour($this->guide());
        $hidden = $this->tour($this->guide(), ['status' => 0]);
        WishlistItem::create(['user_id' => $customer->id, 'guiding_id' => $tour->id]);
        WishlistItem::create(['user_id' => $customer->id, 'guiding_id' => $hidden->id]);

        $this->actingAs($customer);
        Livewire::test(Favourites::class)
            ->assertSee($tour->title)
            ->assertDontSee($hidden->title)
            ->call('remove', $tour->id)
            ->assertDispatched('guide-area-toast')
            ->assertDontSee($tour->title);

        $this->assertDatabaseMissing('wishlist_items', ['user_id' => $customer->id, 'guiding_id' => $tour->id]);
    }

    public function test_account_saves_personal_details_and_changes_the_password(): void
    {
        $customer = $this->customer();
        $this->actingAs($customer);

        Livewire::test(Account::class)
            ->set('firstname', 'Maja')
            ->set('lastname', 'Bergmann')
            ->set('phoneCountryCode', '+49')
            ->set('phone', '170 555 1234')
            ->set('language', 'de')
            ->set('city', 'Kiel')
            ->call('saveDetails')
            ->assertHasNoErrors()
            ->assertDispatched('guide-area-toast');

        $customer->refresh();
        $this->assertSame('Maja', $customer->firstname);
        $this->assertSame('de', $customer->language);
        $this->assertSame('Kiel', $customer->information?->city);

        Livewire::test(Account::class, ['tab' => 'password'])
            ->set('currentPassword', 'wrong-one')
            ->set('newPassword', 'new-secret-22')
            ->set('newPasswordConfirmation', 'new-secret-22')
            ->call('changePassword')
            ->assertHasErrors(['currentPassword']);

        Livewire::test(Account::class, ['tab' => 'password'])
            ->set('currentPassword', 'old-secret-1')
            ->set('newPassword', 'new-secret-22')
            ->set('newPasswordConfirmation', 'new-secret-22')
            ->call('changePassword')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('new-secret-22', $customer->fresh()->password));
    }

    public function test_guides_get_my_bookings_and_account_inside_the_guide_sidebar(): void
    {
        $guide = $this->guide();
        $this->actingAs($guide);

        $this->get(route('profile.my-bookings'))
            ->assertOk()
            ->assertSee(route('guide.area.calendar'), false)
            ->assertSee(__('guide_area.nav.my_bookings'))
            ->assertDontSee(__('guide_area.customer.become_guide.cta'));

        $this->get(route('profile.settings'))->assertOk()->assertSee('ga-shell', false)->assertSee(route('guide.area.tours'), false);
    }

    public function test_guides_following_profile_bookings_from_an_email_land_on_their_requests(): void
    {
        $this->actingAs($this->guide())
            ->get(route('profile.bookings', ['id' => 42]))
            ->assertRedirect(route('guide.area.bookings', ['booking' => 42]));
    }

    public function test_old_show_booking_link_opens_the_card_on_my_bookings(): void
    {
        $customer = $this->customer();
        $booking = $this->booking($this->tour($this->guide()), $customer, 'accepted', now()->addDays(4)->toDateString());

        $this->actingAs($customer)
            ->get(route('profile.showbooking', $booking->id))
            ->assertRedirect(route('profile.bookings', ['booking' => $booking->id]));
    }

    public function test_header_search_band_is_hidden_on_customer_pages(): void
    {
        $customer = $this->customer();
        $this->actingAs($customer);
        $request = Request::create(route('profile.bookings'));
        $request->setUserResolver(fn () => $customer);
        $request->setRouteResolver(fn () => app('router')->getRoutes()->match($request));

        $this->assertFalse(SitePrimaryNav::usesPageHeaderSearch($request));
    }

    public function test_kill_switch_brings_back_the_old_profile_pages(): void
    {
        config(['guide_area.customer_enabled' => false]);

        $this->actingAs($this->customer())
            ->get(route('profile.settings'))
            ->assertOk()
            ->assertDontSee('ga-shell', false);
    }
}
