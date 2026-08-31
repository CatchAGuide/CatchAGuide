<?php

namespace App\Console\Commands;

use App\Services\Media\MediaTrashService;
use Illuminate\Console\Command;

class PurgeMediaTrashCommand extends Command
{
    protected $signature = 'media:purge-trash
        {--days= : Days to keep trashed images (defaults to config media_storage.trash.retention_days)}
        {--dry-run : Show how many trash files would be deleted without deleting}';

    protected $description = 'Permanently delete listing images from the media trash folder after the retention window';

    public function handle(MediaTrashService $trash): int
    {
        $days = (int) ($this->option('days') ?: $trash->retentionDays());
        if ($days < 1) {
            $this->error('Days must be at least 1.');

            return self::FAILURE;
        }

        $this->info('Media trash purge');
        $this->line("  Retention: keep last {$days} day(s)");
        $this->line('  Cutoff:    ' . now()->subDays($days)->toDateString());

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN: no files will be deleted. Remove --dry-run to purge.');

            return self::SUCCESS;
        }

        $purged = $trash->purgeExpired($days);
        $this->info("Done. Purged {$purged} file(s).");

        return self::SUCCESS;
    }
}
