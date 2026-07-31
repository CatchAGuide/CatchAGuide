<?php

namespace App\Services\Sitemap\Contributors;

use App\Contracts\Sitemap\SitemapContributorInterface;
use App\Models\CategoryPage;
use App\Services\Sitemap\SitemapContext;
use App\Services\Sitemap\SitemapEntry;
use App\Services\Sitemap\SitemapPathEncoder;
use Illuminate\Support\Collection;

final class CategorySitemapContributor implements SitemapContributorInterface
{
    public function __construct(
        private readonly SitemapPathEncoder $encoder,
    ) {}

    public function key(): string
    {
        return 'categories';
    }

    public function fileName(string $lang): string
    {
        return '/sitemap_categories_' . $lang . '.xml';
    }

    public function entries(SitemapContext $context): Collection
    {
        $entries = collect([
            SitemapEntry::make(
                $this->encoder->join($context->baseUrl, ['category-page', 'targets']),
                'weekly',
                0.7,
            ),
            SitemapEntry::make(
                $this->encoder->join($context->baseUrl, ['category-page', 'methods']),
                'weekly',
                0.7,
            ),
        ]);

        $pages = CategoryPage::query()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->get(['type', 'slug']);

        foreach ($pages as $page) {
            $type = strtolower((string) $page->type);
            $entries->push(SitemapEntry::make(
                $this->encoder->join($context->baseUrl, ['category-page', $type, $page->slug]),
                'monthly',
                0.6,
            ));
        }

        return $entries;
    }
}
