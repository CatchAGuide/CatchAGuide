<?php

namespace App\Console\Commands;

use App\Models\OAuthToken;
use App\Services\CalendlyService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncCalendlyEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calendly:sync-events {--user-id= : Sync for specific user ID} {--force : Force sync even if recently synced}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Calendly events for users with active connections';

    protected $calendlyService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(CalendlyService $calendlyService)
    {
        parent::__construct();
        $this->calendlyService = $calendlyService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userId = $this->option('user-id');
        $force = $this->option('force');

        if ($userId) {
            // Sync for specific user
            $this->syncUserEvents($userId, $force);
        } else {
            // Sync for all users with active Calendly connections
            $this->syncAllUsers($force);
        }

        return 0;
    }

    /**
     * Sync events for all users with active Calendly connections
     */
    private function syncAllUsers($force = false)
    {
        $query = OAuthToken::where('type', 'calendly')
                          ->where('status', 'active');

        if (!$force) {
            // Only sync if not synced in the last hour
            $query->where(function($q) {
                $q->whereNull('last_sync_at')
                  ->orWhere('last_sync_at', '<', now()->subHour());
            });
        }

        $tokens = $query->get();

        if ($tokens->isEmpty()) {
            $this->info('No users need Calendly sync at this time.');
            return;
        }

        $this->info("Starting Calendly sync for {$tokens->count()} users...");

        $bar = $this->output->createProgressBar($tokens->count());
        $bar->start();

        $successCount = 0;
        $errorCount = 0;

        foreach ($tokens as $token) {
            try {
                $events = $this->calendlyService->syncUserEvents($token->user_id);
                
                if ($events !== false) {
                    $successCount++;
                    $this->line("\nSynced " . count($events['collection'] ?? []) . " events for user {$token->user_id}");
                } else {
                    $errorCount++;
                    $this->line("\nFailed to sync events for user {$token->user_id}");
                }
            } catch (\Exception $e) {
                $errorCount++;
                Log::error('Calendly sync error for user ' . $token->user_id, [
                    'error' => $e->getMessage()
                ]);
                $this->line("\nError syncing events for user {$token->user_id}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Sync completed: {$successCount} successful, {$errorCount} failed");
    }

    /**
     * Sync events for a specific user
     */
    private function syncUserEvents($userId, $force = false)
    {
        $token = OAuthToken::where('user_id', $userId)
                          ->where('type', 'calendly')
                          ->where('status', 'active')
                          ->first();

        if (!$token) {
            $this->error("No active Calendly connection found for user {$userId}");
            return;
        }

        if (!$force && $token->last_sync_at && $token->last_sync_at->isAfter(now()->subHour())) {
            $this->warn("User {$userId} was synced recently. Use --force to sync anyway.");
            return;
        }

        $this->info("Syncing Calendly events for user {$userId}...");

        try {
            $events = $this->calendlyService->syncUserEvents($userId);
            
            if ($events !== false) {
                $eventCount = count($events['collection'] ?? []);
                $this->info("Successfully synced {$eventCount} events for user {$userId}");
            } else {
                $this->error("Failed to sync events for user {$userId}");
            }
        } catch (\Exception $e) {
            Log::error('Calendly sync error for user ' . $userId, [
                'error' => $e->getMessage()
            ]);
            $this->error("Error syncing events for user {$userId}: " . $e->getMessage());
        }
    }
}
