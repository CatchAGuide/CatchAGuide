<?php

namespace App\Http\Controllers\Category;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Http\Controllers\Controller;
use App\Models\CategoryPage;
use App\Services\CategoryPage\CategoryPageContentService;
use App\Services\Homepage\HomepageMixedOfferSelector;
use App\Services\Offers\OfferCatalogPageService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TargetFishPageController extends Controller
{
    public function __construct(
        private OfferCatalogPageService $offerCatalog,
        private CategoryPageContentService $categoryContent,
        private HomepageMixedOfferSelector $mixedOffers,
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

        $placeName = $page->source->name ?? $page->name;

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

        $vm = $this->offerCatalog->buildForTargetFish($request, $speciesId, $scope);

        return view('pages.category.category-show', [
            'row_data' => $page,
            'title' => $page->language->title ?? $page->name,
            'vm' => $vm,
            'content_scope' => $scope,
        ]);
    }
}
