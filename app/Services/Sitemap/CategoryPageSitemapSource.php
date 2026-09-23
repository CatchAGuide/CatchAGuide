<?php

namespace App\Services\Sitemap;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Models\CategoryPage;
use App\Repositories\Guiding\GuidingCategoryAvailabilityRepository;
use App\Services\CategoryPage\CategoryPageContentService;
use App\Services\Vacation\VacationTargetFishSelector;
use Illuminate\Support\Collection;

/**
 * Species and method pages the sitemap may list — the same "renders 200 with listings" checks
 * TargetFishPageController::show() and CategoryController::targets() apply, so a URL is only
 * submitted when the page actually serves it.
 */
class CategoryPageSitemapSource
{
    /** @var array<string, Collection<int, CategoryPage>> */
    private array $pages = [];

    /** @var array<int, array{camps: int, trips: int}> */
    private array $vacationCounts = [];

    public function __construct(
        private readonly CategoryPageContentService $content,
        private readonly GuidingCategoryAvailabilityRepository $guidingAvailability,
        private readonly VacationTargetFishSelector $vacationTargets,
        private readonly SitemapLastmod $lastmod,
    ) {}

    /**
     * @return Collection<int, CategoryPage>
     */
    public function targetPages(): Collection
    {
        return $this->pages['targets'] ??= $this->pagesOfType('targets');
    }

    /**
     * @return Collection<int, CategoryPage>
     */
    public function methodPages(): Collection
    {
        return $this->pages['methods'] ??= $this->pagesOfType('methods');
    }

    /** /targets/{slug}: global copy (with cross-scope fallback) and listings on either side. */
    public function globalTargetIsLive(CategoryPage $page, string $lang): bool
    {
        return $this->hasContent($page, CategoryPageScope::GLOBAL, $lang, true)
            && ($this->hasTours($page) || $this->vacationTotal($page) > 0);
    }

    /** /guidings/targets/{slug}. */
    public function tourTargetIsLive(CategoryPage $page, string $lang): bool
    {
        return $this->hasTours($page) && $this->hasContent($page, CategoryPageScope::TOURS, $lang, false);
    }

    /** /vacations/targets/{slug} (pillar null) or /vacations/{camps|trips}/targets/{slug}. */
    public function vacationTargetIsLive(CategoryPage $page, string $lang, ?string $pillar = null): bool
    {
        $counts = $this->vacationCounts($page);
        $count = match ($pillar) {
            'camp' => $counts['camps'],
            'trip' => $counts['trips'],
            default => $counts['camps'] + $counts['trips'],
        };

        return $count > 0 && $this->hasContent($page, CategoryPageScope::VACATIONS, $lang, false);
    }

    /** /guidings/methods/{slug}. */
    public function methodIsLive(CategoryPage $page, string $lang): bool
    {
        return $this->guidingAvailability->hasGuidingsForMethod((int) $page->source_id)
            && $this->hasContent($page, CategoryPageScope::TOURS, $lang, true);
    }

    public function lastmod(CategoryPage $page, string $scope, string $lang): ?string
    {
        $type = strtolower((string) $page->type) === 'methods'
            ? CategoryPageEntityType::METHOD
            : CategoryPageEntityType::TARGET_FISH;

        return $this->lastmod->forContent($type, $scope, (int) $page->source_id, $lang, $page->updated_at);
    }

    private function hasTours(CategoryPage $page): bool
    {
        return $this->guidingAvailability->hasGuidingsForTarget((int) $page->source_id);
    }

    private function vacationTotal(CategoryPage $page): int
    {
        $counts = $this->vacationCounts($page);

        return $counts['camps'] + $counts['trips'];
    }

    /**
     * @return array{camps: int, trips: int}
     */
    private function vacationCounts(CategoryPage $page): array
    {
        return $this->vacationCounts[$page->id] ??= $this->vacationTargets->activeListingCounts(
            (int) $page->source_id,
            (string) ($page->source?->name ?? $page->name),
        );
    }

    private function hasContent(CategoryPage $page, string $scope, string $lang, bool $allowFallback): bool
    {
        return $this->content->resolveForDisplay($page, $scope, $lang, $allowFallback) !== null;
    }

    /**
     * @return Collection<int, CategoryPage>
     */
    private function pagesOfType(string $type): Collection
    {
        return CategoryPage::query()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->where('source_id', '>', 0)
            ->whereRaw('LOWER(type) = ?', [$type])
            ->orderBy('slug')
            ->get()
            ->unique('slug')
            ->values();
    }
}
