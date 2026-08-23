<?php

namespace Tests\Feature\Console\CategoryPages;

use App\Models\MonthlyHighlight;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RemapMonthlyHighlightCountryIdsCommandTest extends TestCase
{
    use DatabaseTransactions;

    private function mappedCountryId(): array
    {
        $mapping = DB::table('category_entity_migration_map')
            ->where('old_table', 'c_countries')
            ->first();

        if (! $mapping) {
            $this->markTestSkipped('Need at least one c_countries -> category_entities mapping (run category-pages:backfill-phase2 first).');
        }

        return [(int) $mapping->old_id, (int) $mapping->new_id];
    }

    private function highlight(int $month, array $items): MonthlyHighlight
    {
        MonthlyHighlight::query()->where('month', $month)->delete();

        return MonthlyHighlight::query()->create([
            'month' => $month,
            'title_en' => 'Test',
            'title_de' => 'Test',
            'items' => $items,
            'is_active' => true,
        ]);
    }

    public function test_remaps_pair_item_country_id_to_category_entities_id(): void
    {
        [$oldId, $newId] = $this->mappedCountryId();

        $highlight = $this->highlight(1, [
            ['type' => MonthlyHighlight::ITEM_TYPE_PAIR, 'country_id' => $oldId, 'target_id' => 5],
        ]);

        $this->artisan('category-pages:remap-monthly-highlight-countries')->assertExitCode(0);

        $this->assertSame($newId, $highlight->fresh()->items[0]['country_id']);
        $this->assertSame(5, $highlight->fresh()->items[0]['target_id']);
    }

    public function test_remaps_legacy_country_item_id_to_category_entities_id(): void
    {
        [$oldId, $newId] = $this->mappedCountryId();

        $highlight = $this->highlight(2, [
            ['type' => MonthlyHighlight::ITEM_TYPE_COUNTRY, 'id' => $oldId],
        ]);

        $this->artisan('category-pages:remap-monthly-highlight-countries')->assertExitCode(0);

        $this->assertSame($newId, $highlight->fresh()->items[0]['id']);
    }

    public function test_leaves_target_items_untouched(): void
    {
        $highlight = $this->highlight(3, [
            ['type' => MonthlyHighlight::ITEM_TYPE_TARGET, 'id' => 42],
        ]);

        $this->artisan('category-pages:remap-monthly-highlight-countries')->assertExitCode(0);

        $this->assertSame(42, $highlight->fresh()->items[0]['id']);
    }

    public function test_leaves_unmapped_country_id_unchanged_and_does_not_crash(): void
    {
        $highlight = $this->highlight(4, [
            ['type' => MonthlyHighlight::ITEM_TYPE_PAIR, 'country_id' => 999999, 'target_id' => 5],
        ]);

        $this->artisan('category-pages:remap-monthly-highlight-countries')->assertExitCode(0);

        $this->assertSame(999999, $highlight->fresh()->items[0]['country_id']);
    }

    public function test_dry_run_reports_but_does_not_persist(): void
    {
        [$oldId] = $this->mappedCountryId();

        $highlight = $this->highlight(5, [
            ['type' => MonthlyHighlight::ITEM_TYPE_PAIR, 'country_id' => $oldId, 'target_id' => 5],
        ]);

        $this->artisan('category-pages:remap-monthly-highlight-countries', ['--dry-run' => true])
            ->assertExitCode(0);

        $this->assertSame($oldId, $highlight->fresh()->items[0]['country_id']);
    }

    public function test_rerun_is_idempotent(): void
    {
        [$oldId, $newId] = $this->mappedCountryId();

        $highlight = $this->highlight(6, [
            ['type' => MonthlyHighlight::ITEM_TYPE_PAIR, 'country_id' => $oldId, 'target_id' => 5],
        ]);

        $this->artisan('category-pages:remap-monthly-highlight-countries')->assertExitCode(0);
        $this->assertSame($newId, $highlight->fresh()->items[0]['country_id']);

        $this->artisan('category-pages:remap-monthly-highlight-countries')->assertExitCode(0);
        $this->assertSame($newId, $highlight->fresh()->items[0]['country_id'], 'Second run must not remap an already-remapped id.');
    }
}
