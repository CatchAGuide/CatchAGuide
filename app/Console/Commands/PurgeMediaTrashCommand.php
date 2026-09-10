<?php

namespace App\Console\Commands;

use App\Services\Media\MediaTrashService;
use Illuminate\Console\Command;

class PurgeMediaTrashCommand extends Command
{
    protected $signature = 'media:purge-trash
        {--days= : Days to keep trashed/backup images beyond the protected dates (defaults to config)}
        {--keep-dates= : Always keep this many newest backup dates per entity (default from config, usually 2)}
        {--dry-run : Show how many trash files would be deleted without deleting}';

    protected $description = 'Permanently delete old media trash/backups while always keeping the last N backup dates per entity';

    public function handle(MediaTrashService $trash): int
    {
        $days = (int) ($this->option('days') ?: $trash->retentionDays());
        $keepDates = (int) ($this->option('keep-dates') ?: $trash->keepDates());
        $dryRun = (bool) $this->option('dry-run');

        if ($days < 1) {
            $this->error('Days must be at least 1.');

            return self::FAILURE;
        }

        if ($keepDates < 1) {
            $this->error('keep-dates must be at least 1.');

            return self::FAILURE;
        }

        $this->info('Media trash / backup purge');
        $this->line("  Always keep last {$keepDates} backup date(s) per entity");
        $this->line("  Also keep files newer than {$days} day(s)");
        $this->line('  Cutoff:    ' . now()->subDays($days)->toDateString());

        $result = $trash->purgeExpired($days, $keepDates, $dryRun);

        if ($dryRun) {
            $this->warn('DRY RUN: no files deleted.');
        }

        $this->info(sprintf(
            'Done. %s %d file(s). Protected-date skips: %d. Retention skips: %d.',
            $dryRun ? 'Would purge' : 'Purged',
            $result['purged'],
            $result['skipped_protected'],
            $result['skipped_retention']
        ));

        return self::SUCCESS;
    }
}
