<?php

namespace App\Http\Controllers\Admin\Category;

use App\Domain\CategoryPage\CategoryPageDimension;
use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Http\Controllers\Admin\Category\Concerns\HandlesScopedCategoryContent;
use App\Http\Controllers\Controller;
use App\Services\CategoryPage\CategoryPageContentService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AdminCategoryDestinationHubController extends Controller
{
    use HandlesScopedCategoryContent;

    public function __construct(
        private CategoryPageContentService $content
    ) {}

    public function edit()
    {
        $scopes = CategoryPageScope::forDimension(CategoryPageDimension::DESTINATION_HUB);
        $scoped = $this->scopedEditorPayload(
            $this->content,
            CategoryPageEntityType::DESTINATION_HUB,
            CategoryPageEntityType::DESTINATION_HUB_SOURCE_ID,
            $scopes,
            CategoryPageScope::GLOBAL,
        );

        $defaults = $this->content->destinationHubFields($scoped['language']);
        foreach (['title', 'sub_title', 'introduction'] as $field) {
            if (! filled($scoped[$field])) {
                $scoped[$field] = $defaults[$field];
            }
        }

        return view('admin.pages.category.catalog-editor', array_merge($scoped, [
            'formTitle' => __('admin.category_pages.dimensions.destination_hub'),
            'route' => route('admin.category.destination-hub.update'),
            'languageDataUrl' => route('admin.category.destination-hub.language-data'),
            'autosaveUrl' => route('admin.category.destination-hub.autosave'),
            'nameReadonly' => true,
            'showThumbnail' => false,
            'name' => __('admin.category_pages.dimensions.destination_hub'),
            'thumbnail' => asset('assets/images/300x300.png'),
            'publicUrl' => route('destination'),
            'requireSeoFields' => true,
        ]));
    }

    public function update(Request $request)
    {
        $scopes = CategoryPageScope::forDimension(CategoryPageDimension::DESTINATION_HUB);

        $request->validate([
            'title' => 'required|max:255',
            'sub_title' => 'required|max:255',
            'languageSwitch' => ['required', Rule::in(config('app.locales'))],
            'content_scope' => ['required', Rule::in($scopes)],
        ]);

        try {
            DB::beginTransaction();

            $this->saveScopedContent(
                $request,
                $this->content,
                CategoryPageEntityType::DESTINATION_HUB,
                CategoryPageEntityType::DESTINATION_HUB_SOURCE_ID,
                $scopes,
            );

            DB::commit();

            return redirect()
                ->route('admin.category.destination-hub.edit', [
                    'scope' => $request->input('content_scope'),
                    'language' => $request->input('languageSwitch'),
                ])
                ->with('success', __('admin.category_pages.destination_hub_saved'));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Destination hub save failed', ['message' => $e->getMessage()]);

            return redirect()->back()->withErrors(['message' => __('admin.category_pages.save_error')]);
        }
    }

    public function getLanguageData()
    {
        $scopes = CategoryPageScope::forDimension(CategoryPageDimension::DESTINATION_HUB);
        $scope = request('scope', CategoryPageScope::GLOBAL);
        if (! in_array($scope, $scopes, true)) {
            $scope = CategoryPageScope::GLOBAL;
        }

        $payload = $this->scopedLanguageDataResponse(
            $this->content,
            CategoryPageEntityType::DESTINATION_HUB,
            CategoryPageEntityType::DESTINATION_HUB_SOURCE_ID,
            $scope,
        );

        $locale = request('language', 'de');
        if (! in_array($locale, config('app.locales'), true)) {
            $locale = 'de';
        }

        $defaults = $this->content->destinationHubFields($locale);
        foreach (['title', 'sub_title', 'introduction'] as $field) {
            if (! filled($payload[$field])) {
                $payload[$field] = $defaults[$field];
            }
        }

        return response()->json($payload);
    }

    public function autosave(Request $request)
    {
        return $this->autosaveScopedContent(
            $request,
            $this->content,
            CategoryPageEntityType::DESTINATION_HUB,
            CategoryPageEntityType::DESTINATION_HUB_SOURCE_ID,
            CategoryPageScope::forDimension(CategoryPageDimension::DESTINATION_HUB),
        );
    }
}
