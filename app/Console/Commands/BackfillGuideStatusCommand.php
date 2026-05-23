<?php

namespace App\Console\Commands;

use App\Enums\GuideStatus;
use App\Models\User;
use Illuminate\Console\Command;

class BackfillGuideStatusCommand extends Command
{
    protected $signature = 'guide:backfill-status {--dry-run : Show changes without saving}';

    protected $description = 'Backfill guide_status from legacy is_guide column';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $counts = ['verified' => 0, 'pending' => 0, 'skipped' => 0];

        User::query()
            ->whereNotNull('is_guide')
            ->chunkById(100, function ($users) use ($dryRun, &$counts) {
                foreach ($users as $user) {
                    if ($user->guide_status !== null) {
                        $counts['skipped']++;

                        continue;
                    }

                    if ($user->is_guide === 1 || $user->is_guide === true || $user->is_guide === '1') {
                        $counts['verified']++;
                        if (! $dryRun) {
                            $user->guide_status = GuideStatus::VERIFIED;
                            $user->guide_verified_at = $user->guide_verified_at ?? $user->updated_at;
                            $user->saveQuietly();
                        }
                    } elseif ($user->is_guide === 0 || $user->is_guide === '0') {
                        $counts['pending']++;
                        if (! $dryRun) {
                            $user->guide_status = GuideStatus::PENDING;
                            $user->guide_submitted_at = $user->guide_submitted_at ?? $user->updated_at;
                            $user->saveQuietly();
                        }
                    }
                }
            });

        $this->info('Backfill complete' . ($dryRun ? ' (dry run)' : '') . ':');
        $this->table(['Status', 'Count'], collect($counts)->map(fn ($v, $k) => [$k, $v])->values()->all());

        return self::SUCCESS;
    }
}
