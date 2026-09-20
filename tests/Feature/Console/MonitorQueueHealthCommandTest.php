<?php

namespace Tests\Feature\Console;

use App\Mail\QueueStalledAlertMail;
use App\Services\Queue\QueueRecoveryService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Mockery\MockInterface;
use Tests\TestCase;

class MonitorQueueHealthCommandTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // DELETE, not truncate(): truncate is DDL on MySQL and auto-commits, which would
        // break the DatabaseTransactions rollback and permanently wipe this table.
        DB::table('jobs')->delete();
        Cache::forget('queue_health_alert_last_sent');
    }

    public function test_no_alert_when_no_stale_jobs(): void
    {
        Mail::fake();
        $this->recoveryDoes(fn () => $this->fail('Recovery should not run when nothing is stale.'));

        DB::table('jobs')->insert($this->job(now()->subMinutes(2)->getTimestamp()));

        $this->artisan('queue:monitor-health', ['--stale-minutes' => 15])
            ->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_queue_is_restarted_automatically_and_no_alert_sent_when_recovery_works(): void
    {
        Mail::fake();
        $this->recoveryDoes(fn () => DB::table('jobs')->delete());

        DB::table('jobs')->insert($this->job(now()->subMinutes(30)->getTimestamp()));

        $this->artisan('queue:monitor-health', ['--stale-minutes' => 15])
            ->assertSuccessful();

        Mail::assertNothingSent();
        $this->assertSame(0, DB::table('jobs')->count());
    }

    public function test_alert_sent_when_jobs_are_still_stale_after_recovery(): void
    {
        Mail::fake();
        $this->recoveryDoes(fn () => null, locksCleared: 1);

        DB::table('jobs')->insert($this->job(now()->subMinutes(30)->getTimestamp()));

        $this->artisan('queue:monitor-health', ['--stale-minutes' => 15])
            ->assertSuccessful();

        Mail::assertSent(QueueStalledAlertMail::class, fn (QueueStalledAlertMail $mail) => $mail->locksCleared === 1);
    }

    public function test_alert_sent_when_jobs_are_stale(): void
    {
        Mail::fake();
        $this->recoveryDoes(fn () => null);

        DB::table('jobs')->insert($this->job(now()->subMinutes(30)->getTimestamp()));

        $this->artisan('queue:monitor-health', ['--stale-minutes' => 15])
            ->assertSuccessful();

        Mail::assertSent(QueueStalledAlertMail::class, function (QueueStalledAlertMail $mail) {
            return $mail->staleCount === 1 && $mail->oldestAgeMinutes >= 30;
        });
    }

    public function test_repeat_alert_is_suppressed_during_cooldown(): void
    {
        Mail::fake();
        $this->recoveryDoes(fn () => null);

        DB::table('jobs')->insert($this->job(now()->subMinutes(30)->getTimestamp()));

        $this->artisan('queue:monitor-health', ['--stale-minutes' => 15, '--cooldown-minutes' => 60])
            ->assertSuccessful();

        $this->artisan('queue:monitor-health', ['--stale-minutes' => 15, '--cooldown-minutes' => 60])
            ->assertSuccessful();

        Mail::assertSent(QueueStalledAlertMail::class, 1);
    }

    /**
     * Stand in for the real recovery (which would run an actual queue worker).
     */
    private function recoveryDoes(callable $effect, int $locksCleared = 0): void
    {
        $this->mock(QueueRecoveryService::class, function (MockInterface $mock) use ($effect, $locksCleared) {
            $mock->shouldReceive('recover')->andReturnUsing(function () use ($effect, $locksCleared) {
                $effect();

                return $locksCleared;
            });
        });
    }

    private function job(int $createdAt): array
    {
        return [
            'queue' => 'default',
            'payload' => json_encode(['displayName' => 'App\\Jobs\\SendCheckoutEmail']),
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => $createdAt,
            'created_at' => $createdAt,
        ];
    }
}
