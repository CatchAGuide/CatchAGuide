<?php

namespace Tests\Unit\Offers;

use App\Domain\Offers\OfferListingFilter;
use App\Services\Location\GeospatialSearchService;
use App\Services\Offers\OfferCatalogPageService;
use Illuminate\Database\Eloquent\Builder;
use Mockery;
use ReflectionMethod;
use ReflectionProperty;
use Tests\TestCase;

class OfferCatalogPageServiceGeoTest extends TestCase
{
    public function test_country_scope_skips_tight_radius_when_country_filter_present(): void
    {
        $geo = Mockery::mock(GeospatialSearchService::class);
        $geo->shouldReceive('normalizePlaceTypes')->andReturn(['country']);
        $geo->shouldReceive('detectScope')->andReturn(GeospatialSearchService::SCOPE_COUNTRY);
        $geo->shouldReceive('normalizeBounds')->never();

        $service = $this->serviceWithGeo($geo);
        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('whereNotNull')->never();
        $query->shouldReceive('whereRaw')->never();
        $query->shouldReceive('whereBetween')->never();

        $result = $this->invokeApplyListingGeo($service, $query, OfferListingFilter::fromRequest([
            'place' => 'Spain',
            'country' => 'spain',
            'placeLat' => '40.4',
            'placeLng' => '-3.7',
            'place_types' => '["country"]',
        ]));

        $this->assertSame($query, $result);
    }

    public function test_city_scope_applies_distance_filter(): void
    {
        $geo = Mockery::mock(GeospatialSearchService::class);
        $geo->shouldReceive('normalizePlaceTypes')->andReturn([]);
        $geo->shouldReceive('detectScope')->andReturn(GeospatialSearchService::SCOPE_CITY);
        $geo->shouldReceive('normalizeBounds')->andReturn(null);

        $service = $this->serviceWithGeo($geo);
        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('whereNotNull')->with('latitude')->once()->andReturnSelf();
        $query->shouldReceive('whereNotNull')->with('longitude')->once()->andReturnSelf();
        $query->shouldReceive('whereRaw')->once()->andReturnSelf();

        $result = $this->invokeApplyListingGeo($service, $query, OfferListingFilter::fromRequest([
            'place' => 'Madrid',
            'placeLat' => '40.4',
            'placeLng' => '-3.7',
            'city' => 'Madrid',
            'country' => 'spain',
        ]));

        $this->assertSame($query, $result);
    }

    public function test_bbox_is_preferred_over_radius(): void
    {
        $geo = Mockery::mock(GeospatialSearchService::class);
        $geo->shouldReceive('normalizePlaceTypes')->andReturn(['administrative_area_level_1']);
        $geo->shouldReceive('detectScope')->andReturn(GeospatialSearchService::SCOPE_REGION);
        $geo->shouldReceive('normalizeBounds')->andReturn([
            'ne_lat' => 42.0,
            'ne_lng' => 3.0,
            'sw_lat' => 40.0,
            'sw_lng' => 1.0,
        ]);

        $service = $this->serviceWithGeo($geo);
        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('whereNotNull')->with('latitude')->once()->andReturnSelf();
        $query->shouldReceive('whereNotNull')->with('longitude')->once()->andReturnSelf();
        $query->shouldReceive('whereBetween')->with('latitude', [40.0, 42.0])->once()->andReturnSelf();
        $query->shouldReceive('whereBetween')->with('longitude', [1.0, 3.0])->once()->andReturnSelf();
        $query->shouldReceive('whereRaw')->never();

        $result = $this->invokeApplyListingGeo($service, $query, OfferListingFilter::fromRequest([
            'place' => 'Catalonia',
            'placeLat' => '41.0',
            'placeLng' => '2.0',
            'bounds_ne_lat' => '42',
            'bounds_ne_lng' => '3',
            'bounds_sw_lat' => '40',
            'bounds_sw_lng' => '1',
        ]));

        $this->assertSame($query, $result);
    }

    private function serviceWithGeo(GeospatialSearchService $geo): OfferCatalogPageService
    {
        $service = $this->app->make(OfferCatalogPageService::class);
        $property = new ReflectionProperty(OfferCatalogPageService::class, 'geoSearch');
        $property->setAccessible(true);
        $property->setValue($service, $geo);

        return $service;
    }

    private function invokeApplyListingGeo(
        OfferCatalogPageService $service,
        Builder $query,
        OfferListingFilter $filter,
    ): Builder {
        $method = new ReflectionMethod(OfferCatalogPageService::class, 'applyListingGeo');
        $method->setAccessible(true);

        return $method->invoke($service, $query, $filter, 'latitude', 'longitude');
    }
}
