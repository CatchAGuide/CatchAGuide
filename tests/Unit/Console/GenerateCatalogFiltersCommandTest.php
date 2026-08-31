<?php

namespace Tests\Unit\Console;

use App\Services\GuidingFilterService;
use App\Services\Offers\OfferFilterService;
use Mockery;
use Tests\TestCase;

class GenerateCatalogFiltersCommandTest extends TestCase
{
    public function test_all_scope_refreshes_guidings_and_offers(): void
    {
        $guidingFilters = Mockery::mock(GuidingFilterService::class);
        $guidingFilters->shouldReceive('refresh')->once()->andReturn([
            'targets' => [1 => [10]],
            'methods' => [],
            'water_types' => [],
            'duration_types' => [],
            'person_ranges' => [],
            'price_ranges' => [],
            'metadata' => ['total_guidings' => 1],
        ]);

        $offerFilters = Mockery::mock(OfferFilterService::class);
        $offerFilters->shouldReceive('refresh')->once()->andReturn([
            'tours' => ['targets' => [1 => [10]]],
            'trips' => ['targets' => []],
            'camps' => ['targets' => []],
            'metadata' => [
                'total_tours' => 1,
                'total_trips' => 0,
                'total_camps' => 0,
            ],
        ]);

        $this->app->instance(GuidingFilterService::class, $guidingFilters);
        $this->app->instance(OfferFilterService::class, $offerFilters);

        $this->artisan('catalog:generate-filters')
            ->assertSuccessful();
    }

    public function test_only_offers_skips_guidings(): void
    {
        $guidingFilters = Mockery::mock(GuidingFilterService::class);
        $guidingFilters->shouldReceive('refresh')->never();

        $offerFilters = Mockery::mock(OfferFilterService::class);
        $offerFilters->shouldReceive('refresh')->once()->andReturn([
            'tours' => ['targets' => []],
            'trips' => ['targets' => []],
            'camps' => ['targets' => []],
            'metadata' => [
                'total_tours' => 0,
                'total_trips' => 0,
                'total_camps' => 0,
            ],
        ]);

        $this->app->instance(GuidingFilterService::class, $guidingFilters);
        $this->app->instance(OfferFilterService::class, $offerFilters);

        $this->artisan('catalog:generate-filters', ['--only' => 'offers'])
            ->assertSuccessful();
    }

    public function test_deprecated_guidings_alias_delegates(): void
    {
        $guidingFilters = Mockery::mock(GuidingFilterService::class);
        $guidingFilters->shouldReceive('refresh')->once()->andReturn([
            'targets' => [],
            'methods' => [],
            'water_types' => [],
            'duration_types' => [],
            'person_ranges' => [],
            'price_ranges' => [],
            'metadata' => ['total_guidings' => 0],
        ]);

        $offerFilters = Mockery::mock(OfferFilterService::class);
        $offerFilters->shouldReceive('refresh')->never();

        $this->app->instance(GuidingFilterService::class, $guidingFilters);
        $this->app->instance(OfferFilterService::class, $offerFilters);

        $this->artisan('guidings:generate-filters')
            ->assertSuccessful();
    }
}
