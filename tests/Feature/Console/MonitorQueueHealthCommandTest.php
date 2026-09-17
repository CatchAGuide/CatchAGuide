<?php

namespace Tests\Feature\Console;

use App\Mail\QueueStalledAlertMail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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

        DB::table('jobs')->insert($this->job(now()->subMinutes(2)->getTimestamp()));

        $this->artisan('queue:monitor-health', ['--stale-minutes' => 15])
            ->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_alert_sent_when_jobs_are_stale(): void
    {
        Mail::fake();

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

        DB::table('jobs')->insert($this->job(now()->subMinutes(30)->getTimestamp()));

        $this->artisan('queue:monitor-health', ['--stale-minutes' => 15, '--cooldown-minutes' => 60])
            ->assertSuccessful();

        $this->artisan('queue:monitor-health', ['--stale-minutes' => 15, '--cooldown-minutes' => 60])
            ->assertSuccessful();

        Mail::assertSent(QueueStalledAlertMail::class, 1);
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
