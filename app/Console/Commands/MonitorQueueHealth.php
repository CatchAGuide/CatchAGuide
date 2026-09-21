<?php

namespace App\Console\Commands;

use App\Mail\QueueStalledAlertMail;
use App\Services\Queue\QueueRecoveryService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MonitorQueueHealth extends Command
{
    protected $signature = 'queue:monitor-health
        {--stale-minutes=15 : Age in minutes before a pending job is considered stuck}
        {--cooldown-minutes=60 : Minimum time between repeat alerts while the queue stays stuck}';

    protected $description = 'Restart a stalled queue automatically, and alert admins only if queued jobs (e.g. booking confirmation emails) are still stuck afterwards';

    private const CACHE_KEY = 'queue_health_alert_last_sent';

    public function handle(QueueRecoveryService $recovery): int
    {
        $staleMinutes = (int) $this->option('stale-minutes');
        $cooldownMinutes = (int) $this->option('cooldown-minutes');

        $cutoff = now()->subMinutes($staleMinutes)->getTimestamp();

        $staleCount = $this->staleJobCount($cutoff);

        if ($staleCount === 0) {
            return 0;
        }

        Log::warning("Queue health check: {$staleCount} job(s) stuck for over {$staleMinutes} minutes — restarting the queue worker.");

        $locksCleared = $recovery->recover();

        $staleCount = $this->staleJobCount($cutoff);

        if ($staleCount === 0) {
            Log::warning("Queue health check: queue recovered automatically (cleared {$locksCleared} stale scheduler lock(s)).");
            $this->info("Stale jobs detected; queue restarted automatically (cleared {$locksCleared} stale lock(s)).");
            return 0;
        }

        $oldestTimestamp = DB::table('jobs')->min('created_at');
        $oldestAgeMinutes = now()->diffInMinutes(Carbon::createFromTimestamp($oldestTimestamp), absolute: true);

        Log::critical("Queue health check: {$staleCount} job(s) still stuck after an automatic restart (oldest: {$oldestAgeMinutes} min).");

        if (Cache::has(self::CACHE_KEY)) {
            $this->warn("Stale jobs remain ({$staleCount}) but an alert was already sent within the last {$cooldownMinutes} minutes.");
            return 0;
        }

        // Sent synchronously (not ->queue()) on purpose: if the queue itself is what's
        // stuck, a queued alert mail would get stuck right alongside it.
        Mail::to(config('mail.admin_email'))
            ->send(new QueueStalledAlertMail($staleCount, $oldestAgeMinutes, $staleMinutes, $locksCleared));

        Cache::put(self::CACHE_KEY, true, now()->addMinutes($cooldownMinutes));

        $this->error("Alert sent: {$staleCount} stale job(s), oldest {$oldestAgeMinutes} minutes old.");

        return 0;
    }

    private function staleJobCount(int $cutoff): int
    {
        return DB::table('jobs')->where('created_at', '<', $cutoff)->count();
    }
}
