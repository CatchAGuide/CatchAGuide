<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Thread;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class MagazineListingTest extends TestCase
{
    use DatabaseTransactions;

    private string $marker;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost', 'app.locale' => 'en']);
        URL::forceRootUrl('http://localhost');
        app()->setLocale('en');

        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
        $this->marker = 'magfeat_'.uniqid();
    }

    public function test_magazine_index_renders_with_seo_and_filters(): void
    {
        $category = Category::create([
            'name' => $this->marker.'_cat',
            'name_en' => 'Saltwater',
        ]);

        Thread::create([
            'title' => $this->marker.' Featured Guide',
            'language' => 'en',
            'excerpt' => 'A great fishing article excerpt',
            'body' => str_repeat('fishing word ', 250),
            'author' => 'CAG Editor',
            'thumbnail_path' => 'public/uploads/test.jpg',
            'category_id' => $category->id,
        ]);

        $response = $this->get('/fishing-magazine');

        $response->assertOk();
        $response->assertSee(__('message.magazine_meta_title'), false);
        $response->assertSee('og:title', false);
        $response->assertSee('CollectionPage', false);
        $response->assertSee('BreadcrumbList', false);
        $response->assertSee('data-analytics-page="magazine-index"', false);
        $response->assertSee('magazine_article_click', false);
        $response->assertSee($this->marker.' Featured Guide');
        $response->assertSee('Saltwater');
        $response->assertSee(__('magazine.search_placeholder'));
        $response->assertSee('cag-mag-filters__row', false);
        $response->assertSee('cag-mag-filters__field', false);
    }

    public function test_magazine_search_filters_results(): void
    {
        Thread::create([
            'title' => $this->marker.' UniquePerchArticle',
            'language' => 'en',
            'excerpt' => 'Perch tips',
            'body' => 'Body content about perch.',
            'author' => 'CAG Editor',
            'thumbnail_path' => 'public/uploads/test.jpg',
        ]);
        Thread::create([
            'title' => $this->marker.' Carp Weekend',
            'language' => 'en',
            'excerpt' => 'Carp tips',
            'body' => 'Body content about carp.',
            'author' => 'CAG Editor',
            'thumbnail_path' => 'public/uploads/test.jpg',
        ]);

        $response = $this->get('/fishing-magazine?q=UniquePerchArticle');

        $response->assertOk();
        $response->assertSee($this->marker.' UniquePerchArticle');
        $response->assertDontSee($this->marker.' Carp Weekend');
        $response->assertSee('magazine_search_submit', false);
    }

    public function test_category_page_only_shows_locale_threads(): void
    {
        $category = Category::create([
            'name' => $this->marker.'_cat',
            'name_en' => 'Freshwater',
        ]);

        Thread::create([
            'title' => $this->marker.' EN Post',
            'language' => 'en',
            'excerpt' => 'EN',
            'body' => 'EN body',
            'author' => 'Editor',
            'thumbnail_path' => 'public/uploads/test.jpg',
            'category_id' => $category->id,
        ]);
        Thread::create([
            'title' => $this->marker.' DE Post',
            'language' => 'de',
            'excerpt' => 'DE',
            'body' => 'DE body',
            'author' => 'Editor',
            'thumbnail_path' => 'public/uploads/test.jpg',
            'category_id' => $category->id,
        ]);

        $response = $this->get('/fishing-magazine/categories/'.$category->id);

        $response->assertOk();
        $response->assertSee($this->marker.' EN Post');
        $response->assertDontSee($this->marker.' DE Post');
        $response->assertSee(__('magazine.category_meta_title', ['category' => 'Freshwater']), false);
    }

    public function test_article_page_includes_article_schema_and_share(): void
    {
        $thread = Thread::create([
            'title' => $this->marker.' Schema Article',
            'language' => 'en',
            'excerpt' => 'Readable excerpt for SEO',
            'body' => '<p>'.str_repeat('Angling story. ', 80).'</p>',
            'author' => 'CAG Editor',
            'thumbnail_path' => 'public/uploads/test.jpg',
        ]);
        Thread::create([
            'title' => $this->marker.' Related Piece',
            'language' => 'en',
            'excerpt' => 'Related excerpt',
            'body' => '<p>Related body</p>',
            'author' => 'CAG Editor',
            'thumbnail_path' => 'public/uploads/test.jpg',
        ]);

        $response = $this->get('/fishing-magazine/'.$thread->slug);

        $response->assertOk();
        $response->assertSee('"@type":"Article"', false);
        $response->assertSee('magazine_article_view', false);
        $response->assertSee('magazine_share_click', false);
        $response->assertSee(__('magazine.related_title'));
        $response->assertSee('property="og:type" content="article"', false);
    }
}
