<?php

namespace Tests\Feature\Category;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Models\CategoryPage;
use App\Models\Language;
use App\Models\Method;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class MethodsPageRedirectTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');

        $this->withoutMiddleware([
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \App\Http\Middleware\DDoSProtectionMiddleware::class,
        ]);
    }

    public function test_category_page_methods_index_redirects_permanently(): void
    {
        $response = $this->get('/category-page/methods');

        $response->assertRedirect('/guidings/methods');
        $response->assertStatus(301);
    }

    public function test_category_page_methods_index_preserves_query_string(): void
    {
        $response = $this->get('/category-page/methods?page=2');

        $response->assertRedirect('/guidings/methods?page=2');
        $response->assertStatus(301);
    }

    public function test_category_page_methods_slug_redirects_permanently(): void
    {
        $response = $this->get('/category-page/methods/fly-fishing');

        $response->assertRedirect('/guidings/methods/fly-fishing');
        $response->assertStatus(301);
    }

    public function test_category_page_methods_slug_query_param_redirects_to_show(): void
    {
        $response = $this->get('/category-page/methods?slug=fly-fishing');

        $response->assertRedirect('/guidings/methods/fly-fishing');
        $response->assertStatus(301);
    }

    public function test_guidings_methods_is_not_captured_as_destination_country(): void
    {
        $response = $this->get('/guidings/methods');

        $response->assertOk();
        $response->assertViewIs('pages.category.category-index');
        $response->assertViewHas('type', 'methods');
    }

    public function test_guidings_methods_show_renders_method_page(): void
    {
        $method = new Method();
        $method->forceFill([
            'name' => 'Redirect Fly '.uniqid(),
            'name_en' => 'Redirect Fly',
        ])->save();

        $page = CategoryPage::query()->create([
            'name' => 'Redirect Fly',
            'type' => 'Methods',
            'slug' => 'redirect-fly-'.uniqid(),
            'source_id' => $method->id,
            'is_favorite' => false,
        ]);

        Language::query()->create([
            'source_id' => (string) $method->id,
            'type' => CategoryPageEntityType::METHOD,
            'scope' => CategoryPageScope::TOURS,
            'language' => app()->getLocale(),
            'title' => 'Redirect Fly Method Title',
            'sub_title' => 'Sub',
            'introduction' => 'Intro',
            'content' => 'Body',
            'faq_title' => '',
        ]);

        $response = $this->get(route('guidings.methods.show', ['slug' => $page->slug]));

        $response->assertOk();
        $response->assertSee('Redirect Fly Method Title', false);
    }
}
