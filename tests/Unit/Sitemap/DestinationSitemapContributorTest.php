<?php

namespace Tests\Unit\Sitemap;

use App\Models\CategoryEntity;
use App\Services\Sitemap\Contributors\DestinationSitemapContributor;
use App\Services\Sitemap\SitemapContext;
use App\Services\Sitemap\SitemapEntry;
use App\Services\Sitemap\SitemapPathEncoder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DestinationSitemapContributorTest extends TestCase
{
    use DatabaseTransactions;

    public function test_destination_sitemap_includes_country_but_not_region_or_city(): void
    {
        $country = CategoryEntity::countries()->create([
            'type' => 'country',
            'name' => 'Sitemap Spain',
            'slug' => 'sitemap-spain-'.uniqid(),
            'countrycode' => 'ES',
        ]);
        $region = CategoryEntity::regions()->create([
            'type' => 'region',
            'country_id' => $country->id,
            'name' => 'Sitemap Catalonia',
            'slug' => 'sitemap-catalonia-'.$country->slug,
        ]);
        $city = CategoryEntity::cities()->create([
            'type' => 'city',
            'country_id' => $country->id,
            'region_id' => $region->id,
            'name' => 'Sitemap Barcelona',
            'slug' => 'sitemap-barcelona-'.$country->slug,
        ]);

        $contributor = new DestinationSitemapContributor(new SitemapPathEncoder());
        $locs = $contributor->entries(new SitemapContext('https://www.catchaguide.com', 'en'))
            ->map(fn (SitemapEntry $entry) => $entry->loc)
            ->all();

        $this->assertContains('https://www.catchaguide.com/destination', $locs);
        $this->assertContains('https://www.catchaguide.com/destination/'.$country->slug, $locs);
        $this->assertNotContains(
            'https://www.catchaguide.com/destination/'.$country->slug.'/'.$region->slug,
            $locs
        );
        $this->assertNotContains(
            'https://www.catchaguide.com/destination/'.$country->slug.'/'.$region->slug.'/'.$city->slug,
            $locs
        );
    }

    /**
     * A capitalized/umlaut country slug (e.g. "Österreich") must never appear in the sitemap
     * as-is — only its canonical lowercase form, otherwise Google is told to crawl a URL whose
     * only job is to 301 elsewhere (see CLAUDE.md's "SEO / catalog page conventions").
     */
    public function test_destination_sitemap_lists_canonical_slug_not_raw_uppercase_umlaut_slug(): void
    {
        $country = CategoryEntity::countries()->create([
            'type' => 'country',
            'name' => 'Österreich',
            'slug' => 'Österreich-'.uniqid(),
            'countrycode' => 'AT',
        ]);

        $contributor = new DestinationSitemapContributor(new SitemapPathEncoder());
        $locs = $contributor->entries(new SitemapContext('https://www.catchaguide.com', 'de'))
            ->map(fn (SitemapEntry $entry) => $entry->loc)
            ->all();

        $canonical = \App\Domain\Vacation\CountrySlug::canonicalize($country->slug);

        $this->assertContains('https://www.catchaguide.com/destination/'.rawurlencode($canonical), $locs);
        $this->assertNotContains('https://www.catchaguide.com/destination/'.rawurlencode($country->slug), $locs);
    }
}
