<?php

namespace Tests\Feature\Guidings;

use App\Enums\GuideStatus;
use App\Models\Booking;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class GuidingReviewSliderOrderTest extends TestCase
{
    use DatabaseTransactions;

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

    public function test_tour_review_slider_starts_with_the_newest_review(): void
    {
        $guiding = $this->createPublishedTour();
        $guest = User::factory()->create(['is_guide' => 0]);

        $oldest = $this->createReview($guiding, $guest, 'REVIEW_OLDEST_TOKEN_slider_order', now()->subMonths(8));
        $newest = $this->createReview($guiding, $guest, 'REVIEW_NEWEST_TOKEN_slider_order', now()->subDay());

        $response = $this->get($guiding->publicShowUrl());

        $response->assertOk();
        $response->assertViewHas('reviews', function ($reviews) use ($newest, $oldest) {
            $ids = $reviews->pluck('id')->all();

            return $reviews->first()?->id === $newest->id
                && array_search($newest->id, $ids, true) < array_search($oldest->id, $ids, true);
        });
        $response->assertSeeInOrder([
            'REVIEW_NEWEST_TOKEN_slider_order',
            'REVIEW_OLDEST_TOKEN_slider_order',
        ], false);
    }

    public function test_newest_first_scope_orders_by_created_at_then_id(): void
    {
        $ordered = Review::query()
            ->newestFirst()
            ->toSql();

        $this->assertStringContainsString('order by `created_at` desc', strtolower($ordered));
        $this->assertStringContainsString('`id` desc', strtolower($ordered));
    }

    private function createPublishedTour(): Guiding
    {
        $user = User::factory()->create([
            'is_guide' => 1,
            'guide_status' => GuideStatus::VERIFIED,
        ]);

        $guiding = new Guiding();
        $guiding->forceFill([
            'title' => 'Review Slider Tour '.uniqid(),
            'slug' => 'review-slider-tour-'.uniqid(),
            'location' => 'Düsseldorf',
            'status' => 1,
            'max_guests' => 4,
            'duration' => 8,
            'price_type' => 'per_person',
            'price' => 150,
            'prices' => json_encode([
                ['person' => 1, 'amount' => 150],
            ]),
            'fishing_type_id' => FishingType::query()->value('id'),
            'user_id' => $user->id,
        ])->save();

        return $guiding;
    }

    private function createReview(Guiding $guiding, User $guest, string $comment, $createdAt): Review
    {
        $booking = new Booking();
        $booking->forceFill([
            'guiding_id' => $guiding->id,
            'user_id' => $guest->id,
            'status' => 'accepted',
            'token' => 'review-slider-'.uniqid(),
            'is_guest' => false,
            'count_of_users' => 1,
            'price' => 150,
            'book_date' => $createdAt->toDateString(),
        ])->save();

        $review = Review::create([
            'comment' => $comment,
            'overall_score' => 9,
            'guide_score' => 9,
            'region_water_score' => 9,
            'user_id' => $guest->id,
            'guide_id' => $guiding->user_id,
            'booking_id' => $booking->id,
            'guiding_id' => $guiding->id,
            'is_automatic' => false,
        ]);

        $review->created_at = $createdAt;
        $review->updated_at = $createdAt;
        $review->saveQuietly();

        return $review->fresh();
    }
}
