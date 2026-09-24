<?php

namespace Tests\Feature\Category;

use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class TargetsPageRedirectTest extends TestCase
{
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

    public function test_category_page_targets_index_redirects_permanently(): void
    {
        $response = $this->get('/category-page/targets');

        $response->assertRedirect('/targets');
        $response->assertStatus(301);
    }

    public function test_category_page_targets_index_preserves_query_string(): void
    {
        $response = $this->get('/category-page/targets?page=2');

        $response->assertRedirect('/targets?page=2');
        $response->assertStatus(301);
    }

    public function test_category_page_targets_slug_redirects_permanently(): void
    {
        $response = $this->get('/category-page/targets/pike');

        $response->assertRedirect('/targets/pike');
        $response->assertStatus(301);
    }

    public function test_category_page_targets_slug_preserves_query_string(): void
    {
        $response = $this->get('/category-page/targets/pike?type=tour');

        $response->assertRedirect('/targets/pike?type=tour');
        $response->assertStatus(301);
    }

    public function test_category_page_targets_slug_query_param_redirects_to_show(): void
    {
        $response = $this->get('/category-page/targets?slug=pike');

        $response->assertRedirect('/targets/pike');
        $response->assertStatus(301);
    }

    public function test_targets_index_is_not_captured_as_slug(): void
    {
        $response = $this->get('/targets');

        $response->assertOk();
        $response->assertViewIs('pages.category.category-index');
        $response->assertViewHas('type', 'targets');
    }

    public function test_category_page_uppercase_slug_redirects_to_lowercase_in_one_hop(): void
    {
        $this->get('/category-page/targets/%C3%84sche')
            ->assertStatus(301)
            ->assertRedirect(route('targets.show', ['slug' => 'äsche']));

        $this->get('/category-page/methods/Spinnfischen')
            ->assertStatus(301)
            ->assertRedirect(route('guidings.methods.show', ['slug' => 'spinnfischen']));
    }
}
