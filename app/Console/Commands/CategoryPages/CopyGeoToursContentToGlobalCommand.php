<?php

namespace App\Console\Commands\CategoryPages;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Models\CategoryEntity;
use App\Services\CategoryPage\CategoryPageContentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * One-off seed for the region/city `global` scope (/destination/{country}/{region}/{city}),
 * which had no admin editor before Oct 2026. Copies each region's/city's `tours` copy + FAQs
 * per locale as a starting point. Locales whose `global` copy is already filled are skipped
 * unless --force is given, so it is safe to re-run.
 */
class CopyGeoToursContentToGlobalCommand extends Command
{
    protected $signature = 'category-pages:copy-geo-tours-to-global
        {--dry-run : Roll back all changes after reporting what would happen}
        {--force : Overwrite global content that is already filled}';

    protected $description = 'Copy region/city tours-scoped content and FAQs into the global scope (used by /destination pages)';

    public function handle(CategoryPageContentService $content): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $overwrite = (bool) $this->option('force');

        $groups = [
            'region' => [CategoryEntity::regions(), CategoryPageEntityType::GEO_REGION],
            'city' => [CategoryEntity::cities(), CategoryPageEntityType::GEO_CITY],
        ];

        $rows = [];

        DB::beginTransaction();

        try {
            foreach ($groups as $label => [$query, $entityType]) {
                foreach (config('app.locales') as $locale) {
                    $copied = 0;
                    $skipped = 0;

                    $query->clone()->orderBy('id')->each(function (CategoryEntity $entity) use ($content, $entityType, $locale, $overwrite, &$copied, &$skipped) {
                        $content->copyEntityScope($entityType, $entity->id, CategoryPageScope::TOURS, CategoryPageScope::GLOBAL, $locale, $overwrite)
                            ? $copied++
                            : $skipped++;
                    });

                    $rows[] = [$label, $locale, $copied, $skipped];
                }
            }

            $this->table(['Type', 'Locale', 'Copied', 'Skipped (no tours copy / global already filled)'], $rows);

            if ($dryRun) {
                DB::rollBack();
                $this->warn('Dry run — all changes rolled back.');
            } else {
                DB::commit();
                $this->info('Copy committed.');
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return self::SUCCESS;
    }
}
