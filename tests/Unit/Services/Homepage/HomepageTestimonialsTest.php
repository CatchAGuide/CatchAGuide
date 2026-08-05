<?php

namespace Tests\Unit\Services\Homepage;

use App\Models\Review;
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

        $this->assertLessThanOrEqual(6, $items->count());
        $this->assertTrue($items->every(function (array $row) {
            return ($row['score'] ?? 0) >= 8
                && ($row['score'] ?? 0) <= 10
                && filled($row['quote'] ?? null)
                && ! str_starts_with((string) ($row['quote'] ?? ''), 'Successfully completed fishing tour');
        }));
    }
}
