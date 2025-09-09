<?php

namespace App\Http\Controllers;

use App\Models\ICalFeed;
use App\Services\ICalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ICalFeedController extends Controller
{
    protected $icalService;

    public function __construct(ICalService $icalService)
    {
        $this->icalService = $icalService;
    }

    /**
     * Show a specific iCal feed
     */
    public function show(ICalFeed $feed)
    {
        // Ensure user owns this feed
        if ($feed->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            return response()->json([
                'success' => true,
                'feed' => $feed
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get iCal feed', [
                'feed_id' => $feed->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get iCal feed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new iCal feed
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'feed_url' => 'required|url|max:1000',
            'sync_type' => 'required|in:bookings_only,all_events',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Validate the feed URL
            $validation = $this->icalService->validateFeedUrl($request->feed_url);
            
            if (!$validation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid iCal feed URL: ' . $validation['error']
                ], 422);
            }

            // Check if user already has this feed URL
            $existingFeed = ICalFeed::where('user_id', Auth::id())
                ->where('feed_url', $request->feed_url)
                ->first();

            if ($existingFeed) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have this iCal feed configured'
                ], 422);
            }

            // Create the feed
            $feed = ICalFeed::create([
                'user_id' => Auth::id(),
                'name' => $request->name,
                'feed_url' => $request->feed_url,
                'sync_type' => $request->sync_type,
                'is_active' => true,
            ]);

            // Perform initial sync
            $syncResult = $this->icalService->syncFeed($feed);

            return response()->json([
                'success' => true,
                'message' => 'iCal feed added successfully',
                'feed' => $feed,
                'sync_result' => $syncResult
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create iCal feed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create iCal feed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an iCal feed
     */
    public function update(Request $request, ICalFeed $feed)
    {
        // Ensure user owns this feed
        if ($feed->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'feed_url' => 'sometimes|required|url|max:1000',
            'sync_type' => 'sometimes|required|in:bookings_only,all_events',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // If URL is being updated, validate it
            if ($request->has('feed_url') && $request->feed_url !== $feed->feed_url) {
                $validation = $this->icalService->validateFeedUrl($request->feed_url);
                
                if (!$validation['valid']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid iCal feed URL: ' . $validation['error']
                    ], 422);
                }

                // Check if user already has this feed URL
                $existingFeed = ICalFeed::where('user_id', Auth::id())
                    ->where('feed_url', $request->feed_url)
                    ->where('id', '!=', $feed->id)
                    ->first();

                if ($existingFeed) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You already have this iCal feed configured'
                    ], 422);
                }
            }

            $feed->update($request->only(['name', 'feed_url', 'sync_type', 'is_active']));

            return response()->json([
                'success' => true,
                'message' => 'iCal feed updated successfully',
                'feed' => $feed->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update iCal feed', [
                'feed_id' => $feed->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update iCal feed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an iCal feed
     */
    public function destroy(ICalFeed $feed)
    {
        // Ensure user owns this feed
        if ($feed->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $feed->delete();

            return response()->json([
                'success' => true,
                'message' => 'iCal feed deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete iCal feed', [
                'feed_id' => $feed->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete iCal feed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync a specific iCal feed
     */
    public function sync(ICalFeed $feed)
    {
        // Ensure user owns this feed
        if ($feed->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $result = $this->icalService->syncFeed($feed);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'] ?? ($result['success'] ? 'Sync completed' : 'Sync failed'),
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to sync iCal feed', [
                'feed_id' => $feed->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to sync iCal feed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync all user's iCal feeds
     */
    public function syncAll()
    {
        try {
            $results = $this->icalService->syncUserFeeds(Auth::user());

            $successCount = 0;
            $errorCount = 0;
            $totalEvents = 0;

            foreach ($results as $feedId => $result) {
                if ($result['success']) {
                    $successCount++;
                    $totalEvents += $result['events_count'] ?? 0;
                } else {
                    $errorCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Synced {$successCount} feeds successfully, {$errorCount} failed",
                'data' => [
                    'feeds_synced' => $successCount,
                    'feeds_failed' => $errorCount,
                    'total_events' => $totalEvents,
                    'results' => $results
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to sync all iCal feeds', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to sync iCal feeds: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync all iCal feeds using the SyncICalFeeds command
     */
    public function syncAllCommand(Request $request)
    {
        try {
            $userId = Auth::id();
            $force = $request->input('force', false);
            $noCleanup = $request->input('no_cleanup', false);

            // Run the SyncICalFeeds command for the current user
            $command = \Artisan::call('ical:sync-feeds', [
                '--user-id' => $userId,
                '--force' => $force,
                '--no-cleanup' => $noCleanup
            ]);

            // Get the command output
            $output = \Artisan::output();

            // Parse the output to extract results
            $successCount = 0;
            $errorCount = 0;
            $totalEvents = 0;

            // Extract numbers from the output using regex
            if (preg_match('/(\d+) feeds synced successfully/', $output, $matches)) {
                $successCount = (int) $matches[1];
            }
            if (preg_match('/(\d+) failed/', $output, $matches)) {
                $errorCount = (int) $matches[1];
            }
            if (preg_match('/(\d+) total events synced/', $output, $matches)) {
                $totalEvents = (int) $matches[1];
            }

            return response()->json([
                'success' => true,
                'message' => "Synced {$successCount} feeds successfully, {$errorCount} failed, {$totalEvents} total events",
                'data' => [
                    'feeds_synced' => $successCount,
                    'feeds_failed' => $errorCount,
                    'total_events' => $totalEvents,
                    'command_output' => $output
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to sync all iCal feeds via command', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to sync iCal feeds: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's iCal feeds
     */
    public function index()
    {
        try {
            $feeds = Auth::user()->icalFeeds()->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'feeds' => $feeds
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get iCal feeds', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get iCal feeds: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate an iCal feed URL
     */
    public function validateUrl(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->icalService->validateFeedUrl($request->url);

            return response()->json([
                'success' => true,
                'valid' => $result['valid'],
                'message' => $result['message'] ?? $result['error']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'Failed to validate URL: ' . $e->getMessage()
            ], 500);
        }
    }
}
