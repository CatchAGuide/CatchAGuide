<?php

namespace Tests\Unit\Translation;

use App\Models\CampFacility;
use App\Models\Facility;
use App\Models\Target;
use App\Services\Translation\ComponentTranslationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ComponentTranslationServiceTest extends TestCase
{
    use DatabaseTransactions;

    private ComponentTranslationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ComponentTranslationService();
    }

    public function test_resolve_keys_defaults_to_the_full_registry_when_none_requested(): void
    {
        $this->assertSame($this->service->modelKeys(), $this->service->resolveKeys([]));
    }

    public function test_resolve_keys_filters_out_unknown_keys_and_splits_commas(): void
    {
        $resolved = $this->service->resolveKeys(['facility,bathroom-amenity', 'not-a-real-key']);

        $this->assertSame(['facility', 'bathroom-amenity'], $resolved);
    }

    public function test_pending_field_work_translates_de_to_en_when_en_is_missing(): void
    {
        $facility = Facility::query()->create([
            'name' => 'Schwimmbad',
            'name_en' => '',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $work = $this->service->pendingFieldWork($facility, 'facility', false);

        $this->assertCount(1, $work);
        $this->assertSame('de', $work[0]['from']);
        $this->assertSame('en', $work[0]['to']);
        $this->assertSame('Schwimmbad', $work[0]['text']);
    }

    public function test_pending_field_work_falls_back_to_en_to_de_when_de_is_missing(): void
    {
        $facility = Facility::query()->create([
            'name' => '',
            'name_en' => 'Swimming pool',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $work = $this->service->pendingFieldWork($facility, 'facility', false);

        $this->assertCount(1, $work);
        $this->assertSame('en', $work[0]['from']);
        $this->assertSame('de', $work[0]['to']);
        $this->assertSame('Swimming pool', $work[0]['text']);
    }

    public function test_pending_field_work_is_empty_when_both_columns_are_filled_and_not_forced(): void
    {
        $facility = Facility::query()->create([
            'name' => 'Schwimmbad',
            'name_en' => 'Swimming pool',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->assertSame([], $this->service->pendingFieldWork($facility, 'facility', false));
    }

    public function test_pending_field_work_regenerates_de_when_de_is_an_untranslated_copy_of_en(): void
    {
        // Mirrors real data: rows created with the DE column defaulted to
        // the English value instead of an actual German translation.
        $facility = Facility::query()->create([
            'name' => 'Private jetty / boat dock',
            'name_en' => 'Private jetty / boat dock',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $work = $this->service->pendingFieldWork($facility, 'facility', false);

        $this->assertCount(1, $work);
        $this->assertSame('en', $work[0]['from']);
        $this->assertSame('de', $work[0]['to']);
        $this->assertSame('Private jetty / boat dock', $work[0]['text']);
    }

    public function test_pending_field_work_duplicate_detection_is_case_insensitive(): void
    {
        $facility = Facility::query()->create([
            'name' => 'wifi',
            'name_en' => 'WiFi',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $work = $this->service->pendingFieldWork($facility, 'facility', false);

        $this->assertCount(1, $work);
        $this->assertSame('en', $work[0]['from']);
        $this->assertSame('de', $work[0]['to']);
    }

    public function test_rows_to_process_without_force_includes_untranslated_duplicates(): void
    {
        $marker = 'test-'.uniqid();

        $duplicate = Facility::query()->create([
            'name' => $marker.'-same',
            'name_en' => $marker.'-same',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $ids = $this->service->rowsToProcess('facility', false)->pluck('id');

        $this->assertTrue($ids->contains($duplicate->id));
    }

    public function test_pending_field_work_regenerates_en_from_de_when_forced(): void
    {
        $facility = Facility::query()->create([
            'name' => 'Schwimmbad',
            'name_en' => 'Swimming pool',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $work = $this->service->pendingFieldWork($facility, 'facility', true);

        $this->assertCount(1, $work);
        $this->assertSame('de', $work[0]['from']);
        $this->assertSame('en', $work[0]['to']);
    }

    public function test_rows_to_process_without_force_only_returns_incomplete_pairs(): void
    {
        $marker = 'test-'.uniqid();

        $complete = Facility::query()->create([
            'name' => $marker.'-complete-de',
            'name_en' => $marker.'-complete-en',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $incomplete = Facility::query()->create([
            'name' => $marker.'-incomplete-de',
            'name_en' => '',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $ids = $this->service->rowsToProcess('facility', false)->pluck('id');

        $this->assertFalse($ids->contains($complete->id));
        $this->assertTrue($ids->contains($incomplete->id));
    }

    public function test_rows_to_process_with_force_returns_every_row_with_de_content(): void
    {
        $marker = 'test-'.uniqid();

        $complete = Facility::query()->create([
            'name' => $marker.'-complete-de',
            'name_en' => $marker.'-complete-en',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $ids = $this->service->rowsToProcess('facility', true)->pluck('id');

        $this->assertTrue($ids->contains($complete->id));
    }

    public function test_pending_field_work_skips_duplicates_on_tables_without_a_reliable_direction(): void
    {
        // Target/Method/Water duplicates are often genuine shared terms
        // (fish species names, "GPS") rather than copy bugs, so they must
        // not be auto-translated.
        $target = new Target();
        $target->name = 'Zander';
        $target->name_en = 'Zander';
        $target->save();

        $this->assertSame([], $this->service->pendingFieldWork($target, 'target', false));
    }

    public function test_ambiguous_duplicates_reports_untouched_rows_on_null_direction_tables(): void
    {
        $target = new Target();
        $target->name = 'Zander';
        $target->name_en = 'Zander';
        $target->save();

        $ambiguous = $this->service->ambiguousDuplicates($target, 'target');

        $this->assertCount(1, $ambiguous);
        $this->assertSame('name', $ambiguous[0]['de_field']);
        $this->assertSame('name_en', $ambiguous[0]['en_field']);
    }

    public function test_ambiguous_duplicates_is_empty_for_tables_with_a_reliable_direction(): void
    {
        $facility = Facility::query()->create([
            'name' => 'Sauna',
            'name_en' => 'Sauna',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->assertSame([], $this->service->ambiguousDuplicates($facility, 'facility'));
    }

    public function test_camp_facility_uses_name_de_as_the_source_column(): void
    {
        $campFacility = CampFacility::query()->create([
            'name' => 'Swimming pool',
            'name_de' => 'Schwimmbad',
            'name_en' => '',
            'is_active' => true,
        ]);

        $work = $this->service->pendingFieldWork($campFacility, 'camp-facility', false);

        $this->assertCount(1, $work);
        $this->assertSame('name_de', $work[0]['de_field']);
        $this->assertSame('Schwimmbad', $work[0]['text']);
    }
}
