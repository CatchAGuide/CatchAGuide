<?php

namespace Tests\Unit\Sitemap;

use App\Models\Guiding;
use App\Services\Sitemap\Contributors\ListingSitemapContributor;
use App\Services\Sitemap\SitemapContext;
use App\Services\Sitemap\SitemapEntry;
use App\Services\Sitemap\SitemapPathEncoder;
use Tests\TestCase;

class ListingSitemapContributorTest extends TestCase
{
    public function test_listing_entries_use_offer_slug_urls(): void
    {
        $guiding = Guiding::query()
            ->where('status', 1)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->first(['id', 'slug']);

        if (! $guiding) {
            $this->markTestSkipped('No published guiding with a slug in the test database.');
        }

        $contributor = new ListingSitemapContributor(new SitemapPathEncoder());
        $locs = $contributor
            ->entries(new SitemapContext('https://www.catchaguide.com', 'en'))
            ->map(fn (SitemapEntry $entry) => $entry->loc);

        $this->assertTrue(
            $locs->contains('https://www.catchaguide.com/guidings/offer/'.$guiding->slug)
        );
        $this->assertFalse(
            $locs->contains(fn (string $loc) => (bool) preg_match('#/guidings/\d+/#', $loc))
        );
    }
}
