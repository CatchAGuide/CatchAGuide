<?php

namespace App\Console\Commands\CategoryPages;

use App\Models\MonthlyHighlight;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Prep tool for docs/category-pages-data-consolidation-plan.md §14's "Phase 4 handoff" —
 * the MonthlyHighlightController concrete hazard.
 *
 * `monthly_highlights.items` stores raw country ids by value (pair items' `country_id`,
 * legacy country items' `id`), keyed against `c_countries.id` today. `category_entities`
 * ids are fresh auto-increments, not a 1:1 copy of `c_countries.id` (e.g. the Iceland/Island
 * merge lands at id 534) — so once a future Phase 4 session repoints
 * MonthlyHighlightController/StoreMonthlyHighlightRequest/UpdateMonthlyHighlightRequest/
 * HomepageLandingService at CategoryEntity, every existing stored id would silently point at
 * the wrong (or a nonexistent) entity unless remapped first.
 *
 * This command does that remap pass through `category_entity_migration_map`, and only that —
 * it does not touch any of the four files above, which still read/write/validate against
 * `Country`/`c_countries` today and remain correct as long as this command has NOT run. It
 * must be run in the same change that switches those files over, not before — running it
 * against a still-Country-reading site would break admin validation and homepage rendering
 * immediately, since the stored ids would stop matching `c_countries.id`.
 */
class RemapMonthlyHighlightCountryIdsCommand extends Command
{
    protected $signature = 'category-pages:remap-monthly-highlight-countries {--dry-run : Roll back all changes after reporting what would happen}';

    protected $description = 'Remap monthly_highlights.items country ids from c_countries.id to category_entities.id via category_entity_migration_map (Phase 4 prep — do not run before MonthlyHighlightController is switched over)';

    /** @var array<string, int> */
    private array $counts = [];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        DB::beginTransaction();

        try {
            $this->remapHighlights();
            $this->printReport($dryRun);

            if ($dryRun) {
                DB::rollBack();
                $this->warn('Dry run — all changes rolled back.');
            } else {
                DB::commit();
                $this->info('Remap committed.');
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return self::SUCCESS;
    }

    private function remapHighlights(): void
    {
        MonthlyHighlight::query()->orderBy('id')->each(function (MonthlyHighlight $highlight) {
            $items = $highlight->items ?? [];
            $changed = false;

            foreach ($items as $index => $item) {
                if (! is_array($item)) {
                    continue;
                }

                $field = match ($item['type'] ?? null) {
                    MonthlyHighlight::ITEM_TYPE_PAIR => 'country_id',
                    MonthlyHighlight::ITEM_TYPE_COUNTRY => 'id',
                    default => null,
                };

                if ($field === null || ! isset($item[$field])) {
                    continue;
                }

                $remapped = $this->remapId((int) $item[$field]);

                if ($remapped === null) {
                    continue;
                }

                if ($remapped !== (int) $item[$field]) {
                    $items[$index][$field] = $remapped;
                    $changed = true;
                }
            }

            if ($changed) {
                $highlight->update(['items' => $items]);
                $this->bump('monthly_highlights.remapped');
            }
        });
    }

    /**
     * Returns the category_entities id to store, or null to leave the value untouched.
     */
    private function remapId(int $id): ?int
    {
        $alreadyRemapped = DB::table('category_entity_migration_map')
            ->where('new_id', $id)
            ->exists();

        if ($alreadyRemapped) {
            $this->bump('items.already_remapped');

            return null;
        }

        $newId = DB::table('category_entity_migration_map')
            ->where('old_table', 'c_countries')->where('old_id', $id)
            ->value('new_id');

        if ($newId === null) {
            $this->bump('items.no_migration_map_entry');
            $this->warn("No category_entity_migration_map entry for c_countries.id={$id} — left unchanged.");

            return null;
        }

        $this->bump('items.remapped');

        return (int) $newId;
    }

    private function bump(string $key): void
    {
        $this->counts[$key] = ($this->counts[$key] ?? 0) + 1;
    }

    private function printReport(bool $dryRun): void
    {
        $this->info('Monthly highlight country id remap report'.($dryRun ? ' (dry run)' : '').':');
        $this->table(['Metric', 'Count'], collect($this->counts)->map(fn ($v, $k) => [$k, $v])->values()->all());
    }
}
