<?php

namespace Tests\Feature\Guide\Area;

use App\Enums\GuideStatus;
use App\Events\BookingStatusChanged;
use App\Http\Livewire\Guide\Bookings;
use App\Models\Booking;
use App\Models\BookingStatusChange;
use App\Models\CalendarSchedule;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\User;
use App\Services\Guide\Area\GuideBookingActionException;
use App\Services\Guide\Area\GuideBookingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Guide Area › Bookings (/guide/bookings): lists, tiles, accept / decline / cancel, deep links,
 * and the public availability rule "one booking per guide per day".
 */
class GuideBookingsTest extends TestCase
{
    use DatabaseTransactions;

    private const MESSAGE = 'Sorry, I am fully booked that day — but the other dates would work really well for me.';

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost', 'guide_area.enabled' => true]);
        URL::forceRootUrl('http://localhost');
        Event::fake([BookingStatusChanged::class]);
    }

    private function guide(): User
    {
        $guide = User::factory()->create(['is_guide' => 1, 'guide_status' => GuideStatus::VERIFIED, 'language' => 'en']);
        $guide->forceFill(['guide_area_beta' => true])->save();

        return $guide->fresh();
    }

    private function tour(User $guide, array $extra = []): Guiding
    {
        $guiding = new Guiding;
        $guiding->forceFill(array_merge([
            'title' => 'Zander by boat '.uniqid(),
            'slug' => 'zander-boat-'.uniqid(),
            'location' => 'Hamburg, Germany',
            'status' => 1,
            'max_guests' => 4,
            'duration' => 6,
            'duration_type' => 'hours',
            'price' => 340,
            'price_type' => 'per_tour',
            'fishing_type_id' => FishingType::query()->value('id'),
            'user_id' => $guide->id,
        ], $extra))->save();

        return $guiding;
    }

    private function booking(Guiding $tour, string $status, string $date, array $extra = []): Booking
    {
        $schedule = CalendarSchedule::create([
            'type' => 'tour_request',
            'date' => $date,
            'guiding_id' => $tour->id,
            'user_id' => $tour->user_id,
        ]);

        $booking = new Booking;
        $booking->forceFill(array_merge([
            'guiding_id' => $tour->id,
            'blocked_event_id' => $schedule->id,
            'status' => $status,
            'token' => 'gab-'.uniqid(),
            'is_guest' => true,
            'user_id' => 999999,
            'email' => 'guest-'.uniqid().'@example.com',
            'phone' => '151 2345 6789',
            'phone_country_code' => '+49',
            'count_of_users' => 2,
            'price' => 340,
            'cag_percent' => 34,
            'book_date' => $date,
            'expires_at' => now()->addHours(18),
        ], $extra))->save();
        $schedule->update(['booking_id' => $booking->id]);

        return $booking->fresh();
    }

    private function day(int $daysAhead): string
    {
        return now()->addDays($daysAhead)->toDateString();
    }

    public function test_the_page_lists_only_the_guides_own_bookings_with_tiles(): void
    {
        $guide = $this->guide();
        $tour = $this->tour($guide);
        $open = $this->booking($tour, 'pending', $this->day(10));
        $this->booking($tour, 'accepted', $this->day(20));
        $this->booking($tour, 'accepted', $this->day(-5));
        $this->booking($tour, 'rejected', $this->day(30));
        $other = $this->booking($this->tour($this->guide()), 'pending', $this->day(11));

        $component = Livewire::actingAs($guide)->test(Bookings::class)
            ->assertSee($tour->title)
            ->assertDontSee($other->guiding->title)
            ->assertSee(__('guide_area.bookings.contact_after_accept'));

        $counts = $component->viewData('counts');
        $this->assertSame(['all' => 4, 'open' => 1, 'confirmed' => 1, 'completed' => 1, 'closed' => 1], $counts);
        $this->assertCount(1, $component->viewData('openRequests'));
        $this->assertSame($open->id, $component->viewData('openRequests')->first()['id']);

        $component->call('setStatus', 'completed');
        $this->assertSame(['completed'], $component->viewData('bookings')->getCollection()->pluck('status')->all());
        // Clicking the selected tile again resets the filter.
        $component->call('setStatus', 'completed')->assertSet('status', 'all');
    }

    public function test_expired_requests_show_as_expired(): void
    {
        $guide = $this->guide();
        $tour = $this->tour($guide);
        $expired = $this->booking($tour, 'cancelled', $this->day(10), ['expires_at' => now()->subHour()]);
        $expired->statusChanges()->create(['from_status' => 'pending', 'to_status' => 'cancelled', 'reason' => BookingStatusChange::REASON_EXPIRED]);
        $this->booking($tour, 'pending', $this->day(12), ['expires_at' => now()->subMinute()]);

        $page = app(GuideBookingService::class)->paginate($guide, ['status' => 'closed']);
        $statuses = $page->getCollection()->map(fn (Booking $b) => \App\Enums\BookingStatus::forBooking($b)->key())->all();

        $this->assertSame(['expired', 'expired'], $statuses);
    }

    public function test_accept_confirms_and_shows_the_guest_contact(): void
    {
        $guide = $this->guide();
        $booking = $this->booking($this->tour($guide), 'pending', $this->day(10));

        Livewire::actingAs($guide)->test(Bookings::class)
            ->call('accept', $booking->id)
            ->assertDispatched('guide-area-toast', message: __('guide_area.bookings.accepted'))
            // The sidebar badge updates without a reload.
            ->assertDispatched('guide-area-badges', fn ($name, $params) => ($params['badges']['bookings'] ?? null) === 0)
            ->assertSee('+49 151 2345 6789');

        $this->assertSame('accepted', $booking->fresh()->status);
        Event::assertDispatched(BookingStatusChanged::class, fn ($e) => $e->booking->id === $booking->id && $e->status === 'accepted');
    }

    public function test_accept_refuses_a_second_booking_on_the_same_day(): void
    {
        $guide = $this->guide();
        $date = $this->day(14);
        $this->booking($this->tour($guide), 'accepted', $date);
        $second = $this->booking($this->tour($guide), 'pending', $date);

        Livewire::actingAs($guide)->test(Bookings::class)
            ->call('accept', $second->id)
            ->assertDispatched('guide-area-toast', message: __('guide_area.bookings.errors.day_taken'), type: 'error');

        $this->assertSame('pending', $second->fresh()->status);
    }

    public function test_accept_refuses_an_expired_request(): void
    {
        $guide = $this->guide();
        $booking = $this->booking($this->tour($guide), 'pending', $this->day(10), ['expires_at' => now()->subMinute()]);

        $this->expectException(GuideBookingActionException::class);
        app(GuideBookingService::class)->accept($booking);
    }

    public function test_decline_uses_the_emailed_forms_rules(): void
    {
        $guide = $this->guide();
        $booking = $this->booking($this->tour($guide), 'pending', $this->day(10));

        $component = Livewire::actingAs($guide)->test(Bookings::class)
            ->call('openDecline', $booking->id)
            ->set('declineMessage', 'too short')
            ->call('decline')
            ->assertHasErrors(['declineMessage', 'declineDates']);

        // The requested day itself can't be suggested.
        $component->set('declineMessage', self::MESSAGE)
            ->set('declineDates', [$this->day(10), '', ''])
            ->call('decline')
            ->assertHasErrors('declineDates');

        $component->set('declineDates', [$this->day(16), $this->day(13), ''])
            ->call('decline')
            ->assertHasNoErrors()
            ->assertSet('declining', null);

        $booking->refresh();
        $this->assertSame('rejected', $booking->status);
        $this->assertSame([$this->day(13), $this->day(16)], json_decode($booking->alternative_dates, true));
        $this->assertSame(self::MESSAGE, $booking->additional_information);
    }

    public function test_cancel_needs_a_reason_and_only_works_before_the_tour(): void
    {
        $guide = $this->guide();
        $tour = $this->tour($guide);
        $booking = $this->booking($tour, 'accepted', $this->day(5));

        Livewire::actingAs($guide)->test(Bookings::class)
            ->call('openCancel', $booking->id)
            ->call('cancel')
            ->assertHasErrors('cancelReason')
            ->set('cancelReason', 'weather')
            ->call('cancel')
            ->assertHasNoErrors()
            ->assertDispatched('guide-area-toast', message: __('guide_area.bookings.cancelled'));

        $booking->refresh();
        $this->assertSame('cancelled', $booking->status);
        $this->assertSame('weather', $booking->cancel_reason);
        $this->assertSame('guide', $booking->cancelled_by);

        $past = $this->booking($tour, 'accepted', $this->day(-2));
        $this->expectException(GuideBookingActionException::class);
        app(GuideBookingService::class)->cancel($past, 'illness');
    }

    public function test_another_guides_booking_is_not_found(): void
    {
        $guide = $this->guide();
        $foreign = $this->booking($this->tour($this->guide()), 'pending', $this->day(10));

        Livewire::actingAs($guide)->test(Bookings::class)
            ->call('accept', $foreign->id)
            ->assertStatus(404);

        $this->assertSame('pending', $foreign->fresh()->status);
    }

    public function test_deep_link_opens_the_page_holding_the_booking_and_its_decline_form(): void
    {
        $guide = $this->guide();
        $tour = $this->tour($guide);
        $target = $this->booking($tour, 'pending', $this->day(2));
        foreach (range(1, 12) as $i) {
            $this->booking($tour, 'accepted', $this->day(20 + $i));
        }

        $this->actingAs($guide)
            ->get(route('guide.area.bookings', ['booking' => $target->id, 'decline' => 1]))
            ->assertOk()
            ->assertSee('id="booking-'.$target->id.'"', false)
            ->assertSee(__('guide_area.bookings.decline_title'));
    }

    public function test_voucher_orders_moved_into_the_vouchers_tab(): void
    {
        $guide = $this->guide();

        $this->actingAs($guide)->get(route('guide.area.vouchers'))
            ->assertRedirect(route('guide.area.bookings', ['tab' => 'vouchers']));

        $this->actingAs($guide)->get(route('guide.area.bookings', ['tab' => 'vouchers']))
            ->assertOk()
            ->assertSee(__('guide_area.vouchers.search'));
    }

    public function test_one_booking_per_guide_per_day_on_the_public_calendar(): void
    {
        $guide = $this->guide();
        $boat = $this->tour($guide);
        $shore = $this->tour($guide);
        $date = $this->day(21);
        $booking = $this->booking($boat, 'accepted', $date);

        $this->assertTrue($shore->fresh()->isDateBlocked($date), 'a booking on another tour of the guide blocks the day');

        app(GuideBookingService::class)->cancel($booking, 'other');

        $this->assertFalse($shore->fresh()->isDateBlocked($date));
        $this->assertFalse($boat->fresh()->isDateBlocked($date), 'a cancelled booking releases the day');

        $this->booking($boat, 'rejected', $this->day(22));
        $this->assertTrue($boat->fresh()->isDateBlocked($this->day(22)), 'a declined request keeps its tour blocked');
    }
}
