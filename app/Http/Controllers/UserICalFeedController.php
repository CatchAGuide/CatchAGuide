<?php

namespace App\Http\Controllers;

use App\Models\UserICalFeed;
use App\Services\ICalGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;

class UserICalFeedController extends Controller
{
    protected $icalGeneratorService;

    public function __construct(ICalGeneratorService $icalGeneratorService)
    {
        $this->icalGeneratorService = $icalGeneratorService;
    }

    /**
     * Generate iCal feed content (public access with OTP)
     */
    public function generateFeed($token, $otp = null)
    {
        $feed = UserICalFeed::findByToken($token);
        
        if (!$feed) {
            return response('Feed not found', 404);
        }

        if (!$feed->isAccessible()) {
            return response('Feed is not accessible', 403);
        }

        // Verify OTP if provided
        if ($otp && !$feed->verifyOTP($otp)) {
            return response('Invalid OTP', 401);
        }

        try {
            // Update access statistics
            $feed->updateAccessStats();

            // Generate iCal content
            $icalContent = $this->icalGeneratorService->generateICalContent($feed);

            // Return iCal response
            return Response::make($icalContent, 200, [
                'Content-Type' => 'text/calendar; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="' . $feed->name . '.ics"',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to generate iCal feed', [
                'feed_id' => $feed->id,
                'token' => $token,
                'error' => $e->getMessage()
            ]);

            return response('Failed to generate feed', 500);
        }
    }

    /**
     * Show a specific user iCal feed
     */
    public function show(UserICalFeed $feed)
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
                'feed' => [
                    'id' => $feed->id,
                    'name' => $feed->name,
                    'feed_type' => $feed->feed_type,
                    'feed_type_display' => $feed->feed_type_display,
                    'is_active' => $feed->is_active,
                    'feed_url' => $feed->getFeedUrl(),
                    'secure_feed_url' => $feed->getSecureFeedUrl(),
                    'current_otp' => $feed->generateOTP(),
                    'last_accessed_at' => $feed->last_accessed_at,
                    'access_count' => $feed->access_count,
                    'expires_at' => $feed->expires_at,
                    'created_at' => $feed->created_at,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get user iCal feed', [
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
     * Create a new user iCal feed
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'feed_type' => 'required|in:bookings_only,all_events,custom_schedule',
            'expires_at' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $feed = $this->icalGeneratorService->createUserFeed(Auth::user(), $request->all());

            return response()->json([
                'success' => true,
                'message' => 'iCal feed created successfully',
                'feed' => $feed,
                'feed_url' => $feed->getFeedUrl(),
                'secure_feed_url' => $feed->getSecureFeedUrl(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create user iCal feed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create iCal feed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's iCal feeds
     */
    public function index()
    {
        try {
            $feeds = Auth::user()->userIcalFeeds()->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'feeds' => $feeds->map(function($feed) {
                    return [
                        'id' => $feed->id,
                        'name' => $feed->name,
                        'feed_type' => $feed->feed_type,
                        'feed_type_display' => $feed->feed_type_display,
                        'is_active' => $feed->is_active,
                        'feed_url' => $feed->getFeedUrl(),
                        'secure_feed_url' => $feed->getSecureFeedUrl(),
                        'current_otp' => $feed->generateOTP(),
                        'last_accessed_at' => $feed->last_accessed_at,
                        'access_count' => $feed->access_count,
                        'expires_at' => $feed->expires_at,
                        'created_at' => $feed->created_at,
                    ];
                })
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get user iCal feeds', [
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
     * Update user iCal feed
     */
    public function update(Request $request, UserICalFeed $feed)
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
            'feed_type' => 'sometimes|required|in:bookings_only,all_events,custom_schedule',
            'is_active' => 'sometimes|boolean',
            'expires_at' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $feed->update($request->only(['name', 'feed_type', 'is_active', 'expires_at']));

            return response()->json([
                'success' => true,
                'message' => 'iCal feed updated successfully',
                'feed' => $feed->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update user iCal feed', [
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
     * Delete user iCal feed
     */
    public function destroy(UserICalFeed $feed)
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
            Log::error('Failed to delete user iCal feed', [
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
     * Regenerate feed token
     */
    public function regenerateToken(UserICalFeed $feed)
    {
        // Ensure user owns this feed
        if ($feed->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $this->icalGeneratorService->regenerateFeedToken($feed);

            return response()->json([
                'success' => true,
                'message' => 'Feed token regenerated successfully',
                'feed_url' => $feed->fresh()->getFeedUrl(),
                'secure_feed_url' => $feed->fresh()->getSecureFeedUrl(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to regenerate feed token', [
                'feed_id' => $feed->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to regenerate token: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get feed content (same as generateFeed but for authenticated users)
     */
    public function getFeed(UserICalFeed $feed)
    {
        // Ensure user owns this feed
        if ($feed->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if (!$feed->isAccessible()) {
            return response()->json([
                'success' => false,
                'message' => 'Feed is not accessible'
            ], 403);
        }

        try {
            // Update access statistics
            $feed->updateAccessStats();

            // Generate iCal content
            $icalContent = $this->icalGeneratorService->generateICalContent($feed);

            // Return iCal response
            return Response::make($icalContent, 200, [
                'Content-Type' => 'text/calendar; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="' . $feed->name . '.ics"',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get iCal feed content', [
                'feed_id' => $feed->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate feed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current OTP for a feed
     */
    public function getCurrentOTP(UserICalFeed $feed)
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
                'otp' => $feed->generateOTP(),
                'expires_in' => 30 - (time() % 30), // Seconds until next OTP
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate OTP: ' . $e->getMessage()
            ], 500);
        }
    }
} 