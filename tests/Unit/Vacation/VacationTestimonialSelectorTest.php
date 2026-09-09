<?php

namespace Tests\Unit\Vacation;

use App\Models\VacationTestimonial;
use App\Services\Vacation\VacationTestimonialSelector;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class VacationTestimonialSelectorTest extends TestCase
{
    use DatabaseTransactions;

    public function test_latest_only_returns_published_testimonials_ordered_by_sort_order(): void
    {
        Cache::flush();

        VacationTestimonial::query()->create([
            'quote' => 'Hidden testimonial',
            'author' => 'Ghost',
            'rating' => 9.0,
            'is_published' => false,
            'sort_order' => 0,
        ]);

        VacationTestimonial::query()->create([
            'quote' => 'Second in line',
            'author' => 'Bea',
            'rating' => 8.5,
            'is_published' => true,
            'sort_order' => 2,
            'listing_title' => 'Pike Camp Sweden',
            'listing_url' => 'https://example.test/vacations/camps/pike-camp-sweden',
        ]);

        VacationTestimonial::query()->create([
            'quote' => 'First in line',
            'author' => 'Alex',
            'rating' => 9.7,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $items = app(VacationTestimonialSelector::class)->latest(10);

        $this->assertCount(2, $items);
        $this->assertSame('First in line', $items[0]['quote']);
        $this->assertSame('Second in line', $items[1]['quote']);
        $this->assertSame('Pike Camp Sweden', $items[1]['listing_title']);
        $this->assertSame('https://example.test/vacations/camps/pike-camp-sweden', $items[1]['listing_url']);
        $this->assertTrue($items->every(fn (array $row) => ($row['quote'] ?? '') !== 'Hidden testimonial'));
    }

    public function test_latest_respects_limit(): void
    {
        Cache::flush();

        foreach (range(1, 3) as $i) {
            VacationTestimonial::query()->create([
                'quote' => "Quote {$i}",
                'author' => "Author {$i}",
                'rating' => 9.0,
                'is_published' => true,
                'sort_order' => $i,
            ]);
        }

        $items = app(VacationTestimonialSelector::class)->latest(2);

        $this->assertCount(2, $items);
    }
}
