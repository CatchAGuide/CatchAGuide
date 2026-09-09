<?php

namespace Tests\Unit\Reviews;

use App\Models\Review;
use App\Services\Reviews\TestimonialSelector;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TestimonialSelectorTest extends TestCase
{
    public function test_latest_excludes_automatic_and_low_scores(): void
    {
        if (! Review::query()->exists()) {
            $this->markTestSkipped('No reviews in test database.');
        }

        Cache::flush();

        $items = app(TestimonialSelector::class)->latest(15);

        $this->assertLessThanOrEqual(15, $items->count());
        $this->assertTrue($items->every(function (array $row) {
            return ($row['score'] ?? 0) > 8
                && ($row['score'] ?? 0) <= 10
                && filled($row['quote'] ?? null)
                && filled($row['author'] ?? null)
                && ! str_starts_with((string) ($row['quote'] ?? ''), 'Successfully completed fishing tour');
        }));
    }

    public function test_latest_respects_limit(): void
    {
        if (! Review::query()->exists()) {
            $this->markTestSkipped('No reviews in test database.');
        }

        Cache::flush();

        $items = app(TestimonialSelector::class)->latest(2);

        $this->assertLessThanOrEqual(2, $items->count());
    }
}
