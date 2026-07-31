<?php

namespace App\Services\Sitemap\Contributors;

use App\Contracts\Sitemap\SitemapContributorInterface;
use App\Services\Sitemap\SitemapContext;
use App\Services\Sitemap\SitemapEntry;
use App\Services\Sitemap\SitemapPathEncoder;
use Illuminate\Support\Collection;

final class MainSitemapContributor implements SitemapContributorInterface
{
    public function __construct(
        private readonly SitemapPathEncoder $encoder,
    ) {}

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
        $uris = [
            '/' => ['priority' => 1.0, 'changefreq' => 'weekly'],
            '/guidings' => ['priority' => 0.9, 'changefreq' => 'weekly'],
            '/vacations' => ['priority' => 0.9, 'changefreq' => 'weekly'],
            '/faq' => ['priority' => 0.6, 'changefreq' => 'monthly'],
            '/about-us' => ['priority' => 0.7, 'changefreq' => 'monthly'],
            '/for-agents' => ['priority' => 0.5, 'changefreq' => 'monthly'],
        ];

        return collect($uris)->map(function (array $settings, string $uri) use ($context) {
            $loc = $uri === '/'
                ? $context->baseUrl
                : $this->encoder->fromPath($context->baseUrl, $uri);

            return SitemapEntry::make($loc, $settings['changefreq'], $settings['priority']);
        })->values();
    }
}
