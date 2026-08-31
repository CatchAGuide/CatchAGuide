<?php

namespace App\Http\Controllers\Admin\Category\Concerns;

use App\Models\Language;
use App\Services\CategoryPage\CategoryPageContentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

trait HandlesScopedCategoryContent
{
    /**
     * Which locales have any `languages` content for each entity id, for admin index pages that
     * show language-flag indicators (previously read off a `translations()` Eloquent relation).
     *
     * @return Collection<int, Collection<int, string>>
     */
    protected function languagesByEntity(Collection $entityIds, string $entityType): Collection
    {
        return Language::query()
            ->where('type', $entityType)
            ->whereIn('source_id', $entityIds->map(fn ($id) => (string) $id))
            ->get(['source_id', 'language'])
            ->groupBy(fn ($row) => (int) $row->source_id)
            ->map(fn ($rows) => $rows->pluck('language')->unique()->values());
    }

    protected function validateScopedContent(Request $request, array $scopes): void
    {
        $request->validate([
            'content_scope' => ['nullable', Rule::in($scopes)],
            'languageSwitch' => ['nullable', Rule::in(config('app.locales'))],
            'title' => 'nullable|max:255',
            'sub_title' => 'nullable|max:255',
        ]);
    }

    protected function saveScopedContent(
        Request $request,
        CategoryPageContentService $content,
        string $entityType,
        int|string $sourceId,
        array $scopes,
    ): void {
        if (! $request->filled('content_scope')) {
            return;
        }

        $scope = $request->input('content_scope');
        if (! in_array($scope, $scopes, true)) {
            return;
        }

        $locale = $request->input('languageSwitch', $request->input('language', 'de'));
        $faqs = collect($request->input('faq', []))->values()->all();

        $fields = [
            'title' => $request->input('title', ''),
            'sub_title' => $request->input('sub_title', ''),
            'introduction' => $request->input('introduction', ''),
            'content' => $request->input('content', $request->input('body', '')),
            'faq_title' => $request->input('faq_title', ''),
        ];

        // Only present on the main form submit, not the lighter autosave payload — don't null
        // these out when they're simply absent from the request.
        foreach ([
            'fish_avail_title', 'fish_avail_intro', 'size_limit_title',
            'size_limit_intro', 'time_limit_title', 'time_limit_intro',
        ] as $fishField) {
            if ($request->has($fishField)) {
                $fields[$fishField] = $request->input($fishField);
            }
        }

        $content->upsertEntity($entityType, $sourceId, $scope, $locale, $fields, $faqs);

        if ($request->boolean('translate_to_en') && $locale === 'de') {
            $content->translateEntityScope($entityType, $sourceId, $scope, 'de', 'en');
        }
    }

    protected function scopedEditorPayload(
        CategoryPageContentService $content,
        string $entityType,
        int|string $sourceId,
        array $scopes,
        string $defaultScope,
        string $defaultLocale = 'de',
    ): array {
        $activeScope = request('scope', $defaultScope);
        if (! in_array($activeScope, $scopes, true)) {
            $activeScope = $defaultScope;
        }

        $language = request('language', $defaultLocale);
        if (! in_array($language, config('app.locales'), true)) {
            $language = $defaultLocale;
        }

        $languageRow = $content->findForEntity($entityType, $sourceId, $activeScope, $language);
        $faq = $content->faqsForEntity($entityType, $sourceId, $activeScope, $language);

        return [
            'scopes' => $scopes,
            'activeScope' => $activeScope,
            'language' => $language,
            'completeness' => $content->completenessForEntity($entityType, $sourceId, $scopes),
            'title' => $languageRow->title ?? '',
            'sub_title' => $languageRow->sub_title ?? '',
            'introduction' => $languageRow->introduction ?? '',
            'content' => $languageRow->content ?? '',
            'faq_title' => $languageRow->faq_title ?? '',
            'fish_avail_title' => $languageRow->fish_avail_title ?? '',
            'fish_avail_intro' => $languageRow->fish_avail_intro ?? '',
            'size_limit_title' => $languageRow->size_limit_title ?? '',
            'size_limit_intro' => $languageRow->size_limit_intro ?? '',
            'time_limit_title' => $languageRow->time_limit_title ?? '',
            'time_limit_intro' => $languageRow->time_limit_intro ?? '',
            'faq' => $faq,
        ];
    }

    protected function autosaveScopedContent(
        Request $request,
        CategoryPageContentService $content,
        string $entityType,
        int|string $sourceId,
        array $scopes,
    ): JsonResponse {
        $request->validate([
            'content_scope' => ['required', Rule::in($scopes)],
            'languageSwitch' => ['required', Rule::in(config('app.locales'))],
            'title' => 'nullable|string|max:255',
            'sub_title' => 'nullable|string|max:255',
            'introduction' => 'nullable|string',
            'content' => 'nullable|string',
            'faq_title' => 'nullable|string|max:255',
            'faq' => 'nullable|array',
            'faq.*.question' => 'nullable|string',
            'faq.*.answer' => 'nullable|string',
        ]);

        $this->saveScopedContent($request, $content, $entityType, $sourceId, $scopes);

        return response()->json([
            'ok' => true,
            'scope' => $request->input('content_scope'),
            'language' => $request->input('languageSwitch'),
            'completeness' => $content->completenessForEntity($entityType, $sourceId, $scopes),
        ]);
    }

    protected function scopedLanguageDataResponse(
        CategoryPageContentService $content,
        string $entityType,
        int|string $sourceId,
        string $scope,
    ): array {
        $language = request('language', 'de');
        $languageData = $content->findForEntity($entityType, $sourceId, $scope, $language);
        $faq = $content->faqsForEntity($entityType, $sourceId, $scope, $language);

        return [
            'title' => $languageData->title ?? '',
            'sub_title' => $languageData->sub_title ?? '',
            'introduction' => $languageData->introduction ?? '',
            'content' => $languageData->content ?? '',
            'faq_title' => $languageData->faq_title ?? '',
            'fish_avail_title' => $languageData->fish_avail_title ?? '',
            'fish_avail_intro' => $languageData->fish_avail_intro ?? '',
            'size_limit_title' => $languageData->size_limit_title ?? '',
            'size_limit_intro' => $languageData->size_limit_intro ?? '',
            'time_limit_title' => $languageData->time_limit_title ?? '',
            'time_limit_intro' => $languageData->time_limit_intro ?? '',
            'faq' => $faq,
        ];
    }
}
