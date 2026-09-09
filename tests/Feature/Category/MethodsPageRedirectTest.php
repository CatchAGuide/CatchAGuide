<?php

namespace Tests\Feature\Category;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Enums\GuideStatus;
use App\Models\CategoryPage;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\Language;
use App\Models\Method;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
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
        Cache::forget('guiding_category_availability_v1');

        $this->withoutMiddleware([
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \App\Http\Middleware\DDoSProtectionMiddleware::class,
        ]);
    }

    private function createTour(array $overrides = []): Guiding
    {
        $user = User::factory()->create([
            'is_guide' => 1,
            'guide_status' => GuideStatus::VERIFIED,
        ]);

        $guiding = new Guiding();
        $guiding->forceFill(array_merge([
            'title' => 'Test Tour '.uniqid(),
            'slug' => 'test-tour-'.uniqid(),
            'location' => 'Somewhere',
            'status' => 1,
            'max_guests' => 4,
            'duration' => 4,
            'fishing_type_id' => FishingType::query()->value('id'),
            'user_id' => $user->id,
        ], $overrides))->save();

        return $guiding;
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

        $this->createTour(['fishing_methods' => json_encode([$method->id])]);
        Cache::forget('guiding_category_availability_v1');

        $response = $this->get(route('guidings.methods.show', ['slug' => $page->slug]));

        $response->assertOk();
        $response->assertSee('Redirect Fly Method Title', false);
        $response->assertSee('cag-site-nav--overlay', false);
        $response->assertDontSee('hero-tour.webp', false);
        $response->assertSee('data-category-header-shell', false);
        $response->assertSee('action="'.url('/guidings/alloffers').'"', false);
        $response->assertDontSee('action="'.url('/offers').'"', false);
        $response->assertDontSee('guidings-page-header__segment--fish', false);

        $this->assertTrue(
            (bool) preg_match('/<nav[^>]*aria-label="Breadcrumb"[^>]*>(.*?)<\/nav>/s', $response->getContent(), $matches),
            'Expected a breadcrumb nav on the methods show page'
        );
        $crumbs = $matches[1];
        $homePos = strpos($crumbs, 'href="'.route('welcome').'"');
        $landingPos = strpos($crumbs, 'href="'.route('guidings.landing').'"');
        $methodsPos = strpos($crumbs, 'href="'.route('guidings.methods').'"');
        $this->assertNotFalse($homePos);
        $this->assertNotFalse($landingPos);
        $this->assertNotFalse($methodsPos);
        $this->assertLessThan($landingPos, $homePos);
        $this->assertLessThan($methodsPos, $landingPos);
        $this->assertStringContainsString(__('homepage.filter-fishing-near-me'), $crumbs);
        $this->assertStringContainsString(__('category.methods.breadcrumb'), $crumbs);
        $this->assertStringContainsString($page->name, $crumbs);
    }
}
