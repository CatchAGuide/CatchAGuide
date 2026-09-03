<?php

namespace App\Http\Controllers\Category;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Http\Controllers\Controller;
use App\Models\CategoryPage;
use App\Repositories\Guiding\GuidingCategoryAvailabilityRepository;
use App\Services\CategoryPage\CategoryPageContentService;
use App\Services\Homepage\HomepageMixedOfferSelector;
use App\Services\Offers\OfferCatalogPageService;
use App\Services\Vacation\VacationTargetFishSelector;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class TargetFishPageController extends Controller
{
    public function __construct(
        private OfferCatalogPageService $offerCatalog,
        private CategoryPageContentService $categoryContent,
        private HomepageMixedOfferSelector $mixedOffers,
        private GuidingCategoryAvailabilityRepository $guidingAvailability,
        private VacationTargetFishSelector $vacationTargetAvailability,
    ) {}

    public function show(Request $request, string $slug): View
    {
        $scope = (string) ($request->route('content_scope') ?: CategoryPageScope::GLOBAL);
        if (! in_array($scope, [
            CategoryPageScope::GLOBAL,
            CategoryPageScope::TOURS,
            CategoryPageScope::VACATIONS,
        ], true)) {
            abort(404);
        }

        $locale = app()->getLocale();
        $page = CategoryPage::query()
            ->whereSlug($slug)
            ->whereRaw('LOWER(type) = ?', ['targets'])
            ->first();

        if ($page === null) {
            abort(404);
        }

        $allowCrossScopeFallback = $scope === CategoryPageScope::GLOBAL;

        $page->language = $this->categoryContent->resolveForDisplay(
            $page,
            $scope,
            $locale,
            $allowCrossScopeFallback,
        );

        if ($page->language === null) {
            abort(404);
        }

        $page->faq = $this->categoryContent->resolveFaqsForEntityDisplay(
            CategoryPageEntityType::TARGET_FISH,
            $page->source_id,
            $scope,
            $locale,
            null,
            $allowCrossScopeFallback,
        );

        $speciesId = (int) $page->source_id;
        if ($speciesId <= 0) {
            abort(404);
        }

        $placeName = $page->source?->name ?? $page->name;

        // A species with zero listings for this scope has no page — the global
        // page needs listings on either side, tours/vacations need their own.
        $hasTours = fn () => $this->guidingAvailability->hasGuidingsForTarget($speciesId);
        $hasVacations = fn () => $this->vacationTargetAvailability->hasActiveListings($speciesId, $placeName);
        $hasListings = match ($scope) {
            CategoryPageScope::TOURS => $hasTours(),
            CategoryPageScope::VACATIONS => $hasVacations(),
            default => $hasTours() || $hasVacations(),
        };

        if (! $hasListings) {
            abort(404);
        }

        if ($scope === CategoryPageScope::GLOBAL) {
            return view('pages.category.category-show', [
                'row_data' => $page,
                'title' => $page->language->title ?? $page->name,
                'vm' => null,
                'content_scope' => $scope,
                'offerModules' => $this->mixedOffers->byModuleForTargetFish($speciesId),
                'offersTitle' => __('category.targets.offers_title', ['fish' => $placeName]),
                'offersEmptyMessage' => __('category.targets.offers_empty', ['fish' => $placeName]),
                'offersSectionClass' => 'cag-dest-offers',
                'offersVariant' => 'destination',
                'offerBrowseUrls' => [
                    'tour' => route('guidings.targets', ['slug' => $page->slug]),
                    'camp' => route('vacations.targets', ['slug' => $page->slug, 'vacation' => 'camp']),
                    'trip' => route('vacations.targets', ['slug' => $page->slug, 'vacation' => 'trip']),
                ],
            ]);
        }

        $vm = $this->offerCatalog->buildForTargetFish($request, $speciesId, $scope, $placeName);

        return view('pages.category.category-show', [
            'row_data' => $page,
            'title' => $page->language->title ?? $page->name,
            'vm' => $vm,
            'content_scope' => $scope,
            'speciesRedirectOptions' => $this->speciesRedirectOptions($page, $scope, $locale, $speciesId),
            'speciesRedirectCurrent' => $speciesId,
            'speciesRedirectAllUrl' => $scope === CategoryPageScope::TOURS
                ? route('guidings.targets.index')
                : route('vacations.index'),
        ]);
    }

    /**
     * Target-fish category pages usable as a "switch to exactly this species" destination,
     * scoped the same way as the page being viewed (tours listings gated to species with at
     * least one publicly visible tour, matching CategoryController::index's hub filtering).
     * The page currently being viewed is always included — built directly from $currentPage
     * rather than requiring a live `source` (Target) row, so an orphaned/deleted Target
     * (source_id with no matching catalog row) doesn't silently drop the redirect config for
     * its own page and disable the switch-species behavior there.
     *
     * @return Collection<int, array{id: int, name: string, url: string}>
     */
    private function speciesRedirectOptions(CategoryPage $currentPage, string $scope, string $locale, int $currentSpeciesId): Collection
    {
        $currentUrl = $scope === CategoryPageScope::TOURS
            ? route('guidings.targets', ['slug' => $currentPage->slug])
            : route('vacations.targets', ['slug' => $currentPage->slug]);

        $siblings = CategoryPage::query()
            ->whereRaw('LOWER(type) = ?', ['targets'])
            ->get()
            ->map(function (CategoryPage $item) use ($scope, $locale) {
                $item->language = $this->categoryContent->resolveForDisplay($item, $scope, $locale);

                return $item;
            })
            ->filter(fn (CategoryPage $item) => $item->language !== null
                && filled($item->language->title)
                && $item->source !== null
                && (int) $item->source_id !== 0
            )
            ->when(
                $scope === CategoryPageScope::TOURS,
                fn (Collection $items) => $items->filter(
                    fn (CategoryPage $item) => $this->guidingAvailability->hasGuidingsForTarget((int) $item->source_id)
                ),
            )
            ->reject(fn (CategoryPage $item) => (int) $item->source_id === $currentSpeciesId)
            ->map(fn (CategoryPage $item) => [
                'id' => (int) $item->source_id,
                'name' => $item->language->title,
                'url' => $scope === CategoryPageScope::TOURS
                    ? route('guidings.targets', ['slug' => $item->slug])
                    : route('vacations.targets', ['slug' => $item->slug]),
            ])
            ->values();

        return $siblings->push([
            'id' => $currentSpeciesId,
            'name' => $currentPage->language->title ?? $currentPage->name,
            'url' => $currentUrl,
        ])->values();
    }
}
