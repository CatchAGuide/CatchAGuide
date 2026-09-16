<?php

namespace App\Console\Commands;

use App\Mail\QueueStalledAlertMail;
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

    protected $description = 'Alert admins when queued jobs (e.g. booking confirmation emails) are stuck longer than expected';

    private const CACHE_KEY = 'queue_health_alert_last_sent';

    public function handle(): int
    {
        $staleMinutes = (int) $this->option('stale-minutes');
        $cooldownMinutes = (int) $this->option('cooldown-minutes');

        $cutoff = now()->subMinutes($staleMinutes)->getTimestamp();

        $staleCount = DB::table('jobs')->where('created_at', '<', $cutoff)->count();

        if ($staleCount === 0) {
            return 0;
        }

        $oldestTimestamp = DB::table('jobs')->min('created_at');
        $oldestAgeMinutes = now()->diffInMinutes(Carbon::createFromTimestamp($oldestTimestamp), absolute: true);

        Log::critical("Queue health check: {$staleCount} job(s) stuck for over {$staleMinutes} minutes (oldest: {$oldestAgeMinutes} min).");

        if (Cache::has(self::CACHE_KEY)) {
            $this->warn("Stale jobs detected ({$staleCount}) but an alert was already sent within the last {$cooldownMinutes} minutes.");
            return 0;
        }

        // Sent synchronously (not ->queue()) on purpose: if the queue itself is what's
        // stuck, a queued alert mail would get stuck right alongside it.
        Mail::to(config('mail.admin_email'))
            ->send(new QueueStalledAlertMail($staleCount, $oldestAgeMinutes, $staleMinutes));

        Cache::put(self::CACHE_KEY, true, now()->addMinutes($cooldownMinutes));

        $this->error("Alert sent: {$staleCount} stale job(s), oldest {$oldestAgeMinutes} minutes old.");

        return 0;
    }
}
