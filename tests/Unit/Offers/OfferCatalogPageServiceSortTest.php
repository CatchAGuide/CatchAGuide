<?php

namespace Tests\Unit\Offers;

use App\Domain\Offers\OfferListingFilter;
use App\Services\Offers\OfferCatalogPageService;
use Mockery;
use ReflectionMethod;
use Tests\TestCase;

class OfferCatalogPageServiceSortTest extends TestCase
{
    public function test_sort_listing_items_by_price_newest_nearest_and_recommended(): void
    {
        $service = Mockery::mock(OfferCatalogPageService::class)->makePartial();
        $method = new ReflectionMethod(OfferCatalogPageService::class, 'sortListingItems');
        $method->setAccessible(true);

        $items = collect([
            ['type' => 'tour', 'model' => null, 'created_at' => now()->subDay(), 'price' => 200, 'rating' => 8.2, 'review_count' => 4, 'distance' => 12.0],
            ['type' => 'trip', 'model' => null, 'created_at' => now(), 'price' => 100, 'rating' => null, 'review_count' => 0, 'distance' => 3.0],
            ['type' => 'camp', 'model' => null, 'created_at' => now()->subDays(2), 'price' => 300, 'rating' => null, 'review_count' => 0, 'distance' => 8.0],
        ]);

        $asc = $method->invoke($service, $items, OfferListingFilter::fromRequest(['sortby' => 'price-asc']));
        $this->assertSame([100.0, 200.0, 300.0], $asc->pluck('price')->map(fn ($p) => (float) $p)->all());

        $desc = $method->invoke($service, $items, OfferListingFilter::fromRequest(['sortby' => 'price-desc']));
        $this->assertSame([300.0, 200.0, 100.0], $desc->pluck('price')->map(fn ($p) => (float) $p)->all());

        $newest = $method->invoke($service, $items, OfferListingFilter::fromRequest(['sortby' => 'newest']));
        $this->assertSame('trip', $newest->first()['type']);

        $nearest = $method->invoke($service, $items, OfferListingFilter::fromRequest(['sortby' => 'nearest']));
        $this->assertSame(['trip', 'camp', 'tour'], $nearest->pluck('type')->all());

        // Recommended: scored tours first; unrated camps/trips fall back to created_at (trip newest).
        $recommended = $method->invoke($service, $items, OfferListingFilter::fromRequest([]));
        $this->assertSame(['tour', 'trip', 'camp'], $recommended->pluck('type')->all());
    }

    public function test_recommended_sort_orders_by_score_then_review_count(): void
    {
        $service = Mockery::mock(OfferCatalogPageService::class)->makePartial();
        $sort = new ReflectionMethod(OfferCatalogPageService::class, 'sortListingItems');
        $sort->setAccessible(true);

        $items = collect([
            ['type' => 'tour', 'id' => 1, 'created_at' => now()->subDays(5), 'price' => 100, 'rating' => 10.0, 'review_count' => 15],
            ['type' => 'tour', 'id' => 2, 'created_at' => now()->subDays(4), 'price' => 100, 'rating' => 10.0, 'review_count' => 16],
            ['type' => 'tour', 'id' => 3, 'created_at' => now()->subDays(3), 'price' => 100, 'rating' => 10.0, 'review_count' => 8],
            ['type' => 'tour', 'id' => 4, 'created_at' => now()->subDays(2), 'price' => 100, 'rating' => 9.8, 'review_count' => 3],
            ['type' => 'tour', 'id' => 5, 'created_at' => now()->subDay(), 'price' => 100, 'rating' => 9.8, 'review_count' => 10],
        ]);

        $recommended = $sort->invoke($service, $items, OfferListingFilter::fromRequest(['sortby' => 'recommended']));

        $this->assertSame([2, 1, 3, 5, 4], $recommended->pluck('id')->all());
    }

    public function test_recommended_sort_places_unrated_listings_after_scored_tours(): void
    {
        $service = Mockery::mock(OfferCatalogPageService::class)->makePartial();
        $sort = new ReflectionMethod(OfferCatalogPageService::class, 'sortListingItems');
        $sort->setAccessible(true);

        $items = collect([
            ['type' => 'tour', 'id' => 4, 'created_at' => now(), 'price' => 120, 'rating' => 9.8, 'review_count' => 3],
            ['type' => 'tour', 'id' => 1, 'created_at' => now()->subDay(), 'price' => 100, 'rating' => 10.0, 'review_count' => 1],
            ['type' => 'trip', 'id' => 9, 'created_at' => now()->subHour(), 'price' => 90, 'rating' => null, 'review_count' => 0],
        ]);

        $recommended = $sort->invoke($service, $items, OfferListingFilter::fromRequest(['sortby' => 'recommended']));

        $this->assertSame([1, 4, 9], $recommended->pluck('id')->all());
    }
}
