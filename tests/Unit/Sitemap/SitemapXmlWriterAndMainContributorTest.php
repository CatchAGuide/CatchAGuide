<?php

namespace Tests\Unit\Sitemap;

use App\Services\Sitemap\Contributors\MainSitemapContributor;
use App\Services\Sitemap\SitemapContext;
use App\Services\Sitemap\SitemapEntry;
use App\Services\Sitemap\SitemapPathEncoder;
use App\Services\Sitemap\SitemapXmlWriter;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SitemapXmlWriterAndMainContributorTest extends TestCase
{
    public function test_writer_persists_urlset_with_utf8_loc(): void
    {
        Storage::fake('sitemaps');

        $writer = new SitemapXmlWriter();
        $count = $writer->writeUrlset('/sitemap_test_en.xml', [
            SitemapEntry::make('https://www.catchaguide.com/vacations/' . rawurlencode('österreich'), 'weekly', 0.75),
        ]);

        $this->assertSame(1, $count);
        Storage::disk('sitemaps')->assertExists('/sitemap_test_en.xml');
        $xml = Storage::disk('sitemaps')->get('/sitemap_test_en.xml');
        $this->assertStringContainsString('urlset', $xml);
        $this->assertStringContainsString(rawurlencode('österreich'), $xml);
        $this->assertStringNotContainsString('login', $xml);
    }

    public function test_writer_persists_index(): void
    {
        Storage::fake('sitemaps');
        $writer = new SitemapXmlWriter();
        $writer->writeIndex('/sitemap_index_en.xml', [
            'https://www.catchaguide.com/sitemaps/sitemap_listing_en.xml',
            'https://www.catchaguide.com/sitemaps/sitemap_vacations_en.xml',
        ]);

        $xml = Storage::disk('sitemaps')->get('/sitemap_index_en.xml');
        $this->assertStringContainsString('sitemapindex', $xml);
        $this->assertStringContainsString('sitemap_vacations_en.xml', $xml);
    }

    public function test_main_contributor_excludes_noindex_and_login_pages(): void
    {
        $contributor = new MainSitemapContributor(new SitemapPathEncoder());
        $entries = $contributor->entries(new SitemapContext('https://www.catchaguide.com', 'en'));
        $locs = $entries->map(fn (SitemapEntry $e) => $e->loc)->all();

        $this->assertContains('https://www.catchaguide.com', $locs);
        $this->assertContains('https://www.catchaguide.com/guidings', $locs);
        $this->assertContains('https://www.catchaguide.com/vacations', $locs);
        $this->assertContains('https://www.catchaguide.com/faq', $locs);
        $this->assertContains('https://www.catchaguide.com/about-us', $locs);
        $this->assertContains('https://www.catchaguide.com/for-agents', $locs);

        foreach ($locs as $loc) {
            $this->assertStringNotContainsString('/login', $loc);
            $this->assertStringNotContainsString('/contact', $loc);
            $this->assertStringNotContainsString('/imprint', $loc);
            $this->assertStringNotContainsString('/agb', $loc);
            $this->assertStringNotContainsString('/data-protection', $loc);
        }
    }
}
