<?php

namespace Tests\Unit\Translation;

use App\Models\Guiding;
use App\Models\Language;
use App\Services\Translation\GuidingTranslationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Translations made before getTranslatableFields() read the raw list columns stored
 * requirements/other_information as [], so tour pages showed the German text on the
 * English site. missingListRows() finds exactly those rows for the --list-fields backfill.
 */
class GuidingTranslationMissingListRowsTest extends TestCase
{
    use DatabaseTransactions;

    private const GUIDING_ID = 987654321;

    private function guiding(): Guiding
    {
        $guiding = new Guiding();
        $guiding->forceFill([
            'id' => self::GUIDING_ID,
            'language' => 'de',
            'title' => 'Zander angeln',
            'requirements' => json_encode(['1' => 'Ist erforderlich', '2' => 'wetterfeste Bekleidung']),
            'other_information' => json_encode(['7' => 'Catch & Release erwünscht']),
            'recommendations' => json_encode([['id' => 4, 'value' => 'Sonnenbrille']]),
        ]);
        $guiding->syncOriginal();

        return $guiding;
    }

    private function storeTranslation(array $jsonData): void
    {
        Language::query()->create([
            'source_id' => (string) self::GUIDING_ID,
            'type' => 'guidings',
            'language' => 'en',
            'title' => 'Zander fishing',
            'json_data' => $jsonData,
        ]);
    }

    public function test_reports_rows_missing_from_a_stale_translation(): void
    {
        $this->storeTranslation(['title' => 'Zander fishing', 'requirements' => [], 'other_information' => []]);

        $missing = app(GuidingTranslationService::class)->missingListRows($this->guiding(), 'en');

        $this->assertSame([
            'requirements_1' => 'Ist erforderlich',
            'requirements_2' => 'wetterfeste Bekleidung',
            'recommendations_0' => 'Sonnenbrille',
            'other_information_7' => 'Catch & Release erwünscht',
        ], $missing);
    }

    public function test_rows_already_translated_in_either_shape_are_not_reported(): void
    {
        $this->storeTranslation([
            'requirements' => ['1' => 'Is required'],
            'other_information' => [['id' => 7, 'value' => 'Catch & release preferred']],
            'recommendations' => [['id' => 4, 'value' => 'Sunglasses']],
        ]);

        $missing = app(GuidingTranslationService::class)->missingListRows($this->guiding(), 'en');

        $this->assertSame(['requirements_2' => 'wetterfeste Bekleidung'], $missing);
    }

    public function test_nothing_reported_without_an_existing_translation(): void
    {
        $this->assertSame([], app(GuidingTranslationService::class)->missingListRows($this->guiding(), 'en'));
    }
}
