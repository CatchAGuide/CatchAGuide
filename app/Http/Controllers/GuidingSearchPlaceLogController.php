<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GuidingSearchPlaceLogController extends Controller
{
    /**
     * Record a Google Places selection from the guidings navbar search (file log only).
     * Triggered only on place_changed — no server-side Google API calls.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'place_query' => ['nullable', 'string', 'max:500'],
            'place_id' => ['nullable', 'string', 'max:256'],
            'formatted_address' => ['nullable', 'string', 'max:1000'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'region' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:32'],
            'country_short' => ['nullable', 'string', 'max:8'],
            'region_short' => ['nullable', 'string', 'max:32'],
            'place_types' => ['nullable', 'array', 'max:50'],
            'place_types.*' => ['string', 'max:100'],
            'browser_language' => ['nullable', 'string', 'max:64'],
            'browser_languages' => ['nullable', 'array', 'max:25'],
            'browser_languages.*' => ['string', 'max:64'],
            'source_input_id' => ['nullable', 'string', 'max:128'],
            'context' => ['nullable', 'string', 'max:128'],
        ]);

        $payload = array_merge($validated, [
            'client_ip' => $request->ip(),
            'app_locale' => app()->getLocale(),
            'user_id' => $request->user()?->id,
            'user_agent' => Str::limit((string) $request->userAgent(), 512, ''),
            'logged_at' => now()->toIso8601String(),
        ]);

        Log::channel('guiding_search_places')->info('guiding_search_place', $payload);

        return response()->json(['ok' => true]);
    }
}
