<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClearThreatIntelligenceCommand extends Command
{
    protected $signature = 'threat-intelligence:cleanup
        {--days= : Days of data to keep (defaults to config ddos.threat_intelligence.retention_days)}
        {--chunk=1000 : Number of rows to delete per batch}
        {--dry-run : Show how many rows would be deleted without deleting}';

    protected $description = 'Delete threat_intelligence rows older than the retention window (default: 7 days)';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?: config('ddos.threat_intelligence.retention_days', 7));
        $chunk = max(1, (int) $this->option('chunk'));
        $dryRun = (bool) $this->option('dry-run');

        if ($days < 1) {
            $this->error('Days must be at least 1.');

            return self::FAILURE;
        }

        $cutoff = now()->subDays($days);
        $query = DB::table('threat_intelligence')->where('created_at', '<', $cutoff);
        $eligible = (clone $query)->count();
        $remaining = DB::table('threat_intelligence')->where('created_at', '>=', $cutoff)->count();

        $this->info('Threat intelligence cleanup');
        $this->line("  Retention: keep last {$days} day(s)");
        $this->line("  Cutoff:    {$cutoff->toDateTimeString()}");
        $this->line("  Eligible:  {$eligible} row(s) older than cutoff");
        $this->line("  Keeping:   {$remaining} row(s) within retention");

        if ($eligible === 0) {
            $this->info('Nothing to delete.');

            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->warn("DRY RUN: would delete {$eligible} row(s).");

            return self::SUCCESS;
        }

        $deleted = 0;

        do {
            $batch = DB::table('threat_intelligence')
                ->where('created_at', '<', $cutoff)
                ->orderBy('id')
                ->limit($chunk)
                ->delete();

            $deleted += $batch;

            if ($batch > 0) {
                $this->line("  Deleted batch of {$batch} (total: {$deleted}/{$eligible})");
            }
        } while ($batch > 0);

        Log::info('Threat intelligence cleanup completed', [
            'days' => $days,
            'cutoff' => $cutoff->toDateTimeString(),
            'deleted' => $deleted,
            'remaining' => $remaining,
        ]);

        $this->info("Done. Deleted {$deleted} row(s).");

        return self::SUCCESS;
    }
}
