<?php

namespace Tests\Unit\Admin;

use App\Http\Controllers\Admin\GuidingsController;
use App\Models\Guiding;
use App\Models\GuidingAdditionalInformation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Regression coverage for the admin /admin/guidings/{id}/details modal:
 * a translation's json_data always carries the key for a list field
 * (other_information/requirements/recommendations) once any translation
 * run has touched the guiding, even when that field itself was never
 * translated and is stored as []. A plain array_merge($main, $jsonData)
 * let that empty array silently blank out the populated main-language
 * list, making the translation tab look like the content had vanished.
 * mergeTranslationWithMain() instead merges these list fields item-by-item
 * keyed by id, so an id with no translation yet falls back to the
 * main-language entry instead of the whole field disappearing.
 */
class AdminGuidingsDetailsMergeTest extends TestCase
{
    use DatabaseTransactions;

    private function invokeMerge(array $main, array $jsonData): array
    {
        $controller = new GuidingsController();
        $method = new ReflectionMethod($controller, 'mergeTranslationWithMain');
        $method->setAccessible(true);

        return $method->invoke($controller, $main, $jsonData);
    }

    private function invokeBuildGuidingTextPayload(Guiding $guiding): array
    {
        $controller = new GuidingsController();
        $method = new ReflectionMethod($controller, 'buildGuidingTextPayload');
        $method->setAccessible(true);

        return $method->invoke($controller, $guiding);
    }

    public function test_empty_translated_list_field_falls_back_to_populated_main_list(): void
    {
        $main = [
            'title' => 'EK IV Boot Weiß & Blau Marlin Angeln',
            'other_information' => [
                ['id' => 1, 'value' => 'Child allowed', 'name' => 'Kinderfreundlich'],
            ],
        ];
        $jsonData = [
            'title' => 'EK IV Boot Weiß & Blau Marlin Angeln',
            'other_information' => [],
        ];

        $merged = $this->invokeMerge($main, $jsonData);

        $this->assertSame($main['other_information'], $merged['other_information']);
    }

    public function test_populated_translated_list_field_is_kept_as_is(): void
    {
        $main = [
            'other_information' => [
                ['id' => 1, 'value' => 'Child allowed', 'name' => 'Kinderfreundlich'],
            ],
        ];
        $jsonData = [
            'other_information' => [
                ['id' => 1, 'value' => 'Kinderfreundlich (DE)', 'name' => 'Kinderfreundlich'],
            ],
        ];

        $merged = $this->invokeMerge($main, $jsonData);

        $this->assertSame($jsonData['other_information'], $merged['other_information']);
    }

    public function test_scalar_fields_still_prefer_the_translation_over_main(): void
    {
        $main = ['title' => 'English title'];
        $jsonData = ['title' => 'Deutscher Titel'];

        $merged = $this->invokeMerge($main, $jsonData);

        $this->assertSame('Deutscher Titel', $merged['title']);
    }

    public function test_missing_translation_key_falls_back_to_main_list_via_array_merge(): void
    {
        $main = [
            'requirements' => [
                ['id' => 2, 'value' => 'Bring a passport', 'name' => 'Requirement'],
            ],
        ];
        $jsonData = [];

        $merged = $this->invokeMerge($main, $jsonData);

        $this->assertSame($main['requirements'], $merged['requirements']);
    }

    public function test_partially_translated_list_merges_item_by_item_instead_of_replacing_the_whole_field(): void
    {
        $main = [
            'other_information' => [
                ['id' => 1, 'value' => 'Child allowed', 'name' => 'Kinderfreundlich'],
                ['id' => 4, 'value' => 'No alcohol', 'name' => 'Alkohol verboten'],
            ],
        ];
        // Only id 1 has been translated so far — id 4 is absent from the
        // translation's list entirely.
        $jsonData = [
            'other_information' => [
                ['id' => 1, 'value' => 'Kinderfreundlich (DE)', 'name' => 'Kinderfreundlich'],
            ],
        ];

        $merged = $this->invokeMerge($main, $jsonData);

        $this->assertSame(
            [
                ['id' => 1, 'value' => 'Kinderfreundlich (DE)', 'name' => 'Kinderfreundlich'],
                ['id' => 4, 'value' => 'No alcohol', 'name' => 'Alkohol verboten'],
            ],
            $merged['other_information']
        );
    }

    /**
     * Guiding::requirements/recommendations/other_information/pricing_extra go
     * through accessors that return an Illuminate\Support\Collection, not a
     * plain array. buildGuidingTextPayload() must normalize that to an array —
     * mergeTranslationWithMain()'s is_array()/empty() checks silently treat a
     * Collection as absent, so an unconverted Collection made the merge skip
     * its main-language fallback entirely and the translated (possibly
     * single-item) list replaced the whole field instead of being merged in.
     */
    public function test_build_guiding_text_payload_returns_plain_arrays_for_list_fields(): void
    {
        $info = GuidingAdditionalInformation::query()->create([
            'name' => 'Kinderfreundlich',
            'name_en' => 'Child-friendly',
        ]);

        $guiding = new Guiding([
            'inclusions' => null,
            'requirements' => null,
            'recommendations' => null,
            'pricing_extra' => null,
            'other_information' => json_encode([(string) $info->id => 'Child allowed']),
        ]);

        $payload = $this->invokeBuildGuidingTextPayload($guiding);

        $this->assertIsArray($payload['other_information']);
        $this->assertSame($info->id, $payload['other_information'][0]['id']);
        $this->assertSame('Child allowed', $payload['other_information'][0]['value']);
    }
}
