<?php

namespace Tests\Feature\Vacation;

use App\Mail\Guest\VacationReviewRequestMail;
use App\Models\Booking;
use App\Models\Camp;
use App\Models\CampVacationBooking;
use App\Models\Employee;
use App\Models\Trip;
use App\Models\TripBooking;
use App\Models\VacationReview;
use App\Services\Vacation\VacationReviewService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class VacationReviewRequestTest extends TestCase
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

    private function campBooking(array $attributes = []): CampVacationBooking
    {
        return CampVacationBooking::query()->create(array_merge([
            'source_type' => CampVacationBooking::SOURCE_CAMP,
            'source_id' => Camp::query()->value('id') ?? 0,
            'preferred_date' => now()->subWeeks(2)->toDateString(),
            'nights' => 4,
            'number_of_persons' => 2,
            'name' => 'Review Guest',
            'first_name' => 'Review',
            'email' => 'review-guest@example.com',
            'phone_country_code' => '+49',
            'phone' => '1700000000',
            'message' => 'Test request',
            'language' => 'de',
            'status' => CampVacationBooking::STATUS_DONE,
        ], $attributes));
    }

    private function tripBooking(array $attributes = []): TripBooking
    {
        return TripBooking::query()->create(array_merge([
            'source_type' => TripBooking::SOURCE_TRIP,
            'source_id' => Trip::query()->value('id') ?? 0,
            'preferred_date' => now()->subWeeks(3)->toDateString(),
            'preferred_date_to' => now()->subWeeks(2)->toDateString(),
            'number_of_persons' => 3,
            'name' => 'Trip Review Guest',
            'email' => 'trip-review-guest@example.com',
            'phone_country_code' => '+49',
            'phone' => '1700000001',
            'message' => 'Test request',
            'language' => 'en',
            'status' => TripBooking::STATUS_DONE,
        ], $attributes));
    }

    private function scores(array $overrides = []): array
    {
        return array_merge([
            'rating_overall' => 9,
            'rating_accommodation' => 8,
            'rating_fishing' => 10,
            'rating_value' => 7,
            'comment' => 'Great camp, big catfish.',
        ], $overrides);
    }

    public function test_admin_sends_camp_review_request(): void
    {
        Mail::fake();
        $this->actingAsEmployee();
        $booking = $this->campBooking();

        $this->post(route('admin.camp-vacation-bookings.review-request', $booking))
            ->assertRedirect(route('admin.camp-vacation-bookings.index'))
            ->assertSessionHas('success');

        $booking->refresh();
        $this->assertNotNull($booking->review_token);
        $this->assertNotNull($booking->review_requested_at);

        Mail::assertSent(VacationReviewRequestMail::class, function (VacationReviewRequestMail $mail) use ($booking) {
            return $mail->hasTo('review-guest@example.com')
                && $mail->locale === 'de'
                && $mail->reviewUrl === 'http://cag.local/ratings/camp/'.$booking->review_token;
        });
    }

    public function test_admin_sends_trip_review_request(): void
    {
        Mail::fake();
        $this->actingAsEmployee();
        $booking = $this->tripBooking();

        $this->post(route('admin.trip-bookings.review-request', $booking))
            ->assertRedirect(route('admin.trip-bookings.index'))
            ->assertSessionHas('success');

        Mail::assertSent(VacationReviewRequestMail::class, fn (VacationReviewRequestMail $mail) => $mail->locale === 'en'
            && str_contains($mail->reviewUrl, '/ratings/trip/'.$booking->refresh()->review_token));
    }

    public function test_request_is_refused_until_the_booking_is_done(): void
    {
        Mail::fake();
        $this->actingAsEmployee();
        $booking = $this->campBooking(['status' => CampVacationBooking::STATUS_IN_PROCESS]);

        $this->post(route('admin.camp-vacation-bookings.review-request', $booking))
            ->assertSessionHas('error');

        Mail::assertNothingSent();
        $this->assertNull($booking->refresh()->review_token);
    }

    public function test_double_click_does_not_send_twice_but_a_later_resend_keeps_the_token(): void
    {
        Mail::fake();
        $service = app(VacationReviewService::class);
        $booking = $this->campBooking();

        $this->assertTrue($service->sendRequest($booking));
        $token = $booking->review_token;
        $this->assertFalse($service->sendRequest($booking));

        $booking->forceFill(['review_requested_at' => now()->subDay()])->save();
        $this->assertTrue($service->sendRequest($booking));
        $this->assertSame($token, $booking->refresh()->review_token);

        Mail::assertSent(VacationReviewRequestMail::class, 2);
    }

    public function test_guest_sees_review_form_and_unknown_token_is_404(): void
    {
        $booking = $this->campBooking(['review_token' => str_repeat('a', 48)]);

        $this->get(route('ratings.vacation.show', ['type' => 'camp', 'token' => $booking->review_token]))
            ->assertOk()
            ->assertSee('name="rating_accommodation"', false)
            ->assertSee('name="rating_value"', false)
            ->assertSee('noindex,nofollow', false);

        $this->get(route('ratings.vacation.show', ['type' => 'trip', 'token' => $booking->review_token]))->assertNotFound();
        $this->get('/ratings/camp/unknown-token')->assertNotFound();
    }

    public function test_guest_submits_review_once(): void
    {
        $booking = $this->tripBooking(['review_token' => str_repeat('b', 48)]);
        $url = route('ratings.vacation.store', ['type' => 'trip', 'token' => $booking->review_token]);

        $this->postJson($url, $this->scores())->assertOk()->assertJson(['success' => true]);

        $review = VacationReview::query()->where('reviewable_type', TripBooking::class)->where('reviewable_id', $booking->id)->firstOrFail();
        $this->assertSame('trip', $review->listing_type);
        $this->assertSame((int) $booking->source_id, (int) $review->listing_id);
        $this->assertSame(8.5, (float) $review->grandtotal_score);
        $this->assertSame('Great camp, big catfish.', $review->comment);

        $this->postJson($url, $this->scores())->assertStatus(422);
        $this->assertSame(1, VacationReview::query()->where('reviewable_id', $booking->id)->where('reviewable_type', TripBooking::class)->count());

        $this->get(route('ratings.vacation.show', ['type' => 'trip', 'token' => $booking->review_token]))
            ->assertRedirect(route('ratings.notified'));
        $this->assertFalse($booking->refresh()->canRequestReview());
    }

    public function test_review_needs_all_scores_and_a_comment(): void
    {
        $booking = $this->campBooking(['review_token' => str_repeat('c', 48)]);
        $url = route('ratings.vacation.store', ['type' => 'camp', 'token' => $booking->review_token]);

        $this->postJson($url, $this->scores(['rating_fishing' => null, 'rating_value' => 11, 'comment' => '  ']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['rating_fishing', 'rating_value', 'comment']);
    }

    public function test_admin_lists_reviews_and_booking_rows_show_the_review_button(): void
    {
        $this->actingAsEmployee();
        $reviewed = $this->campBooking(['review_token' => str_repeat('d', 48)]);
        app(VacationReviewService::class)->submit($reviewed, [
            'overall_score' => 10, 'accommodation_score' => 9, 'fishing_score' => 8, 'value_score' => 9,
            'comment' => 'Admin list comment',
        ]);
        $open = $this->campBooking(['email' => 'not-reviewed@example.com']);

        $this->get(route('admin.vacation-reviews.index'))
            ->assertOk()
            ->assertSee('Admin list comment')
            ->assertSee('9.0');

        $this->get(route('admin.camp-vacation-bookings.index'))
            ->assertOk()
            ->assertSee(route('admin.camp-vacation-bookings.review-request', $open), false)
            ->assertSee(route('admin.vacation-reviews.index', ['review' => $reviewed->review->id]), false);
    }

    public function test_tour_rating_page_still_renders_with_shared_partials(): void
    {
        $booking = Booking::query()
            ->where('status', 'accepted')
            ->where('is_reviewed', 0)
            ->whereNotNull('token')
            ->whereHas('guiding.user')
            ->first();
        if (! $booking) {
            $this->markTestSkipped('No unreviewed accepted booking available.');
        }

        $this->get(route('ratings.show', ['token' => $booking->token]))
            ->assertOk()
            ->assertSee('name="rating_guide"', false)
            ->assertSee('.rating-page-container', false)
            ->assertSee('["rating_overall","rating_guide","rating_region"]', false);
    }

    public function test_mail_renders_in_both_languages(): void
    {
        $booking = $this->campBooking(['review_token' => str_repeat('e', 48)]);
        $url = app(VacationReviewService::class)->reviewUrl($booking);

        foreach (['de' => 'Bewertung schreiben', 'en' => 'Write a review'] as $locale => $cta) {
            $html = (new VacationReviewRequestMail($booking, $url))->locale($locale)->render();

            $this->assertStringContainsString($cta, $html);
            $this->assertStringContainsString($url, $html);
            $this->assertStringContainsString('Review', $html);
        }

        $this->assertStringContainsString('Write a review', VacationReviewRequestMail::sample()->locale('en')->render());
    }
}
