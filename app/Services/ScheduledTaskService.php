<?php

namespace App\Services;

use App\Models\CustomScheduledTask;
use App\Models\ScheduledTaskSetting;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

class ScheduledTaskService
{
    private function cacheKey(string $key): string
    {
        return 'scheduled_task_resolve:' . $key;
    }

    /**
     * Register all configured tasks on the Laravel schedule (replaces hard-coded Kernel entries).
     */
    public function register(Schedule $schedule): void
    {
        foreach (array_keys(config('scheduled_tasks.tasks', [])) as $key) {
            $resolved = $this->resolveTask($key);
            if ($resolved === null || !$resolved['enabled']) {
                continue;
            }

            $this->attachResolvedCommand($schedule, $resolved);
        }

        foreach (CustomScheduledTask::query()->orderBy('id')->get() as $custom) {
            if (!$custom->is_enabled) {
                continue;
            }

            $resolved = $this->resolvedFromCustomModel($custom);
            $this->attachResolvedCommand($schedule, $resolved);
        }
    }

    /**
     * @param  array<string, mixed>  $resolved
     */
    private function attachResolvedCommand(Schedule $schedule, array $resolved): void
    {
        $event = $schedule->command($resolved['command']);
        $this->applyFrequency($event, $resolved);

        if (!empty($resolved['without_overlapping'])) {
            $event->withoutOverlapping();
        }
        if (!empty($resolved['run_in_background'])) {
            $event->runInBackground();
        }
        if (!empty($resolved['append_output_to'])) {
            $event->appendOutputTo(storage_path($resolved['append_output_to']));
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function resolvedFromCustomModel(CustomScheduledTask $model): array
    {
        $scheduleTime = $model->schedule_time;
        if (is_string($scheduleTime) && $scheduleTime !== '') {
            $scheduleTime = $this->normalizeTime($scheduleTime);
        } else {
            $scheduleTime = null;
        }

        $cronExpression = $model->cron_expression;
        if (is_string($cronExpression) && trim($cronExpression) !== '') {
            $cronExpression = trim($cronExpression);
        } else {
            $cronExpression = null;
        }

        return [
            'key' => 'custom_' . $model->id,
            'command' => $model->command,
            'enabled' => $model->is_enabled,
            'frequency' => $model->frequency,
            'schedule_time' => $scheduleTime,
            'day_of_week' => $model->day_of_week !== null ? (int) $model->day_of_week : null,
            'cron_expression' => $cronExpression,
            'without_overlapping' => (bool) $model->without_overlapping,
            'run_in_background' => (bool) $model->run_in_background,
            'append_output_to' => $model->append_output_to,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function resolveTask(string $key): ?array
    {
        if (!isset(config('scheduled_tasks.tasks', [])[$key])) {
            return null;
        }

        return Cache::remember($this->cacheKey($key), 3600, fn () => $this->resolveTaskUncached($key));
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveTaskUncached(string $key): array
    {
        $meta = config('scheduled_tasks.tasks')[$key];
        $def = $meta['default'];
        $row = ScheduledTaskSetting::query()->where('key', $key)->first();

        $scheduleTime = $row?->schedule_time ?? $def['schedule_time'] ?? null;
        if (is_string($scheduleTime) && $scheduleTime !== '') {
            $scheduleTime = $this->normalizeTime($scheduleTime);
        } else {
            $scheduleTime = null;
        }

        $dow = $row && $row->day_of_week !== null
            ? (int) $row->day_of_week
            : (isset($def['day_of_week']) ? (int) $def['day_of_week'] : null);

        $cronExpression = $row?->cron_expression ?? $def['cron_expression'] ?? null;
        if (is_string($cronExpression)) {
            $cronExpression = trim($cronExpression) !== '' ? trim($cronExpression) : null;
        }

        return [
            'key' => $key,
            'command' => $meta['command'],
            'enabled' => $row ? (bool) $row->is_enabled : (bool) ($def['enabled'] ?? false),
            'frequency' => $row?->frequency ?? $def['frequency'],
            'schedule_time' => $scheduleTime,
            'day_of_week' => $dow,
            'cron_expression' => $cronExpression,
            'without_overlapping' => (bool) ($meta['without_overlapping'] ?? false),
            'run_in_background' => (bool) ($meta['run_in_background'] ?? false),
            'append_output_to' => $meta['append_output_to'] ?? null,
        ];
    }

    public function forgetCache(string $key): void
    {
        Cache::forget($this->cacheKey($key));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function allForAdmin(): array
    {
        $tasks = config('scheduled_tasks.tasks', []);
        $rows = [];
        foreach ($tasks as $key => $meta) {
            $resolved = $this->resolveTaskUncached($key);
            $resolved['label'] = $meta['label'];
            $resolved['description'] = $meta['description'];
            $resolved['schedule_summary'] = $this->humanReadableSchedule($resolved);
            $resolved['is_custom'] = false;
            $resolved['id'] = null;
            $rows[] = $resolved;
        }

        foreach (CustomScheduledTask::query()->orderBy('id')->get() as $custom) {
            $resolved = $this->resolvedFromCustomModel($custom);
            $rows[] = array_merge($resolved, [
                'label' => $custom->label,
                'description' => (string) ($custom->description ?? ''),
                'schedule_summary' => $this->humanReadableSchedule($resolved),
                'is_custom' => true,
                'id' => $custom->id,
            ]);
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $resolved
     */
    public function humanReadableSchedule(array $resolved): string
    {
        $labels = config('scheduled_tasks.frequencies', []);
        $freq = $resolved['frequency'] ?? 'hourly';
        $base = $labels[$freq] ?? $freq;

        return match ($freq) {
            'daily_at' => $base . ' — ' . ($resolved['schedule_time'] ?? '?'),
            'weekly' => $base . ' — '
                . ($this->weekdayLabel($resolved['day_of_week'] ?? 0))
                . ' @ ' . ($resolved['schedule_time'] ?? '00:00'),
            'cron' => $base . ': ' . ($resolved['cron_expression'] ?? ''),
            default => $base,
        };
    }

    private function weekdayLabel(int $dow): string
    {
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        return $days[$dow % 7] ?? (string) $dow;
    }

    /**
     * @param  array<string, mixed>  $resolved
     */
    private function applyFrequency(object $event, array $resolved): void
    {
        $freq = $resolved['frequency'] ?? 'hourly';
        $time = $resolved['schedule_time'] ?? '00:00';
        $dow = (int) ($resolved['day_of_week'] ?? 0);
        $cron = $resolved['cron_expression'] ?? '';

        match ($freq) {
            'every_minute' => $event->everyMinute(),
            'every_five_minutes' => $event->everyFiveMinutes(),
            'every_ten_minutes' => $event->everyTenMinutes(),
            'every_fifteen_minutes' => $event->everyFifteenMinutes(),
            'every_thirty_minutes' => $event->everyThirtyMinutes(),
            'hourly' => $event->hourly(),
            'every_two_hours' => $event->everyTwoHours(),
            'daily' => $event->daily(),
            'daily_at' => $event->dailyAt($time),
            'weekly' => $event->weeklyOn($dow, $time ?: '00:00'),
            'cron' => $event->cron($cron ?: '* * * * *'),
            default => throw new InvalidArgumentException("Unknown schedule frequency: {$freq}"),
        };
    }

    private function normalizeTime(string $time): string
    {
        $time = trim($time);
        if (preg_match('/^\d{1,2}:\d{2}$/', $time)) {
            [$h, $m] = explode(':', $time, 2);

            return sprintf('%02d:%02d', (int) $h, (int) $m);
        }

        return $time;
    }

    public function saveFromAdmin(string $key, array $data): void
    {
        $tasks = config('scheduled_tasks.tasks', []);
        if (!isset($tasks[$key])) {
            abort(404);
        }

        $frequency = (string) $data['frequency'];

        $scheduleTime = null;
        if (in_array($frequency, ['daily_at', 'weekly'], true) && !empty($data['schedule_time'])) {
            $scheduleTime = $this->normalizeTime((string) $data['schedule_time']);
        }

        $dow = null;
        if ($frequency === 'weekly') {
            $dow = (int) ($data['day_of_week'] ?? 0);
        }

        $cronExpression = null;
        if ($frequency === 'cron' && !empty($data['cron_expression'])) {
            $cronExpression = trim((string) $data['cron_expression']);
        }

        ScheduledTaskSetting::query()->updateOrCreate(
            ['key' => $key],
            [
                'is_enabled' => (bool) ($data['is_enabled'] ?? false),
                'frequency' => $frequency,
                'schedule_time' => $scheduleTime,
                'day_of_week' => $dow,
                'cron_expression' => $cronExpression,
            ]
        );

        $this->forgetCache($key);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createCustom(array $data): CustomScheduledTask
    {
        return CustomScheduledTask::query()->create($this->normalizeCustomPayload($data));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateCustom(CustomScheduledTask $task, array $data): void
    {
        $task->update($this->normalizeCustomPayload($data));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function normalizeCustomPayload(array $data): array
    {
        $frequency = (string) $data['frequency'];

        $scheduleTime = null;
        if (in_array($frequency, ['daily_at', 'weekly'], true) && !empty($data['schedule_time'])) {
            $scheduleTime = $this->normalizeTime((string) $data['schedule_time']);
        }

        $dow = null;
        if ($frequency === 'weekly') {
            $dow = (int) ($data['day_of_week'] ?? 0);
        }

        $cronExpression = null;
        if ($frequency === 'cron' && !empty($data['cron_expression'])) {
            $cronExpression = trim((string) $data['cron_expression']);
        }

        return [
            'label' => (string) $data['label'],
            'description' => isset($data['description']) ? (string) $data['description'] : null,
            'command' => trim((string) $data['command']),
            'is_enabled' => (bool) ($data['is_enabled'] ?? false),
            'frequency' => $frequency,
            'schedule_time' => $scheduleTime,
            'day_of_week' => $dow,
            'cron_expression' => $cronExpression,
            'without_overlapping' => (bool) ($data['without_overlapping'] ?? false),
            'run_in_background' => (bool) ($data['run_in_background'] ?? false),
            'append_output_to' => !empty($data['append_output_to'])
                ? trim((string) $data['append_output_to'])
                : null,
        ];
    }

    /** @return list<string> */
    public function allowedFrequencyKeys(): array
    {
        return array_keys(config('scheduled_tasks.frequencies', []));
    }
}
