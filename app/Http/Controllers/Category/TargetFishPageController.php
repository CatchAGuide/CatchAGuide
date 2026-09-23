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
use Illuminate\Http\RedirectResponse;
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

    public function show(Request $request, string $slug): View|RedirectResponse
    {
        $scope = (string) ($request->route('content_scope') ?: CategoryPageScope::GLOBAL);
        if (! in_array($scope, [
            CategoryPageScope::GLOBAL,
            CategoryPageScope::TOURS,
            CategoryPageScope::VACATIONS,
        ], true)) {
            abort(404);
        }

        // One URL per species page: an uppercase slug variant 301s to the lowercase one, the
        // same rule country slugs follow (see CLAUDE.md's "SEO / catalog page conventions").
        $lowerSlug = mb_strtolower(rawurldecode($slug), 'UTF-8');
        if ($lowerSlug !== rawurldecode($slug)) {
            return redirect()->route($request->route()->getName(), ['slug' => $lowerSlug] + $request->query(), 301);
        }

        // Vacations species pages narrow to camps/trips by path segment, never by ?vacation=.
        $vacationPillar = $scope === CategoryPageScope::VACATIONS
            ? $this->routeVacationPillar($request)
            : null;
        if ($scope === CategoryPageScope::VACATIONS
            && ($redirect = $this->redirectVacationQueryToPath($request, $slug, $vacationPillar))) {
            return $redirect;
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

        // A pillar-scoped page whose species has vacations but none of this pillar still renders
        // (the camps/trips toggle links here) but stays out of the index — see CLAUDE.md's
        // "SEO / catalog page conventions" on gating facet pages by inventory.
        $noindex = $vacationPillar !== null
            && $this->vacationTargetAvailability->activeListingCounts($speciesId, $placeName)[$vacationPillar.'s'] < 1;

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
                    'camp' => route('vacations.camps.targets', ['slug' => $page->slug]),
                    'trip' => route('vacations.trips.targets', ['slug' => $page->slug]),
                ],
            ]);
        }

        $vm = $this->offerCatalog->buildForTargetFish(
            $request,
            $speciesId,
            $scope,
            $placeName,
            $vacationPillar,
            $scope === CategoryPageScope::VACATIONS ? [
                'all' => route('vacations.targets', ['slug' => $page->slug]),
                'camp' => route('vacations.camps.targets', ['slug' => $page->slug]),
                'trip' => route('vacations.trips.targets', ['slug' => $page->slug]),
            ] : [],
        );

        return view('pages.category.category-show', [
            'row_data' => $page,
            'title' => $page->language->title ?? $page->name,
            'vm' => $vm,
            'content_scope' => $scope,
            'noindex' => $noindex,
            'speciesRedirectOptions' => $this->speciesRedirectOptions($page, $scope, $locale, $speciesId, $vacationPillar),
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
    private function speciesRedirectOptions(CategoryPage $currentPage, string $scope, string $locale, int $currentSpeciesId, ?string $vacationPillar = null): Collection
    {
        $routeName = match (true) {
            $scope === CategoryPageScope::TOURS => 'guidings.targets',
            $vacationPillar !== null => "vacations.{$vacationPillar}s.targets",
            default => 'vacations.targets',
        };
        $currentUrl = route($routeName, ['slug' => $currentPage->slug]);

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
                'url' => route($routeName, ['slug' => $item->slug]),
            ])
            ->values();

        return $siblings->push([
            'id' => $currentSpeciesId,
            'name' => $currentPage->language->title ?? $currentPage->name,
            'url' => $currentUrl,
        ])->values();
    }

    /**
     * camp|trip when the route is /vacations/{camps|trips}/targets/{slug}, null for /vacations/targets/{slug}.
     */
    private function routeVacationPillar(Request $request): ?string
    {
        $pillar = $request->route('vacation');

        return in_array($pillar, ['camp', 'trip'], true) ? $pillar : null;
    }

    /**
     * 301 a ?vacation= filter to the path that owns it: /vacations/targets/{slug}?vacation=camp
     * → /vacations/camps/targets/{slug}, and a pillar path asked for a different pillar (or "all")
     * → that pillar's path. Keeps each camps/trips species page a single self-canonical URL.
     */
    private function redirectVacationQueryToPath(Request $request, string $slug, ?string $routePillar): ?RedirectResponse
    {
        if (! $request->has('vacation')) {
            return null;
        }

        $requested = strtolower((string) $request->query('vacation', ''));
        $requested = in_array($requested, ['camp', 'trip'], true) ? $requested : null;
        $query = $request->except('vacation', 'type');

        if ($requested === $routePillar) {
            // Same pillar as the path (the filter form re-submits it) — only strip it if the path is plain.
            return null;
        }

        $routeName = $requested === null ? 'vacations.targets' : "vacations.{$requested}s.targets";

        return redirect()->route($routeName, ['slug' => $slug] + $query, 301);
    }
}
