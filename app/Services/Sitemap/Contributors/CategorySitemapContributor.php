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
                $this->encoder->join($context->baseUrl, ['targets']),
                'weekly',
                0.7,
            ),
            SitemapEntry::make(
                $this->encoder->join($context->baseUrl, ['guidings', 'methods']),
                'weekly',
                0.7,
            ),
        ]);

        // Only 'methods' and 'targets' have a real public route (see CategoryController::targets()
        // and routes/web/catalog.php) — any other `type` value on a CategoryPage row is stale/bad
        // data (e.g. a row whose type ended up set to a species slug instead of 'Targets') with no
        // route to serve it. Emitting those into the sitemap submits dead `/category-page/{type}/
        // {slug}` URLs to Google, which is exactly what was piling up as 404s in Search Console.
        $pages = CategoryPage::query()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->whereRaw('LOWER(type) IN (?, ?)', ['methods', 'targets'])
            ->get(['type', 'slug', 'updated_at']);

        foreach ($pages as $page) {
            $type = strtolower((string) $page->type);
            $path = $type === 'methods'
                ? ['guidings', 'methods', $page->slug]
                : ['targets', $page->slug];
            $entries->push(SitemapEntry::make(
                $this->encoder->join($context->baseUrl, $path),
                'monthly',
                0.6,
                $page->updated_at?->toAtomString(),
            ));
        }

        return $entries;
    }
}
