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
            ['type' => 'tour', 'model' => null, 'created_at' => now()->subDay(), 'price' => 200, 'rating' => 8.2, 'distance' => 12.0],
            ['type' => 'trip', 'model' => null, 'created_at' => now(), 'price' => 100, 'rating' => null, 'distance' => 3.0],
            ['type' => 'camp', 'model' => null, 'created_at' => now()->subDays(2), 'price' => 300, 'rating' => null, 'distance' => 8.0],
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

    public function test_bayesian_recommended_score_favors_volume_over_sparse_perfect_avg(): void
    {
        $service = Mockery::mock(OfferCatalogPageService::class)->makePartial();
        $method = new ReflectionMethod(OfferCatalogPageService::class, 'bayesianRecommendedScore');
        $method->setAccessible(true);

        // 1× perfect 10 vs 20× solid 9.0 — volume should win under Bayesian prior (m=7, C=5).
        $sparsePerfect = $method->invoke($service, 10.0, 1);
        $strongVolume = $method->invoke($service, 9.0, 20);

        $this->assertGreaterThan($sparsePerfect, $strongVolume);

        // Expected: (5*7 + 10*1)/(5+1) = 45/6 = 7.5
        $this->assertEqualsWithDelta(7.5, $sparsePerfect, 0.0001);
        // Expected: (5*7 + 9*20)/(5+20) = 215/25 = 8.6
        $this->assertEqualsWithDelta(8.6, $strongVolume, 0.0001);
    }

    public function test_recommended_sort_orders_by_bayesian_score_desc(): void
    {
        $service = Mockery::mock(OfferCatalogPageService::class)->makePartial();
        $sort = new ReflectionMethod(OfferCatalogPageService::class, 'sortListingItems');
        $sort->setAccessible(true);
        $score = new ReflectionMethod(OfferCatalogPageService::class, 'bayesianRecommendedScore');
        $score->setAccessible(true);

        $highVolume = $score->invoke($service, 9.0, 20);
        $sparsePerfect = $score->invoke($service, 10.0, 1);

        $items = collect([
            ['type' => 'tour', 'model' => null, 'created_at' => now()->subDay(), 'price' => 100, 'rating' => $sparsePerfect],
            ['type' => 'tour', 'model' => null, 'created_at' => now(), 'price' => 120, 'rating' => $highVolume],
            ['type' => 'trip', 'model' => null, 'created_at' => now()->subHour(), 'price' => 90, 'rating' => null],
        ]);

        $recommended = $sort->invoke($service, $items, OfferListingFilter::fromRequest(['sortby' => 'recommended']));
        $this->assertEqualsWithDelta($highVolume, $recommended->first()['rating'], 0.0001);
        $this->assertEqualsWithDelta($sparsePerfect, $recommended->get(1)['rating'], 0.0001);
        $this->assertNull($recommended->last()['rating']);
    }
}
