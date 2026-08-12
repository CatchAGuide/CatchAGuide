<?php

namespace App\Http\Controllers\Admin\Category;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageDimension;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Http\Controllers\Controller;
use App\Models\CategoryPage;
use App\Models\Target;
use App\Services\CategoryPage\CategoryPageContentService;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;

class AdminCategoryTargetFishController extends Controller
{
    public function __construct(
        private CategoryPageContentService $content
    ) {}

    public function index()
    {
        $scopes = CategoryPageScope::forDimension(CategoryPageDimension::TARGETS);

        $rows = Target::query()
            ->orderBy('name')
            ->with(['categoryPage' => fn ($query) => $query->where('type', 'Targets')])
            ->paginate(25);

        $rows->getCollection()->transform(function ($target) use ($scopes) {
            $target->scope_completeness = $this->content->completenessForTargetFish($target->id, $scopes);
            $target->filled_locales = $this->content->filledLocalesFromCompleteness($target->scope_completeness);
            $target->has_any_content = $target->filled_locales !== [];

            return $target;
        });

        return view('admin.pages.category.target-fish', compact('rows', 'scopes'));
    }

    public function update(Request $request, $id)
    {
        $scopes = CategoryPageScope::forDimension(CategoryPageDimension::TARGETS);

        $request->validate([
            'name' => 'required|max:255',
            'title' => 'required|max:255',
            'sub_title' => 'required|max:255',
            'languageSwitch' => ['required', Rule::in(config('app.locales'))],
            'content_scope' => ['required', Rule::in($scopes)],
        ]);

        try {
            DB::beginTransaction();

            $categoryPage = $this->resolveCategoryPage((int) $id, $request->name);

            if ($request->hasFile('thumbnailImage')) {
                $categoryPage->thumbnail_path = $this->uploadThumbnail($request->file('thumbnailImage'));
                $categoryPage->save();
            }

            $scope = $request->input('content_scope');
            $locale = $request->input('languageSwitch');
            $faqs = collect($request->input('faq', []))->values()->all();

            $this->content->upsert($categoryPage, $scope, $locale, [
                'title' => $request->title ?? '',
                'sub_title' => $request->sub_title ?? '',
                'introduction' => $request->introduction ?? '',
                'content' => $request->content ?? '',
                'faq_title' => $request->faq_title ?? '',
            ], $faqs);

            if ($request->boolean('translate_to_en') && $locale === 'de') {
                $this->content->translateScope($categoryPage, $scope, 'de', 'en');
            }

            DB::commit();

            return redirect()
                ->route('admin.category.target-fish.edit', [
                    'target_fish' => $id,
                    'scope' => $scope,
                    'language' => $locale,
                ])
                ->with('success', 'Target fish category page saved.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Target fish category save failed', ['message' => $e->getMessage()]);

            return redirect()->back()->withErrors(['message' => 'Something went wrong. Please try again.']);
        }
    }

    public function edit($id)
    {
        $target = Target::with('categoryPage')->find($id);

        if ($target === null) {
            return redirect()->back();
        }

        $categoryPage = $target->categoryPage;
        $scopes = CategoryPageScope::forDimension(CategoryPageDimension::TARGETS);
        $activeScope = request('scope', CategoryPageScope::TOURS);
        if (! in_array($activeScope, $scopes, true)) {
            $activeScope = CategoryPageScope::TOURS;
        }

        $language = request('language', 'de');
        if (! in_array($language, config('app.locales'), true)) {
            $language = 'de';
        }

        $title = '';
        $sub_title = '';
        $introduction = '';
        $content = '';
        $faq_title = '';
        $faq = collect();

        $languageRow = $this->content->findForEntity(
            CategoryPageEntityType::TARGET_FISH,
            $target->id,
            $activeScope,
            $language,
        );

        if ($languageRow) {
            $title = $languageRow->title ?? '';
            $sub_title = $languageRow->sub_title ?? '';
            $introduction = $languageRow->introduction ?? '';
            $content = $languageRow->content ?? '';
            $faq_title = $languageRow->faq_title ?? '';
        }

        $faq = $this->content->faqsForEntity(
            CategoryPageEntityType::TARGET_FISH,
            $target->id,
            $activeScope,
            $language,
        );
        $completeness = $this->content->completenessForTargetFish($target->id, $scopes);

        $slug = $categoryPage?->slug ?? $this->slugFormat($target->getRawOriginal('name') ?? $target->name);

        return view('admin.pages.category.catalog-editor', [
            'formTitle' => 'Target Fish',
            'route' => route('admin.category.target-fish.update', $id),
            'languageDataUrl' => route('admin.category.target-fish.language-data', $target->id),
            'backToListRoute' => route('admin.category.target-fish.index'),
            'nameReadonly' => true,
            'language' => $language,
            'activeScope' => $activeScope,
            'scopes' => $scopes,
            'completeness' => $completeness,
            'name' => $target->name ?? '',
            'thumbnail' => $categoryPage ? $categoryPage->getThumbnailPath() : asset('assets/images/300x300.png'),
            'publicUrl' => url('/category-page/targets/'.$slug),
            'title' => $title,
            'sub_title' => $sub_title,
            'introduction' => $introduction,
            'content' => $content,
            'faq_title' => $faq_title,
            'faq' => $faq,
        ]);
    }

    public function toggleFavorite(Request $request)
    {
        try {
            $target = Target::findOrFail($request->id);

            if ($target->categoryPage) {
                $categoryPage = $target->categoryPage;
                $categoryPage->is_favorite = (int) $request->status;
                $categoryPage->save();
            } else {
                $categoryPage = CategoryPage::query()->create([
                    'name' => $target->name,
                    'is_favorite' => (int) $request->status,
                    'type' => 'Targets',
                    'source_id' => $target->id,
                    'slug' => $this->slugFormat($target->name),
                ]);
            }

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getLanguageData($id)
    {
        $target = Target::find($id);

        if ($target === null) {
            return response()->json(['error' => 'Target not found'], 404);
        }

        $scopes = CategoryPageScope::forDimension(CategoryPageDimension::TARGETS);
        $scope = request('scope', CategoryPageScope::TOURS);
        if (! in_array($scope, $scopes, true)) {
            $scope = CategoryPageScope::TOURS;
        }

        $language = request('language', 'de');
        $languageData = $this->content->findForEntity(CategoryPageEntityType::TARGET_FISH, $target->id, $scope, $language);
        $faq = $this->content->faqsForEntity(CategoryPageEntityType::TARGET_FISH, $target->id, $scope, $language);

        return response()->json([
            'title' => $languageData->title ?? '',
            'sub_title' => $languageData->sub_title ?? '',
            'introduction' => $languageData->introduction ?? '',
            'content' => $languageData->content ?? '',
            'faq_title' => $languageData->faq_title ?? '',
            'faq' => $faq,
        ]);
    }

    private function resolveCategoryPage(int $targetId, string $name): CategoryPage
    {
        $categoryPage = CategoryPage::query()
            ->where('source_id', $targetId)
            ->where('type', 'Targets')
            ->first();

        if ($categoryPage) {
            $categoryPage->update(['name' => $name]);

            return $categoryPage;
        }

        return CategoryPage::query()->create([
            'type' => 'Targets',
            'source_id' => $targetId,
            'name' => $name,
            'slug' => $this->slugFormat($name),
        ]);
    }

    private function uploadThumbnail($thumbnailImage): string
    {
        $thumbnail_path = $thumbnailImage->store('public');
        $imagePath = Storage::disk()->path($thumbnail_path);

        $image = Image::make($imagePath);
        $webpImageName = pathinfo($thumbnail_path, PATHINFO_FILENAME).'.webp';
        $webpImage = $image->encode('webp', 75);

        $webp_path = 'category/targets/';

        if (! Storage::disk('public_path')->exists($webp_path)) {
            Storage::disk('public_path')->makeDirectory($webp_path);
        }

        $webp_path .= $webpImageName;

        Storage::disk('public_path')->put($webp_path, $webpImage->encoded);
        $webpImage->save(public_path($webp_path));

        return $webp_path;
    }

    private function slugFormat(string $value): string
    {
        return str_replace(' ', '-', strtolower($value));
    }
}
