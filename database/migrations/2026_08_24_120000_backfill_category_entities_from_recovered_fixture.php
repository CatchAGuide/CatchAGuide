<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Recovers the data that `BackfillCategoryEntitiesCommand` (docs/category-pages-data-consolidation-plan.md
 * §13) produced when it was run against the local dev DB — that command was deleted after its one-time run
 * and never committed, so `category_entities`/`category_entity_migration_map` and the migrated
 * `languages`/`faqs` rows never existed anywhere in version control. On any environment where migration
 * `2026_08_21_160000_drop_legacy_category_geo_tables` runs, the source tables
 * (c_countries/c_regions/c_cities/c_*_translations/destinations) are gone and there is no way to regenerate
 * this data from scratch — this migration replays the exact recovered dataset instead, from
 * database/data/category_entities_backfill_2026_08_24.json (exported from the dev DB, the only environment
 * that still had it).
 *
 * Idempotent by design so it is safe on every environment regardless of what already ran there:
 *  - category_entities: fresh table everywhere this branch lands, so original ids are preserved exactly
 *    (other data — e.g. monthly_highlights.items via category_entity_migration_map — is documented as
 *    referencing specific category_entities ids, so id stability matters). insertOrIgnore on the primary key
 *    makes re-running a no-op.
 *  - category_entity_migration_map: same — fresh table, insertOrIgnore relies on its own
 *    unique(old_table, old_id) constraint.
 *  - languages / faqs: these are live, shared, actively-written tables whose auto-increment ids differ
 *    per environment, so fixture rows are inserted WITHOUT their original ids (fresh ids assigned per
 *    environment) and deduplicated by natural key instead, to avoid any risk of colliding with or
 *    overwriting unrelated content already sitting at the same id on a given environment.
 */
return new class extends Migration
{
    private const FIXTURE_PATH = __DIR__.'/../data/category_entities_backfill_2026_08_24.json';

    public function up(): void
    {
        $fixture = $this->loadFixture();

        DB::transaction(function () use ($fixture) {
            $this->backfillCategoryEntities($fixture['category_entities']);
            $this->backfillMigrationMap($fixture['category_entity_migration_map']);
            $this->backfillLanguages($fixture['languages']);
            $this->backfillFaqs($fixture['faqs']);
        });
    }

    public function down(): void
    {
        throw new \RuntimeException(
            'Not reversible: this migration recovers category_entities/languages/faqs data that has no '
            .'other surviving source once the legacy geo tables are dropped. Rolling it back would delete '
            .'live geo category-page content, not restore the pre-migration state — restore from a database '
            .'backup instead, per the same convention as 2026_08_21_160000_drop_legacy_category_geo_tables.'
        );
    }

    private function loadFixture(): array
    {
        if (! file_exists(self::FIXTURE_PATH)) {
            throw new \RuntimeException('Missing fixture: '.self::FIXTURE_PATH);
        }

        return json_decode(file_get_contents(self::FIXTURE_PATH), true, 512, JSON_THROW_ON_ERROR);
    }

    private function backfillCategoryEntities(array $rows): void
    {
        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('category_entities')->insertOrIgnore($chunk);
        }
    }

    private function backfillMigrationMap(array $rows): void
    {
        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('category_entity_migration_map')->insertOrIgnore($chunk);
        }
    }

    private function backfillLanguages(array $rows): void
    {
        $existing = DB::table('languages')
            ->whereIn('type', ['geo_country', 'geo_region', 'geo_city'])
            ->get(['source_id', 'type', 'scope', 'language'])
            ->map(fn ($row) => $this->languageKey((array) $row))
            ->flip();

        $toInsert = array_values(array_filter($rows, fn ($row) => ! $existing->has($this->languageKey($row))));

        foreach (array_chunk($toInsert, 100) as $chunk) {
            DB::table('languages')->insert($chunk);
        }
    }

    private function backfillFaqs(array $rows): void
    {
        $existing = DB::table('faqs')
            ->whereIn('page', ['geo_country', 'geo_region', 'geo_city'])
            ->get(['source_id', 'page', 'scope', 'language', 'question'])
            ->map(fn ($row) => $this->faqKey((array) $row))
            ->flip();

        $toInsert = array_values(array_filter($rows, fn ($row) => ! $existing->has($this->faqKey($row))));

        foreach (array_chunk($toInsert, 100) as $chunk) {
            DB::table('faqs')->insert($chunk);
        }
    }

    private function languageKey(array $row): string
    {
        return implode('|', [$row['source_id'], $row['type'], $row['scope'], $row['language']]);
    }

    private function faqKey(array $row): string
    {
        return implode('|', [$row['source_id'], $row['page'], $row['scope'], $row['language'], md5($row['question'])]);
    }
};
