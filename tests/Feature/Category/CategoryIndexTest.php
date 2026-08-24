<?php

namespace Tests\Feature\Category;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Models\CategoryPage;
use App\Models\Language;
use App\Models\Method;
use App\Models\Target;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class CategoryIndexTest extends TestCase
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

    public function test_targets_index_shows_rekeyed_target_fish_content(): void
    {
        $target = new Target();
        $target->forceFill([
            'name' => 'Index Pike '.uniqid(),
            'name_en' => 'Index Pike',
        ])->save();

        $page = CategoryPage::query()->create([
            'name' => 'Index Pike',
            'type' => 'Targets',
            'slug' => 'index-pike-'.uniqid(),
            'source_id' => $target->id,
            'is_favorite' => true,
        ]);

        Language::query()->create([
            'source_id' => (string) $target->id,
            'type' => CategoryPageEntityType::TARGET_FISH,
            'scope' => CategoryPageScope::TOURS,
            'language' => app()->getLocale(),
            'title' => 'Visible Pike Index Title',
            'sub_title' => 'Sub',
            'introduction' => 'Intro',
            'content' => 'Body',
            'faq_title' => '',
        ]);

        $response = $this->get(route('targets.index'));

        $response->assertOk();
        $response->assertSee('Visible Pike Index Title', false);
        $response->assertViewHas('allTargets', function ($items) use ($page) {
            return $items->contains(fn ($item) => (int) $item->id === (int) $page->id);
        });
    }

    public function test_targets_index_skips_pages_with_orphaned_source(): void
    {
        $page = CategoryPage::query()->create([
            'name' => 'Ghost Target',
            'type' => 'Targets',
            'slug' => 'ghost-target-'.uniqid(),
            'source_id' => 999999,
            'is_favorite' => true,
        ]);

        Language::query()->create([
            'source_id' => '999999',
            'type' => CategoryPageEntityType::TARGET_FISH,
            'scope' => CategoryPageScope::TOURS,
            'language' => app()->getLocale(),
            'title' => 'Ghost Target Title',
            'sub_title' => 'Sub',
            'introduction' => 'Intro',
            'content' => 'Body',
            'faq_title' => '',
        ]);

        $response = $this->get(route('targets.index'));

        $response->assertOk();
        $response->assertDontSee('Ghost Target Title', false);
        $response->assertViewHas('allTargets', function ($items) use ($page) {
            return ! $items->contains(fn ($item) => (int) $item->id === (int) $page->id);
        });
    }

    public function test_guidings_targets_index_shows_tours_scoped_content_linking_to_guidings_show(): void
    {
        $target = new Target();
        $target->forceFill([
            'name' => 'Guidings Index Pike '.uniqid(),
            'name_en' => 'Guidings Index Pike',
        ])->save();

        $page = CategoryPage::query()->create([
            'name' => 'Guidings Index Pike',
            'type' => 'Targets',
            'slug' => 'guidings-index-pike-'.uniqid(),
            'source_id' => $target->id,
            'is_favorite' => true,
        ]);

        Language::query()->create([
            'source_id' => (string) $target->id,
            'type' => CategoryPageEntityType::TARGET_FISH,
            'scope' => CategoryPageScope::TOURS,
            'language' => app()->getLocale(),
            'title' => 'Visible Guidings Pike Title',
            'sub_title' => 'Sub',
            'introduction' => 'Intro',
            'content' => 'Body',
            'faq_title' => '',
        ]);

        $response = $this->get(route('guidings.targets.index'));

        $response->assertOk();
        $response->assertSee('Visible Guidings Pike Title', false);
        $response->assertSee('href="'.route('guidings.targets', ['slug' => $page->slug]).'"', false);
        $response->assertDontSee('href="'.route('targets.show', ['slug' => $page->slug]).'"', false);
        $response->assertSee('data-category-header-shell', false);
        $response->assertDontSee('data-site-page-header-shell', false);
        $response->assertViewHas('allTargets', function ($items) use ($page) {
            return $items->contains(fn ($item) => (int) $item->id === (int) $page->id);
        });
    }

    public function test_methods_index_shows_rekeyed_method_content(): void
    {
        $method = new Method();
        $method->forceFill([
            'name' => 'Index Fly '.uniqid(),
            'name_en' => 'Index Fly',
        ])->save();

        $page = CategoryPage::query()->create([
            'name' => 'Index Fly',
            'type' => 'Methods',
            'slug' => 'index-fly-'.uniqid(),
            'source_id' => $method->id,
            'is_favorite' => false,
        ]);

        Language::query()->create([
            'source_id' => (string) $method->id,
            'type' => CategoryPageEntityType::METHOD,
            'scope' => CategoryPageScope::TOURS,
            'language' => app()->getLocale(),
            'title' => 'Visible Fly Method Title',
            'sub_title' => 'Sub',
            'introduction' => 'Intro',
            'content' => 'Body',
            'faq_title' => '',
        ]);

        $response = $this->get(route('guidings.methods'));

        $response->assertOk();
        $response->assertSee('Visible Fly Method Title', false);
        $response->assertViewHas('allTargets', function ($items) use ($page) {
            return $items->contains(fn ($item) => (int) $item->id === (int) $page->id);
        });
    }

    public function test_targets_index_uses_gray_catalog_header(): void
    {
        $response = $this->get(route('targets.index'));

        $response->assertOk();
        $response->assertSee('cag-site-nav--overlay', false);
        $response->assertDontSee('hero-tour.webp', false);
        $response->assertSee('data-category-header-shell', false);
        $response->assertSee('offers-page-header__hero', false);
        $response->assertSee(__('category.targets.breadcrumb'), false);
        $response->assertSee('action="'.url('/offers').'"', false);
        $response->assertDontSee('action="'.url('/guidings/alloffers').'"', false);
        $response->assertDontSee('navbar-custom short-header long-header', false);
    }

    public function test_methods_index_uses_gray_catalog_header(): void
    {
        $response = $this->get(route('guidings.methods'));

        $response->assertOk();
        $response->assertSee('cag-site-nav--overlay', false);
        $response->assertDontSee('hero-tour.webp', false);
        $response->assertSee('data-category-header-shell', false);
        $response->assertSee('offers-page-header__hero', false);
        $response->assertSee(__('category.methods.breadcrumb'), false);
        $response->assertSee('action="'.url('/guidings/alloffers').'"', false);
        $response->assertDontSee('action="'.url('/offers').'"', false);
        $response->assertDontSee('navbar-custom short-header long-header', false);
        $response->assertDontSee('guidings-page-header__segment--fish', false);
    }

    public function test_legacy_methods_index_redirects_to_guidings_methods(): void
    {
        $response = $this->get(route('category.types', ['type' => 'methods']));

        $response->assertRedirect(route('guidings.methods'));
        $response->assertStatus(301);
    }

    public function test_legacy_targets_index_redirects_to_targets(): void
    {
        $response = $this->get(route('category.types', ['type' => 'targets']));

        $response->assertRedirect(route('targets.index'));
        $response->assertStatus(301);
    }
}
