<?php

namespace App\Http\Controllers;

use App\Services\Maps\LandmarkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MapLandmarkController extends Controller
{
    public function __invoke(Request $request, LandmarkService $landmarks): JsonResponse
    {
        $validated = $request->validate([
            'sw_lat' => ['required', 'numeric', 'between:-90,90'],
            'sw_lng' => ['required', 'numeric', 'between:-180,180'],
            'ne_lat' => ['required', 'numeric', 'between:-90,90'],
            'ne_lng' => ['required', 'numeric', 'between:-180,180'],
            'zoom' => ['required', 'integer', 'min:0', 'max:22'],
        ]);

        $items = $landmarks->forBounds(
            (float) $validated['sw_lat'],
            (float) $validated['sw_lng'],
            (float) $validated['ne_lat'],
            (float) $validated['ne_lng'],
            (int) $validated['zoom']
        );

        return response()->json([
            'landmarks' => $items,
        ]);
    }
}
