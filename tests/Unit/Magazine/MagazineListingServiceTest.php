<?php

namespace Tests\Unit\Magazine;

use App\Models\Category;
use App\Models\Thread;
use App\Services\Magazine\MagazineListingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MagazineListingServiceTest extends TestCase
{
    use DatabaseTransactions;

    private MagazineListingService $service;

    private string $marker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MagazineListingService();
        $this->marker = 'magtest_'.uniqid();
    }

    public function test_build_filters_by_locale_and_excludes_other_languages(): void
    {
        $category = $this->makeCategory();

        $en = $this->makeThread([
            'title' => $this->marker.' Pike EN',
            'language' => 'en',
            'category_id' => $category->id,
        ]);
        $this->makeThread([
            'title' => $this->marker.' Hecht DE',
            'language' => 'de',
            'category_id' => $category->id,
        ]);

        $result = $this->service->build('en');

        $ids = collect([$result['featured']])->filter()
            ->concat($result['threads']->items())
            ->pluck('id');

        $this->assertTrue($ids->contains($en->id));
        $this->assertTrue(
            collect([$result['featured']])->filter()
                ->concat($result['threads']->items())
                ->every(fn (Thread $t) => $t->language === 'en')
        );
    }

    public function test_build_search_matches_title(): void
    {
        $needle = $this->marker.'_unique_trout';
        $match = $this->makeThread([
            'title' => 'Guide to '.$needle,
            'language' => 'en',
            'excerpt' => 'About trout',
        ]);
        $this->makeThread([
            'title' => $this->marker.' Carp basics',
            'language' => 'en',
            'excerpt' => 'About carp',
        ]);

        $result = $this->service->build('en', $needle);

        $this->assertSame(1, $result['totalCount']);
        $this->assertNull($result['featured']);
        $this->assertTrue(
            collect($result['threads']->items())->contains(fn (Thread $t) => $t->id === $match->id)
        );
    }

    public function test_build_filters_by_category_and_locale(): void
    {
        $catA = $this->makeCategory(['name' => $this->marker.'_A']);
        $catB = $this->makeCategory(['name' => $this->marker.'_B']);

        $inA = $this->makeThread([
            'title' => $this->marker.' In A',
            'language' => 'en',
            'category_id' => $catA->id,
        ]);
        $this->makeThread([
            'title' => $this->marker.' In B',
            'language' => 'en',
            'category_id' => $catB->id,
        ]);

        $result = $this->service->build('en', null, $catA);

        $this->assertSame(1, $result['totalCount']);
        $ids = collect([$result['featured']])->filter()
            ->concat($result['threads']->items())
            ->pluck('id');
        $this->assertTrue($ids->contains($inA->id));
        $this->assertSame($catA->id, $result['activeCategory']->id);
    }

    public function test_related_threads_prefer_same_category(): void
    {
        $cat = $this->makeCategory();
        $other = $this->makeCategory(['name' => $this->marker.'_other']);

        $main = $this->makeThread([
            'title' => $this->marker.' Main',
            'language' => 'en',
            'category_id' => $cat->id,
        ]);
        $same = $this->makeThread([
            'title' => $this->marker.' Same cat',
            'language' => 'en',
            'category_id' => $cat->id,
        ]);
        $this->makeThread([
            'title' => $this->marker.' Other cat',
            'language' => 'en',
            'category_id' => $other->id,
        ]);

        $related = $this->service->relatedThreads($main, 'en', 1);

        $this->assertCount(1, $related);
        $this->assertSame($same->id, $related->first()->id);
    }

    public function test_estimated_reading_minutes_minimum_one(): void
    {
        $thread = new Thread(['body' => 'Short']);
        $this->assertSame(1, $thread->estimatedReadingMinutes());
    }

    private function makeCategory(array $overrides = []): Category
    {
        return Category::create(array_merge([
            'name' => $this->marker.'_cat',
            'name_en' => $this->marker.'_cat_en',
        ], $overrides));
    }

    private function makeThread(array $overrides = []): Thread
    {
        return Thread::create(array_merge([
            'title' => $this->marker.' Title',
            'language' => 'en',
            'excerpt' => 'Excerpt',
            'body' => str_repeat('word ', 400),
            'author' => 'Tester',
            'thumbnail_path' => 'public/uploads/test.jpg',
            'category_id' => null,
        ], $overrides));
    }
}
