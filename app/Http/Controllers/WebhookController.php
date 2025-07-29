<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Generic webhook handler for future integrations
     */
    public function handle(Request $request, $provider)
    {
        Log::info("Webhook received from {$provider}", [
            'headers' => $request->headers->all(),
            'body' => $request->all()
        ]);
        
        // For future webhook integrations
        return response()->json(['status' => 'received']);
    }
}
