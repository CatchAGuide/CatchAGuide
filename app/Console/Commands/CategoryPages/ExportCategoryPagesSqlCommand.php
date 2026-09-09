<?php

namespace App\Console\Commands\CategoryPages;

use App\Domain\CategoryPage\CategoryPageEntityType;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Dumps every table behind the admin category hub (targets, methods, category_pages,
 * category_entities, category_entity_migration_map, plus the category-page-scoped rows of the
 * shared languages/faqs tables) into one portable .sql file.
 *
 * Meant to be run on the source environment (e.g. staging) after content edits there, then the
 * resulting file copied down and applied to another environment (e.g. local) with:
 *   mysql -u <user> -p <database> < storage/app/category-pages-export.sql
 *
 * Each table's section is a scoped DELETE followed by a fresh INSERT of the exported rows
 * (ids preserved as-is), not a blind TRUNCATE — languages/faqs also hold unrelated content, and
 * category_entities ids are referenced elsewhere (e.g. monthly_highlights.items via
 * category_entity_migration_map, see docs/category-pages-data-consolidation-plan.md), so ids must
 * stay stable across the copy.
 */
class ExportCategoryPagesSqlCommand extends Command
{
    protected $signature = 'category-pages:export-sql {--path=storage/app/category-pages-export.sql}';

    protected $description = 'Export all admin category hub data (targets, methods, category pages, category entities, and their languages/faqs content) to a portable .sql file';

    private const LANGUAGE_TYPES = [
        CategoryPageEntityType::CATEGORY_PAGE,
        CategoryPageEntityType::TARGET_FISH,
        CategoryPageEntityType::METHOD,
        CategoryPageEntityType::GEO_COUNTRY,
        CategoryPageEntityType::GEO_REGION,
        CategoryPageEntityType::GEO_CITY,
        CategoryPageEntityType::DESTINATION_COUNTRY,
        CategoryPageEntityType::DESTINATION_HUB,
    ];

    private const FAQ_PAGES = [
        'Targets',
        'Methods',
        CategoryPageEntityType::GEO_COUNTRY,
        CategoryPageEntityType::GEO_REGION,
        CategoryPageEntityType::GEO_CITY,
    ];

    public function handle(): int
    {
        $path = base_path($this->option('path'));

        $handle = fopen($path, 'w');

        if ($handle === false) {
            $this->error("Could not open {$path} for writing.");

            return self::FAILURE;
        }

        fwrite($handle, '-- Category pages export generated '.now()->toDateTimeString().' on the "'.app()->environment()."\" environment --\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\nSTART TRANSACTION;\n\n");

        $this->exportTable($handle, 'targets');
        $this->exportTable($handle, 'methods');
        $this->exportTable($handle, 'category_pages');
        $this->exportTable($handle, 'category_entities');
        $this->exportTable($handle, 'category_entity_migration_map');
        $this->exportTable(
            $handle,
            'languages',
            fn ($query) => $query->whereIn('type', self::LANGUAGE_TYPES),
            '`type` IN ('.$this->quotedList(self::LANGUAGE_TYPES).')'
        );
        $this->exportTable(
            $handle,
            'faqs',
            fn ($query) => $query->whereIn('page', self::FAQ_PAGES),
            '`page` IN ('.$this->quotedList(self::FAQ_PAGES).')'
        );

        fwrite($handle, "COMMIT;\nSET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);

        $this->info("Category pages exported to {$path}");

        return self::SUCCESS;
    }

    private function exportTable(mixed $handle, string $table, ?\Closure $scope = null, ?string $deleteWhere = null): void
    {
        $query = DB::table($table);

        if ($scope) {
            $scope($query);
        }

        $count = (clone $query)->count();

        fwrite($handle, "-- {$table} ({$count} row".($count === 1 ? '' : 's').")\n");
        fwrite($handle, 'DELETE FROM `'.$table.'`'.($deleteWhere ? " WHERE {$deleteWhere}" : '').";\n");

        if ($count === 0) {
            fwrite($handle, "\n");

            return;
        }

        $pdo = DB::connection()->getPdo();

        $query->orderBy('id')->chunk(200, function (Collection $rows) use ($handle, $table, $pdo) {
            $columns = array_keys((array) $rows->first());
            $columnList = implode(', ', array_map(fn ($column) => "`{$column}`", $columns));

            $valueRows = $rows->map(function ($row) use ($columns, $pdo) {
                $row = (array) $row;
                $values = array_map(fn ($column) => $this->quoteValue($pdo, $row[$column]), $columns);

                return '('.implode(', ', $values).')';
            })->implode(",\n");

            fwrite($handle, "INSERT INTO `{$table}` ({$columnList}) VALUES\n{$valueRows};\n");
        });

        fwrite($handle, "\n");
    }

    private function quoteValue(\PDO $pdo, mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return $pdo->quote((string) $value);
    }

    private function quotedList(array $values): string
    {
        return implode(', ', array_map(fn ($value) => "'".addslashes($value)."'", $values));
    }
}
