<?php

namespace Tests\Unit\Translation;

use App\Models\Language;
use App\Models\Vacation;
use App\Services\Translation\VacationTranslationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use ReflectionMethod;
use Tests\TestCase;

/**
 * VacationTranslationService::translateVacation() used to be dead code: the real translate call
 * was commented out and it referenced undefined $translatedData/$mergedData variables, so every
 * call threw, was swallowed, and returned false. These tests cover the fixed field
 * collection/reconstruction and the content-hash "needs update" gate that replaced the old
 * AdminChangeTracker-based check, without invoking the real translation engine (no network calls
 * in tests, matching GuidingTranslationServiceTest/ComponentTranslationServiceTest).
 */
class VacationTranslationServiceTest extends TestCase
{
    use DatabaseTransactions;

    private VacationTranslationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new VacationTranslationService();
    }

    private function createVacation(array $overrides = []): Vacation
    {
        $vacation = new Vacation();
        $vacation->forceFill(array_merge([
            'title' => 'Test Vacation '.uniqid(),
            'slug' => 'test-vacation-'.uniqid(),
            'location' => 'Somewhere',
            'city' => 'Somewhere',
            'country' => 'Germany',
            'latitude' => '0',
            'longitude' => '0',
            'region' => 'Bavaria',
            'best_travel_times' => 'Summer',
            'surroundings_description' => 'Lakeside surroundings',
            'target_fish' => ['Pike', 'Zander'],
            'status' => true,
        ], $overrides))->save();

        // This dev DB is shared/non-empty and MySQL never reuses auto_increment values, but a
        // freshly assigned id can still collide with an orphaned Language row left over from a
        // vacation that was deleted long ago. Clear it so "no translation exists yet" tests are
        // not flaky against pre-existing data.
        Language::where(['source_id' => $vacation->id, 'type' => 'vacations'])->delete();

        return $vacation;
    }

    private function invokeGetTranslatableFields(Vacation $vacation): array
    {
        $method = new ReflectionMethod($this->service, 'getTranslatableFields');
        $method->setAccessible(true);

        return $method->invoke($this->service, $vacation);
    }

    public function test_get_translatable_fields_includes_scalar_and_flattened_list_fields(): void
    {
        $vacation = $this->createVacation([
            'travel_included' => 'Self-drive',
            'included_services' => ['WiFi', 'Sauna'],
        ]);

        $fields = $this->invokeGetTranslatableFields($vacation);

        $this->assertSame('Test Vacation', substr($fields['title'], 0, 13));
        $this->assertSame('Self-drive', $fields['travel_included']);
        $this->assertSame('Pike', $fields['target_fish_0']);
        $this->assertSame('Zander', $fields['target_fish_1']);
        $this->assertSame('WiFi', $fields['included_services_0']);
        $this->assertSame('Sauna', $fields['included_services_1']);
    }

    public function test_get_translatable_fields_excludes_non_translatable_columns(): void
    {
        $vacation = $this->createVacation();

        $fields = $this->invokeGetTranslatableFields($vacation);

        $this->assertArrayNotHasKey('latitude', $fields);
        $this->assertArrayNotHasKey('longitude', $fields);
        $this->assertArrayNotHasKey('slug', $fields);
    }

    public function test_has_significant_changes_is_true_when_no_translation_exists(): void
    {
        $vacation = $this->createVacation();

        $this->assertTrue($this->service->hasSignificantChanges($vacation, 'en'));
    }

    public function test_has_significant_changes_is_false_once_the_content_hash_matches(): void
    {
        $vacation = $this->createVacation();

        Language::create([
            'source_id' => $vacation->id,
            'type' => 'vacations',
            'language' => 'en',
            'title' => $vacation->title,
            'json_data' => ['title' => $vacation->title],
            'content' => md5(serialize($this->invokeGetTranslatableFields($vacation))),
        ]);

        $this->assertFalse($this->service->hasSignificantChanges($vacation, 'en'));
    }

    public function test_has_significant_changes_is_true_again_after_the_source_content_changes(): void
    {
        $vacation = $this->createVacation();

        Language::create([
            'source_id' => $vacation->id,
            'type' => 'vacations',
            'language' => 'en',
            'title' => $vacation->title,
            'json_data' => ['title' => $vacation->title],
            'content' => md5(serialize($this->invokeGetTranslatableFields($vacation))),
        ]);

        $vacation->surroundings_description = 'Completely different surroundings now';
        $vacation->save();

        $this->assertTrue($this->service->hasSignificantChanges($vacation, 'en'));
    }

    public function test_needs_translation_update_is_false_when_source_equals_target(): void
    {
        $vacation = $this->createVacation(['language' => 'en']);

        $this->assertFalse($this->service->needsTranslationUpdate($vacation, 'en'));
    }

    public function test_needs_translation_update_defaults_source_language_to_de_when_unset(): void
    {
        // The vacations.language column defaults to 'de' when not set explicitly.
        $vacation = $this->createVacation();

        $this->assertTrue($this->service->needsTranslationUpdate($vacation, 'en'));
        $this->assertFalse($this->service->needsTranslationUpdate($vacation, 'de'));
    }
}
