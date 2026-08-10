<?php

namespace Tests\Unit\Offers;

use App\Domain\Offers\OfferListingFilter;
use App\Services\GuidingFilterService;
use App\Services\Offers\OfferCatalogPageService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Mockery;
use ReflectionMethod;
use Tests\TestCase;

class OfferCatalogPageServiceFacetTest extends TestCase
{
    public function test_apply_tour_facets_intersects_guiding_ids(): void
    {
        $guidingFilters = Mockery::mock(GuidingFilterService::class);
        $guidingFilters->shouldReceive('getFilteredGuidingIds')
            ->once()
            ->with(Mockery::on(function (Request $request) {
                return $request->input('methods') === ['5']
                    && $request->input('water') === ['9']
                    && $request->input('duration_types') === ['half_day'];
            }))
            ->andReturn([11, 22]);

        $service = $this->app->make(OfferCatalogPageService::class);
        $this->setPrivate($service, 'guidingFilters', $guidingFilters);

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('whereIn')->once()->with('guidings.id', [11, 22])->andReturnSelf();

        $this->invoke($service, 'applyTourFacets', $query, OfferListingFilter::fromRequest([
            'type' => 'tour',
            'methods' => '5',
            'water' => '9',
            'duration_types' => 'half_day',
        ]));
    }

    public function test_apply_tour_facets_skips_when_not_tour_view(): void
    {
        $guidingFilters = Mockery::mock(GuidingFilterService::class);
        $guidingFilters->shouldReceive('getFilteredGuidingIds')->never();

        $service = $this->app->make(OfferCatalogPageService::class);
        $this->setPrivate($service, 'guidingFilters', $guidingFilters);

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('whereIn')->never();

        $this->invoke($service, 'applyTourFacets', $query, OfferListingFilter::fromRequest([
            'type' => 'vacation',
            'methods' => '5',
        ]));
    }

    public function test_apply_trip_facets_maps_duration_buckets(): void
    {
        $service = $this->app->make(OfferCatalogPageService::class);

        $between = Mockery::mock(Builder::class);
        $between->shouldReceive('whereBetween')->once()->with('duration_days', [4, 7])->andReturnSelf();
        $this->invoke($service, 'applyTripFacets', $between, OfferListingFilter::fromRequest([
            'type' => 'vacation',
            'vacation' => 'trip',
            'duration' => '4-7',
        ]));

        $extended = Mockery::mock(Builder::class);
        $extended->shouldReceive('where')->once()->with('duration_days', '>=', 8)->andReturnSelf();
        $this->invoke($service, 'applyTripFacets', $extended, OfferListingFilter::fromRequest([
            'type' => 'vacation',
            'vacation' => 'trip',
            'duration' => '8+',
        ]));
    }

    public function test_apply_camp_facets_uses_where_has_helpers(): void
    {
        $service = $this->app->make(OfferCatalogPageService::class);

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('whereHas')->once()->with('accommodations', Mockery::type(\Closure::class))->andReturnSelf();
        $query->shouldReceive('whereHas')->once()->with('guidings')->andReturnSelf();
        $query->shouldReceive('whereDoesntHave')->once()->with('rentalBoats')->andReturnSelf();

        $this->invoke($service, 'applyCampFacets', $query, OfferListingFilter::fromRequest([
            'type' => 'vacation',
            'vacation' => 'camp',
            'accommodation_type' => '4',
            'has_guiding' => '1',
            'has_rental_boat' => '0',
        ]));
    }

    private function invoke(OfferCatalogPageService $service, string $method, mixed ...$args): mixed
    {
        $reflection = new ReflectionMethod(OfferCatalogPageService::class, $method);
        $reflection->setAccessible(true);

        return $reflection->invoke($service, ...$args);
    }

    private function setPrivate(object $service, string $property, mixed $value): void
    {
        $reflection = new \ReflectionProperty($service, $property);
        $reflection->setAccessible(true);
        $reflection->setValue($service, $value);
    }
}
