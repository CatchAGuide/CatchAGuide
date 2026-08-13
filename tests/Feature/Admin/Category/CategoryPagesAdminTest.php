<?php

namespace Tests\Feature\Admin\Category;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Models\CategoryPage;
use App\Models\Country;
use App\Models\Employee;
use App\Models\Language;
use App\Models\Method;
use App\Models\Target;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class CategoryPagesAdminTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://cag.local']);
        URL::forceRootUrl('http://cag.local');
    }

    private function actingAsEmployee(): Employee
    {
        $employee = Employee::query()->first();
        if (! $employee) {
            $this->markTestSkipped('No employee available for admin auth.');
        }

        $this->actingAs($employee, 'employees');

        return $employee;
    }

    public function test_category_pages_hub_is_accessible(): void
    {
        $this->actingAsEmployee();

        $response = $this->get(route('admin.category.hub'));

        $response->assertOk();
        $response->assertSee(__('admin.category_pages.hub_title'), false);
    }

    public function test_admin_can_save_scoped_target_fish_content(): void
    {
        $this->actingAsEmployee();

        $target = new Target();
        $target->forceFill([
            'name' => 'Scoped Pike '.uniqid(),
            'name_en' => 'Scoped Pike',
        ])->save();

        $response = $this->put(route('admin.category.target-fish.update', $target->id), [
            'name' => $target->getRawOriginal('name'),
            'title' => 'Vacations Pike Title',
            'sub_title' => 'Vacations Pike Sub',
            'introduction' => 'Intro',
            'content' => 'Body',
            'faq_title' => 'FAQ',
            'languageSwitch' => 'de',
            'content_scope' => CategoryPageScope::VACATIONS,
            'faq' => [
                ['question' => 'When?', 'answer' => 'Spring'],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('languages', [
            'source_id' => (string) $target->id,
            'type' => CategoryPageEntityType::TARGET_FISH,
            'scope' => CategoryPageScope::VACATIONS,
            'language' => 'de',
            'title' => 'Vacations Pike Title',
        ]);
    }

    public function test_target_fish_editor_renders_scoped_form(): void
    {
        $this->actingAsEmployee();

        $target = new Target();
        $target->forceFill([
            'name' => 'Editor Pike '.uniqid(),
            'name_en' => 'Editor Pike',
        ])->save();

        Language::query()->create([
            'source_id' => (string) $target->id,
            'type' => CategoryPageEntityType::TARGET_FISH,
            'scope' => CategoryPageScope::GLOBAL,
            'language' => 'de',
            'title' => 'Global title',
            'sub_title' => 'Global sub',
        ]);

        $response = $this->get(route('admin.category.target-fish.edit', [
            'target_fish' => $target->id,
            'scope' => CategoryPageScope::GLOBAL,
        ]));

        $response->assertOk();
        $response->assertSee(__('admin.category_pages.scopes.global'), false);
        $response->assertSee(__('admin.category_pages.scopes.vacations'), false);
    }

    public function test_admin_can_save_scoped_method_content(): void
    {
        $this->actingAsEmployee();

        $method = Method::query()->first();
        if (! $method) {
            $this->markTestSkipped('No method available.');
        }

        $page = CategoryPage::query()->firstOrCreate(
            ['source_id' => $method->id, 'type' => 'Methods'],
            [
                'name' => $method->name,
                'slug' => 'method-'.uniqid(),
                'is_favorite' => false,
            ],
        );

        $response = $this->put(route('admin.category.methods.update', $method->id), [
            'name' => $method->name,
            'title' => 'Tours Method Title',
            'sub_title' => 'Tours Method Sub',
            'introduction' => 'Intro',
            'content' => 'Body',
            'faq_title' => 'FAQ',
            'languageSwitch' => 'de',
            'content_scope' => CategoryPageScope::TOURS,
            'faq' => [
                ['question' => 'How?', 'answer' => 'Like this'],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('languages', [
            'source_id' => (string) $method->id,
            'type' => CategoryPageEntityType::METHOD,
            'scope' => CategoryPageScope::TOURS,
            'language' => 'de',
            'title' => 'Tours Method Title',
        ]);
    }

    public function test_country_editor_renders_scoped_tabs(): void
    {
        $this->actingAsEmployee();

        $country = Country::query()->first();
        if (! $country) {
            $this->markTestSkipped('No country available.');
        }

        $response = $this->get(route('admin.category.country.edit', $country->id));

        $response->assertOk();
        $response->assertSee(__('admin.category_pages.scopes.global'), false);
        $response->assertSee(__('admin.category_pages.scopes.camps'), false);
    }

    public function test_admin_can_save_scoped_country_content(): void
    {
        $this->actingAsEmployee();

        $country = Country::query()->first();
        if (! $country) {
            $this->markTestSkipped('No country available.');
        }

        $response = $this->put(route('admin.category.country.update', $country->id), [
            'name' => $country->name,
            'filters' => $country->filters ?? ['place' => 'Test'],
            'language' => 'de',
            'title' => 'Global Country Title',
            'sub_title' => 'Global Country Sub',
            'introduction' => 'Intro',
            'content' => 'Body',
            'faq_title' => 'FAQ',
            'languageSwitch' => 'de',
            'content_scope' => CategoryPageScope::GLOBAL,
            'faq' => [
                ['question' => 'When?', 'answer' => 'Now'],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('languages', [
            'source_id' => (string) $country->id,
            'type' => CategoryPageEntityType::GEO_COUNTRY,
            'scope' => CategoryPageScope::GLOBAL,
            'language' => 'de',
            'title' => 'Global Country Title',
        ]);
    }

    public function test_target_fish_index_shows_language_flags(): void
    {
        $this->actingAsEmployee();

        $target = new Target();
        $target->forceFill([
            'name' => 'Flag Pike '.uniqid(),
            'name_en' => 'Flag Pike',
        ])->save();

        Language::query()->create([
            'source_id' => (string) $target->id,
            'type' => CategoryPageEntityType::TARGET_FISH,
            'scope' => CategoryPageScope::TOURS,
            'language' => 'de',
            'title' => 'DE title',
            'sub_title' => 'DE sub',
        ]);

        $response = $this->get(route('admin.category.target-fish.index'));

        $response->assertOk();
        $response->assertSee('fi-de', false);
    }

    public function test_admin_can_autosave_scoped_target_fish_content(): void
    {
        $this->actingAsEmployee();

        $target = new Target();
        $target->forceFill([
            'name' => 'Autosave Pike '.uniqid(),
            'name_en' => 'Autosave Pike',
        ])->save();

        $response = $this->postJson(route('admin.category.target-fish.autosave', $target->id), [
            'title' => 'Tours Pike Autosave',
            'sub_title' => 'Tours Pike Sub',
            'introduction' => 'Intro',
            'content' => 'Body',
            'faq_title' => 'FAQ',
            'languageSwitch' => 'de',
            'content_scope' => CategoryPageScope::TOURS,
            'faq' => [
                ['question' => 'When?', 'answer' => 'Spring'],
            ],
        ]);

        $response->assertOk();
        $response->assertJsonPath('ok', true);
        $response->assertJsonPath('scope', CategoryPageScope::TOURS);
        $response->assertJsonPath('language', 'de');

        $this->assertDatabaseHas('languages', [
            'source_id' => (string) $target->id,
            'type' => CategoryPageEntityType::TARGET_FISH,
            'scope' => CategoryPageScope::TOURS,
            'language' => 'de',
            'title' => 'Tours Pike Autosave',
        ]);
    }

    public function test_target_fish_autosave_rejects_invalid_scope(): void
    {
        $this->actingAsEmployee();

        $target = new Target();
        $target->forceFill([
            'name' => 'Invalid Scope Pike '.uniqid(),
            'name_en' => 'Invalid Scope Pike',
        ])->save();

        $response = $this->postJson(route('admin.category.target-fish.autosave', $target->id), [
            'title' => 'Nope',
            'languageSwitch' => 'de',
            'content_scope' => 'not-a-scope',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['content_scope']);
    }

    public function test_admin_can_autosave_country_scope_without_identity_fields(): void
    {
        $this->actingAsEmployee();

        $country = Country::query()->first();
        if (! $country) {
            $this->markTestSkipped('No country available.');
        }

        $response = $this->postJson(route('admin.category.country.autosave', $country->id), [
            'title' => 'Camps Country Autosave',
            'sub_title' => 'Camps Sub',
            'introduction' => 'Intro',
            'content' => 'Body',
            'languageSwitch' => 'de',
            'content_scope' => CategoryPageScope::CAMPS,
        ]);

        $response->assertOk();
        $response->assertJsonPath('ok', true);
        $response->assertJsonPath('completeness.camps.de', true);

        $this->assertDatabaseHas('languages', [
            'source_id' => (string) $country->id,
            'type' => CategoryPageEntityType::GEO_COUNTRY,
            'scope' => CategoryPageScope::CAMPS,
            'language' => 'de',
            'title' => 'Camps Country Autosave',
        ]);
    }

    public function test_target_fish_editor_includes_autosave_endpoint(): void
    {
        $this->actingAsEmployee();

        $target = new Target();
        $target->forceFill([
            'name' => 'Autosave Editor Pike '.uniqid(),
            'name_en' => 'Autosave Editor Pike',
        ])->save();

        $response = $this->get(route('admin.category.target-fish.edit', $target->id));

        $response->assertOk();
        $response->assertSee('category-autosave-status', false);
        $response->assertSee('target-fish\/'.$target->id.'\/autosave', false);
    }
}
