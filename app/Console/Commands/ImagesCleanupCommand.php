<?php

namespace App\Console\Commands;

use App\Services\ImageCleanup\ImageCleanupService;
use Illuminate\Console\Command;

/**
 * Check image paths from vacations, guidings, accommodations, camps, etc.
 * against the filesystem, report missing refs, optionally fix DB and delete orphans.
 *
 * Run with --report-only first. Use --fix-db / --delete-orphans only after review.
 * Destructive actions default to --dry-run; use --no-dry-run to apply.
 */
class ImagesCleanupCommand extends Command
{
    protected $signature = 'images:cleanup
        {--report-only : Only run report, no DB fixes or file deletions}
        {--fix-db : Remove DB references to missing image files}
        {--delete-orphans : Delete image files on disk that are not referenced by any model}
        {--dry-run : Do not write changes (default for fix-db and delete-orphans)}
        {--no-dry-run : Actually apply fix-db and/or delete-orphans}
        {--backup : Backup orphan files before deletion (only with --delete-orphans)}
        {--models=* : Limit to these models, e.g. guiding,vacation,camp}';

    protected $description = 'Check and cleanup image references (vacations, guidings, accommodations, camps, etc.)';

    public function handle(ImageCleanupService $service): int
    {
        $reportOnly = (bool) $this->option('report-only');
        $fixDb = (bool) $this->option('fix-db');
        $deleteOrphans = (bool) $this->option('delete-orphans');
        $dryRun = $this->option('no-dry-run') ? false : (bool) $this->option('dry-run');
        $backup = (bool) $this->option('backup');
        $models = $this->option('models');
        $models = !empty($models) && is_array($models) ? $models : null;
        if (is_array($models) && count($models) === 1 && str_contains($models[0], ',')) {
            $models = array_map('trim', explode(',', $models[0]));
        }

        $validModels = ImageCleanupService::getModelKeys();
        if ($models !== null) {
            $invalid = array_diff(array_map('strtolower', $models), $validModels);
            if (!empty($invalid)) {
                $this->error('Invalid --models: ' . implode(', ', $invalid));
                $this->line('Valid: ' . implode(', ', $validModels));
                return self::FAILURE;
            }
        }

        if (!$reportOnly && !$fixDb && !$deleteOrphans) {
            $this->warn('No action selected. Use --report-only, --fix-db and/or --delete-orphans.');
            $this->line('Running report only by default.');
            $reportOnly = true;
        }

        $this->info('🖼  Image cleanup');
        if ($models) {
            $this->line('   Models: ' . implode(', ', $models));
        } else {
            $this->line('   Models: all');
        }
        $this->newLine();

        $report = $service->runReport($models);
        $this->printReport($report);

        if ($reportOnly) {
            return self::SUCCESS;
        }

        if ($fixDb) {
            $this->newLine();
            $this->runFixDb($service, $models, $dryRun);
        }

        if ($deleteOrphans) {
            $this->newLine();
            $this->runDeleteOrphans($service, $models, $dryRun, $backup);
        }

        return self::SUCCESS;
    }

    private function printReport(array $report): void
    {
        $this->info('📋 Report');
        $this->line('   Referenced paths: ' . count($report['referenced']));
        $this->line('   Unique paths: ' . count($report['referenced_unique']));
        $this->line('   ✅ Existing: ' . $report['existing_count']);
        $this->line('   ❌ Missing: ' . $report['missing_count']);

        if ($report['missing_count'] > 0) {
            $this->newLine();
            $this->warn('Missing references (DB points to non-existent files):');
            $rows = [];
            foreach (array_slice($report['missing'], 0, 50) as $m) {
                $rows[] = [$m['model'], $m['id'], $m['field'], $m['path']];
            }
            $this->table(['Model', 'ID', 'Field', 'Path'], $rows);
            if (count($report['missing']) > 50) {
                $this->line('   ... and ' . (count($report['missing']) - 50) . ' more.');
            }
        }
    }

    private function runFixDb(ImageCleanupService $service, ?array $models, bool $dryRun): void
    {
        $this->info('🔧 Fix DB references');
        if ($dryRun) {
            $this->warn('   Dry run – no changes will be written. Use --no-dry-run to apply.');
        }

        $result = $service->fixDbRefs([
            'models' => $models,
            'dry_run' => $dryRun,
        ]);

        $fixed = $result['fixed'];
        $errors = $result['errors'];

        if (!empty($errors)) {
            foreach ($errors as $e) {
                $this->error('   ' . $e);
            }
        }

        if (empty($fixed)) {
            $this->line('   No fixes applied.');
            return;
        }

        $this->line('   Fixed: ' . count($fixed));
        $rows = [];
        foreach (array_slice($fixed, 0, 30) as $f) {
            $path = $f['path'] ?? ($f['paths_removed'] ?? []);
            if (is_array($path)) {
                $path = implode(', ', array_slice($path, 0, 3)) . (count($path) > 3 ? '…' : '');
            }
            $rows[] = [$f['model'], $f['id'], $f['field'], $path];
        }
        $this->table(['Model', 'ID', 'Field', 'Path(s)'], $rows);
        if (count($fixed) > 30) {
            $this->line('   ... and ' . (count($fixed) - 30) . ' more.');
        }
    }

    private function runDeleteOrphans(ImageCleanupService $service, ?array $models, bool $dryRun, bool $backup): void
    {
        $this->info('🗑  Delete orphan files');
        if ($dryRun) {
            $this->warn('   Dry run – no files will be deleted. Use --no-dry-run to apply.');
        }
        if ($backup) {
            $this->line('   Backups will be written to storage/app/image-cleanup-backups/');
        }

        $result = $service->deleteOrphanFiles([
            'models' => $models,
            'dry_run' => $dryRun,
            'backup' => $backup,
        ]);

        $orphans = $result['orphans'];
        $deleted = $result['deleted'];
        $backedUp = $result['backed_up'];
        $errors = $result['errors'];

        if (!empty($errors)) {
            foreach ($errors as $e) {
                $this->error('   ' . $e);
            }
        }

        $this->line('   Orphan files found: ' . count($orphans));
        $this->line('   ' . ($dryRun ? 'Would delete' : 'Deleted') . ': ' . count($deleted));
        if ($backup && !empty($backedUp)) {
            $this->line('   Backed up: ' . count($backedUp));
        }

        if (!empty($deleted) && count($deleted) <= 20) {
            foreach ($deleted as $p) {
                $this->line('      - ' . $p);
            }
        } elseif (!empty($deleted)) {
            foreach (array_slice($deleted, 0, 10) as $p) {
                $this->line('      - ' . $p);
            }
            $this->line('      ... and ' . (count($deleted) - 10) . ' more.');
        }
    }
}
