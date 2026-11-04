<?php

namespace Tests\Unit\Guiding;

use App\Models\CategoryEntity;
use App\Models\Guiding;
use App\Services\Guiding\GuidingDestinationTrail;
use App\Services\Seo\CatalogInventoryGate;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Mockery;
use Tests\TestCase;

class GuidingDestinationTrailTest extends TestCase
{
    use DatabaseTransactions;

    private CategoryEntity $country;

    private CategoryEntity $region;

    private CategoryEntity $city;

    protected function setUp(): void
    {
        parent::setUp();

        URL::forceRootUrl('http://localhost');

        $this->country = CategoryEntity::create([
            'type' => 'country',
            'name' => 'Trailland',
            'slug' => 'trailland',
            'countrycode' => 'zz',
        ]);
        $this->region = CategoryEntity::create([
            'type' => 'region',
            'name' => 'Oberland',
            'slug' => 'oberland',
            'country_id' => $this->country->id,
            'filters' => ['region' => 'Upper Land'],
        ]);
        $this->city = CategoryEntity::create([
            'type' => 'city',
            'name' => 'Trailstadt',
            'slug' => 'trailstadt',
            'country_id' => $this->country->id,
            'region_id' => $this->region->id,
        ]);
    }

    public function test_links_country_region_and_city_when_all_are_indexable(): void
    {
        $trail = $this->trail(indexable: ['country', 'region', 'city'])->for($this->tour([
            'region' => 'Upper Land',
            'city' => 'trailstadt',
        ]));

        $this->assertSame([
            'http://localhost/guidings/trailland',
            'http://localhost/guidings/trailland/oberland',
            'http://localhost/guidings/trailland/oberland/trailstadt',
        ], array_column($trail, 'url'));
        $this->assertSame('Oberland', $trail[1]['label']);
    }

    public function test_stops_before_a_noindexed_level(): void
    {
        $trail = $this->trail(indexable: ['country'])->for($this->tour([
            'region' => 'Oberland',
            'city' => 'Trailstadt',
        ]));

        $this->assertSame(['http://localhost/guidings/trailland'], array_column($trail, 'url'));
    }

    public function test_is_empty_when_the_country_page_is_not_indexable(): void
    {
        $this->assertSame([], $this->trail(indexable: [])->for($this->tour()));
    }

    public function test_matches_the_country_by_iso_code_and_ignores_an_unknown_region(): void
    {
        $trail = $this->trail(indexable: ['country', 'region', 'city'])->for($this->tour([
            'country' => 'Some Other Spelling',
            'region' => 'Nowhere',
        ]));

        $this->assertSame(['http://localhost/guidings/trailland'], array_column($trail, 'url'));
    }

    /**
     * @param  list<string>  $indexable  levels the gate lets through
     */
    private function trail(array $indexable): GuidingDestinationTrail
    {
        $gate = Mockery::mock(CatalogInventoryGate::class);
        $gate->shouldReceive('guidingDestinationIndexable')->andReturnUsing(
            function (CategoryEntity $country, ?CategoryEntity $region = null, ?CategoryEntity $city = null) use ($indexable) {
                $level = $city ? 'city' : ($region ? 'region' : 'country');

                return in_array($level, $indexable, true);
            }
        );
        $this->app->instance(CatalogInventoryGate::class, $gate);

        return $this->app->make(GuidingDestinationTrail::class);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function tour(array $attributes = []): Guiding
    {
        return (new Guiding())->forceFill(array_merge([
            'country' => 'Trailland',
            'country_iso' => 'ZZ',
        ], $attributes));
    }
}
