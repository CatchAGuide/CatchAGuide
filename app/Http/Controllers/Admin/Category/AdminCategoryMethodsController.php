<?php

namespace App\Http\Controllers\Admin\Category;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageDimension;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Http\Controllers\Admin\Category\Concerns\HandlesScopedCategoryContent;
use App\Http\Controllers\Controller;
use App\Models\CategoryPage;
use App\Models\Method;
use App\Services\CategoryPage\CategoryPageContentService;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;

class AdminCategoryMethodsController extends Controller
{
    use HandlesScopedCategoryContent;

    public function __construct(
        private CategoryPageContentService $content
    ) {}

    public function index()
    {
        $scopes = CategoryPageScope::forDimension(CategoryPageDimension::METHODS);

        $rows = Method::query()
            ->orderBy('name')
            ->with(['categoryPage' => fn ($query) => $query->where('type', 'Methods')])
            ->paginate(25);

        $rows->getCollection()->transform(function ($method) use ($scopes) {
            $method->scope_completeness = $this->content->completenessForMethod($method->id, $scopes);
            $method->filled_locales = $this->content->filledLocalesFromCompleteness($method->scope_completeness);
            $method->has_any_content = $method->filled_locales !== [];

            return $method;
        });

        return view('admin.pages.category.methods', compact('rows', 'scopes'));
    }

    public function update(Request $request, $id)
    {
        $scopes = CategoryPageScope::forDimension(CategoryPageDimension::METHODS);

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
                ->route('admin.category.methods.edit', [
                    'method' => $id,
                    'scope' => $scope,
                    'language' => $locale,
                ])
                ->with('success', 'Method category page saved.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Method category save failed', ['message' => $e->getMessage()]);

            return redirect()->back()->withErrors(['message' => 'Something went wrong. Please try again.']);
        }
    }

    public function edit($id)
    {
        $method = Method::with('categoryPage')->find($id);

        if ($method === null) {
            return redirect()->back();
        }

        $categoryPage = $method->categoryPage;
        $scopes = CategoryPageScope::forDimension(CategoryPageDimension::METHODS);
        $activeScope = CategoryPageScope::TOURS;

        if ($categoryPage) {
            $scoped = $this->scopedEditorPayload(
                $this->content,
                CategoryPageEntityType::METHOD,
                $method->id,
                $scopes,
                $activeScope,
            );
        } else {
            $scoped = [
                'scopes' => $scopes,
                'activeScope' => $activeScope,
                'language' => 'de',
                'completeness' => $this->content->completenessForMethod($method->id, $scopes),
                'title' => '',
                'sub_title' => '',
                'introduction' => '',
                'content' => '',
                'faq_title' => '',
                'faq' => collect(),
            ];
        }

        $slug = $categoryPage?->slug ?? $this->slugFormat($method->name);

        return view('admin.pages.category.catalog-editor', array_merge($scoped, [
            'formTitle' => 'Methods',
            'route' => route('admin.category.methods.update', $id),
            'languageDataUrl' => route('admin.category.methods.language-data', $method->id),
            'autosaveUrl' => route('admin.category.methods.autosave', $method->id),
            'backToListRoute' => route('admin.category.methods.index'),
            'nameReadonly' => false,
            'name' => $method->name ?? '',
            'thumbnail' => $categoryPage ? $categoryPage->getThumbnailPath() : asset('assets/images/300x300.png'),
            'publicUrl' => route('guidings.methods.show', ['slug' => $slug]),
        ]));
    }

    public function toggleFavorite(Request $request)
    {
        try {
            $method = Method::findOrFail($request->id);

            if ($method->categoryPage) {
                $categoryPage = $method->categoryPage;
                $categoryPage->is_favorite = (int) $request->status;
                $categoryPage->save();
            } else {
                CategoryPage::query()->create([
                    'name' => $method->name,
                    'is_favorite' => (int) $request->status,
                    'type' => 'Methods',
                    'source_id' => $method->id,
                    'slug' => $this->slugFormat($method->name),
                ]);
            }

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getLanguageData($id)
    {
        $method = Method::find($id);

        if ($method === null) {
            return response()->json(['error' => 'Method not found'], 404);
        }

        $scopes = CategoryPageScope::forDimension(CategoryPageDimension::METHODS);
        $scope = request('scope', CategoryPageScope::TOURS);
        if (! in_array($scope, $scopes, true)) {
            $scope = CategoryPageScope::TOURS;
        }

        return response()->json(
            $this->scopedLanguageDataResponse($this->content, CategoryPageEntityType::METHOD, $method->id, $scope)
        );
    }

    public function autosave(Request $request, $id)
    {
        $method = Method::find($id);

        if ($method === null) {
            return response()->json(['error' => 'Method not found'], 404);
        }

        return $this->autosaveScopedContent(
            $request,
            $this->content,
            CategoryPageEntityType::METHOD,
            $method->id,
            CategoryPageScope::forDimension(CategoryPageDimension::METHODS),
        );
    }

    private function resolveCategoryPage(int $methodId, string $name): CategoryPage
    {
        $categoryPage = CategoryPage::query()
            ->where('source_id', $methodId)
            ->where('type', 'Methods')
            ->first();

        if ($categoryPage) {
            $categoryPage->update(['name' => $name]);

            return $categoryPage;
        }

        return CategoryPage::query()->create([
            'type' => 'Methods',
            'source_id' => $methodId,
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
        $webp_path = 'category/methods/';

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
