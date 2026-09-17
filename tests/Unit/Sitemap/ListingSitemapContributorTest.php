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

    public function test_listing_entries_use_the_guidings_own_updated_at_as_lastmod(): void
    {
        $guiding = Guiding::query()
            ->where('status', 1)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->first(['id', 'slug', 'updated_at']);

        if (! $guiding) {
            $this->markTestSkipped('No published guiding with a slug in the test database.');
        }

        $contributor = new ListingSitemapContributor(new SitemapPathEncoder());
        $entry = $contributor
            ->entries(new SitemapContext('https://www.catchaguide.com', 'en'))
            ->first(fn (SitemapEntry $entry) => $entry->loc === 'https://www.catchaguide.com/guidings/offer/'.$guiding->slug);

        $this->assertNotNull($entry);
        $this->assertSame($guiding->updated_at?->toAtomString(), $entry->lastmod);
    }

    public function test_listing_entries_carry_cross_domain_hreflang_alternates(): void
    {
        $guiding = Guiding::query()
            ->where('status', 1)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->first(['id', 'slug']);

        if (! $guiding) {
            $this->markTestSkipped('No published guiding with a slug in the test database.');
        }

        config([
            'cag.en_app_url' => 'https://www.catchaguide.com',
            'cag.de_app_url' => 'https://www.catchaguide.de',
        ]);

        $contributor = new ListingSitemapContributor(new SitemapPathEncoder());
        $entry = $contributor
            ->entries(new SitemapContext('https://www.catchaguide.de', 'de'))
            ->first(fn (SitemapEntry $entry) => $entry->loc === 'https://www.catchaguide.de/guidings/offer/'.$guiding->slug);

        $this->assertNotNull($entry);
        $this->assertSame('https://www.catchaguide.com/guidings/offer/'.$guiding->slug, $entry->alternates['en']);
        $this->assertSame('https://www.catchaguide.de/guidings/offer/'.$guiding->slug, $entry->alternates['de']);
        $this->assertSame($entry->alternates['en'], $entry->alternates['x-default']);
    }
}
