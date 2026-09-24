<?php

namespace Tests\Unit\Sitemap;

use App\Models\CategoryEntity;
use App\Models\CategoryPage;
use App\Services\Seo\CatalogInventoryGate;
use App\Services\Sitemap\CategoryPageSitemapSource;
use App\Services\Sitemap\Contributors\TourFacetSitemapContributor;
use App\Services\Sitemap\Contributors\TourGeoSitemapContributor;
use App\Services\Sitemap\SitemapContext;
use App\Services\Sitemap\SitemapEntry;
use App\Services\Sitemap\SitemapLastmod;
use App\Services\Sitemap\SitemapListingFreshness;
use App\Services\Sitemap\SitemapPathEncoder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\TestCase;

/**
 * sitemap-facets-tours and sitemap-geo-tours: which tour hub/facet/geo URLs are listed, gated
 * by CatalogInventoryGate / CategoryPageSitemapSource (mocked here — their own rules are tested
 * in CatalogInventoryGateTest and CategoryPageSitemapSourceTest).
 */
class TourSitemapContributorsTest extends TestCase
{
    use DatabaseTransactions;

    private const BASE = 'https://www.catchaguide.com';

    /**
     * @param  callable(CategoryEntity, ?CategoryEntity, ?CategoryEntity): bool  $indexable
     */
    private function gate(callable $indexable): CatalogInventoryGate
    {
        $gate = Mockery::mock(CatalogInventoryGate::class);
        $gate->shouldReceive('guidingDestinationIndexable')->andReturnUsing(
            fn (CategoryEntity $country, ?CategoryEntity $region = null, ?CategoryEntity $city = null) => $indexable($country, $region, $city)
        );

        return $gate;
    }

    private function noCategoryPages(): CategoryPageSitemapSource
    {
        $source = Mockery::mock(CategoryPageSitemapSource::class);
        $source->shouldReceive('targetPages')->andReturn(collect());
        $source->shouldReceive('methodPages')->andReturn(collect());

        return $source;
    }

    private function locs(iterable $entries): array
    {
        return collect($entries)->map(fn (SitemapEntry $entry) => $entry->loc)->all();
    }

    public function test_facets_file_lists_hubs_and_only_gated_countries_under_canonical_slug(): void
    {
        $open = CategoryEntity::create(['type' => 'country', 'name' => 'Österreich', 'slug' => 'Österreich-'.uniqid()]);
        $closed = CategoryEntity::create(['type' => 'country', 'name' => 'Leer', 'slug' => 'leer-'.uniqid()]);

        $contributor = new TourFacetSitemapContributor(
            new SitemapPathEncoder(),
            $this->gate(fn (CategoryEntity $country) => $country->is($open)),
            $this->noCategoryPages(),
            app(SitemapLastmod::class),
            app(SitemapListingFreshness::class),
        );
        $locs = $this->locs($contributor->entries(new SitemapContext(self::BASE, 'de')));

        $this->assertContains(self::BASE.'/guidings/countries', $locs);
        $this->assertContains(self::BASE.'/guidings/targets', $locs);
        $this->assertContains(self::BASE.'/guidings/methods', $locs);
        // Stored lowercase by CategoryEntity's saving hook, so no uppercase variant can leak.
        $this->assertContains(self::BASE.'/guidings/'.rawurlencode(mb_strtolower($open->slug)), $locs);
        $this->assertSame(mb_strtolower($open->slug), $open->fresh()->slug);
        $this->assertNotContains(self::BASE.'/guidings/'.$closed->slug, $locs);
    }

    public function test_facets_file_lists_only_live_species_and_method_pages(): void
    {
        $liveSpecies = new CategoryPage(['type' => 'Targets', 'slug' => 'zander']);
        $deadSpecies = new CategoryPage(['type' => 'Targets', 'slug' => 'wels']);
        $liveMethod = new CategoryPage(['type' => 'Methods', 'slug' => 'spinnfischen']);
        $deadMethod = new CategoryPage(['type' => 'Methods', 'slug' => 'eisangeln']);

        $source = Mockery::mock(CategoryPageSitemapSource::class);
        $source->shouldReceive('targetPages')->andReturn(collect([$liveSpecies, $deadSpecies]));
        $source->shouldReceive('methodPages')->andReturn(collect([$liveMethod, $deadMethod]));
        $source->shouldReceive('tourTargetIsLive')->andReturnUsing(fn (CategoryPage $page) => $page === $liveSpecies);
        $source->shouldReceive('methodIsLive')->andReturnUsing(fn (CategoryPage $page) => $page === $liveMethod);
        $source->shouldReceive('lastmod')->andReturn('2026-01-01T00:00:00+00:00');

        $contributor = new TourFacetSitemapContributor(
            new SitemapPathEncoder(),
            $this->gate(fn () => false),
            $source,
            app(SitemapLastmod::class),
            app(SitemapListingFreshness::class),
        );
        $entries = $contributor->entries(new SitemapContext(self::BASE, 'de'))->keyBy('loc');

        $this->assertTrue($entries->has(self::BASE.'/guidings/targets/zander'));
        $this->assertSame('2026-01-01T00:00:00+00:00', $entries[self::BASE.'/guidings/targets/zander']->lastmod);
        $this->assertFalse($entries->has(self::BASE.'/guidings/targets/wels'));
        $this->assertTrue($entries->has(self::BASE.'/guidings/methods/spinnfischen'));
        $this->assertFalse($entries->has(self::BASE.'/guidings/methods/eisangeln'));
    }

    public function test_geo_file_lists_regions_and_cities_that_clear_the_gate(): void
    {
        $country = CategoryEntity::create(['type' => 'country', 'name' => 'Geo Land', 'slug' => 'geo-land-'.uniqid()]);
        $region = CategoryEntity::create(['type' => 'region', 'name' => 'Nord', 'slug' => 'nord-'.uniqid(), 'country_id' => $country->id]);
        $thinRegion = CategoryEntity::create(['type' => 'region', 'name' => 'Süd', 'slug' => 'sued-'.uniqid(), 'country_id' => $country->id]);
        $city = CategoryEntity::create(['type' => 'city', 'name' => 'Hafen', 'slug' => 'hafen-'.uniqid(), 'country_id' => $country->id, 'region_id' => $region->id]);
        $thinCity = CategoryEntity::create(['type' => 'city', 'name' => 'Dorf', 'slug' => 'dorf-'.uniqid(), 'country_id' => $country->id, 'region_id' => $region->id]);

        $gate = $this->gate(function (CategoryEntity $c, ?CategoryEntity $r = null, ?CategoryEntity $ci = null) use ($country, $region, $city) {
            if (! $c->is($country)) {
                return false;
            }

            return match (true) {
                $ci !== null => $ci->is($city),
                $r !== null => $r->is($region),
                default => true,
            };
        });

        $contributor = new TourGeoSitemapContributor(new SitemapPathEncoder(), $gate, app(SitemapLastmod::class));
        $locs = $this->locs($contributor->entries(new SitemapContext(self::BASE, 'de')));

        $this->assertContains(self::BASE."/guidings/{$country->slug}/{$region->slug}", $locs);
        $this->assertContains(self::BASE."/guidings/{$country->slug}/{$region->slug}/{$city->slug}", $locs);
        $this->assertNotContains(self::BASE."/guidings/{$country->slug}/{$thinRegion->slug}", $locs);
        $this->assertNotContains(self::BASE."/guidings/{$country->slug}/{$region->slug}/{$thinCity->slug}", $locs);
        // Country pages live in the facets file, not the geo file.
        $this->assertNotContains(self::BASE."/guidings/{$country->slug}", $locs);
    }
}
