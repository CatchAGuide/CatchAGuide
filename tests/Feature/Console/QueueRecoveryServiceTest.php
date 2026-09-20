<?php

namespace Tests\Feature\Console;

use App\Services\Queue\QueueRecoveryService;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;

class QueueRecoveryServiceTest extends TestCase
{
    public function test_recover_clears_only_the_queue_worker_lock(): void
    {
        $worker = $this->scheduledEvent('queue:work');
        $monitor = $this->scheduledEvent('queue:monitor-health');

        $this->assertTrue($worker->mutex->create($worker));
        $this->assertTrue($monitor->mutex->create($monitor));

        $cleared = app(QueueRecoveryService::class)->recover();

        $this->assertSame(1, $cleared);
        $this->assertFalse($worker->mutex->exists($worker), 'Stale queue:work lock should be cleared.');
        $this->assertTrue($monitor->mutex->exists($monitor), 'The health monitor must keep its own lock.');

        $monitor->mutex->forget($monitor);
    }

    public function test_recover_reports_zero_when_no_lock_is_stuck(): void
    {
        $worker = $this->scheduledEvent('queue:work');
        $worker->mutex->forget($worker);

        $this->assertSame(0, app(QueueRecoveryService::class)->recover());
    }

    private function scheduledEvent(string $commandFragment): Event
    {
        foreach (app(Schedule::class)->events() as $event) {
            if (str_contains((string) $event->command, $commandFragment)) {
                return $event;
            }
        }

        $this->fail("No scheduled event found for [{$commandFragment}].");
    }
}
