<?php

namespace App\Services\Sitemap\Contributors;

use App\Contracts\Sitemap\SitemapContributorInterface;
use App\Services\Sitemap\SitemapContext;
use App\Services\Sitemap\SitemapEntry;
use App\Services\Sitemap\SitemapPathEncoder;
use Illuminate\Support\Collection;

/**
 * sitemap-pages-{lang}.xml — home, the pillar landing pages, the cross-type catalogs and the
 * indexable static pages. Contact and the legal pages are left out: they render noindex, and a
 * sitemap only lists indexable URLs. Section hubs (/guidings/countries, /vacations/targets, …)
 * live in their section's facets file.
 */
final class PagesSitemapContributor implements SitemapContributorInterface
{
    private const PATHS = [
        '/',
        '/offers',
        '/guidings',
        '/guidings/alloffers',
        '/vacations',
        '/vacations/trips',
        '/vacations/camps',
        '/vacations/all-offers',
        '/faq',
        '/partner',
        '/for-agents',
        '/about-us',
    ];

    public function __construct(
        private readonly SitemapPathEncoder $encoder,
    ) {}

    public function key(): string
    {
        return 'pages';
    }

    public function fileName(string $lang): string
    {
        return '/sitemap-pages-' . $lang . '.xml';
    }

    public function entries(SitemapContext $context): Collection
    {
        // No lastmod: these pages have no single content timestamp, and an invented one is worse
        // than none.
        return collect(self::PATHS)
            ->map(fn (string $path) => SitemapEntry::make($this->encoder->fromPath($context->baseUrl, $path)))
            ->values();
    }
}
