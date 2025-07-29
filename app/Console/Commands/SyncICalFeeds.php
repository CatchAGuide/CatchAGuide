<?php

namespace App\Console\Commands;

use App\Models\ICalFeed;
use App\Services\ICalService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncICalFeeds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ical:sync-feeds 
                            {--user-id= : Sync for specific user ID} 
                            {--feed-id= : Sync specific feed ID}
                            {--force : Force sync even if recently synced}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync iCal feeds for users with active connections';

    protected $icalService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ICalService $icalService)
    {
        parent::__construct();
        $this->icalService = $icalService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userId = $this->option('user-id');
        $feedId = $this->option('feed-id');
        $force = $this->option('force');

        if ($feedId) {
            // Sync specific feed
            $this->syncSpecificFeed($feedId, $force);
        } elseif ($userId) {
            // Sync for specific user
            $this->syncUserFeeds($userId, $force);
        } else {
            // Sync all feeds that need syncing
            $this->syncAllFeeds($force);
        }

        return 0;
    }

    /**
     * Sync a specific feed
     */
    private function syncSpecificFeed($feedId, $force = false)
    {
        $feed = ICalFeed::find($feedId);
        
        if (!$feed) {
            $this->error("Feed with ID {$feedId} not found.");
            return;
        }

        if (!$force && !$feed->needsSync()) {
            $this->info("Feed '{$feed->name}' doesn't need syncing at this time.");
            return;
        }

        $this->info("Syncing feed: {$feed->name}");
        
        try {
            $result = $this->icalService->syncFeed($feed);
            
            if ($result['success']) {
                $this->info("✓ Successfully synced {$result['synced_count']} events from '{$feed->name}'");
            } else {
                $this->error("✗ Failed to sync '{$feed->name}': {$result['error']}");
            }
            
        } catch (\Exception $e) {
            $this->error("✗ Exception syncing '{$feed->name}': {$e->getMessage()}");
            Log::error('iCal feed sync exception', [
                'feed_id' => $feed->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Sync all feeds for a specific user
     */
    private function syncUserFeeds($userId, $force = false)
    {
        $user = \App\Models\User::find($userId);
        
        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return;
        }

        $this->info("Syncing iCal feeds for user: {$user->email}");

        try {
            $results = $this->icalService->syncUserFeeds($user);
            
            $successCount = 0;
            $errorCount = 0;
            $totalEvents = 0;

            foreach ($results as $feedId => $result) {
                if ($result['success']) {
                    $successCount++;
                    $totalEvents += $result['synced_count'] ?? 0;
                    $this->info("✓ Feed {$feedId}: {$result['synced_count']} events synced");
                } else {
                    $errorCount++;
                    $this->error("✗ Feed {$feedId}: {$result['error']}");
                }
            }

            $this->info("Completed: {$successCount} feeds synced successfully, {$errorCount} failed, {$totalEvents} total events");

        } catch (\Exception $e) {
            $this->error("Exception syncing user feeds: {$e->getMessage()}");
            Log::error('iCal user feeds sync exception', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Sync all feeds that need syncing
     */
    private function syncAllFeeds($force = false)
    {
        $query = ICalFeed::where('is_active', true);

        if (!$force) {
            $query->where(function($q) {
                $q->whereNull('last_sync_at')
                  ->orWhere('last_sync_at', '<', now()->subHours(24));
            });
        }

        $feeds = $query->get();

        if ($feeds->isEmpty()) {
            $this->info('No iCal feeds need syncing at this time.');
            return;
        }

        $this->info("Starting iCal sync for {$feeds->count()} feeds...");

        $bar = $this->output->createProgressBar($feeds->count());
        $bar->start();

        $successCount = 0;
        $errorCount = 0;
        $totalEvents = 0;

        foreach ($feeds as $feed) {
            try {
                $result = $this->icalService->syncFeed($feed);
                
                if ($result['success']) {
                    $successCount++;
                    $totalEvents += $result['synced_count'] ?? 0;
                    $this->line("\n✓ '{$feed->name}': {$result['synced_count']} events synced");
                } else {
                    $errorCount++;
                    $this->line("\n✗ '{$feed->name}': {$result['error']}");
                }
                
            } catch (\Exception $e) {
                $errorCount++;
                $this->line("\n✗ '{$feed->name}': Exception - {$e->getMessage()}");
                Log::error('iCal feed sync exception', [
                    'feed_id' => $feed->id,
                    'error' => $e->getMessage()
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("iCal sync completed:");
        $this->info("- Feeds synced successfully: {$successCount}");
        $this->info("- Feeds with errors: {$errorCount}");
        $this->info("- Total events synced: {$totalEvents}");

        if ($errorCount > 0) {
            $this->warn("Some feeds had errors. Check the logs for details.");
        }
    }
}
