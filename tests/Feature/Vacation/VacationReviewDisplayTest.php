<?php

namespace Tests\Feature\Vacation;

use App\Models\Camp;
use App\Models\CampVacationBooking;
use App\Models\Employee;
use App\Models\Trip;
use App\Models\TripBooking;
use App\Models\VacationReview;
use App\Services\Vacation\VacationReviewService;
use App\Services\Vacation\VacationReviewStats;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Camp/trip reviews on the product pages and listing cards, and the admin's manual review form
 * and "Open for review" list.
 */
class VacationReviewDisplayTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://cag.local', 'sales_documents.language_domains' => false]);
        URL::forceRootUrl('http://cag.local');
    }

    private function actingAsEmployee(): void
    {
        $employee = Employee::query()->first();
        if (! $employee) {
            $this->markTestSkipped('No employee available for admin auth.');
        }

        $this->actingAs($employee, 'employees');
    }

    private function activeTrip(): Trip
    {
        $trip = Trip::query()->where('status', 'active')->whereNotNull('slug')->first();
        if (! $trip) {
            $this->markTestSkipped('No active trip available.');
        }

        return $trip;
    }

    private function activeCamp(): Camp
    {
        $camp = Camp::query()->where('status', 'active')->whereNotNull('slug')->first();
        if (! $camp) {
            $this->markTestSkipped('No active camp available.');
        }

        return $camp;
    }

    private function manualReview(string $type, int $listingId, array $overrides = []): VacationReview
    {
        return app(VacationReviewService::class)->createManual(array_merge([
            'listing_type' => $type,
            'listing_id' => $listingId,
            'guest_name' => 'Maximilian',
            'reviewed_at' => '2026-09-14',
            'language' => 'en',
            'overall_score' => 9,
            'accommodation_score' => 8,
            'fishing_score' => 10,
            'value_score' => 9,
            'comment' => 'Unique test comment about the fjord.',
        ], $overrides));
    }

    private function doneCampBooking(array $attributes = []): CampVacationBooking
    {
        return CampVacationBooking::query()->create(array_merge([
            'source_type' => CampVacationBooking::SOURCE_CAMP,
            'source_id' => Camp::query()->value('id') ?? 0,
            'preferred_date' => now()->subWeeks(2)->toDateString(),
            'nights' => 4,
            'number_of_persons' => 2,
            'name' => 'Open Review Guest',
            'first_name' => 'Open',
            'email' => 'open-review@example.com',
            'phone_country_code' => '+49',
            'phone' => '1700000000',
            'message' => 'Test request',
            'language' => 'de',
            'status' => CampVacationBooking::STATUS_DONE,
        ], $attributes));
    }

    public function test_manual_review_stores_name_date_and_scores_without_a_booking(): void
    {
        $trip = $this->activeTrip();

        $review = $this->manualReview('trip', $trip->id);

        $this->assertNull($review->reviewable_id);
        $this->assertTrue($review->is_manual);
        $this->assertSame('Maximilian', $review->displayName());
        $this->assertSame('2026-09-14', $review->created_at->toDateString());
        $this->assertSame(9.0, (float) $review->grandtotal_score);
    }

    public function test_listing_reviews_average_every_review_of_the_listing(): void
    {
        $camp = $this->activeCamp();
        VacationReview::query()->forListing('camp', $camp->id)->delete();

        $this->manualReview('camp', $camp->id, ['overall_score' => 10, 'accommodation_score' => 10, 'fishing_score' => 10, 'value_score' => 10]);
        $this->manualReview('camp', $camp->id, ['overall_score' => 6, 'accommodation_score' => 8, 'fishing_score' => 8, 'value_score' => 6]);

        $listing = app(VacationReviewService::class)->listingReviews('camp', $camp->id);

        $this->assertSame(2, $listing['count']);
        $this->assertEqualsWithDelta(8.5, $listing['averages']['grandtotal'], 0.001);
        $this->assertEqualsWithDelta(8.0, $listing['averages']['overall'], 0.001);
        $this->assertEqualsWithDelta(9.0, $listing['averages']['fishing'], 0.001);
        $this->assertCount(2, $listing['reviews']);

        $this->assertSame(['rating' => 8.5, 'count' => 2], (new VacationReviewStats())->forListing('camp', $camp->id));
        $this->assertNull(app(VacationReviewService::class)->listingReviews('camp', 0));
    }

    public function test_trip_page_shows_review_section_and_title_badge(): void
    {
        $trip = $this->activeTrip();
        $this->manualReview('trip', $trip->id);

        $this->get(route('vacations.trips.show', $trip->slug))
            ->assertOk()
            ->assertSee('class="listing-reviews"', false)
            ->assertSee('id="reviews"', false)
            ->assertSee('Unique test comment about the fjord.')
            ->assertSee('Maximilian')
            ->assertSee('listing-review-badge', false)
            ->assertSee(__('vacations.rating_accommodation'))
            ->assertSee('tour-reviews-m', false);
    }

    public function test_camp_page_shows_review_section(): void
    {
        $camp = $this->activeCamp();
        $this->manualReview('camp', $camp->id);

        $this->get(route('vacations.camps.show', $camp->slug))
            ->assertOk()
            ->assertSee('class="listing-reviews"', false)
            ->assertSee('Unique test comment about the fjord.')
            ->assertSee('listing-review-badge', false);
    }

    public function test_page_without_reviews_has_no_review_section(): void
    {
        $trip = $this->activeTrip();
        VacationReview::query()->forListing('trip', $trip->id)->delete();

        $this->get(route('vacations.trips.show', $trip->slug))
            ->assertOk()
            ->assertDontSee('class="listing-reviews"', false)
            ->assertDontSee('listing-review-badge__value', false);
    }

    public function test_admin_adds_a_manual_review_for_a_listing(): void
    {
        $this->actingAsEmployee();
        $trip = $this->activeTrip();

        $this->get(route('admin.vacation-reviews.create'))
            ->assertRedirect(route('admin.vacation-reviews.index', ['add' => 1]));

        $this->get(route('admin.vacation-reviews.index', ['add' => 1]))
            ->assertOk()
            ->assertSee('id="vacationReviewModal"', false)
            ->assertSee('data-auto-open="1"', false)
            ->assertSee('value="trip:'.$trip->id.'"', false);

        $response = $this->post(route('admin.vacation-reviews.store'), [
            'listing' => 'trip:'.$trip->id,
            'guest_name' => '  Lea ',
            'reviewed_at' => '2026-08-01',
            'language' => 'de',
            'overall_score' => 10,
            'accommodation_score' => 9,
            'fishing_score' => 10,
            'value_score' => 9,
            'comment' => 'Telefonisch abgegebene Bewertung.',
        ]);

        $review = VacationReview::query()->where('comment', 'Telefonisch abgegebene Bewertung.')->firstOrFail();
        $response->assertRedirect(route('admin.vacation-reviews.index', ['review' => $review->id]))->assertSessionHas('success');
        $this->assertSame('trip', $review->listing_type);
        $this->assertSame($trip->id, (int) $review->listing_id);
        $this->assertSame('Lea', $review->guest_name);
        $this->assertSame('de', $review->language);
        $this->assertTrue($review->is_manual);

        $this->get(route('admin.vacation-reviews.index'))
            ->assertOk()
            ->assertSee('Telefonisch abgegebene Bewertung.')
            ->assertSee(__('admin.vacation_reviews.manual_badge'));
    }

    public function test_manual_review_validates_listing_scores_and_date(): void
    {
        $this->actingAsEmployee();

        $this->post(route('admin.vacation-reviews.store'), [
            'listing' => 'camp:999999999',
            'guest_name' => 'Ben',
            'reviewed_at' => now()->toDateString(),
            'language' => 'en',
            'overall_score' => 10,
            'accommodation_score' => 10,
            'fishing_score' => 10,
            'value_score' => 10,
            'comment' => 'Fine',
        ])->assertSessionHasErrors('listing');

        $this->post(route('admin.vacation-reviews.store'), [
            'listing' => 'boat:1',
            'guest_name' => '',
            'reviewed_at' => now()->addDay()->toDateString(),
            'language' => 'fr',
            'overall_score' => 11,
            'comment' => '',
        ])->assertSessionHasErrors(['listing', 'guest_name', 'reviewed_at', 'language', 'overall_score', 'accommodation_score', 'comment']);
    }

    public function test_open_for_review_lists_done_unreviewed_bookings_and_entering_a_review_closes_them(): void
    {
        $this->actingAsEmployee();
        $open = $this->doneCampBooking();
        $inProcess = $this->doneCampBooking(['status' => CampVacationBooking::STATUS_IN_PROCESS, 'email' => 'in-process@example.com']);

        $this->get(route('admin.vacation-reviews.index', ['tab' => 'open']))
            ->assertOk()
            ->assertSee(route('admin.camp-vacation-bookings.review-request', $open), false)
            ->assertSee('data-booking-id="'.$open->id.'"', false)
            ->assertSee('data-auto-open="0"', false)
            ->assertDontSee('in-process@example.com');

        // The old form URL now opens the modal prefilled for the booking.
        $modalUrl = route('admin.vacation-reviews.index', ['tab' => 'open', 'add' => 1, 'booking_type' => 'camp', 'booking_id' => $open->id]);
        $this->get(route('admin.vacation-reviews.create', ['booking_type' => 'camp', 'booking_id' => $open->id]))
            ->assertRedirect($modalUrl);
        $this->get($modalUrl)
            ->assertOk()
            ->assertSee('name="booking_id" value="'.$open->id.'"', false)
            ->assertSee('value="Open"', false);

        $this->post(route('admin.vacation-reviews.store'), [
            'booking_type' => 'camp',
            'booking_id' => $open->id,
            'guest_name' => 'Open',
            'reviewed_at' => now()->toDateString(),
            'language' => 'de',
            'overall_score' => 8,
            'accommodation_score' => 8,
            'fishing_score' => 8,
            'value_score' => 8,
            'comment' => 'Per Telefon.',
        ])->assertSessionHas('success');

        $review = $open->refresh()->review;
        $this->assertNotNull($review);
        $this->assertSame((int) $open->source_id, (int) $review->listing_id);
        $this->assertFalse($open->canRequestReview());

        $this->get(route('admin.vacation-reviews.index', ['tab' => 'open']))
            ->assertDontSee('data-booking-id="'.$open->id.'"', false);

        // A second manual review for the same booking is refused.
        $this->post(route('admin.vacation-reviews.store'), [
            'booking_type' => 'camp',
            'booking_id' => $open->id,
            'guest_name' => 'Open',
            'reviewed_at' => now()->toDateString(),
            'language' => 'de',
            'overall_score' => 8,
            'accommodation_score' => 8,
            'fishing_score' => 8,
            'value_score' => 8,
            'comment' => 'Twice.',
        ])->assertSessionHasErrors('booking_id');
    }

    public function test_failed_manual_review_reopens_the_modal_with_the_input(): void
    {
        $this->actingAsEmployee();
        $trip = $this->activeTrip();
        $index = route('admin.vacation-reviews.index');

        $this->from($index)->post(route('admin.vacation-reviews.store'), [
            'listing' => 'trip:'.$trip->id,
            'guest_name' => 'Mia',
            'reviewed_at' => now()->toDateString(),
            'language' => 'en',
            'overall_score' => 9,
            'comment' => 'Missing scores.',
        ])->assertRedirect($index);

        $this->get($index)
            ->assertOk()
            ->assertSee('data-auto-open="1"', false)
            ->assertSee('value="Mia"', false)
            ->assertSee('Missing scores.')
            ->assertSee('value="trip:'.$trip->id.'" selected', false);
    }

    public function test_review_request_from_the_open_list_returns_there(): void
    {
        $this->actingAsEmployee();
        Mail::fake();
        $booking = TripBooking::query()->create([
            'source_type' => TripBooking::SOURCE_TRIP,
            'source_id' => Trip::query()->value('id') ?? 0,
            'preferred_date' => now()->subWeeks(3)->toDateString(),
            'number_of_persons' => 2,
            'name' => 'Trip Open Guest',
            'email' => 'trip-open@example.com',
            'phone_country_code' => '+49',
            'phone' => '1700000001',
            'message' => 'Test request',
            'language' => 'en',
            'status' => TripBooking::STATUS_DONE,
        ]);

        $this->post(route('admin.trip-bookings.review-request', $booking), ['return' => 'reviews'])
            ->assertRedirect(route('admin.vacation-reviews.index', ['tab' => 'open']))
            ->assertSessionHas('success');
    }
}
