<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TripRequest;
use App\Models\BoatExtras;
use App\Models\GuidingBoatType;
use App\Models\Method;
use App\Models\Target;
use App\Models\Trip;
use App\Models\Water;
use App\Services\Trip\TripCacheService;
use App\Services\Trip\TripDataProcessor;
use App\Services\Trip\TripImageProcessor;
use App\Services\Trip\TripSeoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class TripsController extends Controller
{
    public function __construct(
        private TripDataProcessor $dataProcessor,
        private TripImageProcessor $imageProcessor,
        private TripSeoService $seoService,
        private TripCacheService $cacheService
    ) {
    }

    public function index()
    {
        $trips = $this->cacheService->getTripsList(15);

        return view('admin.pages.trips.index', compact('trips'));
    }

    public function create()
    {
        $formData = [
            'is_update' => 0,
            'user_id'   => Auth::id(),
            'status'    => 'active',
        ];

        return view('admin.pages.trips.create', array_merge([
            'formData'       => $formData,
            'targetRedirect' => route('admin.trips.index'),
        ], $this->cacheService->getFormData(), $this->getFormWhitelists()));
    }

    public function store(TripRequest $request)
    {
        $isDraft = $request->input('is_draft') == '1';

        try {
            $tripData = $this->dataProcessor->processRequestData($request);
            $tripData['status'] = $isDraft ? 'draft' : ($request->status ?? 'active');
            $tripData['slug'] = $this->seoService->generateSlug($request->title ?? 'Untitled');

            $imageData = $this->imageProcessor->processImageUploads($request, $tripData['slug'], null);
            if ($imageData) {
                $tripData['thumbnail_path'] = $imageData['thumbnail_path'];
                $tripData['gallery_images'] = $imageData['gallery_images'];
            }

            $trip = Trip::create($tripData);

            if ($imageData && $trip->id) {
                $updatedImageData = $this->imageProcessor->moveImagesToFinalDirectory($trip->id, $trip->slug, $imageData);
                $trip->update([
                    'gallery_images' => $updatedImageData['gallery_images'],
                    'thumbnail_path' => $updatedImageData['thumbnail_path'],
                ]);
                $trip->refresh();
            }

            $providerPhoto = $this->imageProcessor->processProviderPhoto($request, $trip->slug, $trip->id);
            if ($providerPhoto) {
                $trip->update(['provider_photo' => $providerPhoto]);
            }

            $this->dataProcessor->processAvailabilityDates($request, $trip);
            $this->cacheService->clearAllCaches();

            if ($request->expectsJson()) {
                return response()->json([
                    'success'   => true,
                    'message'   => $isDraft ? 'Draft saved successfully!' : 'Trip created successfully!',
                    'trip_id'   => $trip->id,
                    'redirect_url' => $request->input('target_redirect') ?? route('admin.trips.index'),
                ]);
            }

            return redirect()->route('admin.trips.index')
                ->with('success', 'Trip created successfully.');
        } catch (\Exception $e) {
            Log::error('TripsController::store - Error creating trip', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating trip: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating trip: ' . $e->getMessage());
        }
    }

    public function show(Trip $trip)
    {
        $trip = $this->cacheService->getTrip($trip->id);
        if (!$trip) {
            abort(404);
        }

        return view('admin.pages.trips.show', compact('trip'));
    }

    public function edit(Trip $trip)
    {
        $formData = $this->dataProcessor->prepareEditFormData($trip);

        return view('admin.pages.trips.edit', array_merge([
            'trip'           => $trip,
            'formData'       => $formData,
            'targetRedirect' => route('admin.trips.index'),
        ], $this->cacheService->getFormData(), $this->getFormWhitelists()));
    }

    public function update(TripRequest $request, Trip $trip)
    {
        $isDraft = $request->input('is_draft') == '1';

        try {
            $tripData = $this->dataProcessor->processRequestData($request, $trip);
            $tripData['status'] = $isDraft ? 'draft' : ($request->status ?? $trip->status);

            if ($trip->title !== $request->title && $request->title) {
                $tripData['slug'] = $this->seoService->generateSlug($request->title, $trip->id);
            }

            $imageData = $this->imageProcessor->processImageUploads($request, $trip->slug, $trip->id);
            if ($imageData) {
                $tripData['thumbnail_path'] = $imageData['thumbnail_path'];
                $tripData['gallery_images'] = $imageData['gallery_images'];
            }

            $trip->update($tripData);

            if ($imageData) {
                $updatedImageData = $this->imageProcessor->moveImagesToFinalDirectory($trip->id, $trip->slug, $imageData);
                $trip->update([
                    'gallery_images' => $updatedImageData['gallery_images'],
                    'thumbnail_path' => $updatedImageData['thumbnail_path'],
                ]);
            }

            $providerPhoto = $this->imageProcessor->processProviderPhoto($request, $trip->slug, $trip->id);
            if ($providerPhoto) {
                $trip->update(['provider_photo' => $providerPhoto]);
            }

            $this->dataProcessor->processAvailabilityDates($request, $trip);

            $this->cacheService->clearTripCache($trip->id);
            $this->cacheService->clearTripsListCache();

            if ($request->expectsJson()) {
                return response()->json([
                    'success'   => true,
                    'message'   => $isDraft ? 'Draft updated successfully!' : 'Trip updated successfully!',
                    'trip_id'   => $trip->id,
                    'redirect_url' => $request->input('target_redirect') ?? route('admin.trips.index'),
                ]);
            }

            return redirect()->route('admin.trips.index')
                ->with('success', 'Trip updated successfully.');
        } catch (\Exception $e) {
            Log::error('TripsController::update - Error updating trip', [
                'trip_id' => $trip->id,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating trip: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating trip: ' . $e->getMessage());
        }
    }

    public function destroy(Trip $trip)
    {
        $trip->delete();

        $this->cacheService->clearTripCache($trip->id);
        $this->cacheService->clearTripsListCache();

        return redirect()->route('admin.trips.index')
            ->with('success', 'Trip deleted successfully.');
    }

    /**
     * Load locale-aware DB-backed whitelists fresh per request.
     *
     * Mirrors GuidingsController's approach: Target, Method, Water each have a
     * getNameAttribute() accessor that returns name_en or name based on locale,
     * so these must NOT be cached — they are loaded fresh on every form page load.
     */
    private function getFormWhitelists(): array
    {
        $locale = Config::get('app.locale');
        $nameField = $locale === 'en' ? 'name_en' : 'name';

        $mapToWhitelist = fn ($model) => $model->map(fn ($item) => [
            'id'    => $item->id,
            'value' => $item->$nameField ?? $item->name,
        ])->sortBy('value')->values();

        return [
            'targets'            => $mapToWhitelist(Target::all()),
            'methods'            => $mapToWhitelist(Method::all()),
            'waters'             => $mapToWhitelist(Water::all()),
            'guiding_boat_types' => $mapToWhitelist(GuidingBoatType::all()),
            'boat_extras'        => $mapToWhitelist(BoatExtras::all()),
        ];
    }

    public function changeStatus(Request $request, $id)
    {
        $trip = Trip::findOrFail($id);
        $trip->status = $request->status;
        $trip->save();

        $this->cacheService->clearTripCache($trip->id);
        $this->cacheService->clearTripsListCache();

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.',
        ]);
    }
}

