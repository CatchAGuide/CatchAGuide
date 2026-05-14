<?php

namespace App\Console\Commands;

use App\Models\EmailLog;
use App\Services\Email\EmailLogZeroRecipientRepair;
use Illuminate\Console\Command;

class RepairEmailLogZeroRecipientsCommand extends Command
{
    protected $signature = 'email-logs:repair-zero-recipients
                            {--force : Write updates to the database (default is dry-run)}';

    protected $description = 'Backfill email_logs.email where it was stored as 0 due to Symfony Mailer getTo() format.';

    public function handle(EmailLogZeroRecipientRepair $repair): int
    {
        $dryRun = ! $this->option('force');

        if ($dryRun) {
            $this->warn('Dry run: no rows will be updated. Re-run with --force to apply changes.');
        }

        $updated = 0;
        $failed = 0;

        EmailLog::query()
            ->where(function ($q) {
                $q->where('email', '0')
                    ->orWhere('email', '');
            })
            ->orderBy('id')
            ->chunkById(200, function ($logs) use ($repair, $dryRun, &$updated, &$failed) {
                foreach ($logs as $log) {
                    $email = $repair->resolve($log);
                    if ($email === null) {
                        $this->warn("ID {$log->id}: unresolved (type={$log->type}, target={$log->target})");
                        $failed++;

                        continue;
                    }
                    if (! $dryRun) {
                        EmailLog::query()->whereKey($log->id)->update(['email' => $email]);
                    }
                    $this->line("ID {$log->id}: \"{$log->email}\" -> {$email}");
                    $updated++;
                }
            });

        if ($dryRun) {
            $this->info("Dry run finished. {$updated} row(s) would be updated, {$failed} unresolved.");
        } else {
            $this->info("Updated {$updated} row(s). {$failed} unresolved.");
        }

        return self::SUCCESS;
    }
}
