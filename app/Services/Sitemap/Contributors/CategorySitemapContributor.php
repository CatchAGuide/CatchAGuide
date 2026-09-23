<?php

namespace App\Services\Sitemap\Contributors;

use App\Contracts\Sitemap\SitemapContributorInterface;
use App\Models\CategoryPage;
use App\Models\Target;
use App\Repositories\Guiding\GuidingCategoryAvailabilityRepository;
use App\Services\Sitemap\SitemapContext;
use App\Services\Sitemap\SitemapEntry;
use App\Services\Sitemap\SitemapPathEncoder;
use App\Services\Vacation\VacationTargetFishSelector;
use Illuminate\Support\Collection;

final class CategorySitemapContributor implements SitemapContributorInterface
{
    public function __construct(
        private readonly SitemapPathEncoder $encoder,
        private readonly GuidingCategoryAvailabilityRepository $guidingAvailability,
        private readonly VacationTargetFishSelector $vacationTargetAvailability,
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
            ->get(['type', 'slug', 'updated_at', 'source_id', 'name']);

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

            // The tours- and vacations-scoped species pages (guidings/targets/{slug},
            // vacations/targets/{slug}) render genuinely different, substantial content from the
            // global /targets/{slug} page (confirmed live: distinct title, several times the byte
            // size) — gated the same way TargetFishPageController::show() gates them, so a species
            // with zero listings for that scope never gets a thin page submitted (see CLAUDE.md's
            // "SEO / catalog page conventions").
            if ($type === 'targets') {
                $speciesId = (int) $page->source_id;
                $target = $speciesId > 0 ? Target::find($speciesId) : null;
                $speciesName = $target?->name ?? $page->name;

                if ($speciesId > 0 && $this->guidingAvailability->hasGuidingsForTarget($speciesId)) {
                    $entries->push(SitemapEntry::make(
                        $this->encoder->join($context->baseUrl, ['guidings', 'targets', $page->slug]),
                        'monthly',
                        0.6,
                        $page->updated_at?->toAtomString(),
                    ));
                }

                if ($speciesId > 0 && $this->vacationTargetAvailability->hasActiveListings($speciesId, (string) $speciesName)) {
                    $entries->push(SitemapEntry::make(
                        $this->encoder->join($context->baseUrl, ['vacations', 'targets', $page->slug]),
                        'monthly',
                        0.6,
                        $page->updated_at?->toAtomString(),
                    ));
                }
            }
        }

        return $entries;
    }
}
