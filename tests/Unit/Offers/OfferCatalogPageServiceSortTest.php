<?php

namespace Tests\Unit\Offers;

use App\Domain\Offers\OfferListingFilter;
use App\Services\Offers\OfferCatalogPageService;
use Mockery;
use ReflectionMethod;
use Tests\TestCase;

class OfferCatalogPageServiceSortTest extends TestCase
{
    public function test_sort_listing_items_by_price_and_newest(): void
    {
        $service = Mockery::mock(OfferCatalogPageService::class)->makePartial();
        $method = new ReflectionMethod(OfferCatalogPageService::class, 'sortListingItems');
        $method->setAccessible(true);

        $items = collect([
            ['type' => 'tour', 'model' => null, 'created_at' => now()->subDay(), 'price' => 200],
            ['type' => 'trip', 'model' => null, 'created_at' => now(), 'price' => 100],
            ['type' => 'camp', 'model' => null, 'created_at' => now()->subDays(2), 'price' => 300],
        ]);

        $asc = $method->invoke($service, $items, OfferListingFilter::fromRequest(['sortby' => 'price-asc']));
        $this->assertSame([100.0, 200.0, 300.0], $asc->pluck('price')->map(fn ($p) => (float) $p)->all());

        $desc = $method->invoke($service, $items, OfferListingFilter::fromRequest(['sortby' => 'price-desc']));
        $this->assertSame([300.0, 200.0, 100.0], $desc->pluck('price')->map(fn ($p) => (float) $p)->all());

        $newest = $method->invoke($service, $items, OfferListingFilter::fromRequest([]));
        $this->assertSame('trip', $newest->first()['type']);
    }
}
