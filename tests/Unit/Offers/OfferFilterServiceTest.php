<?php

namespace Tests\Unit\Offers;

use App\Models\Target;
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

        $map = $this->baseMap([
            'tours' => [
                'targets' => [
                    5 => [10, 11],
                    8 => [11, 12],
                ],
                'custom_targets' => [],
            ],
            'trips' => [
                'targets' => [
                    5 => [100],
                ],
                'custom_targets' => [],
            ],
            'camps' => [
                'targets' => [],
                'custom_targets' => [],
            ],
            'targets_by_country' => [
                'spain' => [5, 8],
                'spanien' => [5, 8],
            ],
        ]);

        $this->bindMap($map, 'test-fingerprint');

        $service = $this->app->make(OfferFilterService::class);

        $tourIds = $service->listingIdsForSpecies('tours', [5, 8]);
        sort($tourIds);

        $this->assertSame([10, 11, 12], $tourIds);
        $this->assertSame([100], $service->listingIdsForSpecies('trips', [5]));
        $this->assertSame([], $service->listingIdsForSpecies('camps', [5]));
        $this->assertNull($service->listingIdsForSpecies('tours', []));
    }

    public function test_listing_ids_for_custom_species_uses_custom_map(): void
    {
        Cache::flush();

        $map = $this->baseMap([
            'tours' => [
                'targets' => [
                    5 => [10],
                ],
                'custom_targets' => [
                    'thunfisch' => [20, 21],
                    'amberjack' => [21, 22],
                ],
            ],
            'custom_target_labels' => [
                'thunfisch' => 'Thunfisch',
                'amberjack' => 'Amberjack',
            ],
        ]);

        $this->bindMap($map, 'custom-fingerprint');

        $service = $this->app->make(OfferFilterService::class);
        $ids = $service->listingIdsForCustomSpecies('tours', ['Thunfisch', 'AMBERJACK']);
        sort($ids);

        $this->assertSame([20, 21, 22], $ids);
        $this->assertNull($service->listingIdsForCustomSpecies('tours', []));
        $this->assertSame([], $service->listingIdsForCustomSpecies('trips', ['Thunfisch']));
    }

    public function test_target_ids_for_country_uses_precomputed_country_index(): void
    {
        Cache::flush();

        $map = $this->baseMap([
            'tours' => ['targets' => [5 => [1], 9 => [2]], 'custom_targets' => []],
            'trips' => ['targets' => [], 'custom_targets' => []],
            'camps' => ['targets' => [7 => [3]], 'custom_targets' => []],
            'targets_by_country' => [
                'spain' => [5, 7],
                'spanien' => [5, 7],
            ],
        ]);

        $this->bindMap($map, 'country-fingerprint');

        $service = $this->app->make(OfferFilterService::class);
        $ids = $service->targetIdsForCountry('spain');
        sort($ids);

        $this->assertSame([5, 7], $ids);
    }

    public function test_species_options_include_catalog_and_custom_labels(): void
    {
        Cache::flush();

        Target::query()->delete();
        $pike = new Target;
        $pike->forceFill([
            'name' => 'Hecht',
            'name_en' => 'Pike',
        ]);
        $pike->save();

        $map = $this->baseMap([
            'tours' => [
                'targets' => [
                    (int) $pike->id => [1],
                ],
                'custom_targets' => [
                    'thunfisch' => [2],
                ],
            ],
            'custom_target_labels' => [
                'thunfisch' => 'Thunfisch',
            ],
        ]);

        $this->bindMap($map, 'options-fingerprint');

        app()->setLocale('en');
        $service = $this->app->make(OfferFilterService::class);
        $options = $service->speciesOptions()->values()->all();

        $this->assertContains([
            'id' => (int) $pike->id,
            'name' => 'Pike',
        ], $options);
        $this->assertContains([
            'id' => 'Thunfisch',
            'name' => 'Thunfisch',
        ], $options);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function baseMap(array $overrides = []): array
    {
        return array_replace_recursive([
            'tours' => ['targets' => [], 'custom_targets' => []],
            'trips' => ['targets' => [], 'custom_targets' => []],
            'camps' => ['targets' => [], 'custom_targets' => []],
            'targets_by_country' => [],
            'custom_targets_by_country' => [],
            'custom_target_labels' => [],
            'metadata' => [
                'generated_at' => now()->toISOString(),
                'total_tours' => 0,
                'total_trips' => 0,
                'total_camps' => 0,
                'counts' => [],
            ],
        ], $overrides);
    }

    private function bindMap(array $map, string $fingerprint): void
    {
        $builder = Mockery::mock(OfferFilterMapBuilder::class);
        $builder->shouldReceive('fingerprint')->andReturn($fingerprint);
        $builder->shouldReceive('build')->andReturn($map);
        $this->app->instance(OfferFilterMapBuilder::class, $builder);
    }
}
