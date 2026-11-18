<?php

namespace Tests\Feature\Guide\Area;

use App\Enums\GuideStatus;
use App\Events\BookingStatusChanged;
use App\Http\Livewire\Guide\Overview;
use App\Http\Livewire\Guide\Reviews;
use App\Models\Booking;
use App\Models\CalendarSchedule;
use App\Models\FinanceItem;
use App\Models\FishingType;
use App\Models\GuideTag;
use App\Models\Guiding;
use App\Models\ICalFeed;
use App\Models\Review;
use App\Models\User;
use App\Services\Guide\Area\GuideEarningsService;
use App\Services\Guide\Area\GuideOptimizationService;
use App\Support\GuideAreaNav;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Guide Area › Overview, To-dos, Reviews, Invoices & commission and the sidebar badges.
 */
class GuideDashboardPagesTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost', 'guide_area.enabled' => true]);
        URL::forceRootUrl('http://localhost');
        Event::fake([BookingStatusChanged::class]);
    }

    private function guide(array $extra = []): User
    {
        $guide = User::factory()->create(array_merge(['firstname' => 'Lars', 'is_guide' => 1, 'guide_status' => GuideStatus::VERIFIED, 'language' => 'en'], $extra));
        $guide->forceFill(['guide_area_beta' => true])->save();

        return $guide->fresh(['information']);
    }

    private function tour(User $guide, array $extra = []): Guiding
    {
        $guiding = new Guiding;
        $guiding->forceFill(array_merge([
            'title' => 'Pike by boat '.uniqid(), 'slug' => 'pike-'.uniqid(), 'location' => 'Wedel', 'status' => 1,
            'max_guests' => 4, 'duration' => 6, 'price' => 520, 'price_type' => 'per_tour',
            'fishing_type_id' => FishingType::query()->value('id'), 'user_id' => $guide->id,
        ], $extra))->save();

        return $guiding;
    }

    private function booking(Guiding $tour, string $status, string $date, array $extra = []): Booking
    {
        $schedule = CalendarSchedule::create(['type' => 'tour_request', 'date' => $date, 'guiding_id' => $tour->id, 'user_id' => $tour->user_id]);
        $booking = new Booking;
        $booking->forceFill(array_merge([
            'guiding_id' => $tour->id, 'blocked_event_id' => $schedule->id, 'status' => $status, 'token' => 'dash-'.uniqid(),
            'is_guest' => true, 'user_id' => 999999, 'email' => 'guest@example.com', 'count_of_users' => 3,
            'price' => 520, 'cag_percent' => 52, 'book_date' => $date, 'expires_at' => now()->addDays(2),
        ], $extra))->save();

        return $booking;
    }

    public function test_every_new_page_renders_for_a_guide_area_guide(): void
    {
        $guide = $this->guide();
        $this->tour($guide);

        foreach (['overview', 'bookings', 'todos', 'calendar', 'reviews', 'invoices'] as $page) {
            $this->actingAs($guide)->get(route('guide.area.'.$page))->assertOk()->assertSee('NOINDEX', false);
        }
    }

    public function test_kill_switch_sends_guides_to_the_matching_old_page(): void
    {
        $guide = $this->guide();
        config(['guide_area.enabled' => false]);

        $this->actingAs($guide)->get(route('guide.area.overview'))->assertRedirect(route('profile.index'));
        $this->actingAs($guide)->get(route('guide.area.bookings'))->assertRedirect(route('profile.bookings'));
        $this->actingAs($guide)->get(route('guide.area.todos'))->assertRedirect(route('profile.guide-profile'));
    }

    public function test_overview_shows_requests_upcoming_tours_and_accepts_inline(): void
    {
        $guide = $this->guide();
        $tour = $this->tour($guide);
        $request = $this->booking($tour, 'pending', now()->addDays(6)->toDateString());
        $this->booking($tour, 'accepted', now()->addDays(3)->toDateString(), ['phone' => '170 9876 5432', 'phone_country_code' => '+49']);

        $component = Livewire::actingAs($guide)->test(Overview::class)
            ->assertSee(trans_choice('guide_area.overview.requests_waiting', 1, ['count' => 1]))
            ->assertSee('+49 170 9876 5432')
            ->assertSee(e(route('guide.area.bookings', ['booking' => $request->id, 'decline' => 1])), false);

        $component->call('accept', $request->id)->assertDispatched('guide-area-toast', message: __('guide_area.bookings.accepted'));
        $this->assertSame('accepted', $request->fresh()->status);
    }

    public function test_optimization_score_counts_profile_tour_payment_and_calendar_checks(): void
    {
        $guide = $this->guide(['profil_image' => 'profile/1.webp', 'banktransfer_iban' => 'DE89370400440532013000', 'banktransfer_holder' => 'Lars P.']);
        $guide->information->forceFill(['display_name' => 'Lars', 'languages' => 'de,en', 'about_me' => str_repeat('Elbe pike and zander. ', 10), 'favorite_fish' => 'Zander', 'fishing_start_year' => 1990])->save();
        GuideTag::query()->create(['user_id' => $guide->id, 'kind' => GuideTag::KIND_SPECIALTY, 'value' => 'Zander']);
        $this->tour($guide, ['is_boat' => 1, 'boat_information' => '', 'gallery_images' => json_encode(['a', 'b', 'c', 'd']), 'inclusions' => json_encode(['Rods'])]);

        $result = app(GuideOptimizationService::class)->evaluate($guide, fresh: true);

        $failing = collect(app(GuideOptimizationService::class)->openChecks($result))->pluck('key')->all();
        $this->assertContains('good_for', $failing);
        $this->assertContains('certificates', $failing);
        $this->assertContains('boat_description', $failing);
        $this->assertContains('photos', $failing);
        $this->assertContains('calendar_connected', $failing);
        $this->assertNotContains('photo', $failing);
        $this->assertNotContains('bank_details', $failing);
        $this->assertNotContains('included_services', $failing);
        // Payment details come first on the Overview (spec 4.2).
        $this->assertSame('payment', app(GuideOptimizationService::class)->openChecks($result, 1)[0]['section']);

        ICalFeed::query()->create(['user_id' => $guide->id, 'name' => 'Private', 'feed_url' => 'https://example.com/a.ics', 'sync_type' => 'all_events', 'is_active' => true]);
        $after = app(GuideOptimizationService::class)->evaluate($guide, fresh: true);
        $this->assertSame(100, $after['sections']['calendar']['score']);
        $this->assertGreaterThan($result['score'], $after['score']);

        $this->actingAs($guide)->get(route('guide.area.todos'))
            ->assertOk()
            ->assertSee(__('guide_area.todos.score', ['score' => $after['score']]))
            ->assertSee(__('guide_area.todos.checks.good_for.fix'));
    }

    public function test_reviews_summary_new_badge_and_marking_seen(): void
    {
        $guide = $this->guide();
        $tour = $this->tour($guide);
        $booking = $this->booking($tour, 'accepted', now()->subDays(20)->toDateString());
        $fresh = Review::query()->create(['guide_id' => $guide->id, 'guiding_id' => $tour->id, 'booking_id' => $booking->id, 'user_id' => 999999, 'overall_score' => 10, 'guide_score' => 10, 'region_water_score' => 9, 'comment' => 'So patient with us beginners', 'is_automatic' => false]);
        $other = $this->booking($tour, 'accepted', now()->subDays(30)->toDateString());
        Review::query()->create(['guide_id' => $guide->id, 'guiding_id' => $tour->id, 'booking_id' => $other->id, 'user_id' => 999998, 'overall_score' => 8, 'guide_score' => 8, 'region_water_score' => 8, 'comment' => 'Fishing tour completed successfully', 'is_automatic' => true])
            ->forceFill(['seen_by_guide_at' => now()])->save();

        $nav = collect(GuideAreaNav::sections($guide))->pluck('items')->flatten(1)->keyBy('key');
        $this->assertSame(1, $nav['reviews']['badge']);

        Livewire::actingAs($guide)->test(Reviews::class)
            ->assertSee('9.0')
            ->assertSee(__('guide_area.reviews.labels.excellent'))
            ->assertSee(trans_choice('guide_area.reviews.verified', 1, ['count' => 1]))
            ->assertSee(__('guide_area.reviews.new'))
            ->assertSee(__('guide_area.reviews.automatic'));

        $this->assertNotNull($fresh->fresh()->seen_by_guide_at);
    }

    public function test_invoices_page_counts_completed_bookings_and_lists_invoices(): void
    {
        $guide = $this->guide();
        $tour = $this->tour($guide);
        $year = (int) now()->year;
        $past = now()->startOfYear()->addDays(10);
        if ($past->isFuture()) {
            $past = now()->subDay();
        }
        $done = $this->booking($tour, 'accepted', $past->toDateString(), ['created_at' => $past->copy()->subDays(5)]);
        $this->booking($tour, 'rejected', now()->addDays(10)->toDateString(), ['created_at' => $past]);
        $item = new FinanceItem;
        $item->forceFill(['billable_type' => $done->getMorphClass(), 'billable_id' => $done->id, 'invoice_status' => 'sent', 'invoice_sent_at' => $past->copy()->addDay(), 'gross_amount' => 520, 'commission_amount' => 52, 'currency' => 'EUR', 'paid_status' => 'unpaid', 'invoice_due_at' => now()->subDay()])->save();

        $data = app(GuideEarningsService::class)->year($guide, $year);
        $this->assertSame(520.0, $data['value']);
        $this->assertSame(52.0, $data['commission']);
        $this->assertSame(468.0, $data['earnings']);
        $this->assertSame(1, $data['declined']);
        $this->assertSame(50, $data['acceptance_rate']);
        $this->assertTrue(app(GuideEarningsService::class)->hasOverdue($guide));

        $this->actingAs($guide)->get(route('guide.area.invoices'))
            ->assertOk()
            ->assertSee(__('guide_area.invoices.status.overdue'))
            ->assertSee(route('guide.area.invoices.show', $item->id), false);

        $this->actingAs($guide)->get(route('guide.area.invoices.show', $item->id))->assertOk()->assertSee((string) $done->id);
        $this->actingAs($this->guide())->get(route('guide.area.invoices.show', $item->id))->assertNotFound();
    }

    public function test_sidebar_has_the_spec_items_and_booking_badge(): void
    {
        $guide = $this->guide();
        $this->booking($this->tour($guide), 'pending', now()->addDays(4)->toDateString());

        $items = collect(GuideAreaNav::sections($guide))->pluck('items')->flatten(1)->keyBy('key');

        $this->assertSame(['overview', 'bookings', 'todos', 'calendar', 'tours', 'vouchers', 'reviews', 'invoices', 'profile', 'account'], $items->keys()->all());
        $this->assertSame(1, $items['bookings']['badge']);
        $this->assertSame(route('guide.area.vouchers.settings'), $items['vouchers']['url']);
    }
}
