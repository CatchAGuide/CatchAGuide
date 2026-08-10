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
            ['type' => 'tour', 'model' => null, 'created_at' => now()->subDay(), 'price' => 200, 'rating' => 4.2, 'distance' => 12.0],
            ['type' => 'trip', 'model' => null, 'created_at' => now(), 'price' => 100, 'rating' => null, 'distance' => 3.0],
            ['type' => 'camp', 'model' => null, 'created_at' => now()->subDays(2), 'price' => 300, 'rating' => 4.9, 'distance' => 8.0],
        ]);

        $asc = $method->invoke($service, $items, OfferListingFilter::fromRequest(['sortby' => 'price-asc']));
        $this->assertSame([100.0, 200.0, 300.0], $asc->pluck('price')->map(fn ($p) => (float) $p)->all());

        $desc = $method->invoke($service, $items, OfferListingFilter::fromRequest(['sortby' => 'price-desc']));
        $this->assertSame([300.0, 200.0, 100.0], $desc->pluck('price')->map(fn ($p) => (float) $p)->all());

        $newest = $method->invoke($service, $items, OfferListingFilter::fromRequest(['sortby' => 'newest']));
        $this->assertSame('trip', $newest->first()['type']);

        $nearest = $method->invoke($service, $items, OfferListingFilter::fromRequest(['sortby' => 'nearest']));
        $this->assertSame(['trip', 'camp', 'tour'], $nearest->pluck('type')->all());

        $recommended = $method->invoke($service, $items, OfferListingFilter::fromRequest([]));
        $this->assertSame(['camp', 'tour', 'trip'], $recommended->pluck('type')->all());
    }
}
