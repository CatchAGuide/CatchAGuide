<?php

namespace Tests\Unit\Sitemap;

use App\Models\Category;
use App\Models\GuideThread;
use App\Models\Thread;
use App\Services\Seo\LocalePathMapper;
use App\Services\Sitemap\Contributors\MagazineSitemapContributor;
use App\Services\Sitemap\SitemapContext;
use App\Services\Sitemap\SitemapEntry;
use App\Services\Sitemap\SitemapPathEncoder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Tests\TestCase;

class MagazineSitemapContributorTest extends TestCase
{
    use DatabaseTransactions;

    private const BASE = 'https://www.catchaguide.de';

    /**
     * @return Collection<string, SitemapEntry>
     */
    private function entries(string $lang = 'de'): Collection
    {
        return (new MagazineSitemapContributor(new LocalePathMapper(), new SitemapPathEncoder()))
            ->entries(new SitemapContext(self::BASE, $lang))
            ->keyBy(fn (SitemapEntry $entry) => $entry->loc);
    }

    private function thread(string $lang, ?int $categoryId): Thread
    {
        $thread = new Thread();
        $thread->forceFill([
            'language' => $lang,
            'title' => 'Artikel '.uniqid(),
            'slug' => 'artikel-'.uniqid(),
            'body' => 'Text',
            'excerpt' => 'Kurz',
            'author' => 'Redaktion',
            'thumbnail_path' => '',
            'category_id' => $categoryId,
        ])->save();

        return $thread;
    }

    public function test_lists_articles_without_alternates_and_the_index_with_them(): void
    {
        $thread = $this->thread('de', null);
        $entries = $this->entries();

        $article = $entries->get(self::BASE.'/angelmagazin/'.$thread->slug);
        $this->assertNotNull($article);
        $this->assertFalse($article->localized);
        $this->assertTrue($entries->get(self::BASE.'/angelmagazin')->localized);
    }

    /**
     * Magazine category filter pages are indexable and linked from every article; they belong
     * in the sitemap when the category has articles in that language.
     */
    public function test_lists_category_pages_that_have_articles_in_this_language(): void
    {
        $withArticles = Category::query()->create(['name' => 'Mit '.uniqid(), 'name_en' => 'With']);
        $empty = Category::query()->create(['name' => 'Leer '.uniqid(), 'name_en' => 'Empty']);
        $this->thread('de', $withArticles->id);

        $entries = $this->entries();

        $category = $entries->get(self::BASE.'/angelmagazin/categories/'.$withArticles->id);
        $this->assertNotNull($category);
        // No English article in this category, so the .com counterpart would be empty.
        $this->assertFalse($category->localized);
        $this->assertFalse($entries->has(self::BASE.'/angelmagazin/categories/'.$empty->id));
    }

    /**
     * Guide articles live at the site root (/{slug}) and were in no sitemap at all.
     */
    public function test_lists_root_level_guide_articles_for_this_language_only(): void
    {
        $de = GuideThread::query()->forceCreate(['language' => 'de', 'title' => 'Guide', 'slug' => 'angeln-guide-'.uniqid(), 'body' => 'Text']);
        $en = GuideThread::query()->forceCreate(['language' => 'en', 'title' => 'Guide', 'slug' => 'fishing-guide-'.uniqid(), 'body' => 'Text']);

        $entries = $this->entries();

        $this->assertTrue($entries->has(self::BASE.'/'.$de->slug));
        $this->assertFalse($entries[self::BASE.'/'.$de->slug]->localized);
        $this->assertFalse($entries->has(self::BASE.'/'.$en->slug));
    }
}
