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
    public function test_generator_writes_contributor_files_and_index(): void
    {
        Storage::fake('sitemaps');

        $stub = new class implements SitemapContributorInterface {
            public function key(): string
            {
                return 'stub';
            }

            public function fileName(string $lang): string
            {
                return '/sitemap_stub_' . $lang . '.xml';
            }

            public function entries(SitemapContext $context): Collection
            {
                return collect([
                    SitemapEntry::make($context->baseUrl . '/guidings', 'weekly', 0.9),
                ]);
            }
        };

        $generator = new SitemapGenerator([$stub], new SitemapXmlWriter());
        $result = $generator->generateForLanguage('en', 'https://www.catchaguide.com');

        $this->assertSame(1, $result['counts']['stub']);
        $this->assertSame(1, $result['counts']['index']);
        Storage::disk('sitemaps')->assertExists('/sitemap_stub_en.xml');
        Storage::disk('sitemaps')->assertExists('/sitemap_index_en.xml');
    }

    public function test_magazine_contributor_uses_angelmagazin_for_de(): void
    {
        Storage::fake('sitemaps');

        // Avoid DB: subclass and override Thread query by testing prefix via mapper + encoder path.
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

        // Regression: DE must never use fishing-magazine prefix in sitemap locs.
        $this->assertNotSame('fishing-magazine', $mapper->magazinePrefix('de'));
    }

    public function test_dual_domain_base_urls_stay_separate(): void
    {
        Storage::fake('sitemaps');

        $stub = new class implements SitemapContributorInterface {
            public function key(): string
            {
                return 'main';
            }

            public function fileName(string $lang): string
            {
                return '/sitemap_' . $lang . '.xml';
            }

            public function entries(SitemapContext $context): Collection
            {
                return collect([SitemapEntry::make($context->baseUrl . '/guidings')]);
            }
        };

        $generator = new SitemapGenerator([$stub], new SitemapXmlWriter());
        $generator->generateForLanguage('en', 'https://www.catchaguide.com');
        $generator->generateForLanguage('de', 'https://www.catchaguide.de');

        $en = Storage::disk('sitemaps')->get('/sitemap_en.xml');
        $de = Storage::disk('sitemaps')->get('/sitemap_de.xml');

        $this->assertStringContainsString('https://www.catchaguide.com/guidings', $en);
        $this->assertStringContainsString('https://www.catchaguide.de/guidings', $de);
        $this->assertStringNotContainsString('https://www.catchaguide.de/guidings', $en);
        $this->assertStringNotContainsString('https://www.catchaguide.com/guidings', $de);
    }
}
