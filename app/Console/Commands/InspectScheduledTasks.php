<?php

namespace App\Console\Commands;

use App\Models\CustomScheduledTask;
use App\Services\ScheduledTaskService;
use Illuminate\Console\Command;

class InspectScheduledTasks extends Command
{
    protected $signature = 'scheduled-tasks:inspect';

    protected $description = 'Print all scheduler tasks with merged admin config (same source as Kernel schedule).';

    public function handle(ScheduledTaskService $service): int
    {
        $this->info('Merged scheduled tasks (defaults + database overrides):');
        $this->newLine();

        foreach (array_keys(config('scheduled_tasks.tasks', [])) as $key) {
            $row = $service->resolveTask($key);
            if ($row === null) {
                continue;
            }
            $summary = $service->humanReadableSchedule($row);
            $on = $row['enabled'] ? 'ON ' : 'OFF';
            $this->line(sprintf(
                '<fg=cyan>%s</> [%s] %s — %s',
                $key,
                $on,
                $row['command'],
                $summary
            ));
        }

        foreach (CustomScheduledTask::query()->orderBy('id')->get() as $custom) {
            $row = $service->resolvedFromCustomModel($custom);
            $summary = $service->humanReadableSchedule($row);
            $on = $row['enabled'] ? 'ON ' : 'OFF';
            $this->line(sprintf(
                '<fg=magenta>custom_%s</> [%s] %s — %s',
                $custom->id,
                $on,
                $row['command'],
                $summary
            ));
        }

        $this->newLine();
        $this->comment('Run `php artisan schedule:list` to see the final Laravel schedule events.');

        return self::SUCCESS;
    }
}
