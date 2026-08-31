<?php

namespace Tests\Feature\Admin\Category;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Models\CategoryEntity;
use App\Models\CategoryEntityMigrationMap;
use App\Models\CategoryPage;
use App\Models\DestinationFishChart;
use App\Models\Employee;
use App\Models\Faq;
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

        $country = CategoryEntity::countries()->first();
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

        $country = CategoryEntity::countries()->first();
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

    public function test_admin_can_save_scoped_region_content(): void
    {
        $this->actingAsEmployee();

        $region = CategoryEntity::regions()->first();
        if (! $region) {
            $this->markTestSkipped('No region available.');
        }

        $response = $this->put(route('admin.category.region.update', $region->id), [
            'country_id' => $region->country_id,
            'name' => $region->name,
            'filters' => $region->filters ?? ['place' => 'Test'],
            'language' => 'de',
            'title' => 'Tours Region Title',
            'sub_title' => 'Tours Region Sub',
            'introduction' => 'Intro',
            'content' => 'Body',
            'faq_title' => 'FAQ',
            'languageSwitch' => 'de',
            'content_scope' => CategoryPageScope::TOURS,
            'faq' => [
                ['question' => 'When?', 'answer' => 'Now'],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('languages', [
            'source_id' => (string) $region->id,
            'type' => CategoryPageEntityType::GEO_REGION,
            'scope' => CategoryPageScope::TOURS,
            'language' => 'de',
            'title' => 'Tours Region Title',
        ]);
    }

    public function test_admin_can_save_scoped_city_content(): void
    {
        $this->actingAsEmployee();

        $city = CategoryEntity::cities()->first();
        if (! $city) {
            $this->markTestSkipped('No city available.');
        }

        $response = $this->put(route('admin.category.city.update', $city->id), [
            'country_id' => $city->country_id,
            'region_id' => $city->region_id,
            'name' => $city->name,
            'filters' => $city->filters ?? ['place' => 'Test'],
            'language' => 'de',
            'title' => 'Tours City Title',
            'sub_title' => 'Tours City Sub',
            'introduction' => 'Intro',
            'content' => 'Body',
            'faq_title' => 'FAQ',
            'languageSwitch' => 'de',
            'content_scope' => CategoryPageScope::TOURS,
            'faq' => [
                ['question' => 'When?', 'answer' => 'Now'],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('languages', [
            'source_id' => (string) $city->id,
            'type' => CategoryPageEntityType::GEO_CITY,
            'scope' => CategoryPageScope::TOURS,
            'language' => 'de',
            'title' => 'Tours City Title',
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

        $country = CategoryEntity::countries()->first();
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

    public function test_category_pages_hub_includes_destination_hub_card(): void
    {
        $this->actingAsEmployee();

        $response = $this->get(route('admin.category.hub'));

        $response->assertOk();
        $response->assertSee(__('admin.category_pages.dimensions.destination_hub'), false);
        $response->assertSee(route('admin.category.destination-hub.edit', [], false), false);
    }

    public function test_admin_can_save_destination_hub_content(): void
    {
        $this->actingAsEmployee();

        $response = $this->put(route('admin.category.destination-hub.update'), [
            'title' => 'CMS Destination Hub Title',
            'sub_title' => 'CMS Destination Hub Sub',
            'introduction' => 'CMS intro',
            'content' => 'CMS body',
            'faq_title' => 'FAQ',
            'languageSwitch' => 'en',
            'content_scope' => CategoryPageScope::GLOBAL,
            'faq' => [
                ['question' => 'Where?', 'answer' => 'Europe'],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('languages', [
            'source_id' => CategoryPageEntityType::DESTINATION_HUB_SOURCE_ID,
            'type' => CategoryPageEntityType::DESTINATION_HUB,
            'scope' => CategoryPageScope::GLOBAL,
            'language' => 'en',
            'title' => 'CMS Destination Hub Title',
        ]);
    }

    public function test_destination_hub_editor_renders_and_includes_autosave_endpoint(): void
    {
        $this->actingAsEmployee();

        $response = $this->get(route('admin.category.destination-hub.edit', [
            'language' => 'en',
        ]));

        $response->assertOk();
        $response->assertSee('category-autosave-status', false);
        $response->assertSee('destination-hub\/autosave', false);
    }

    public function test_admin_can_autosave_destination_hub_content(): void
    {
        $this->actingAsEmployee();

        $response = $this->postJson(route('admin.category.destination-hub.autosave'), [
            'title' => 'Autosave Hub Title',
            'sub_title' => 'Autosave Hub Sub',
            'introduction' => 'Intro',
            'content' => 'Body',
            'faq_title' => 'FAQ',
            'languageSwitch' => 'de',
            'content_scope' => CategoryPageScope::GLOBAL,
        ]);

        $response->assertOk();
        $response->assertJsonPath('ok', true);
        $response->assertJsonPath('scope', CategoryPageScope::GLOBAL);

        $this->assertDatabaseHas('languages', [
            'source_id' => CategoryPageEntityType::DESTINATION_HUB_SOURCE_ID,
            'type' => CategoryPageEntityType::DESTINATION_HUB,
            'scope' => CategoryPageScope::GLOBAL,
            'language' => 'de',
            'title' => 'Autosave Hub Title',
        ]);
    }

    public function test_admin_can_create_country_via_legacy_form(): void
    {
        $this->actingAsEmployee();

        $name = 'Legacy Country '.uniqid();

        $response = $this->post(route('admin.category.country.store'), [
            'name' => $name,
            'filters' => ['place' => 'Test'],
            'language' => 'de',
            'title' => 'Legacy Title',
            'sub_title' => 'Legacy Sub',
            'introduction' => 'Legacy Intro',
            'body' => 'Legacy Body',
            'faq_title' => 'FAQ',
            'faq' => [
                ['question' => 'Legacy Q?', 'answer' => 'Legacy A'],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $country = CategoryEntity::countries()->where('name', $name)->firstOrFail();

        $this->assertDatabaseHas('languages', [
            'source_id' => (string) $country->id,
            'type' => CategoryPageEntityType::GEO_COUNTRY,
            'scope' => CategoryPageScope::GLOBAL,
            'language' => 'de',
            'title' => 'Legacy Title',
        ]);
        $this->assertDatabaseHas('faqs', [
            'source_id' => $country->id,
            'page' => CategoryPageEntityType::GEO_COUNTRY,
            'scope' => CategoryPageScope::TOURS,
            'language' => 'de',
            'question' => 'Legacy Q?',
        ]);
    }

    public function test_admin_can_create_region_via_legacy_form(): void
    {
        $this->actingAsEmployee();

        $country = CategoryEntity::countries()->first();
        if (! $country) {
            $this->markTestSkipped('No country available.');
        }

        $name = 'Legacy Region '.uniqid();

        $response = $this->post(route('admin.category.region.store'), [
            'country_id' => $country->id,
            'name' => $name,
            'filters' => ['place' => 'Test'],
            'language' => 'de',
            'title' => 'Legacy Region Title',
            'sub_title' => 'Legacy Region Sub',
            'introduction' => 'Intro',
            'body' => 'Body',
            'faq_title' => 'FAQ',
            'faq' => [
                ['question' => 'Region Q?', 'answer' => 'Region A'],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $region = CategoryEntity::regions()->where('name', $name)->firstOrFail();
        $this->assertSame($country->id, $region->country_id);

        $this->assertDatabaseHas('languages', [
            'source_id' => (string) $region->id,
            'type' => CategoryPageEntityType::GEO_REGION,
            'scope' => CategoryPageScope::TOURS,
            'language' => 'de',
            'title' => 'Legacy Region Title',
        ]);
        $this->assertDatabaseHas('faqs', [
            'source_id' => $region->id,
            'page' => CategoryPageEntityType::GEO_REGION,
            'scope' => CategoryPageScope::TOURS,
            'language' => 'de',
            'question' => 'Region Q?',
        ]);
    }

    public function test_country_editor_shows_legacy_fish_chart_data_via_migration_map(): void
    {
        $this->actingAsEmployee();

        $country = CategoryEntity::countries()->create([
            'type' => 'country',
            'name' => 'Fish Country '.uniqid(),
            'slug' => 'fish-country-'.uniqid(),
        ]);

        $legacyId = 900000 + $country->id;

        CategoryEntityMigrationMap::query()->create([
            'old_table' => 'c_countries',
            'old_id' => $legacyId,
            'new_id' => $country->id,
        ]);

        DestinationFishChart::query()->create([
            'destination_id' => $legacyId,
            'destination_type' => 'country',
            'fish' => 'Legacy Pike Chart',
            'language' => 'de',
            'jan' => 0, 'feb' => 0, 'mar' => 0, 'apr' => 0, 'may' => 0, 'jun' => 0,
            'jul' => 0, 'aug' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dec' => 0,
        ]);

        $response = $this->get(route('admin.category.country.edit', $country->id));

        $response->assertOk();
        $response->assertSee('Legacy Pike Chart', false);
    }

    public function test_country_fish_chart_save_uses_legacy_id_and_sets_destination_type(): void
    {
        $this->actingAsEmployee();

        $country = CategoryEntity::countries()->create([
            'type' => 'country',
            'name' => 'Fish Save Country '.uniqid(),
            'slug' => 'fish-save-country-'.uniqid(),
        ]);

        $legacyId = 900000 + $country->id;

        CategoryEntityMigrationMap::query()->create([
            'old_table' => 'c_countries',
            'old_id' => $legacyId,
            'new_id' => $country->id,
        ]);

        $response = $this->put(route('admin.category.country.update', $country->id), [
            'name' => $country->name,
            'filters' => ['place' => 'Test'],
            'language' => 'de',
            'languageSwitch' => 'de',
            'title' => 'Fish Save Title',
            'sub_title' => 'Fish Save Sub',
            'fish_chart' => [
                [
                    'id' => 0, 'fish' => 'New Pike Chart',
                    'jan' => 1, 'feb' => 0, 'mar' => 0, 'apr' => 0, 'may' => 0, 'jun' => 0,
                    'jul' => 0, 'aug' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dec' => 0,
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('destination_fish_charts', [
            'destination_id' => $legacyId,
            'destination_type' => 'country',
            'fish' => 'New Pike Chart',
        ]);
    }

    public function test_country_destroy_cleans_up_languages_and_faqs(): void
    {
        $this->actingAsEmployee();

        $country = CategoryEntity::countries()->create([
            'type' => 'country',
            'name' => 'Destroy Me '.uniqid(),
            'slug' => 'destroy-me-'.uniqid(),
        ]);

        Language::query()->create([
            'source_id' => (string) $country->id,
            'type' => CategoryPageEntityType::GEO_COUNTRY,
            'scope' => CategoryPageScope::GLOBAL,
            'language' => 'de',
            'title' => 'To be deleted',
        ]);
        Faq::query()->create([
            'page' => CategoryPageEntityType::GEO_COUNTRY,
            'source_id' => $country->id,
            'scope' => CategoryPageScope::TOURS,
            'language' => 'de',
            'question' => 'Q',
            'answer' => 'A',
        ]);

        $response = $this->delete(route('admin.category.country.destroy', $country->id));

        $response->assertRedirect();

        $this->assertSoftDeleted('category_entities', ['id' => $country->id]);
        $this->assertDatabaseMissing('languages', [
            'source_id' => (string) $country->id,
            'type' => CategoryPageEntityType::GEO_COUNTRY,
        ]);
        $this->assertDatabaseMissing('faqs', [
            'source_id' => $country->id,
            'page' => CategoryPageEntityType::GEO_COUNTRY,
        ]);
    }
}
