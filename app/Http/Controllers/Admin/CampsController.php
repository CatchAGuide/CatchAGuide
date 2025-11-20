<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CampRequest;
use App\Models\Camp;
use App\Models\CampFacility;
use App\Services\Camp\CampDataProcessor;
use App\Services\Camp\CampImageProcessor;
use App\Services\Camp\CampSeoService;
use App\Services\Camp\CampCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CampsController extends Controller
{
    public function __construct(
        private CampDataProcessor $dataProcessor,
        private CampImageProcessor $imageProcessor,
        private CampSeoService $seoService,
        private CampCacheService $cacheService
    ) {}
    /**
     * Display a listing of the resource with caching
     */
    public function index()
    {
        $camps = $this->cacheService->getCampsList(15);
        return view('admin.pages.camps.index', compact('camps'));
    }

    /**
     * Show the form for creating a new resource with cached form data
     */
    public function create()
    {
        $formData = [
            'is_update' => 0,
            'user_id' => Auth::id(),
            'status' => 'active'
        ];
        
        $cachedFormData = $this->cacheService->getFormData();
        
        return view('admin.pages.camps.create', array_merge([
            'formData' => $formData,
            'targetRedirect' => route('admin.camps.index'),
        ], $cachedFormData));
    }

    /**
     * Store a newly created resource in storage using services
     */
    public function store(CampRequest $request)
    {
        $isDraft = $request->input('is_draft') == '1';

        try {
            // Log incoming request data
            Log::info('CampsController::store - Incoming request data', $request->all());

            // Process form data using service
            $campData = $this->dataProcessor->processRequestData($request);
            $campData['status'] = $isDraft ? 'draft' : ($request->status ?? 'active');
            $campData['slug'] = $this->seoService->generateSlug($request->title ?? 'Untitled');

            // Process images
            $imageData = $this->imageProcessor->processImageUploads($request, $campData['slug'], null);
            
            if ($imageData) {
                $campData['thumbnail_path'] = $imageData['thumbnail_path'];
                $campData['gallery_images'] = $imageData['gallery_images'];
            } elseif ($request->has('thumbnail_path') && !empty($request->input('thumbnail_path'))) {
                // Handle case where no new images uploaded but thumbnail_path was updated
                $campData['thumbnail_path'] = $request->input('thumbnail_path');
            }

            // Create camp
            $camp = Camp::create($campData);

            // Move images from temp directory to final directory if they were created
            if ($imageData && $camp->id) {
                $updatedImageData = $this->imageProcessor->moveImagesToFinalDirectory($camp->id, $camp->slug, $imageData);
                $camp->update([
                    'gallery_images' => $updatedImageData['gallery_images'],
                    'thumbnail_path' => $updatedImageData['thumbnail_path']
                ]);
                $camp->refresh();
            }

            // Sync relationships
            $this->syncCampRelationships($camp, $request);

            // Clear caches
            $this->cacheService->clearAllCaches();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $isDraft ? 'Draft saved successfully!' : 'Camp created successfully!',
                    'camp_id' => $camp->id,
                    'redirect_url' => $request->input('target_redirect') ?? route('admin.camps.index'),
                ]);
            }

            return redirect()->route('admin.camps.index')
                ->with('success', 'Camp created successfully.');
        } catch (\Exception $e) {
            Log::error('CampsController::store - Error creating camp', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating camp: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating camp: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource with caching
     */
    public function show(Camp $camp)
    {
        $camp = $this->cacheService->getCamp($camp->id);
        if (!$camp) {
            abort(404);
        }

        return view('admin.pages.camps.show', compact('camp'));
    }

    /**
     * Show the form for editing the specified resource with cached data
     */
    public function edit(Camp $camp)
    {
        $formData = $this->dataProcessor->prepareEditFormData($camp);
        $cachedFormData = $this->cacheService->getFormData();
        
        return view('admin.pages.camps.edit', array_merge([
            'camp' => $camp,
            'formData' => $formData,
            'targetRedirect' => route('admin.camps.index'),
        ], $cachedFormData));
    }

    /**
     * Update the specified resource in storage using services
     */
    public function update(CampRequest $request, Camp $camp)
    {
        $isDraft = $request->input('is_draft') == '1';

        try {
            // Log incoming request data
            Log::info('CampsController::update - Incoming request data', [
                'camp_id' => $camp->id,
                'title' => $request->title,
                'location' => $request->location,
                'is_draft' => $isDraft
            ]);

            // Process form data using service
            $campData = $this->dataProcessor->processRequestData($request, $camp);
            $campData['status'] = $isDraft ? 'draft' : ($request->status ?? $camp->status);
            
            // Generate slug from title if title changed
            if ($camp->title !== $request->title && $request->title) {
                $campData['slug'] = $this->seoService->generateSlug($request->title, $camp->id);
            }

            // Process images
            $imageData = $this->imageProcessor->processImageUploads($request, $camp->slug, $camp->id);
            
            if ($imageData) {
                $campData['thumbnail_path'] = $imageData['thumbnail_path'];
                $campData['gallery_images'] = $imageData['gallery_images'];
            } elseif ($request->has('thumbnail_path') && !empty($request->input('thumbnail_path'))) {
                // Handle case where no new images uploaded but thumbnail_path was updated
                $requestedThumbnail = $request->input('thumbnail_path');
                
                // Extract relative path from full URL if needed
                if (filter_var($requestedThumbnail, FILTER_VALIDATE_URL)) {
                    $parsedUrl = parse_url($requestedThumbnail);
                    $requestedThumbnail = ltrim($parsedUrl['path'] ?? '', '/');
                }
                $requestedThumbnail = ltrim($requestedThumbnail, '/');
                
                // Verify the thumbnail exists in existing gallery images
                $existingImages = json_decode($request->input('existing_images', '[]'), true) ?? [];
                $normalizedExisting = array_map(function($img) {
                    return ltrim($img, '/');
                }, $existingImages);
                
                $foundThumbnail = null;
                foreach ($existingImages as $existingImage) {
                    $normalizedExistingImage = ltrim($existingImage, '/');
                    $normalizedRequested = ltrim($requestedThumbnail, '/');
                    
                    if ($normalizedExistingImage === $normalizedRequested || 
                        basename($normalizedExistingImage) === basename($normalizedRequested)) {
                        $foundThumbnail = $existingImage;
                        break;
                    }
                }
                
                if ($foundThumbnail) {
                    $campData['thumbnail_path'] = $foundThumbnail;
                } else {
                    Log::warning('Requested thumbnail not found in existing images', [
                        'requested' => $requestedThumbnail,
                        'existing_images' => $existingImages
                    ]);
                }
            }

            // Update camp
            $camp->update($campData);

            // Move images from temp directory to final directory if they were created
            if ($imageData) {
                $updatedImageData = $this->imageProcessor->moveImagesToFinalDirectory($camp->id, $camp->slug, $imageData);
                $camp->update([
                    'gallery_images' => $updatedImageData['gallery_images'],
                    'thumbnail_path' => $updatedImageData['thumbnail_path']
                ]);
            }

            // Sync relationships
            $this->syncCampRelationships($camp, $request);

            // Clear caches - clear both the specific camp and the list
            $this->cacheService->clearCampCache($camp->id);
            $this->cacheService->clearCampsListCache();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $isDraft ? 'Draft updated successfully!' : 'Camp updated successfully!',
                    'camp_id' => $camp->id,
                    'redirect_url' => $request->input('target_redirect') ?? route('admin.camps.index'),
                ]);
            }

            return redirect()->route('admin.camps.index')
                ->with('success', 'Camp updated successfully.');
        } catch (\Exception $e) {
            Log::error('CampsController::update - Error updating camp', [
                'camp_id' => $camp->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating camp: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating camp: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage with cache clearing
     */
    public function destroy(Camp $camp)
    {
            $camp->delete();

        // Clear caches - clear both the specific camp and the list
        $this->cacheService->clearCampCache($camp->id);
        $this->cacheService->clearCampsListCache();

        return redirect()->route('admin.camps.index')
            ->with('success', 'Camp deleted successfully.');
    }

    /**
     * Change camp status
     */
    public function changeStatus(Request $request, $id)
    {
            $camp = Camp::findOrFail($id);
        $camp->status = $request->status;
        $camp->save();

        // Clear caches - clear both the specific camp and the list
        $this->cacheService->clearCampCache($camp->id);
        $this->cacheService->clearCampsListCache();

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.'
        ]);
    }

    /**
     * Sync camp relationships with related entities
     */
    private function syncCampRelationships(Camp $camp, $request): void
    {
        // Sync camp facilities
        if ($request->has('camp_facilities')) {
            $facilities = $request->input('camp_facilities');
            if (!empty($facilities)) {
                $facilityNames = array_filter(array_map('trim', explode(',', $facilities)));
                $facilityIds = CampFacility::whereIn('name', $facilityNames)->pluck('id')->toArray();
                $camp->facilities()->sync($facilityIds);
            } else {
                $camp->facilities()->sync([]);
            }
        }

        // Sync accommodations
        if ($request->has('accommodations')) {
            $camp->accommodations()->sync($request->input('accommodations'));
        }

        // Sync rental boats
        if ($request->has('rental_boats')) {
            $camp->rentalBoats()->sync($request->input('rental_boats'));
        }

        // Sync guidings
        if ($request->has('guidings')) {
            $camp->guidings()->sync($request->input('guidings'));
        }
    }
}
