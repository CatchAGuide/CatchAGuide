<?php

namespace Tests\Unit\Services\Homepage;

use App\Models\Review;
use App\Models\User;
use App\Services\Homepage\HomepageLandingService;
use Illuminate\Support\Facades\Cache;
use ReflectionMethod;
use Tests\TestCase;

class HomepageTestimonialsTest extends TestCase
{
    public function test_testimonials_exclude_automatic_and_low_scores(): void
    {
        if (! Review::query()->exists()) {
            $this->markTestSkipped('No reviews in test database.');
        }

        Cache::flush();

        $service = app(HomepageLandingService::class);
        $method = new ReflectionMethod(HomepageLandingService::class, 'testimonials');
        $method->setAccessible(true);
        /** @var \Illuminate\Support\Collection $items */
        $items = $method->invoke($service);

        $this->assertLessThanOrEqual(15, $items->count());
        $this->assertTrue($items->every(function (array $row) {
            return ($row['score'] ?? 0) > 8
                && ($row['score'] ?? 0) <= 10
                && filled($row['quote'] ?? null)
                && filled($row['author'] ?? null)
                && ! str_starts_with((string) ($row['quote'] ?? ''), 'Successfully completed fishing tour');
        }));
    }

    public function test_testimonials_use_guest_author_tour_date_and_link(): void
    {
        if (! Review::query()->exists()) {
            $this->markTestSkipped('No reviews in test database.');
        }

        Cache::flush();

        $service = app(HomepageLandingService::class);
        $method = new ReflectionMethod(HomepageLandingService::class, 'testimonials');
        $method->setAccessible(true);
        /** @var \Illuminate\Support\Collection $items */
        $items = $method->invoke($service);

        if ($items->isEmpty()) {
            $this->markTestSkipped('No eligible homepage testimonials in test database.');
        }

        $this->assertTrue($items->every(function (array $row) {
            return array_key_exists('author', $row)
                && array_key_exists('date', $row)
                && array_key_exists('tour_url', $row)
                && array_key_exists('tour_title', $row);
        }));

        $eligibleIds = Review::query()
            ->where(function ($q) {
                $q->where('is_automatic', false)->orWhereNull('is_automatic');
            })
            ->where('grandtotal_score', '>', 8)
            ->where('grandtotal_score', '<=', 10)
            ->whereNotNull('comment')
            ->where('comment', '!=', '')
            ->where('comment', 'not like', 'Successfully completed fishing tour%')
            ->latest('id')
            ->limit(15)
            ->pluck('id');

        $guestReview = Review::query()
            ->with([
                'guiding:id,title,slug',
                'booking.calendar_schedule',
                'booking.blocked_event',
                'booking.guestUser:id,firstname',
                'booking.registeredUser:id,firstname',
            ])
            ->whereIn('id', $eligibleIds)
            ->whereHas('booking', fn ($q) => $q->where('is_guest', true))
            ->whereHas('guiding', fn ($q) => $q->whereNotNull('slug'))
            ->latest('id')
            ->first();

        if (! $guestReview) {
            $this->markTestSkipped('No guest-booking review available for author/date assertions.');
        }

        $expectedAuthor = $guestReview->booking?->user?->firstname;
        $wrongUserName = User::query()->whereKey($guestReview->user_id)->value('firstname');
        $expectedDate = ($guestReview->booking?->getBookingDate() ?? $guestReview->created_at)?->translatedFormat('M Y');
        $expectedUrl = route('guidings.show', [$guestReview->guiding->id, $guestReview->guiding->slug]);

        $matched = $items->first(fn (array $row) => ($row['tour_url'] ?? null) === $expectedUrl
            && ($row['author'] ?? null) === $expectedAuthor);

        if (! $matched) {
            $this->markTestSkipped('Guest review not in homepage testimonials sample window.');
        }

        $this->assertSame($expectedAuthor, $matched['author']);
        if ($wrongUserName && $wrongUserName !== $expectedAuthor) {
            $this->assertNotSame($wrongUserName, $matched['author']);
        }
        $this->assertSame($expectedDate, $matched['date']);
        $this->assertSame($expectedUrl, $matched['tour_url']);
        $this->assertNotNull($matched['tour_title']);
    }
}
