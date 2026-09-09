<?php

namespace Tests\Feature\Admin;

use App\Models\Employee;
use App\Models\Guiding;
use App\Models\GuidingAdditionalInformation;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Regression coverage for POST /admin/guidings/{id}/details-field: editing a
 * translation-tab list item (other_information/requirements/recommendations)
 * for an id that has never been translated before used to silently do
 * nothing. The write only matched an existing row by positional list_index
 * into the translation's own array — which is empty (or shorter than the
 * main list) for any field that hasn't been translated yet — so a first-time
 * edit responded success:true but never actually persisted, and reopening
 * the modal showed the edit had vanished.
 */
class AdminGuidingsDetailsFieldSaveTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://cag.local']);
        URL::forceRootUrl('http://cag.local');
    }

    private function actingAsEmployee(): void
    {
        $employee = Employee::query()->first();
        if (! $employee) {
            $this->markTestSkipped('No employee available for admin auth.');
        }

        $this->actingAs($employee, 'employees');
    }

    /**
     * @return array{0: Guiding, 1: GuidingAdditionalInformation, 2: GuidingAdditionalInformation}
     */
    private function makeGuidingWithOtherInformation(): array
    {
        $user = User::query()->first();
        if (! $user) {
            $this->markTestSkipped('No user available to own a test guiding.');
        }

        // Base the test row on a real guiding's attributes (via replicate) so we
        // don't have to hand-satisfy every NOT NULL column without a default —
        // only the fields this test actually cares about are overridden.
        $template = Guiding::query()->first();
        if (! $template) {
            $this->markTestSkipped('No existing guiding available to template a test guiding from.');
        }

        $info = GuidingAdditionalInformation::query()->create([
            'name' => 'Kinderfreundlich',
            'name_en' => 'Child-friendly',
        ]);
        $otherInfo = GuidingAdditionalInformation::query()->create([
            'name' => 'Rauchen verboten',
            'name_en' => 'No smoking',
        ]);

        $guiding = $template->replicate();
        $guiding->title = 'Test Tour';
        $guiding->user_id = $user->id;
        $guiding->language = 'en';
        $guiding->other_information = json_encode([
            (string) $info->id => 'Child allowed',
            (string) $otherInfo->id => 'No smoking on board',
        ]);
        $guiding->slug = null;
        $guiding->save();

        return [$guiding, $info, $otherInfo];
    }

    public function test_editing_a_never_translated_list_item_persists_instead_of_silently_no_oping(): void
    {
        $this->actingAsEmployee();
        [$guiding, $info] = $this->makeGuidingWithOtherInformation();

        $this->assertNull(
            Language::where('source_id', (string) $guiding->id)
                ->where('type', 'guidings')
                ->where('language', 'de')
                ->first(),
            'Guiding should have no existing German translation row yet.'
        );

        $response = $this->post("/admin/guidings/{$guiding->id}/details-field", [
            'field' => 'other_information',
            'value' => 'Kinderfreundlich (bearbeitet)',
            'language' => 'de',
            'list_index' => 0,
            'list_id' => (string) $info->id,
        ]);

        if ($response->status() === 404) {
            $this->markTestSkipped('Admin guidings details-field route not reachable in this test environment.');
        }

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $translation = Language::where('source_id', (string) $guiding->id)
            ->where('type', 'guidings')
            ->where('language', 'de')
            ->first();

        $this->assertNotNull($translation, 'Editing a translation-tab field should create the Language row.');

        $saved = collect($translation->json_data['other_information'] ?? [])->firstWhere('id', $info->id);
        $this->assertNotNull($saved, 'The edited id should now exist in the translation list.');
        $this->assertSame('Kinderfreundlich (bearbeitet)', $saved['value']);
    }

    public function test_saved_translation_is_reflected_on_a_subsequent_details_fetch_and_untranslated_siblings_still_show(): void
    {
        $this->actingAsEmployee();
        [$guiding, $info, $otherInfo] = $this->makeGuidingWithOtherInformation();

        $save = $this->post("/admin/guidings/{$guiding->id}/details-field", [
            'field' => 'other_information',
            'value' => 'Kinderfreundlich (bearbeitet)',
            'language' => 'de',
            'list_index' => 0,
            'list_id' => (string) $info->id,
        ]);

        if ($save->status() === 404) {
            $this->markTestSkipped('Admin guidings details-field route not reachable in this test environment.');
        }
        $save->assertOk();

        $details = $this->getJson("/admin/guidings/{$guiding->id}/details");
        $details->assertOk();

        $deOtherInformation = $details->json('translations.de.other_information');
        $edited = collect($deOtherInformation)->firstWhere('id', $info->id);
        $untouched = collect($deOtherInformation)->firstWhere('id', $otherInfo->id);

        $this->assertNotNull($edited, 'Refreshing the modal should still show the edited item.');
        $this->assertSame('Kinderfreundlich (bearbeitet)', $edited['value']);

        // The sibling id was never translated — it must still fall back to the
        // main-language value instead of disappearing from the German tab.
        $this->assertNotNull($untouched, 'An untranslated sibling item must still fall back to the main-language entry.');
        $this->assertSame('No smoking on board', $untouched['value']);
    }
}
