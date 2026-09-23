<?php

namespace App\Services\Sitemap\Contributors;

use App\Contracts\Sitemap\SitemapContributorInterface;
use App\Models\Category;
use App\Models\GuideThread;
use App\Models\Thread;
use App\Services\Seo\LocalePathMapper;
use App\Services\Sitemap\SitemapContext;
use App\Services\Sitemap\SitemapEntry;
use App\Services\Sitemap\SitemapPathEncoder;
use Illuminate\Support\Collection;

final class MagazineSitemapContributor implements SitemapContributorInterface
{
    public function __construct(
        private readonly LocalePathMapper $localePathMapper,
        private readonly SitemapPathEncoder $encoder,
    ) {}

    public function key(): string
    {
        return 'magazine';
    }

    public function fileName(string $lang): string
    {
        return '/sitemap-magazine-' . $lang . '.xml';
    }

    public function entries(SitemapContext $context): Collection
    {
        $prefix = $this->localePathMapper->magazinePrefix($context->lang);
        $threads = Thread::query()
            ->where('language', $context->lang)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->orderBy('id')
            ->get(['slug', 'category_id', 'updated_at'])
            ->unique('slug');

        $entries = collect([SitemapEntry::make(
            $this->encoder->join($context->baseUrl, [$prefix]),
            $threads->max('updated_at')?->toAtomString(),
        )]);

        // Articles are written per language with unrelated slugs — the same path on the other
        // domain redirects to its magazine index — so they carry no hreflang alternates.
        foreach ($threads as $thread) {
            $entries->push(SitemapEntry::make(
                $this->encoder->join($context->baseUrl, [$prefix, $thread->slug]),
                $thread->updated_at?->toAtomString(),
                localized: false,
            ));
        }

        $this->pushCategoryPages($entries, $context, $prefix, $threads);
        $this->pushGuideThreads($entries, $context);

        return $entries;
    }

    /**
     * /{magazine}/categories/{id} — the magazine's category filter pages, linked from every
     * article. Listed only when the category has articles in this language (an empty category
     * page is thin); hreflang only when the other language's page isn't empty either.
     *
     * @param  Collection<int, Thread>  $threads
     */
    private function pushCategoryPages(Collection $entries, SitemapContext $context, string $prefix, Collection $threads): void
    {
        $otherLang = $context->lang === 'de' ? 'en' : 'de';
        $otherLangCategoryIds = Thread::query()
            ->where('language', $otherLang)
            ->whereNotNull('category_id')
            ->distinct()
            ->pluck('category_id')
            ->flip();

        $byCategory = $threads->whereNotNull('category_id')->groupBy('category_id');
        foreach (Category::query()->whereIn('id', $byCategory->keys())->orderBy('id')->get(['id']) as $category) {
            $entries->push(SitemapEntry::make(
                $this->encoder->join($context->baseUrl, [$prefix, 'categories', (string) $category->id]),
                $byCategory[$category->id]->max('updated_at')?->toAtomString(),
                localized: $otherLangCategoryIds->has($category->id),
            ));
        }
    }

    /**
     * Guide articles served at the site root (/{slug}, GuideThreadController@categoryIndex),
     * written per language with unrelated slugs — so no hreflang alternates.
     */
    private function pushGuideThreads(Collection $entries, SitemapContext $context): void
    {
        $guideThreads = GuideThread::query()
            ->where('language', $context->lang)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->orderBy('id')
            ->get(['slug', 'updated_at'])
            ->unique('slug');

        foreach ($guideThreads as $thread) {
            $entries->push(SitemapEntry::make(
                $this->encoder->join($context->baseUrl, [$thread->slug]),
                $thread->updated_at?->toAtomString(),
                localized: false,
            ));
        }
    }
}
