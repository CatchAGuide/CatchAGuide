<?php

namespace Tests\Unit\Offers;

use App\Domain\Offers\OfferListingFilter;
use App\Models\Country;
use App\Repositories\Vacation\VacationDestinationRepository;
use App\Services\Offers\OfferCatalogPageService;
use Mockery;
use ReflectionMethod;
use ReflectionProperty;
use Tests\TestCase;

class OfferCatalogNearbyTest extends TestCase
{
    public function test_nearby_origin_uses_country_centroid_when_place_coords_missing(): void
    {
        $country = new Country([
            'name' => 'Latvia',
            'slug' => 'lettland',
            'filters' => [
                'placeLat' => '56.88',
                'placeLng' => '24.60',
            ],
        ]);

        $filter = OfferListingFilter::fromRequest([
            'type' => 'vacation',
            'vacation' => 'camp',
            'country' => 'lettland',
        ]);

        $destinations = Mockery::mock(VacationDestinationRepository::class);
        $destinations->shouldReceive('findCountryForLocale')
            ->once()
            ->with($filter->country)
            ->andReturn($country);

        $origin = $this->invoke(
            $this->serviceWithDestinations($destinations),
            'resolveNearbyOrigin',
            $filter,
            false,
        );

        $this->assertSame(56.88, $origin['lat']);
        $this->assertSame(24.60, $origin['lng']);
        $this->assertTrue($origin['country_scope']);
    }

    public function test_nearby_origin_is_null_when_country_has_no_centroid(): void
    {
        $country = new Country([
            'name' => 'Latvia',
            'slug' => 'lettland',
            'filters' => [],
        ]);

        $filter = OfferListingFilter::fromRequest(['country' => 'lettland']);
        $destinations = Mockery::mock(VacationDestinationRepository::class);
        $destinations->shouldReceive('findCountryForLocale')
            ->once()
            ->with($filter->country)
            ->andReturn($country);

        $origin = $this->invoke(
            $this->serviceWithDestinations($destinations),
            'resolveNearbyOrigin',
            $filter,
            false,
        );

        $this->assertNull($origin);
    }

    public function test_nearby_origin_prefers_explicit_place_coords(): void
    {
        $destinations = Mockery::mock(VacationDestinationRepository::class);
        $destinations->shouldReceive('findCountryForLocale')->never();

        $origin = $this->invoke(
            $this->serviceWithDestinations($destinations),
            'resolveNearbyOrigin',
            OfferListingFilter::fromRequest([
                'place' => 'Latvia',
                'country' => 'lettland',
                'placeLat' => '56.95',
                'placeLng' => '24.10',
                'place_types' => '["country"]',
            ]),
            true,
        );

        $this->assertSame(56.95, $origin['lat']);
        $this->assertSame(24.10, $origin['lng']);
        $this->assertTrue($origin['country_scope']);
    }

    public function test_nearby_radius_is_wider_for_country_scope(): void
    {
        $service = $this->app->make(OfferCatalogPageService::class);

        $this->assertSame(200000, $this->invoke($service, 'nearbyRadiusMeters', 'tour', false));
        $this->assertSame(200000, $this->invoke($service, 'nearbyRadiusMeters', 'trip', false));
        $this->assertSame(200000, $this->invoke($service, 'nearbyRadiusMeters', 'camp', false));
        $this->assertSame(400000, $this->invoke($service, 'nearbyRadiusMeters', 'camp', true));
    }

    public function test_nearby_city_radius_can_differ_per_product_type(): void
    {
        config([
            'location_search.nearby_radius_km.tour' => 120,
            'location_search.nearby_radius_km.trip' => 80,
            'location_search.nearby_radius_km.camp' => 200,
        ]);

        $service = $this->app->make(OfferCatalogPageService::class);

        $this->assertSame(120000, $this->invoke($service, 'nearbyRadiusMeters', 'tour', false));
        $this->assertSame(80000, $this->invoke($service, 'nearbyRadiusMeters', 'trip', false));
        $this->assertSame(200000, $this->invoke($service, 'nearbyRadiusMeters', 'camp', false));
    }

    private function serviceWithDestinations(VacationDestinationRepository $destinations): OfferCatalogPageService
    {
        $service = $this->app->make(OfferCatalogPageService::class);
        $property = new ReflectionProperty(OfferCatalogPageService::class, 'destinations');
        $property->setAccessible(true);
        $property->setValue($service, $destinations);

        return $service;
    }

    private function invoke(OfferCatalogPageService $service, string $method, mixed ...$args): mixed
    {
        $reflection = new ReflectionMethod(OfferCatalogPageService::class, $method);
        $reflection->setAccessible(true);

        return $reflection->invoke($service, ...$args);
    }
}
