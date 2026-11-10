<?php

namespace Tests\Unit\Offers;

use App\Domain\Offers\OfferListingFilter;
use App\Services\Offers\OfferCatalogPageService;
use Mockery;
use ReflectionMethod;
use Tests\TestCase;

class ListingPageSizeTest extends TestCase
{
    public function test_catalog_page_size_follows_the_listing_type(): void
    {
        config([
            'offers.per_page' => 9,
            'offers.tour_per_page' => 24,
            'offers.vacation_per_page' => 18,
        ]);

        $service = Mockery::mock(OfferCatalogPageService::class)->makePartial();
        $method = new ReflectionMethod(OfferCatalogPageService::class, 'perPageFor');
        $method->setAccessible(true);

        $this->assertSame(24, $method->invoke($service, OfferListingFilter::fromRequest(['type' => 'tour'])));
        $this->assertSame(18, $method->invoke($service, OfferListingFilter::fromRequest(['type' => 'vacation'])));
        $this->assertSame(18, $method->invoke($service, OfferListingFilter::fromRequest(['type' => 'vacation', 'vacation' => 'camp'])));
        $this->assertSame(9, $method->invoke($service, OfferListingFilter::fromRequest(['type' => 'all'])));
    }

    public function test_vacation_listing_pages_use_the_longer_page_size(): void
    {
        $this->assertSame(18, (int) config('vacations.country_page_per_page'));
        $this->assertSame(18, (int) config('vacations.pillar_index_per_page'));
    }
}
