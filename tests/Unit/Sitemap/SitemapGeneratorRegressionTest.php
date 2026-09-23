<?php

namespace Tests\Unit\Sitemap;

use App\Contracts\Sitemap\SitemapContributorInterface;
use App\Services\Seo\LocalePathMapper;
use App\Services\Sitemap\SitemapContext;
use App\Services\Sitemap\SitemapEntry;
use App\Services\Sitemap\SitemapGenerator;
use App\Services\Sitemap\SitemapPathEncoder;
use App\Services\Sitemap\SitemapXmlWriter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SitemapGeneratorRegressionTest extends TestCase
{
    private const BASE_URLS = ['en' => 'https://www.catchaguide.com', 'de' => 'https://www.catchaguide.de'];

    /**
     * @param  callable(SitemapContext): list<SitemapEntry>  $entries
     */
    private function stub(string $key, callable $entries): SitemapContributorInterface
    {
        return new class($key, $entries) implements SitemapContributorInterface {
            public function __construct(private string $key, private $entries) {}

            public function key(): string
            {
                return $this->key;
            }

            public function fileName(string $lang): string
            {
                return '/sitemap-'.$this->key.'-'.$lang.'.xml';
            }

            public function entries(SitemapContext $context): Collection
            {
                return collect(($this->entries)($context));
            }
        };
    }

    private function generator(SitemapContributorInterface ...$contributors): SitemapGenerator
    {
        return new SitemapGenerator($contributors, new SitemapXmlWriter(), new LocalePathMapper(), self::BASE_URLS);
    }

    public function test_generator_writes_contributor_files_and_index(): void
    {
        Storage::fake('sitemaps');

        $result = $this->generator(
            $this->stub('pages', fn (SitemapContext $c) => [SitemapEntry::make($c->baseUrl.'/guidings')]),
        )->generateForLanguage('en', 'https://www.catchaguide.com');

        $this->assertSame(1, $result['counts']['pages']);
        $this->assertSame(1, $result['counts']['index']);
        Storage::disk('sitemaps')->assertExists('/sitemap-pages-en.xml');
        $this->assertStringContainsString(
            '<loc>https://www.catchaguide.com/sitemaps/sitemap-pages-en.xml</loc>',
            Storage::disk('sitemaps')->get('/sitemap_index_en.xml'),
        );
    }

    /**
     * Every file carries the same de/en/x-default set the page's own <head> declares, so the
     * facet and magazine pages get what only the listing file used to have.
     */
    public function test_generator_adds_hreflang_alternates_including_mapped_magazine_prefix(): void
    {
        Storage::fake('sitemaps');

        $this->generator(
            $this->stub('facets', fn (SitemapContext $c) => [
                SitemapEntry::make($c->baseUrl.'/guidings/schweden'),
                SitemapEntry::make($c->baseUrl.'/angelmagazin'),
            ]),
        )->generateForLanguage('de', 'https://www.catchaguide.de');

        $xml = Storage::disk('sitemaps')->get('/sitemap-facets-de.xml');
        $this->assertStringContainsString('hreflang="en" href="https://www.catchaguide.com/guidings/schweden"', $xml);
        $this->assertStringContainsString('hreflang="de" href="https://www.catchaguide.de/guidings/schweden"', $xml);
        $this->assertStringContainsString('hreflang="x-default" href="https://www.catchaguide.com/guidings/schweden"', $xml);
        $this->assertStringContainsString('hreflang="en" href="https://www.catchaguide.com/fishing-magazine"', $xml);
    }

    public function test_non_localized_entries_get_no_alternates(): void
    {
        Storage::fake('sitemaps');

        $this->generator(
            $this->stub('magazine', fn (SitemapContext $c) => [
                SitemapEntry::make($c->baseUrl.'/angelmagazin/nur-deutsch', localized: false),
            ]),
        )->generateForLanguage('de', 'https://www.catchaguide.de');

        $this->assertStringNotContainsString('xhtml:link', Storage::disk('sitemaps')->get('/sitemap-magazine-de.xml'));
    }

    public function test_generator_deletes_the_legacy_file_set_and_restores_locale(): void
    {
        Storage::fake('sitemaps');
        foreach (['/sitemap_de.xml', '/sitemap_listing_de.xml', '/sitemap_vacations_de.xml', '/sitemap_routes.xml', '/sitemap_listing_en.xml'] as $legacy) {
            Storage::disk('sitemaps')->put($legacy, '<urlset/>');
        }
        app()->setLocale('en');

        $this->generator(
            $this->stub('pages', function (SitemapContext $c) {
                // Contributors resolve CMS copy against the app locale.
                $this->assertSame('de', app()->getLocale());

                return [SitemapEntry::make($c->baseUrl)];
            }),
        )->generateForLanguage('de', 'https://www.catchaguide.de');

        Storage::disk('sitemaps')->assertMissing('/sitemap_de.xml');
        Storage::disk('sitemaps')->assertMissing('/sitemap_listing_de.xml');
        Storage::disk('sitemaps')->assertMissing('/sitemap_vacations_de.xml');
        Storage::disk('sitemaps')->assertMissing('/sitemap_routes.xml');
        // The other language's files belong to its own run.
        Storage::disk('sitemaps')->assertExists('/sitemap_listing_en.xml');
        $this->assertSame('en', app()->getLocale());
    }

    public function test_magazine_prefix_is_angelmagazin_for_de(): void
    {
        $mapper = new LocalePathMapper();
        $encoder = new SitemapPathEncoder();

        $this->assertSame(
            'https://www.catchaguide.de/angelmagazin',
            $encoder->join('https://www.catchaguide.de', [$mapper->magazinePrefix('de')])
        );
        $this->assertSame(
            'https://www.catchaguide.com/fishing-magazine',
            $encoder->join('https://www.catchaguide.com', [$mapper->magazinePrefix('en')])
        );
    }

    public function test_dual_domain_base_urls_stay_separate(): void
    {
        Storage::fake('sitemaps');

        $generator = $this->generator(
            $this->stub('pages', fn (SitemapContext $c) => [SitemapEntry::make($c->baseUrl.'/guidings', localized: false)]),
        );
        $generator->generateForLanguage('en', 'https://www.catchaguide.com');
        $generator->generateForLanguage('de', 'https://www.catchaguide.de');

        $en = Storage::disk('sitemaps')->get('/sitemap-pages-en.xml');
        $de = Storage::disk('sitemaps')->get('/sitemap-pages-de.xml');

        $this->assertStringContainsString('https://www.catchaguide.com/guidings', $en);
        $this->assertStringContainsString('https://www.catchaguide.de/guidings', $de);
        $this->assertStringNotContainsString('https://www.catchaguide.de/guidings', $en);
        $this->assertStringNotContainsString('https://www.catchaguide.com/guidings', $de);
    }
}
