<?php

namespace Tests\Unit\Vacation;

use App\Domain\Vacation\VacationListingFilter;
use App\Services\Vacation\VacationFilterApplicator;
use Illuminate\Database\Eloquent\Builder;
use Mockery;
use Tests\TestCase;

class VacationFilterApplicatorGuestsTest extends TestCase
{
    public function test_apply_trip_query_filters_by_group_size_max_when_guests_set(): void
    {
        $applicator = $this->app->make(VacationFilterApplicator::class);

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('where')->once()->with(Mockery::type(\Closure::class))->andReturnUsing(function ($closure) use ($query) {
            $inner = Mockery::mock(Builder::class);
            $inner->shouldReceive('whereNull')->once()->with('group_size_max')->andReturnSelf();
            $inner->shouldReceive('orWhere')->once()->with('group_size_max', '>=', 4)->andReturnSelf();
            $closure($inner);

            return $query;
        });

        $result = $applicator->applyToTripQuery($query, VacationListingFilter::fromRequest([
            'pillar' => 'trips',
            'num_guests' => '4',
        ]));

        $this->assertSame($query, $result);
    }

    public function test_apply_trip_query_skips_guests_filter_when_unset(): void
    {
        $applicator = $this->app->make(VacationFilterApplicator::class);

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('where')->never();

        $result = $applicator->applyToTripQuery($query, VacationListingFilter::fromRequest([
            'pillar' => 'trips',
        ]));

        $this->assertSame($query, $result);
    }

    public function test_apply_camp_query_filters_by_accommodation_capacity_when_guests_set(): void
    {
        $applicator = $this->app->make(VacationFilterApplicator::class);

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('whereHas')->once()->with('accommodations', Mockery::type(\Closure::class))->andReturnSelf();

        $result = $applicator->applyToCampQuery($query, VacationListingFilter::fromRequest([
            'pillar' => 'camps',
            'num_guests' => '6',
        ]));

        $this->assertSame($query, $result);
    }

    public function test_apply_camp_query_skips_guests_filter_when_unset(): void
    {
        $applicator = $this->app->make(VacationFilterApplicator::class);

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('whereHas')->never();

        $result = $applicator->applyToCampQuery($query, VacationListingFilter::fromRequest([
            'pillar' => 'camps',
        ]));

        $this->assertSame($query, $result);
    }
}
