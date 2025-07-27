<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\CalendlyService;
use App\Models\OAuthToken;
use App\Models\CalendarSchedule;
use Carbon\Carbon;

class WebhookController extends Controller
{
    protected $calendlyService;
    
    public function __construct(CalendlyService $calendlyService)
    {
        $this->calendlyService = $calendlyService;
    }
    
    /**
     * Handle Calendly webhooks
     */
    public function calendly(Request $request)
    {
        try {
            // Verify webhook signature
            if (!$this->verifyWebhookSignature($request)) {
                Log::warning('Invalid Calendly webhook signature');
                return response()->json(['error' => 'Invalid signature'], 401);
            }
            
            $payload = $request->all();
            $eventType = $payload['event'] ?? null;
            
            Log::info('Calendly webhook received', [
                'event_type' => $eventType,
                'payload' => $payload
            ]);
            
            switch ($eventType) {
                case 'invitee.created':
                    $this->handleInviteeCreated($payload);
                    break;
                case 'invitee.canceled':
                    $this->handleInviteeCanceled($payload);
                    break;
                case 'invitee.updated':
                    $this->handleInviteeUpdated($payload);
                    break;
                default:
                    Log::info('Unhandled Calendly webhook event', ['event_type' => $eventType]);
            }
            
            return response()->json(['status' => 'success']);
            
        } catch (\Exception $e) {
            Log::error('Calendly webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
    
    /**
     * Verify webhook signature
     */
    private function verifyWebhookSignature(Request $request)
    {
        $signature = $request->header('Calendly-Webhook-Signature');
        $payload = $request->getContent();
        $secret = config('services.calendly.webhook_signing_key');
        
        if (!$signature || !$secret) {
            return false;
        }
        
        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expectedSignature, $signature);
    }
    
    /**
     * Handle invitee created event
     */
    private function handleInviteeCreated($payload)
    {
        $invitee = $payload['payload']['invitee'] ?? null;
        $event = $payload['payload']['event'] ?? null;
        
        if (!$invitee || !$event) {
            return;
        }
        
        // Find user by Calendly URI
        $token = OAuthToken::where('provider_data->provider_user_id', $event['uri'])
                          ->where('type', 'calendly')
                          ->where('status', 'active')
                          ->first();
        
        if (!$token) {
            Log::warning('No active Calendly connection found for event', [
                'event_uri' => $event['uri']
            ]);
            return;
        }
        
        // Create calendar schedule entry
        $startTime = Carbon::parse($invitee['start_time']);
        $endTime = Carbon::parse($invitee['end_time']);
        
        CalendarSchedule::create([
            'type' => 'calendly_event',
            'date' => $startTime->format('Y-m-d'),
            'note' => "Calendly: {$invitee['name']} - {$event['name']}",
            'user_id' => $token->user_id,
            'guiding_id' => null,
            'vacation_id' => null,
            'booking_id' => null,
        ]);
        
        Log::info('Calendly event synced to calendar', [
            'user_id' => $token->user_id,
            'event_name' => $event['name'],
            'invitee_name' => $invitee['name']
        ]);
    }
    
    /**
     * Handle invitee canceled event
     */
    private function handleInviteeCanceled($payload)
    {
        $invitee = $payload['payload']['invitee'] ?? null;
        $event = $payload['payload']['event'] ?? null;
        
        if (!$invitee || !$event) {
            return;
        }
        
        // Find and remove calendar schedule entry
        $startTime = Carbon::parse($invitee['start_time']);
        
        CalendarSchedule::where('type', 'calendly_event')
                       ->where('date', $startTime->format('Y-m-d'))
                       ->where('note', 'like', "%{$invitee['name']}%")
                       ->delete();
        
        Log::info('Calendly canceled event removed from calendar', [
            'event_name' => $event['name'],
            'invitee_name' => $invitee['name']
        ]);
    }
    
    /**
     * Handle invitee updated event
     */
    private function handleInviteeUpdated($payload)
    {
        $invitee = $payload['payload']['invitee'] ?? null;
        $event = $payload['payload']['event'] ?? null;
        
        if (!$invitee || !$event) {
            return;
        }
        
        // Update calendar schedule entry
        $startTime = Carbon::parse($invitee['start_time']);
        
        CalendarSchedule::where('type', 'calendly_event')
                       ->where('date', $startTime->format('Y-m-d'))
                       ->where('note', 'like', "%{$invitee['name']}%")
                       ->update([
                           'date' => $startTime->format('Y-m-d'),
                           'note' => "Calendly: {$invitee['name']} - {$event['name']}",
                       ]);
        
        Log::info('Calendly event updated in calendar', [
            'event_name' => $event['name'],
            'invitee_name' => $invitee['name']
        ]);
    }
}
