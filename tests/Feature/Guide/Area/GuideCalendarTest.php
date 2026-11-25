<?php

namespace Tests\Feature\Guide\Area;

use App\Enums\GuideStatus;
use App\Http\Livewire\Guide\Calendar;
use App\Models\Booking;
use App\Models\CalendarSchedule;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\User;
use App\Services\Guide\Area\GuideCalendarService;
use App\Services\Guide\Area\GuideCalendarSyncService;
use App\Services\ICalGeneratorService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Guide Area › Calendar & availability: day states match the public tour calendar, blocking
 * and unblocking, per-tour switches, private-calendar days, export options.
 */
class GuideCalendarTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost', 'guide_area.enabled' => true]);
        URL::forceRootUrl('http://localhost');
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
            'title' => 'Calendar tour '.uniqid(),
            'slug' => 'calendar-tour-'.uniqid(),
            'location' => 'Hamburg',
            'status' => 1,
            'max_guests' => 4,
            'duration' => 4,
            'price' => 200,
            'price_type' => 'per_tour',
            'fishing_type_id' => FishingType::query()->value('id'),
            'user_id' => $guide->id,
            'booking_window' => 'no_limitation',
            'allowed_booking_advance' => null,
        ], $extra))->save();

        return $guiding;
    }

    private function booking(Guiding $tour, string $status, string $date): Booking
    {
        $schedule = CalendarSchedule::create(['type' => 'tour_request', 'date' => $date, 'guiding_id' => $tour->id, 'user_id' => $tour->user_id]);
        $booking = new Booking;
        $booking->forceFill([
            'guiding_id' => $tour->id, 'blocked_event_id' => $schedule->id, 'status' => $status, 'token' => 'cal-'.uniqid(),
            'is_guest' => false, 'user_id' => $this->customer()->id, 'email' => 'g@example.com', 'count_of_users' => 1, 'price' => 200,
            'book_date' => $date, 'expires_at' => now()->addDay(),
        ])->save();

        return $booking;
    }

    private ?User $customer = null;

    private function customer(): User
    {
        return $this->customer ??= User::factory()->create(['firstname' => 'Thomas', 'lastname' => 'Roth']);
    }

    private function day(int $ahead): CarbonImmutable
    {
        return CarbonImmutable::today()->addDays($ahead);
    }

    private function state(User $guide, CarbonImmutable $date): array
    {
        return app(GuideCalendarService::class)->day($guide, $date);
    }

    public function test_day_states_follow_bookings_blocks_and_tour_switches(): void
    {
        $guide = $this->guide();
        $boat = $this->tour($guide);
        $shore = $this->tour($guide);
        $service = app(GuideCalendarService::class);

        $this->assertSame('available', $this->state($guide, $this->day(30))['state']);
        $this->assertSame('past', $this->state($guide, $this->day(-1))['state']);

        $this->booking($boat, 'accepted', $this->day(31)->toDateString());
        $this->assertSame('booked', $this->state($guide, $this->day(31))['state']);

        $this->booking($shore, 'pending', $this->day(32)->toDateString());
        $this->assertSame('pending', $this->state($guide, $this->day(32))['state']);

        // One tour switched off → partial; the public calendar of that tour agrees.
        $service->setTourOpen($guide, $boat->id, $this->day(33), false);
        $day = $this->state($guide, $this->day(33));
        $this->assertSame('partial', $day['state']);
        $this->assertSame(1, $day['bookable_count']);
        $this->assertSame('manual', collect($day['tours'])->firstWhere('id', $boat->id)['reason']);
        $this->assertTrue($boat->fresh()->isDateBlocked($this->day(33)->toDateString()));
        $this->assertFalse($shore->fresh()->isDateBlocked($this->day(33)->toDateString()));

        $service->setTourOpen($guide, $boat->id, $this->day(33), true);
        $this->assertSame('available', $this->state($guide, $this->day(33))['state']);
    }

    public function test_tour_rules_close_a_tour_without_a_switch(): void
    {
        $guide = $this->guide();
        $date = $this->day(40);
        $weekdays = array_values(array_diff(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'], [strtolower($date->format('l'))]));
        $tour = $this->tour($guide, ['weekdays' => json_encode($weekdays)]);

        $day = $this->state($guide, $date);
        $this->assertSame('closed', $day['state']);
        $this->assertSame('rules', collect($day['tours'])->firstWhere('id', $tour->id)['reason']);
    }

    public function test_block_period_skips_booked_days_and_unblock_reopens(): void
    {
        $guide = $this->guide();
        $tour = $this->tour($guide);
        $this->booking($tour, 'accepted', $this->day(51)->toDateString());

        $result = app(GuideCalendarService::class)->block($guide, $this->day(50), $this->day(52), null, 'Vacation');

        $this->assertSame(['blocked' => 2, 'skipped' => 1], $result);
        $this->assertSame('blocked', $this->state($guide, $this->day(50))['state']);
        $this->assertSame('booked', $this->state($guide, $this->day(51))['state']);
        $this->assertTrue($tour->fresh()->isDateBlocked($this->day(52)->toDateString()));

        Livewire::actingAs($guide)->test(Calendar::class)
            ->call('select', $this->day(50)->toDateString())
            ->call('unblockSelectedDay')
            ->assertDispatched('guide-area-toast', message: __('guide_area.calendar.day_unblocked'));

        $this->assertSame('available', $this->state($guide, $this->day(50))['state']);
    }

    public function test_private_calendar_days_cannot_be_unblocked_here(): void
    {
        $guide = $this->guide();
        $this->tour($guide);
        CalendarSchedule::create(['type' => 'custom_schedule', 'source' => CalendarSchedule::SOURCE_ICAL, 'date' => $this->day(60)->toDateString(), 'user_id' => $guide->id, 'note' => 'Dentist']);

        $this->assertSame(0, app(GuideCalendarService::class)->unblockDay($guide, $this->day(60)));
        $day = $this->state($guide, $this->day(60));
        $this->assertSame('blocked', $day['state']);
        $this->assertSame('ical', $day['blocks'][0]['source']);
        // Only "busy" — never what the private event is.
        $this->assertNull($day['blocks'][0]['note']);
    }

    public function test_a_declined_request_names_why_its_tour_is_closed(): void
    {
        $guide = $this->guide();
        $boat = $this->tour($guide);
        $this->tour($guide);
        $this->booking($boat, 'rejected', $this->day(45)->toDateString());

        $day = $this->state($guide, $this->day(45));
        $this->assertSame('partial', $day['state']);
        $this->assertSame('declined', collect($day['tours'])->firstWhere('id', $boat->id)['reason']);
    }

    public function test_block_days_form_validates_and_blocks_one_tour(): void
    {
        $guide = $this->guide();
        $boat = $this->tour($guide);
        $shore = $this->tour($guide);

        Livewire::actingAs($guide)->test(Calendar::class)
            ->call('openBlockForm')
            ->set('blockMode', 'period')
            ->set('blockFrom', $this->day(70)->toDateString())
            ->set('blockTo', $this->day(68)->toDateString())
            ->call('blockDays')
            ->assertHasErrors('blockTo')
            ->set('blockTo', $this->day(71)->toDateString())
            ->set('blockTour', $boat->id)
            ->call('blockDays')
            ->assertHasNoErrors();

        $this->assertTrue($boat->fresh()->isDateBlocked($this->day(71)->toDateString()));
        $this->assertFalse($shore->fresh()->isDateBlocked($this->day(71)->toDateString()));
    }

    public function test_another_guides_tour_cannot_be_switched(): void
    {
        $guide = $this->guide();
        $foreign = $this->tour($this->guide());

        Livewire::actingAs($guide)->test(Calendar::class)
            ->call('setTour', $foreign->id, false)
            ->assertStatus(404);
    }

    public function test_page_renders_and_export_leaves_out_declined_bookings(): void
    {
        $guide = $this->guide();
        $tour = $this->tour($guide);
        $this->booking($tour, 'accepted', $this->day(80)->toDateString());
        $this->booking($tour, 'rejected', $this->day(81)->toDateString());
        $this->booking($tour, 'pending', $this->day(82)->toDateString());

        $this->actingAs($guide)->get(route('guide.area.calendar'))
            ->assertOk()
            ->assertSee(__('guide_area.calendar.sync.export_title'));

        $sync = app(GuideCalendarSyncService::class);
        $feed = $sync->export($guide);
        $ics = app(ICalGeneratorService::class)->generateICalContent($feed);
        $this->assertStringContainsString('Booked: '.$tour->title, $ics);
        $this->assertStringContainsString('Request pending', $ics);
        $this->assertSame(2, substr_count($ics, 'BEGIN:VEVENT'));

        $sync->setExportOptions($guide, ['include_pending' => false]);
        $ics = app(ICalGeneratorService::class)->generateICalContent($feed->fresh());
        $this->assertSame(1, substr_count($ics, 'BEGIN:VEVENT'));
    }
}
