<?php

namespace Tests\Unit\Models;

use App\Models\Guiding;
use App\Models\GuidingAdditionalInformation;
use App\Models\Language;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Guiding::other_information/requirements/recommendations rows are usually
 * translated one id at a time (a translation run may skip some, an admin
 * edit touches one row via the details modal), so Guiding::__get() merges
 * the translated rows over the main-language rows by id rather than
 * returning the translated array as-is — otherwise any id missing from the
 * translation (including "all of them", when the field was never touched)
 * would silently disappear from the German product page, which reads
 * guiding->other_information straight through __get().
 */
class GuidingTranslatedAttributeFallbackTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @return array{0: Guiding, 1: GuidingAdditionalInformation, 2: GuidingAdditionalInformation}
     */
    private function makeGuidingWithOtherInformation(): array
    {
        $info = GuidingAdditionalInformation::query()->create([
            'name' => 'Kinderfreundlich',
            'name_en' => 'Child-friendly',
        ]);
        $otherInfo = GuidingAdditionalInformation::query()->create([
            'name' => 'Rauchen verboten',
            'name_en' => 'No smoking',
        ]);

        $guiding = new Guiding([
            'language' => 'en',
            'other_information' => json_encode([
                (string) $info->id => 'Child allowed',
                (string) $otherInfo->id => 'No smoking on board',
            ]),
        ]);

        return [$guiding, $info, $otherInfo];
    }

    public function test_empty_translated_other_information_falls_back_to_main_language_data(): void
    {
        [$guiding] = $this->makeGuidingWithOtherInformation();

        $guiding->translated = ['other_information' => []];

        $result = collect($guiding->other_information);

        $this->assertCount(2, $result);
        $this->assertSame('Child allowed', $result->first()['value']);
    }

    public function test_populated_translated_other_information_merges_by_id_with_main_language_fallback(): void
    {
        [$guiding, $info, $otherInfo] = $this->makeGuidingWithOtherInformation();

        // Only $info has been translated so far — $otherInfo has not.
        $guiding->translated = [
            'other_information' => [
                ['id' => $info->id, 'value' => 'Kinderfreundlich (translated)', 'name' => 'Kinderfreundlich'],
            ],
        ];

        $result = collect($guiding->other_information);

        $this->assertCount(2, $result, 'The untranslated sibling id must still appear via main-language fallback.');
        $this->assertSame('Kinderfreundlich (translated)', $result->firstWhere('id', $info->id)['value']);
        $this->assertSame('No smoking on board', $result->firstWhere('id', $otherInfo->id)['value']);
    }

    public function test_eager_loaded_translation_relation_with_empty_list_falls_back_to_main_data(): void
    {
        [$guiding, $info] = $this->makeGuidingWithOtherInformation();

        $translation = new Language([
            'source_id' => '999999',
            'type' => 'guidings',
            'language' => 'de',
            'title' => 'Test',
            'json_data' => ['other_information' => []],
        ]);
        $guiding->setRelation('translationForCurrentLocale', $translation);

        app()->setLocale('de');

        $result = collect($guiding->other_information);

        app()->setLocale('en');

        $this->assertCount(2, $result);
        $this->assertSame($info->id, $result->first()['id']);
    }

    public function test_scalar_field_still_returns_empty_translated_value_when_actually_set(): void
    {
        $guiding = new Guiding(['title' => 'English title']);
        $guiding->translated = ['title' => ''];

        $this->assertSame('', $guiding->title);
    }

    public function test_populated_translated_pricing_extra_merges_by_id_with_main_language_fallback(): void
    {
        $guiding = new Guiding([
            'pricing_extra' => json_encode([
                ['name' => 'Fishing rod rental', 'price' => 10],
                ['name' => 'Extra bait', 'price' => 5],
            ]),
        ]);

        // Custom (non-ExtrasPrice) names get a counter-based id from the
        // accessor — read it once so the translated fixture can target it.
        $mainExtras = collect($guiding->pricing_extra);
        $first = $mainExtras->first();
        $second = $mainExtras->last();

        $guiding->translated = [
            'pricing_extra' => [
                ['id' => $first['id'], 'name' => 'Angelrutenverleih', 'price' => 10],
            ],
        ];

        $result = collect($guiding->pricing_extra);

        $this->assertCount(2, $result, 'The untranslated sibling extra must still appear via main-language fallback.');
        $this->assertSame('Angelrutenverleih', $result->firstWhere('id', $first['id'])['name']);
        $this->assertSame('Extra bait', $result->firstWhere('id', $second['id'])['name']);
    }
}
