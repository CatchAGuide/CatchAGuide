<?php

namespace App\Services;

use App\Models\OAuthToken;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CalendlyService extends OAuthService
{
    private const CALENDLY_API_BASE = 'https://api.calendly.com';
    private const CALENDLY_OAUTH_BASE = 'https://auth.calendly.com';
    
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    
    public function __construct()
    {
        parent::__construct('calendly');
        $this->clientId = config('services.calendly.client_id');
        $this->clientSecret = config('services.calendly.client_secret');
        $this->redirectUri = config('services.calendly.redirect_uri');
    }
    
    /**
     * Get OAuth authorization URL
     */
    public function getAuthorizationUrl($state = null)
    {
        $params = [
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'redirect_uri' => $this->redirectUri,
        ];
        
        if ($state) {
            $params['state'] = $state;
        }
        
        return self::CALENDLY_OAUTH_BASE . '/oauth/authorize?' . http_build_query($params);
    }
    
    /**
     * Exchange authorization code for access token
     */
    public function exchangeCodeForToken($code)
    {
        try {
            $response = Http::post(self::CALENDLY_OAUTH_BASE . '/oauth/token', [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $this->redirectUri,
            ]);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            Log::error('Calendly OAuth token exchange failed', [
                'response' => $response->json(),
                'status' => $response->status()
            ]);
            
            return null;
        } catch (\Exception $e) {
            Log::error('Calendly OAuth token exchange exception', [
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Refresh access token
     */
    public function refreshToken($refreshToken)
    {
        try {
            $response = Http::post(self::CALENDLY_OAUTH_BASE . '/oauth/token', [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
            ]);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            Log::error('Calendly token refresh failed', [
                'response' => $response->json(),
                'status' => $response->status()
            ]);
            
            return null;
        } catch (\Exception $e) {
            Log::error('Calendly token refresh exception', [
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Get user's Calendly profile
     */
    public function getUserProfile($accessToken)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
            ])->get(self::CALENDLY_API_BASE . '/users/me');
            
            if ($response->successful()) {
                return $response->json();
            }
            
            Log::error('Calendly user profile fetch failed', [
                'response' => $response->json(),
                'status' => $response->status()
            ]);
            
            return null;
        } catch (\Exception $e) {
            Log::error('Calendly user profile fetch exception', [
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Get user's scheduled events
     */
    public function getScheduledEvents($accessToken, $userUri, $params = [])
    {
        try {
            $defaultParams = [
                'user' => $userUri,
                'status' => 'active',
                'count' => 100,
            ];
            
            $queryParams = array_merge($defaultParams, $params);
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
            ])->get(self::CALENDLY_API_BASE . '/scheduled_events?' . http_build_query($queryParams));
            
            if ($response->successful()) {
                return $response->json();
            }
            
            Log::error('Calendly scheduled events fetch failed', [
                'response' => $response->json(),
                'status' => $response->status()
            ]);
            
            return null;
        } catch (\Exception $e) {
            Log::error('Calendly scheduled events fetch exception', [
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Create a Calendly event
     */
    public function createEvent($accessToken, $eventData)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post(self::CALENDLY_API_BASE . '/scheduling_links', $eventData);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            Log::error('Calendly event creation failed', [
                'response' => $response->json(),
                'status' => $response->status(),
                'event_data' => $eventData
            ]);
            
            return null;
        } catch (\Exception $e) {
            Log::error('Calendly event creation exception', [
                'message' => $e->getMessage(),
                'event_data' => $eventData
            ]);
            return null;
        }
    }
    
    /**
     * Generate ICS file content for a booking
     */
    public function generateICSContent($booking, $guiding)
    {
        $startTime = Carbon::parse($booking->book_date);
        $endTime = $startTime->copy()->addHours($guiding->duration ?? 4);
        
        $icsContent = "BEGIN:VCALENDAR\r\n";
        $icsContent .= "VERSION:2.0\r\n";
        $icsContent .= "PRODID:-//CatchAGuide//Booking//EN\r\n";
        $icsContent .= "CALSCALE:GREGORIAN\r\n";
        $icsContent .= "METHOD:PUBLISH\r\n";
        $icsContent .= "BEGIN:VEVENT\r\n";
        $icsContent .= "UID:" . $booking->id . "@catchaguide.com\r\n";
        $icsContent .= "DTSTART:" . $startTime->format('Ymd\THis\Z') . "\r\n";
        $icsContent .= "DTEND:" . $endTime->format('Ymd\THis\Z') . "\r\n";
        $icsContent .= "DTSTAMP:" . now()->format('Ymd\THis\Z') . "\r\n";
        $icsContent .= "SUMMARY:Fishing Trip with " . $guiding->user->firstname . " " . $guiding->user->lastname . "\r\n";
        $icsContent .= "DESCRIPTION:Fishing trip booking confirmed. Location: " . ($guiding->location ?? 'TBD') . "\r\n";
        $icsContent .= "LOCATION:" . ($guiding->location ?? 'TBD') . "\r\n";
        $icsContent .= "ORGANIZER;CN=" . $guiding->user->firstname . " " . $guiding->user->lastname . ":mailto:" . $guiding->user->email . "\r\n";
        $icsContent .= "ATTENDEE;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;CN=" . $booking->user->firstname . " " . $booking->user->lastname . ":mailto:" . $booking->email . "\r\n";
        $icsContent .= "STATUS:CONFIRMED\r\n";
        $icsContent .= "SEQUENCE:0\r\n";
        $icsContent .= "END:VEVENT\r\n";
        $icsContent .= "END:VCALENDAR\r\n";
        
        return $icsContent;
    }
    

    
    /**
     * Sync user's Calendly events to local calendar
     */
    public function syncUserEvents($userId)
    {
        $token = OAuthToken::getTokenForUser($userId, 'calendly');
        
        if (!$token || !$token->isActive()) {
            return false;
        }
        
        $user = User::find($userId);
        if (!$user) {
            return false;
        }
        
        $profile = $this->getUserProfile($token->access_token);
        if (!$profile) {
            return false;
        }
        
        $events = $this->getScheduledEvents($token->access_token, $profile['resource']['uri']);
        if (!$events) {
            return false;
        }
        
        // Update last sync timestamp
        $token->updateLastSync();
        
        return $events;
    }
} 