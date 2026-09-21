<?php

namespace App\Services\Queue;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Automates the manual fix for a stalled database queue: clear the stale
 * withoutOverlapping() lock left behind by a killed `queue:work` run, then drain the queue.
 */
class QueueRecoveryService
{
    /** Shared with the scheduler in App\Console\Kernel so both run the worker the same way. */
    public const WORKER_COMMAND = 'queue:work --queue=default --stop-when-empty --tries=3 --max-time=50';

    public function __construct(private readonly Schedule $schedule)
    {
    }

    /**
     * Restart the queue worker. Returns how many stale scheduler locks were cleared.
     */
    public function recover(): int
    {
        $cleared = $this->clearStuckWorkerLocks();

        $this->drainQueue();

        return $cleared;
    }

    /**
     * Forget the overlap mutex of the scheduled queue:work event(s) only — other tasks
     * (including the health monitor that is calling us) keep their locks.
     */
    public function clearStuckWorkerLocks(): int
    {
        $cleared = 0;

        foreach ($this->schedule->events() as $event) {
            if (! str_contains((string) $event->command, 'queue:work') || ! $event->mutex->exists($event)) {
                continue;
            }

            $event->mutex->forget($event);
            $cleared++;
        }

        return $cleared;
    }

    private function drainQueue(): void
    {
        try {
            Artisan::call(self::WORKER_COMMAND);
        } catch (Throwable $e) {
            Log::error('Queue recovery: draining the queue failed: ' . $e->getMessage());
        }
    }
}
