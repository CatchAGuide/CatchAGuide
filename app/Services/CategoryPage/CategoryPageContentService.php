<?php

namespace App\Services\CategoryPage;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Models\CategoryEntity;
use App\Models\CategoryPage;
use App\Models\Faq;
use App\Models\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CategoryPageContentService
{
    public function findLanguage(CategoryPage $page, string $scope, string $locale): ?Language
    {
        [$entityType, $sourceId] = $this->entityKeyForPage($page);

        return $this->findForEntity($entityType, $sourceId, $scope, $locale);
    }

    public function findForEntity(string $entityType, int|string $sourceId, string $scope, string $locale): ?Language
    {
        $query = Language::query()
            ->where('source_id', (string) $sourceId)
            ->where('language', $locale);

        if ($this->usesLegacyToursLookup($entityType, $scope)) {
            $query->where(function ($builder) use ($entityType) {
                $builder->where('type', $entityType)->orWhereNull('type');
            })->where(function ($builder) {
                $builder->where('scope', CategoryPageScope::TOURS)->orWhereNull('scope');
            });
        } else {
            $query->where('type', $entityType)->where('scope', $scope);
        }

        return $query->first();
    }

    public function resolveForDisplay(
        CategoryPage $page,
        string $scope,
        string $locale,
        bool $allowCrossScopeFallback = true,
    ): ?Language {
        [$entityType, $sourceId] = $this->entityKeyForPage($page);

        return $this->resolveEntityForDisplay(
            $entityType,
            $sourceId,
            $scope,
            $locale,
            fn (string $loc) => $this->legacyCategoryPageLanguage($page, $scope, $loc),
            $allowCrossScopeFallback,
        );
    }

    public function resolveEntityForDisplay(
        string $entityType,
        int|string $sourceId,
        string $scope,
        string $locale,
        ?callable $legacyResolver = null,
        bool $allowCrossScopeFallback = true,
    ): ?Language {
        $content = $this->findForEntity($entityType, $sourceId, $scope, $locale);

        if ($this->hasMeaningfulContent($content)) {
            return $content;
        }

        if ($allowCrossScopeFallback && $scope === CategoryPageScope::GLOBAL) {
            $tours = $this->findForEntity($entityType, $sourceId, CategoryPageScope::TOURS, $locale);
            if ($this->hasMeaningfulContent($tours)) {
                return $tours;
            }
        }

        if ($legacyResolver !== null) {
            $legacy = $legacyResolver($locale);
            if ($legacy !== null) {
                return $legacy;
            }
        }

        return $content;
    }

    /** @return Collection<int, Faq> */
    public function faqsFor(CategoryPage $page, string $scope, string $locale): Collection
    {
        [$entityType, $sourceId] = $this->entityKeyForPage($page);

        return $this->faqsForEntity($entityType, $sourceId, $scope, $locale);
    }

    /** @return Collection<int, Faq> */
    public function faqsForEntity(string $entityType, int|string $sourceId, string $scope, string $locale): Collection
    {
        $query = Faq::query()
            ->where('source_id', $sourceId)
            ->where('page', CategoryPageEntityType::faqPageKey($entityType))
            ->where('language', $locale);

        if ($this->usesLegacyToursLookup($entityType, $scope)) {
            $query->where(function ($builder) {
                $builder->where('scope', CategoryPageScope::TOURS)->orWhereNull('scope');
            });
        } else {
            $query->where('scope', $scope);
        }

        return $query->orderBy('id')->get();
    }

    /**
     * @param  array{title?: string, sub_title?: string, introduction?: string, content?: string, faq_title?: string}  $fields
     * @param  list<array{question: string, answer: string}>  $faqs
     */
    public function upsert(CategoryPage $page, string $scope, string $locale, array $fields, array $faqs = []): Language
    {
        [$entityType, $sourceId] = $this->entityKeyForPage($page);

        return $this->upsertEntity($entityType, $sourceId, $scope, $locale, $fields, $faqs);
    }

    /**
     * @param  array{title?: string, sub_title?: string, introduction?: string, content?: string, faq_title?: string, fish_avail_title?: string, fish_avail_intro?: string, size_limit_title?: string, size_limit_intro?: string, time_limit_title?: string, time_limit_intro?: string}  $fields
     * @param  list<array{question: string, answer: string}>  $faqs
     */
    public function upsertEntity(
        string $entityType,
        int|string $sourceId,
        string $scope,
        string $locale,
        array $fields,
        array $faqs = [],
    ): Language {
        $payload = [
            'title' => $fields['title'] ?? '',
            'sub_title' => $fields['sub_title'] ?? '',
            'introduction' => $fields['introduction'] ?? '',
            'content' => $fields['content'] ?? '',
            'faq_title' => $fields['faq_title'] ?? '',
        ];

        // Only set these when the caller actually provided them, so callers that don't know
        // about the fish-section fields (e.g. translateEntityScope()) don't null out existing
        // values on update.
        foreach ([
            'fish_avail_title', 'fish_avail_intro', 'size_limit_title',
            'size_limit_intro', 'time_limit_title', 'time_limit_intro',
        ] as $fishField) {
            if (array_key_exists($fishField, $fields)) {
                $payload[$fishField] = $fields[$fishField];
            }
        }

        $language = Language::query()->updateOrCreate(
            [
                'source_id' => (string) $sourceId,
                'type' => $entityType,
                'scope' => $scope,
                'language' => $locale,
            ],
            $payload,
        );

        $this->replaceFaqsForEntity($entityType, $sourceId, $scope, $locale, $faqs);

        return $language;
    }

    /**
     * Replace the FAQ rows for one (entity, scope, locale) key, independent of `languages`
     * content. Needed because a dimension's FAQ scope doesn't always match its content scope —
     * e.g. Country content defaults to `global` but legacy-migrated FAQs all live at `tours`
     * (per the 2026-08-12 scope-backfill precedent, applied uniformly across dimensions).
     *
     * @param  list<array{question: string, answer: string}>  $faqs
     */
    public function replaceFaqsForEntity(string $entityType, int|string $sourceId, string $scope, string $locale, array $faqs): void
    {
        Faq::query()
            ->where('source_id', $sourceId)
            ->where('page', CategoryPageEntityType::faqPageKey($entityType))
            ->where('scope', $scope)
            ->where('language', $locale)
            ->delete();

        foreach ($faqs as $faq) {
            if (trim($faq['question'] ?? '') === '' && trim($faq['answer'] ?? '') === '') {
                continue;
            }

            Faq::query()->create([
                'page' => CategoryPageEntityType::faqPageKey($entityType),
                'source_id' => $sourceId,
                'scope' => $scope,
                'language' => $locale,
                'question' => $faq['question'] ?? '',
                'answer' => $faq['answer'] ?? '',
            ]);
        }
    }

    public function translateScope(CategoryPage $page, string $scope, string $fromLocale, string $toLocale): void
    {
        [$entityType, $sourceId] = $this->entityKeyForPage($page);

        $this->translateEntityScope($entityType, $sourceId, $scope, $fromLocale, $toLocale);
    }

    public function translateEntityScope(
        string $entityType,
        int|string $sourceId,
        string $scope,
        string $fromLocale,
        string $toLocale,
    ): void {
        $source = $this->findForEntity($entityType, $sourceId, $scope, $fromLocale);

        if ($source === null) {
            return;
        }

        $fields = [
            'title' => $this->translateField($source->title, $toLocale),
            'sub_title' => $this->translateField($source->sub_title, $toLocale),
            'introduction' => $this->translateField($source->introduction, $toLocale),
            'content' => $this->translateField($source->content, $toLocale),
            'faq_title' => $this->translateField($source->faq_title, $toLocale),
        ];

        $sourceFaqs = $this->faqsForEntity($entityType, $sourceId, $scope, $fromLocale)
            ->map(fn (Faq $faq) => [
                'question' => $this->translateField($faq->question, $toLocale),
                'answer' => $this->translateField($faq->answer, $toLocale),
            ])->all();

        $this->upsertEntity($entityType, $sourceId, $scope, $toLocale, $fields, $sourceFaqs);
    }

    /**
     * @param  list<string>  $scopes
     * @return array<string, array{de: bool, en: bool}>
     */
    public function completenessForScopes(CategoryPage $page, array $scopes): array
    {
        [$entityType, $sourceId] = $this->entityKeyForPage($page);

        return $this->completenessForEntity($entityType, $sourceId, $scopes);
    }

    /**
     * @param  list<string>  $scopes
     * @return array<string, array{de: bool, en: bool}>
     */
    public function completenessForTargetFish(int $targetId, array $scopes): array
    {
        return $this->completenessForEntity(CategoryPageEntityType::TARGET_FISH, $targetId, $scopes);
    }

    /**
     * @param  list<string>  $scopes
     * @return array<string, array{de: bool, en: bool}>
     */
    public function completenessForMethod(int $methodId, array $scopes): array
    {
        return $this->completenessForEntity(CategoryPageEntityType::METHOD, $methodId, $scopes);
    }

    /**
     * @return array{title: string, sub_title: string, introduction: string, content: string, faq_title: string}
     */
    public function destinationHubFields(string $locale, bool $fallbackToLang = true): array
    {
        $content = $this->resolveEntityForDisplay(
            CategoryPageEntityType::DESTINATION_HUB,
            CategoryPageEntityType::DESTINATION_HUB_SOURCE_ID,
            CategoryPageScope::GLOBAL,
            $locale,
            null,
            false,
        );

        $title = $content?->title;
        $subTitle = $content?->sub_title;
        $introduction = $content?->introduction;

        if ($fallbackToLang) {
            $title = filled($title) ? $title : trans('destination.title', [], $locale);
            $subTitle = filled($subTitle) ? $subTitle : trans('destination.header_sub_title', [], $locale);
            $introduction = filled($introduction) ? $introduction : trans('destination.introduction', [], $locale);
        }

        return [
            'title' => $title ?? '',
            'sub_title' => $subTitle ?? '',
            'introduction' => $introduction ?? '',
            'content' => $content?->content ?? '',
            'faq_title' => $content?->faq_title ?? '',
        ];
    }

    /**
     * @param  array<string, array{de: bool, en: bool}>  $completeness
     * @return list<string>
     */
    public function filledLocalesFromCompleteness(array $completeness): array
    {
        $filled = [];

        foreach (config('app.locales') as $locale) {
            foreach ($completeness as $scopeLocales) {
                if ($scopeLocales[$locale] ?? false) {
                    $filled[] = $locale;
                    break;
                }
            }
        }

        return $filled;
    }

    /**
     * @param  list<string>  $scopes
     * @return array<string, array{de: bool, en: bool}>
     */
    public function completenessForEntity(string $entityType, int|string $sourceId, array $scopes): array
    {
        $result = [];

        foreach ($scopes as $scope) {
            $result[$scope] = [
                'de' => $this->hasMeaningfulContent($this->findForEntity($entityType, $sourceId, $scope, 'de')),
                'en' => $this->hasMeaningfulContent($this->findForEntity($entityType, $sourceId, $scope, 'en')),
            ];
        }

        return $result;
    }

    /**
     * Entity source IDs that have filled category-page copy for the given scope.
     *
     * @return list<string>
     */
    public function sourceIdsWithMeaningfulScope(string $entityType, string $scope): array
    {
        return Language::query()
            ->where('type', $entityType)
            ->where('scope', $scope)
            ->where(function ($query) {
                foreach (['title', 'sub_title', 'introduction', 'content'] as $field) {
                    $query->orWhere(function ($inner) use ($field) {
                        $inner->whereNotNull($field)->where($field, '!=', '');
                    });
                }
            })
            ->pluck('source_id')
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Overlay scoped SEO fields onto a geo/destination model for frontend display.
     */
    public function applyScopedContentToModel(
        Model $model,
        string $entityType,
        string $scope,
        ?string $locale = null,
        ?callable $legacyResolver = null,
        bool $allowCrossScopeFallback = true,
    ): Model {
        $locale = $locale ?? app()->getLocale();
        $content = $this->resolveEntityForDisplay(
            $entityType,
            $model->getKey(),
            $scope,
            $locale,
            $legacyResolver,
            $allowCrossScopeFallback,
        );

        if ($model instanceof CategoryEntity) {
            $model->overlayScopedTranslation($content);

            return $model;
        }

        if ($content === null) {
            return $model;
        }

        $model->setAttribute('title', $content->title ?: $model->getAttribute('title'));
        $model->setAttribute('sub_title', $content->sub_title ?: $model->getAttribute('sub_title'));
        $model->setAttribute('introduction', $content->introduction ?: $model->getAttribute('introduction'));
        $model->setAttribute('content', $content->content ?: $model->getAttribute('content'));
        $model->setAttribute('faq_title', $content->faq_title ?: $model->getAttribute('faq_title'));

        return $model;
    }

    /** @return Collection<int, Faq> */
    public function resolveFaqsForEntityDisplay(
        string $entityType,
        int|string $sourceId,
        string $scope,
        string $locale,
        ?callable $legacyResolver = null,
        bool $allowCrossScopeFallback = true,
    ): Collection {
        $faqs = $this->faqsForEntity($entityType, $sourceId, $scope, $locale);

        if ($faqs->isNotEmpty()) {
            return $faqs;
        }

        if ($allowCrossScopeFallback && $scope === CategoryPageScope::GLOBAL) {
            $toursFaqs = $this->faqsForEntity($entityType, $sourceId, CategoryPageScope::TOURS, $locale);
            if ($toursFaqs->isNotEmpty()) {
                return $toursFaqs;
            }
        }

        if ($legacyResolver !== null) {
            return $legacyResolver($locale);
        }

        return collect();
    }


    private function hasMeaningfulContent(?Language $language): bool
    {
        if ($language === null) {
            return false;
        }

        return filled($language->title)
            || filled($language->sub_title)
            || filled($language->introduction)
            || filled($language->content);
    }

    private function translateField(?string $value, string $toLocale): string
    {
        if ($value === null || trim($value) === '') {
            return '';
        }

        return translate($value, $toLocale);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function entityKeyForPage(CategoryPage $page): array
    {
        return match (strtolower((string) $page->type)) {
            'targets' => [CategoryPageEntityType::TARGET_FISH, (string) $page->source_id],
            'methods' => [CategoryPageEntityType::METHOD, (string) $page->source_id],
            default => [CategoryPageEntityType::CATEGORY_PAGE, (string) $page->id],
        };
    }

    private function usesLegacyToursLookup(string $entityType, string $scope): bool
    {
        if ($scope !== CategoryPageScope::TOURS) {
            return false;
        }

        return in_array($entityType, [
            CategoryPageEntityType::CATEGORY_PAGE,
            CategoryPageEntityType::TARGET_FISH,
            CategoryPageEntityType::METHOD,
        ], true);
    }

    private function legacyCategoryPageLanguage(CategoryPage $page, string $scope, string $locale): ?Language
    {
        if ($this->catalogEntityForPage($page) === null) {
            return null;
        }

        $legacy = $this->findForEntity(CategoryPageEntityType::CATEGORY_PAGE, (string) $page->id, $scope, $locale);

        return $this->hasMeaningfulContent($legacy) ? $legacy : null;
    }

    private function catalogEntityForPage(CategoryPage $page): ?string
    {
        return match (strtolower((string) $page->type)) {
            'targets' => CategoryPageEntityType::TARGET_FISH,
            'methods' => CategoryPageEntityType::METHOD,
            default => null,
        };
    }
}
