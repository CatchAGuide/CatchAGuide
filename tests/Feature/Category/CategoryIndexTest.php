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

        $response = $this->get(route('category.types', ['type' => 'targets']));

        $response->assertOk();
        $response->assertSee('Visible Pike Index Title', false);
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

    public function test_legacy_methods_index_redirects_to_guidings_methods(): void
    {
        $response = $this->get(route('category.types', ['type' => 'methods']));

        $response->assertRedirect(route('guidings.methods'));
        $response->assertStatus(301);
    }
}
