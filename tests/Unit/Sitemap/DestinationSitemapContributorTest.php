<?php

namespace Tests\Unit\Sitemap;

use App\Models\City;
use App\Models\Country;
use App\Models\Region;
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
        $country = Country::query()->create([
            'name' => 'Sitemap Spain',
            'slug' => 'sitemap-spain-'.uniqid(),
            'countrycode' => 'ES',
        ]);
        $region = Region::query()->create([
            'country_id' => $country->id,
            'name' => 'Sitemap Catalonia',
            'slug' => 'sitemap-catalonia-'.$country->slug,
        ]);
        $city = City::query()->create([
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
}
