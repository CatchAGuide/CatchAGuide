<?php

namespace App\Services\RentalBoat;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RentalBoatExtrasProcessor
{
    /**
     * Process boat extras from request
     */
    public function processBoatExtras(Request $request): ?array
    {
        if (!$request->has('boat_extras')) {
            Log::info('RentalBoatExtrasProcessor::processBoatExtras - No boat extras found');
            return null;
        }

        $boatExtras = $request->boat_extras;

        // Ensure we return an array
        if (is_string($boatExtras)) {
            $decoded = json_decode($boatExtras, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
            // If not valid JSON, try comma-separated
            return explode(',', $boatExtras);
        }

        return is_array($boatExtras) ? $boatExtras : [];
    }

    /**
     * Process inclusions from request
     */
    public function processInclusions(Request $request): ?array
    {
        if (!$request->has('inclusions')) {
            Log::info('RentalBoatExtrasProcessor::processInclusions - No inclusions found');
            return null;
        }

        $inclusions = $request->inclusions;

        // Ensure we return an array
        if (is_string($inclusions)) {
            $decoded = json_decode($inclusions, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
            // If not valid JSON, try comma-separated
            return explode(',', $inclusions);
        }

        return is_array($inclusions) ? $inclusions : [];
    }
}
