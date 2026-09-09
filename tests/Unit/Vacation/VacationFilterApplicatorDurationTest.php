<?php

namespace Tests\Unit\Vacation;

use App\Domain\Vacation\VacationListingFilter;
use App\Services\Vacation\VacationFilterApplicator;
use Illuminate\Database\Eloquent\Builder;
use Mockery;
use Tests\TestCase;

class VacationFilterApplicatorDurationTest extends TestCase
{
    public function test_apply_trip_query_maps_duration_buckets(): void
    {
        $applicator = $this->app->make(VacationFilterApplicator::class);

        $between = Mockery::mock(Builder::class);
        $between->shouldReceive('whereBetween')->once()->with('duration_days', [1, 3])->andReturnSelf();
        $applicator->applyToTripQuery($between, VacationListingFilter::fromRequest([
            'pillar' => 'trips',
            'duration' => '1-3',
        ]));

        $mid = Mockery::mock(Builder::class);
        $mid->shouldReceive('whereBetween')->once()->with('duration_days', [4, 7])->andReturnSelf();
        $applicator->applyToTripQuery($mid, VacationListingFilter::fromRequest([
            'pillar' => 'trips',
            'duration' => '4-7',
        ]));

        $extended = Mockery::mock(Builder::class);
        $extended->shouldReceive('where')->once()->with('duration_days', '>=', 8)->andReturnSelf();
        $applicator->applyToTripQuery($extended, VacationListingFilter::fromRequest([
            'pillar' => 'trips',
            'duration' => '8+',
        ]));
    }

    public function test_apply_trip_query_skips_duration_when_unset(): void
    {
        $applicator = $this->app->make(VacationFilterApplicator::class);

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('whereBetween')->never();
        $query->shouldReceive('where')->never();

        $result = $applicator->applyToTripQuery($query, VacationListingFilter::fromRequest([
            'pillar' => 'trips',
        ]));

        $this->assertSame($query, $result);
    }

    public function test_apply_camp_query_maps_accommodation_guiding_and_boat_facets(): void
    {
        $applicator = $this->app->make(VacationFilterApplicator::class);

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('whereHas')->once()->with('accommodations', Mockery::type(\Closure::class))->andReturnSelf();
        $query->shouldReceive('whereHas')->once()->with('guidings')->andReturnSelf();
        $query->shouldReceive('whereDoesntHave')->once()->with('rentalBoats')->andReturnSelf();

        $applicator->applyToCampQuery($query, VacationListingFilter::fromRequest([
            'pillar' => 'camps',
            'accommodation_type' => '4',
            'has_guiding' => '1',
            'has_rental_boat' => '0',
        ]));
    }

    public function test_apply_camp_query_skips_facets_when_unset(): void
    {
        $applicator = $this->app->make(VacationFilterApplicator::class);

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('whereHas')->never();
        $query->shouldReceive('whereDoesntHave')->never();

        $result = $applicator->applyToCampQuery($query, VacationListingFilter::fromRequest([
            'pillar' => 'camps',
        ]));

        $this->assertSame($query, $result);
    }
}
