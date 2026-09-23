<?php

namespace Tests\Unit\Sitemap;

use App\Models\CategoryEntity;
use App\Models\CategoryPage;
use App\Repositories\Vacation\VacationDestinationRepository;
use App\Services\Seo\CatalogInventoryGate;
use App\Services\Sitemap\CategoryPageSitemapSource;
use App\Services\Sitemap\Contributors\GlobalFacetSitemapContributor;
use App\Services\Sitemap\Contributors\HolidayFacetSitemapContributor;
use App\Services\Sitemap\SitemapContext;
use App\Services\Sitemap\SitemapEntry;
use App\Services\Sitemap\SitemapLastmod;
use App\Services\Sitemap\SitemapListingFreshness;
use App\Services\Sitemap\SitemapPathEncoder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\TestCase;

class HolidayAndGlobalSitemapContributorsTest extends TestCase
{
    use DatabaseTransactions;

    private const BASE = 'https://www.catchaguide.de';

    private function locs(iterable $entries): array
    {
        return collect($entries)->map(fn (SitemapEntry $entry) => $entry->loc)->all();
    }

    /**
     * The pillar facets live on path segments now (/vacations/camps/countries,
     * /vacations/camps/targets/{species}) — never ?pillar= or ?vacation= — and each pillar×country
     * page is gated on that pillar's own inventory.
     */
    public function test_holiday_facets_use_pillar_paths_and_gate_each_pillar_separately(): void
    {
        $destinations = Mockery::mock(VacationDestinationRepository::class);
        $destinations->shouldReceive('countriesForHubGrid')->andReturn(collect([
            ['slug' => 'norwegen', 'destination' => null, 'camps' => 3, 'trips' => 0],
            ['slug' => 'Malediven', 'destination' => null, 'camps' => 0, 'trips' => 0],
        ]));

        $gate = Mockery::mock(CatalogInventoryGate::class);
        $gate->shouldReceive('vacationCountryIndexable')->andReturnUsing(
            fn (string $slug, ?string $pillar = null) => $slug === 'norwegen' && $pillar !== 'trips'
        );

        $zander = new CategoryPage(['type' => 'Targets', 'slug' => 'zander']);
        $source = Mockery::mock(CategoryPageSitemapSource::class);
        $source->shouldReceive('targetPages')->andReturn(collect([$zander]));
        $source->shouldReceive('vacationTargetIsLive')->andReturnUsing(
            fn (CategoryPage $page, string $lang, ?string $pillar = null) => $pillar !== 'trip'
        );
        $source->shouldReceive('lastmod')->andReturn(null);

        $contributor = new HolidayFacetSitemapContributor(
            new SitemapPathEncoder(),
            $gate,
            $destinations,
            $source,
            app(SitemapLastmod::class),
            app(SitemapListingFreshness::class),
        );
        $locs = $this->locs($contributor->entries(new SitemapContext(self::BASE, 'de')));

        foreach (['/vacations/countries', '/vacations/targets', '/vacations/camps/countries', '/vacations/trips/countries'] as $hub) {
            $this->assertContains(self::BASE.$hub, $locs);
        }
        $this->assertContains(self::BASE.'/vacations/norwegen', $locs);
        $this->assertContains(self::BASE.'/vacations/camps/norwegen', $locs);
        $this->assertNotContains(self::BASE.'/vacations/trips/norwegen', $locs);
        $this->assertNotContains(self::BASE.'/vacations/malediven', $locs);
        $this->assertNotContains(self::BASE.'/vacations/camps/malediven', $locs);

        $this->assertContains(self::BASE.'/vacations/targets/zander', $locs);
        $this->assertContains(self::BASE.'/vacations/camps/targets/zander', $locs);
        $this->assertNotContains(self::BASE.'/vacations/trips/targets/zander', $locs);

        foreach ($locs as $loc) {
            $this->assertStringNotContainsString('?', $loc);
        }
    }

    public function test_global_facets_list_destination_geo_pages_that_clear_the_gate(): void
    {
        $country = CategoryEntity::create(['type' => 'country', 'name' => 'Ziel', 'slug' => 'ziel-'.uniqid()]);
        $region = CategoryEntity::create(['type' => 'region', 'name' => 'Voll', 'slug' => 'voll-'.uniqid(), 'country_id' => $country->id]);
        $thinRegion = CategoryEntity::create(['type' => 'region', 'name' => 'Duenn', 'slug' => 'duenn-'.uniqid(), 'country_id' => $country->id]);
        $emptyCountry = CategoryEntity::create(['type' => 'country', 'name' => 'Leer', 'slug' => 'leer-'.uniqid()]);

        $gate = Mockery::mock(CatalogInventoryGate::class);
        $gate->shouldReceive('destinationIndexable')->andReturnUsing(
            fn (CategoryEntity $c, ?CategoryEntity $r = null, ?CategoryEntity $ci = null) => $c->is($country)
                && ($r === null || $r->is($region))
                && $ci === null
        );

        $source = Mockery::mock(CategoryPageSitemapSource::class);
        $source->shouldReceive('targetPages')->andReturn(collect());

        $contributor = new GlobalFacetSitemapContributor(new SitemapPathEncoder(), $gate, $source, app(SitemapLastmod::class), app(SitemapListingFreshness::class));
        $locs = $this->locs($contributor->entries(new SitemapContext(self::BASE, 'de')));

        $this->assertContains(self::BASE.'/destination', $locs);
        $this->assertContains(self::BASE.'/targets', $locs);
        $this->assertContains(self::BASE."/destination/{$country->slug}", $locs);
        $this->assertContains(self::BASE."/destination/{$country->slug}/{$region->slug}", $locs);
        $this->assertNotContains(self::BASE."/destination/{$country->slug}/{$thinRegion->slug}", $locs);
        $this->assertNotContains(self::BASE."/destination/{$emptyCountry->slug}", $locs);
    }
}
