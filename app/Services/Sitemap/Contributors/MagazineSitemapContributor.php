<?php

namespace App\Services\Sitemap\Contributors;

use App\Contracts\Sitemap\SitemapContributorInterface;
use App\Models\Thread;
use App\Services\Seo\LocalePathMapper;
use App\Services\Sitemap\SitemapContext;
use App\Services\Sitemap\SitemapEntry;
use App\Services\Sitemap\SitemapPathEncoder;
use Illuminate\Support\Collection;

final class MagazineSitemapContributor implements SitemapContributorInterface
{
    public function __construct(
        private readonly LocalePathMapper $localePathMapper,
        private readonly SitemapPathEncoder $encoder,
    ) {}

    public function key(): string
    {
        return 'magazine';
    }

    public function fileName(string $lang): string
    {
        return '/sitemap_fishing_magazine_' . $lang . '.xml';
    }

    public function entries(SitemapContext $context): Collection
    {
        $prefix = $this->localePathMapper->magazinePrefix($context->lang);
        $entries = collect([
            SitemapEntry::make(
                $this->encoder->join($context->baseUrl, [$prefix]),
                'weekly',
                0.8,
            ),
        ]);

        $threads = Thread::query()
            ->where('language', $context->lang)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->get(['slug']);

        foreach ($threads as $thread) {
            $entries->push(SitemapEntry::make(
                $this->encoder->join($context->baseUrl, [$prefix, $thread->slug]),
                'monthly',
                0.6,
            ));
        }

        return $entries;
    }
}
