<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Services\CalendlyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OAuthController extends Controller
{
    protected $calendlyService;
    
    public function __construct(CalendlyService $calendlyService)
    {
        $this->calendlyService = $calendlyService;
    }
    
    /**
     * Redirect to Calendly OAuth
     */
    public function calendly()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to connect your Calendly account.');
        }
        
        $state = Str::random(40);
        session(['oauth_state' => $state]);
        
        $authUrl = $this->calendlyService->getAuthorizationUrl($state);
        
        return redirect($authUrl);
    }
    
    /**
     * Handle Calendly OAuth callback
     */
    public function calendlyCallback(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to complete the OAuth process.');
        }
        
        // Verify state parameter to prevent CSRF attacks
        $state = session('oauth_state');
        if (!$state || $state !== $request->get('state')) {
            Log::warning('OAuth state mismatch', [
                'user_id' => Auth::id(),
                'expected_state' => $state,
                'received_state' => $request->get('state')
            ]);
            return redirect()->route('profile.account')->with('error', 'OAuth authentication failed. Please try again.');
        }
        
        // Clear the state from session
        session()->forget('oauth_state');
        
        $code = $request->get('code');
        if (!$code) {
            Log::error('No authorization code received in Calendly callback', [
                'user_id' => Auth::id(),
                'error' => $request->get('error'),
                'error_description' => $request->get('error_description')
            ]);
            return redirect()->route('profile.account')->with('error', 'OAuth authentication failed. Please try again.');
        }
        
        try {
            // Exchange code for token
            $tokenData = $this->calendlyService->exchangeCodeForToken($code);
            if (!$tokenData) {
                return redirect()->route('profile.account')->with('error', 'Failed to authenticate with Calendly. Please try again.');
            }
            
            // Get user profile from Calendly
            $profile = $this->calendlyService->getUserProfile($tokenData['access_token']);
            if (!$profile) {
                return redirect()->route('profile.account')->with('error', 'Failed to retrieve your Calendly profile. Please try again.');
            }
            
            // Store the token
            $token = $this->calendlyService->storeToken(
                Auth::id(),
                $tokenData,
                [
                    'provider_user_id' => $profile['resource']['uri'] ?? null,
                    'email' => $profile['resource']['email'] ?? null,
                    'name' => $profile['resource']['name'] ?? null,
                    'slug' => $profile['resource']['slug'] ?? null,
                ]
            );
            
            if (!$token) {
                return redirect()->route('profile.account')->with('error', 'Failed to save your Calendly connection. Please try again.');
            }
            
            Log::info('Calendly OAuth successful', [
                'user_id' => Auth::id(),
                'calendly_email' => $profile['resource']['email'] ?? null
            ]);
            
            return redirect()->route('profile.account')->with('success', 'Your Calendly account has been successfully connected!');
            
        } catch (\Exception $e) {
            Log::error('Calendly OAuth callback exception', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('profile.account')->with('error', 'An error occurred while connecting your Calendly account. Please try again.');
        }
    }
    
    /**
     * Disconnect Calendly account
     */
    public function disconnectCalendly()
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Not authenticated']);
        }
        
        try {
            $success = $this->calendlyService->revokeToken(Auth::id());
            
            if ($success) {
                Log::info('Calendly account disconnected', ['user_id' => Auth::id()]);
                return response()->json(['success' => true, 'message' => 'Calendly account disconnected successfully']);
            } else {
                return response()->json(['success' => false, 'message' => 'No active Calendly connection found']);
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to disconnect Calendly account', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['success' => false, 'message' => 'Failed to disconnect Calendly account']);
        }
    }
    
    /**
     * Sync Calendly events
     */
    public function syncCalendly()
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Not authenticated']);
        }
        
        try {
            $events = $this->calendlyService->syncUserEvents(Auth::id());
            
            if ($events !== false) {
                return response()->json([
                    'success' => true, 
                    'message' => 'Calendly events synced successfully',
                    'event_count' => count($events['collection'] ?? [])
                ]);
            } else {
                return response()->json(['success' => false, 'message' => 'No active Calendly connection or sync failed']);
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to sync Calendly events', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['success' => false, 'message' => 'Failed to sync Calendly events']);
        }
    }
}
