<?php

namespace Tests\Unit\Sitemap;

use App\Services\Sitemap\Contributors\PagesSitemapContributor;
use App\Services\Sitemap\SitemapContext;
use App\Services\Sitemap\SitemapEntry;
use App\Services\Sitemap\SitemapPathEncoder;
use App\Services\Sitemap\SitemapXmlWriter;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SitemapXmlWriterAndPagesContributorTest extends TestCase
{
    public function test_writer_persists_urlset_with_utf8_loc(): void
    {
        Storage::fake('sitemaps');

        $result = (new SitemapXmlWriter())->writeUrlset('/sitemap-test-en.xml', [
            SitemapEntry::make('https://www.catchaguide.com/vacations/%C3%B6sterreich', '2024-01-01T00:00:00+00:00'),
        ]);

        $this->assertSame(1, $result['count']);
        $xml = Storage::disk('sitemaps')->get('/sitemap-test-en.xml');
        $this->assertStringContainsString('<loc>https://www.catchaguide.com/vacations/%C3%B6sterreich</loc>', $xml);
        $this->assertStringContainsString('<lastmod>2024-01-01T00:00:00+00:00</lastmod>', $xml);
    }

    /**
     * Google ignores changefreq/priority, and a lastmod stamped with the generation time on every
     * URL teaches it to ignore lastmod for the whole domain — so none of the three is invented.
     */
    public function test_writer_emits_no_changefreq_priority_or_invented_lastmod(): void
    {
        Storage::fake('sitemaps');

        (new SitemapXmlWriter())->writeUrlset('/sitemap-test-en.xml', [
            SitemapEntry::make('https://www.catchaguide.com/offers'),
        ]);

        $xml = Storage::disk('sitemaps')->get('/sitemap-test-en.xml');
        $this->assertStringNotContainsString('<changefreq>', $xml);
        $this->assertStringNotContainsString('<priority>', $xml);
        $this->assertStringNotContainsString('<lastmod>', $xml);
    }

    public function test_writer_emits_hreflang_alternate_links(): void
    {
        Storage::fake('sitemaps');

        (new SitemapXmlWriter())->writeUrlset('/sitemap-test-de.xml', [
            SitemapEntry::make('https://www.catchaguide.de/guidings/offer/test', null, [
                'en' => 'https://www.catchaguide.com/guidings/offer/test',
                'de' => 'https://www.catchaguide.de/guidings/offer/test',
                'x-default' => 'https://www.catchaguide.com/guidings/offer/test',
            ]),
        ]);

        $xml = Storage::disk('sitemaps')->get('/sitemap-test-de.xml');
        $this->assertStringContainsString('xmlns:xhtml="http://www.w3.org/1999/xhtml"', $xml);
        $this->assertStringContainsString('<xhtml:link rel="alternate" hreflang="en" href="https://www.catchaguide.com/guidings/offer/test" />', $xml);
        $this->assertStringContainsString('<xhtml:link rel="alternate" hreflang="x-default" href="https://www.catchaguide.com/guidings/offer/test" />', $xml);
    }

    public function test_writer_reports_newest_lastmod_and_index_uses_it_per_child(): void
    {
        Storage::fake('sitemaps');
        $writer = new SitemapXmlWriter();

        $result = $writer->writeUrlset('/sitemap-test-en.xml', [
            SitemapEntry::make('https://www.catchaguide.com/a', '2024-01-01T00:00:00+00:00'),
            SitemapEntry::make('https://www.catchaguide.com/b', '2025-06-01T00:00:00+00:00'),
            SitemapEntry::make('https://www.catchaguide.com/c'),
        ]);
        $this->assertSame('2025-06-01T00:00:00+00:00', $result['lastmod']);

        $writer->writeIndex('/sitemap_index_en.xml', [
            'https://www.catchaguide.com/sitemaps/sitemap-tours-en.xml' => $result['lastmod'],
            'https://www.catchaguide.com/sitemaps/sitemap-pages-en.xml' => null,
        ]);

        $xml = Storage::disk('sitemaps')->get('/sitemap_index_en.xml');
        $this->assertStringContainsString('<sitemapindex', $xml);
        $this->assertStringContainsString('<loc>https://www.catchaguide.com/sitemaps/sitemap-tours-en.xml</loc>'."\n\t\t".'<lastmod>2025-06-01T00:00:00+00:00</lastmod>', $xml);
        $this->assertStringContainsString('<loc>https://www.catchaguide.com/sitemaps/sitemap-pages-en.xml</loc>'."\n\t".'</sitemap>', $xml);
    }

    public function test_pages_contributor_lists_landing_pages_and_excludes_noindex_pages(): void
    {
        $locs = (new PagesSitemapContributor(new SitemapPathEncoder()))
            ->entries(new SitemapContext('https://www.catchaguide.com', 'en'))
            ->map(fn (SitemapEntry $entry) => $entry->loc)
            ->all();

        foreach (['', '/offers', '/guidings', '/vacations', '/vacations/trips', '/vacations/camps', '/faq', '/partner', '/for-agents', '/about-us'] as $path) {
            $this->assertContains('https://www.catchaguide.com'.$path, $locs);
        }

        // noindex pages (contact, legal) and account pages never go in a sitemap.
        foreach (['/contact', '/imprint', '/data-protection', '/agb', '/notice-and-takedown', '/login', '/password/reset'] as $path) {
            $this->assertNotContains('https://www.catchaguide.com'.$path, $locs);
        }

        // Section hubs belong to their section's facets file, not this one.
        $this->assertNotContains('https://www.catchaguide.com/guidings/countries', $locs);
        $this->assertNotContains('https://www.catchaguide.com/vacations/countries', $locs);
    }
}
