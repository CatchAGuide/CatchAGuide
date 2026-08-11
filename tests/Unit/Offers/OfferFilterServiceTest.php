<?php

namespace Tests\Unit\Offers;

use App\Services\Offers\OfferFilterMapBuilder;
use App\Services\Offers\OfferFilterService;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class OfferFilterServiceTest extends TestCase
{
    public function test_listing_ids_for_species_unions_precomputed_maps(): void
    {
        Cache::flush();

        $map = [
            'tours' => [
                'targets' => [
                    5 => [10, 11],
                    8 => [11, 12],
                ],
            ],
            'trips' => [
                'targets' => [
                    5 => [100],
                ],
            ],
            'camps' => [
                'targets' => [],
            ],
            'targets_by_country' => [
                'spain' => [5, 8],
                'spanien' => [5, 8],
            ],
            'metadata' => [
                'generated_at' => now()->toISOString(),
                'total_tours' => 3,
                'total_trips' => 1,
                'total_camps' => 0,
                'counts' => [],
            ],
        ];

        $builder = Mockery::mock(OfferFilterMapBuilder::class);
        $builder->shouldReceive('fingerprint')->andReturn('test-fingerprint');
        $builder->shouldReceive('build')->andReturn($map);
        $this->app->instance(OfferFilterMapBuilder::class, $builder);

        $service = $this->app->make(OfferFilterService::class);

        $tourIds = $service->listingIdsForSpecies('tours', [5, 8]);
        sort($tourIds);

        $this->assertSame([10, 11, 12], $tourIds);
        $this->assertSame([100], $service->listingIdsForSpecies('trips', [5]));
        $this->assertSame([], $service->listingIdsForSpecies('camps', [5]));
        $this->assertNull($service->listingIdsForSpecies('tours', []));
    }

    public function test_target_ids_for_country_uses_precomputed_country_index(): void
    {
        Cache::flush();

        $map = [
            'tours' => ['targets' => [5 => [1], 9 => [2]]],
            'trips' => ['targets' => []],
            'camps' => ['targets' => [7 => [3]]],
            'targets_by_country' => [
                'spain' => [5, 7],
                'spanien' => [5, 7],
            ],
            'metadata' => [],
        ];

        $builder = Mockery::mock(OfferFilterMapBuilder::class);
        $builder->shouldReceive('fingerprint')->andReturn('country-fingerprint');
        $builder->shouldReceive('build')->andReturn($map);
        $this->app->instance(OfferFilterMapBuilder::class, $builder);

        $service = $this->app->make(OfferFilterService::class);
        $ids = $service->targetIdsForCountry('spain');
        sort($ids);

        $this->assertSame([5, 7], $ids);
    }
}
