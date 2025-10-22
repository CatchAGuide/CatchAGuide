<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RentalBoatRequest;
use App\Models\RentalBoat;
use App\Services\RentalBoat\RentalBoatDataProcessor;
use App\Services\RentalBoat\RentalBoatImageProcessor;
use App\Services\RentalBoat\RentalBoatSeoService;
use App\Services\RentalBoat\RentalBoatCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RentalBoatsController extends Controller
{
    public function __construct(
        private RentalBoatDataProcessor $dataProcessor,
        private RentalBoatImageProcessor $imageProcessor,
        private RentalBoatSeoService $seoService,
        private RentalBoatCacheService $cacheService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rentalBoats = $this->cacheService->getRentalBoatsList();
        return view('admin.pages.rental-boats.index', compact('rentalBoats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $formData = [
            'is_update' => 0,
            'user_id' => Auth::id(),
            'status' => 'active'
        ];
        
        $formData = array_merge($formData, $this->cacheService->getFormData());
        
        return view('admin.pages.rental-boats.create', [
            'pageTitle' => 'Create Rental Boat',
            'formData' => $formData,
            'targetRedirect' => route('admin.rental-boats.index'),
            'rentalBoatTypes' => $formData['rentalBoatTypes'],
            'rentalBoatRequirements' => $formData['rentalBoatRequirements'],
            'boatExtras' => $formData['boatExtras'] ?? [],
            'inclusions' => $formData['inclusions'] ?? [],
            'guiding_boat_descriptions' => $formData['guiding_boat_descriptions'] ?? [],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RentalBoatRequest $request)
    {
        $isDraft = $request->input('is_draft') == '1';

        try {
            // Log incoming request data
            Log::info('RentalBoatsController::store - Incoming request data', $request->all());

            // Process form data using service
            $rentalBoatData = $this->dataProcessor->processRequestData($request);
            $rentalBoatData['status'] = $isDraft ? 'draft' : ($request->status ?? 'active');
            $rentalBoatData['slug'] = $this->seoService->generateSlug($request->title ?? 'Untitled');

            
            // Process images
            $imageData = $this->imageProcessor->processImageUploads($request, $rentalBoatData['slug'], null);
            
            if ($imageData) {
                $rentalBoatData['thumbnail_path'] = $imageData['thumbnail_path'];
                $rentalBoatData['gallery_images'] = $imageData['gallery_images'];
            }

            // Create rental boat
            $rentalBoat = RentalBoat::create($rentalBoatData);

            // Move images from temp directory to final directory if they were created
            if ($imageData && $rentalBoat->id) {
                $updatedImageData = $this->imageProcessor->moveImagesToFinalDirectory($rentalBoat->id, $rentalBoat->slug, $imageData);
                $rentalBoat->update([
                    'gallery_images' => $updatedImageData['gallery_images'],
                    'thumbnail_path' => $updatedImageData['thumbnail_path']
                ]);
                $rentalBoat->refresh();
            }

            // Clear caches
            $this->cacheService->clearAllCaches();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $isDraft ? 'Draft saved successfully!' : 'Rental boat created successfully!',
                    'rental_boat_id' => $rentalBoat->id,
                    'redirect_url' => $request->input('target_redirect') ?? route('admin.rental-boats.index'),
                ]);
            }

            return redirect()->route('admin.rental-boats.index')
                ->with('success', 'Rental boat created successfully.');
        } catch (\Exception $e) {
            Log::error('RentalBoatsController::store - Error creating rental boat', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating rental boat: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating rental boat: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(RentalBoat $rentalBoat)
    {
        $rentalBoat = $this->cacheService->getRentalBoat($rentalBoat->id);
        if (!$rentalBoat) {
            abort(404);
        }
        
        return view('admin.pages.rental-boats.show', compact('rentalBoat'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RentalBoat $rentalBoat)
    {
        $formData = $this->prepareEditFormData($rentalBoat);
        $cachedFormData = $this->cacheService->getFormData();
        
        return view('admin.pages.rental-boats.edit', array_merge([
            'pageTitle' => 'Edit Rental Boat',
            'formData' => $formData,
            'targetRedirect' => route('admin.rental-boats.index'),
        ], $cachedFormData));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RentalBoatRequest $request, RentalBoat $rentalBoat)
    {
        $isDraft = $request->input('is_draft') == '1';

        try {
            // Log incoming request data
            Log::info('RentalBoatsController::update - Incoming request data', [
                'rental_boat_id' => $rentalBoat->id,
                'title' => $request->title,
                'location' => $request->location,
                'boat_type' => $request->boat_type,
                'price_type_checkboxes' => $request->input('price_type_checkboxes', []),
                'is_draft' => $isDraft
            ]);
            
            // Log ALL request data for debugging
            Log::info('RentalBoatsController::update - ALL REQUEST DATA', [
                'all_input' => $request->all(),
                'method' => $request->method(),
                'has_files' => $request->hasFile('title_image')
            ]);

            // Process form data using service
            $rentalBoatData = $this->dataProcessor->processRequestData($request, $rentalBoat);
            $rentalBoatData['status'] = $isDraft ? 'draft' : ($request->status ?? $rentalBoat->status);
            
            // Generate slug from title if title changed
            if ($rentalBoat->title !== $request->title && $request->title) {
                $rentalBoatData['slug'] = $this->seoService->generateSlug($request->title, $rentalBoat->id);
            }

            
            // Process images
            $imageData = $this->imageProcessor->processImageUploads($request, $rentalBoat->slug, $rentalBoat->id);
            
            if ($imageData) {
                $rentalBoatData['thumbnail_path'] = $imageData['thumbnail_path'];
                $rentalBoatData['gallery_images'] = $imageData['gallery_images'];
            }

            // Update rental boat
            $rentalBoat->update($rentalBoatData);

            // Move images from temp directory to final directory if they were created
            if ($imageData) {
                $updatedImageData = $this->imageProcessor->moveImagesToFinalDirectory($rentalBoat->id, $rentalBoat->slug, $imageData);
                $rentalBoat->update([
                    'gallery_images' => $updatedImageData['gallery_images'],
                    'thumbnail_path' => $updatedImageData['thumbnail_path']
                ]);
            }

            // Clear caches
            $this->cacheService->clearRentalBoatCache($rentalBoat->id);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $isDraft ? 'Draft updated successfully!' : 'Rental boat updated successfully!',
                    'rental_boat_id' => $rentalBoat->id,
                    'redirect_url' => $request->input('target_redirect') ?? route('admin.rental-boats.index'),
                ]);
            }

            return redirect()->route('admin.rental-boats.index')
                ->with('success', 'Rental boat updated successfully.');
        } catch (\Exception $e) {
            Log::error('RentalBoatsController::update - Error updating rental boat', [
                'rental_boat_id' => $rentalBoat->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating rental boat: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating rental boat: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RentalBoat $rentalBoat)
    {
        $rentalBoat->delete();
        
        // Clear caches
        $this->cacheService->clearRentalBoatCache($rentalBoat->id);
        
        return redirect()->route('admin.rental-boats.index')
            ->with('success', 'Rental boat deleted successfully.');
    }

    /**
     * Change the status of a rental boat
     */
    public function changeStatus(Request $request, $id)
    {
        $rentalBoat = RentalBoat::findOrFail($id);
        $rentalBoat->status = $request->status;
        $rentalBoat->save();

        // Clear caches
        $this->cacheService->clearRentalBoatCache($rentalBoat->id);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.'
        ]);
    }

    /**
     * Prepare form data for editing
     */
    private function prepareEditFormData(RentalBoat $rentalBoat): array
    {
        $formData = [
            'is_update' => 1,
            'id' => $rentalBoat->id,
            'rental_boat_id' => $rentalBoat->id,
            'user_id' => $rentalBoat->user_id,
            'title' => $rentalBoat->title,
            'location' => $rentalBoat->location,
            'city' => $rentalBoat->city,
            'country' => $rentalBoat->country,
            'region' => $rentalBoat->region,
            'latitude' => $rentalBoat->lat,
            'longitude' => $rentalBoat->lng,
            'boat_type' => $rentalBoat->boat_type,
            'desc_of_boat' => $rentalBoat->desc_of_boat,
            'status' => $rentalBoat->status,
            'thumbnail_path' => $rentalBoat->thumbnail_path,
            'gallery_images' => $rentalBoat->gallery_images,
            'existing_images' => json_encode($rentalBoat->gallery_images ?? []),
        ];

        // Process requirements (handled by model accessor)
        $formData['requirements'] = $rentalBoat->requirements ?? [];

        // Process boat information (handled by model accessor)
        $formData['boat_information'] = $rentalBoat->boat_information ?? [];

        // Process pricing
        $prices = $rentalBoat->prices ?? [];
        $formData['prices'] = $prices;

        // Process pricing extra
        if (isset($prices['pricing_extra'])) {
            $formData['pricing_extra'] = $prices['pricing_extra'];
        } else {
            $formData['pricing_extra'] = [];
        }

        // Process boat extras (handled by model accessor)
        $formData['boat_extras'] = $rentalBoat->boat_extras ?? [];

        // Process inclusions (handled by model accessor)
        $formData['inclusions'] = $rentalBoat->inclusions ?? [];

        return $formData;
    }
}
