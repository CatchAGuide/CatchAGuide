<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Catalog\TripCatalogService;
use Illuminate\Http\JsonResponse;

class CatalogController extends Controller
{
    public function __construct(
        protected TripCatalogService $tripCatalogService
    ) {
    }

    /**
     * Unified catalog of all trips (guidings + vacations).
     *
     * Public, read-only endpoint intended for AI agents, tools and developers.
     */
    public function trips(): JsonResponse
    {
        $trips = $this->tripCatalogService->getAllTrips();

        return response()->json([
            'version' => '1.0',
            'generated_at' => now()->toIso8601String(),
            'trips' => $trips,
        ]);
    }

    /**
     * Guidings-only view of the catalog.
     */
    public function guidings(): JsonResponse
    {
        $trips = $this->tripCatalogService->getGuidingTrips();

        return response()->json([
            'version' => '1.0',
            'generated_at' => now()->toIso8601String(),
            'trips' => $trips,
        ]);
    }

    /**
     * Vacations-only view of the catalog.
     */
    public function vacations(): JsonResponse
    {
        $trips = $this->tripCatalogService->getVacationTrips();

        return response()->json([
            'version' => '1.0',
            'generated_at' => now()->toIso8601String(),
            'trips' => $trips,
        ]);
    }
}

