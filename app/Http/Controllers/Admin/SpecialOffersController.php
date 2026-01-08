<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SpecialOfferRequest;
use App\Models\SpecialOffer;
use App\Services\SpecialOffer\SpecialOfferDataProcessor;
use App\Services\SpecialOffer\SpecialOfferImageProcessor;
use App\Services\SpecialOffer\SpecialOfferSeoService;
use App\Services\SpecialOffer\SpecialOfferCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SpecialOffersController extends Controller
{
    public function __construct(
        private SpecialOfferDataProcessor $dataProcessor,
        private SpecialOfferImageProcessor $imageProcessor,
        private SpecialOfferSeoService $seoService,
        private SpecialOfferCacheService $cacheService
    ) {}

    /**
     * Display a listing of the resource with caching
     */
    public function index()
    {
        $specialOffers = $this->cacheService->getSpecialOffersList();
        return view('admin.pages.special-offers.index', compact('specialOffers'));
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
        
        return view('admin.pages.special-offers.create', array_merge([
            'formData' => $formData,
            'targetRedirect' => route('admin.special-offers.index'),
        ], $cachedFormData));
    }

    /**
     * Store a newly created resource in storage using services
     */
    public function store(SpecialOfferRequest $request)
    {
        $isDraft = $request->input('is_draft') == '1';

        try {

            // Process form data using service
            $specialOfferData = $this->dataProcessor->processRequestData($request);
            $specialOfferData['status'] = $isDraft ? 'draft' : ($request->status ?? 'active');
            $specialOfferData['slug'] = $this->seoService->generateSlug($request->title ?? 'Untitled');

            // Process images
            $imageData = $this->imageProcessor->processImageUploads($request, $specialOfferData['slug'], null);
            
            if ($imageData) {
                $specialOfferData['thumbnail_path'] = $imageData['thumbnail_path'];
                $specialOfferData['gallery_images'] = $imageData['gallery_images'];
            } elseif ($request->has('thumbnail_path') && !empty($request->input('thumbnail_path'))) {
                // Handle case where no new images uploaded but thumbnail_path was updated
                $specialOfferData['thumbnail_path'] = $request->input('thumbnail_path');
            }

            // Create special offer
            $specialOffer = SpecialOffer::create($specialOfferData);

            // Move images from temp directory to final directory if they were created
            if ($imageData && $specialOffer->id) {
                $updatedImageData = $this->imageProcessor->moveImagesToFinalDirectory($specialOffer->id, $specialOffer->slug, $imageData);
                $specialOffer->update([
                    'gallery_images' => $updatedImageData['gallery_images'],
                    'thumbnail_path' => $updatedImageData['thumbnail_path']
                ]);
                $specialOffer->refresh();
            }

            // Sync relationships
            Log::info('SpecialOffersController::store - Before sync relationships', [
                'accommodations_ids' => $request->input('accommodations_ids'),
                'rental_boats_ids' => $request->input('rental_boats_ids'),
                'guidings_ids' => $request->input('guidings_ids'),
                'all_input_keys' => array_keys($request->all()),
            ]);
            $this->syncSpecialOfferRelationships($specialOffer, $request);
            Log::info('SpecialOffersController::store - After sync relationships', [
                'accommodations_count' => $specialOffer->accommodations()->count(),
                'rental_boats_count' => $specialOffer->rentalBoats()->count(),
                'guidings_count' => $specialOffer->guidings()->count(),
            ]);

            // Clear caches
            $this->cacheService->clearAllCaches();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $isDraft ? 'Draft saved successfully!' : 'Special offer created successfully!',
                    'special_offer_id' => $specialOffer->id,
                    'redirect_url' => $request->input('target_redirect') ?? route('admin.special-offers.index'),
                ]);
            }

            return redirect()->route('admin.special-offers.index')
                ->with('success', 'Special offer created successfully.');
        } catch (\Exception $e) {
            Log::error('SpecialOffersController::store - Error creating special offer', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating special offer: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating special offer: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource with caching
     */
    public function show(SpecialOffer $specialOffer)
    {
        $specialOffer = $this->cacheService->getSpecialOffer($specialOffer->id);
        if (!$specialOffer) {
            abort(404);
        }

        return view('admin.pages.special-offers.show', compact('specialOffer'));
    }

    /**
     * Show the form for editing the specified resource with cached data
     */
    public function edit(SpecialOffer $specialOffer)
    {
        $formData = $this->dataProcessor->prepareEditFormData($specialOffer);
        $cachedFormData = $this->cacheService->getFormData();
        
        return view('admin.pages.special-offers.edit', array_merge([
            'specialOffer' => $specialOffer,
            'formData' => $formData,
            'targetRedirect' => route('admin.special-offers.index'),
        ], $cachedFormData));
    }

    /**
     * Update the specified resource in storage using services
     */
    public function update(SpecialOfferRequest $request, SpecialOffer $specialOffer)
    {
        $isDraft = $request->input('is_draft') == '1';

        try {

            // Process form data using service
            $specialOfferData = $this->dataProcessor->processRequestData($request, $specialOffer);
            $specialOfferData['status'] = $isDraft ? 'draft' : ($request->status ?? $specialOffer->status);
            
            // Generate slug from title if title changed
            if ($specialOffer->title !== $request->title && $request->title) {
                $specialOfferData['slug'] = $this->seoService->generateSlug($request->title, $specialOffer->id);
            }

            // Process images
            $imageData = $this->imageProcessor->processImageUploads($request, $specialOffer->slug, $specialOffer->id);
            
            if ($imageData) {
                $specialOfferData['thumbnail_path'] = $imageData['thumbnail_path'];
                $specialOfferData['gallery_images'] = $imageData['gallery_images'];
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
                    $specialOfferData['thumbnail_path'] = $foundThumbnail;
                } else {
                    Log::warning('Requested thumbnail not found in existing images', [
                        'requested' => $requestedThumbnail,
                        'existing_images' => $existingImages
                    ]);
                }
            }

            // Update special offer
            $specialOffer->update($specialOfferData);

            // Move images from temp directory to final directory if they were created
            if ($imageData) {
                $updatedImageData = $this->imageProcessor->moveImagesToFinalDirectory($specialOffer->id, $specialOffer->slug, $imageData);
                $specialOffer->update([
                    'gallery_images' => $updatedImageData['gallery_images'],
                    'thumbnail_path' => $updatedImageData['thumbnail_path']
                ]);
            }

            // Sync relationships
            Log::info('SpecialOffersController::update - Before sync relationships', [
                'accommodations_ids' => $request->input('accommodations_ids'),
                'rental_boats_ids' => $request->input('rental_boats_ids'),
                'guidings_ids' => $request->input('guidings_ids'),
            ]);
            $this->syncSpecialOfferRelationships($specialOffer, $request);
            Log::info('SpecialOffersController::update - After sync relationships', [
                'accommodations_count' => $specialOffer->accommodations()->count(),
                'rental_boats_count' => $specialOffer->rentalBoats()->count(),
                'guidings_count' => $specialOffer->guidings()->count(),
            ]);

            // Clear caches - clear both the specific special offer and the list
            $this->cacheService->clearSpecialOfferCache($specialOffer->id);
            $this->cacheService->clearSpecialOffersListCache();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $isDraft ? 'Draft updated successfully!' : 'Special offer updated successfully!',
                    'special_offer_id' => $specialOffer->id,
                    'redirect_url' => $request->input('target_redirect') ?? route('admin.special-offers.index'),
                ]);
            }

            return redirect()->route('admin.special-offers.index')
                ->with('success', 'Special offer updated successfully.');
        } catch (\Exception $e) {
            Log::error('SpecialOffersController::update - Error updating special offer', [
                'special_offer_id' => $specialOffer->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating special offer: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating special offer: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage with cache clearing
     */
    public function destroy(SpecialOffer $specialOffer)
    {
        $specialOffer->delete();

        // Clear caches - clear both the specific special offer and the list
        $this->cacheService->clearSpecialOfferCache($specialOffer->id);
        $this->cacheService->clearSpecialOffersListCache();

        return redirect()->route('admin.special-offers.index')
            ->with('success', 'Special offer deleted successfully.');
    }

    /**
     * Change special offer status
     */
    public function changeStatus(Request $request, $id)
    {
        $specialOffer = SpecialOffer::findOrFail($id);
        $specialOffer->status = $request->status;
        $specialOffer->save();

        // Clear caches - clear both the specific special offer and the list
        $this->cacheService->clearSpecialOfferCache($specialOffer->id);
        $this->cacheService->clearSpecialOffersListCache();

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.'
        ]);
    }

    /**
     * Sync special offer relationships with related entities
     */
    private function syncSpecialOfferRelationships(SpecialOffer $specialOffer, $request): void
    {
        // Sync accommodations - prioritize accommodations_ids, fallback to accommodations array
        $accommodationIds = $request->input('accommodations_ids');
        
        Log::info('SpecialOffersController::syncSpecialOfferRelationships - Accommodations', [
            'raw_value' => $accommodationIds,
            'type' => gettype($accommodationIds),
            'is_null' => is_null($accommodationIds),
            'is_empty_string' => $accommodationIds === '',
        ]);
        
        if ($accommodationIds !== null && $accommodationIds !== '') {
            // Process comma-separated string or array
            $ids = is_array($accommodationIds) ? $accommodationIds : explode(',', $accommodationIds);
            $ids = array_filter(array_map('intval', $ids));
            Log::info('SpecialOffersController::syncSpecialOfferRelationships - Syncing accommodations', [
                'ids' => $ids,
                'special_offer_id' => $specialOffer->id,
            ]);
            $specialOffer->accommodations()->sync($ids);
        } elseif ($request->has('accommodations') && is_array($request->input('accommodations'))) {
            // Fallback to old format
            Log::info('SpecialOffersController::syncSpecialOfferRelationships - Using fallback accommodations');
            $specialOffer->accommodations()->sync($request->input('accommodations', []));
        } else {
            // Clear if neither format is present
            Log::info('SpecialOffersController::syncSpecialOfferRelationships - Clearing accommodations');
            $specialOffer->accommodations()->sync([]);
        }

        // Sync rental boats - prioritize rental_boats_ids, fallback to rental_boats array
        $rentalBoatIds = $request->input('rental_boats_ids');
        
        Log::info('SpecialOffersController::syncSpecialOfferRelationships - Rental Boats', [
            'raw_value' => $rentalBoatIds,
            'type' => gettype($rentalBoatIds),
            'is_null' => is_null($rentalBoatIds),
            'is_empty_string' => $rentalBoatIds === '',
        ]);
        
        if ($rentalBoatIds !== null && $rentalBoatIds !== '') {
            // Process comma-separated string or array
            $ids = is_array($rentalBoatIds) ? $rentalBoatIds : explode(',', $rentalBoatIds);
            $ids = array_filter(array_map('intval', $ids));
            Log::info('SpecialOffersController::syncSpecialOfferRelationships - Syncing rental_boats', [
                'ids' => $ids,
                'special_offer_id' => $specialOffer->id,
            ]);
            $specialOffer->rentalBoats()->sync($ids);
        } elseif ($request->has('rental_boats') && is_array($request->input('rental_boats'))) {
            // Fallback to old format
            Log::info('SpecialOffersController::syncSpecialOfferRelationships - Using fallback rental_boats');
            $specialOffer->rentalBoats()->sync($request->input('rental_boats', []));
        } else {
            // Clear if neither format is present
            Log::info('SpecialOffersController::syncSpecialOfferRelationships - Clearing rental_boats');
            $specialOffer->rentalBoats()->sync([]);
        }

        // Sync guidings - prioritize guidings_ids, fallback to guidings array
        $guidingIds = $request->input('guidings_ids');
        
        Log::info('SpecialOffersController::syncSpecialOfferRelationships - Guidings', [
            'raw_value' => $guidingIds,
            'type' => gettype($guidingIds),
            'is_null' => is_null($guidingIds),
            'is_empty_string' => $guidingIds === '',
        ]);
        
        if ($guidingIds !== null && $guidingIds !== '') {
            // Process comma-separated string or array
            $ids = is_array($guidingIds) ? $guidingIds : explode(',', $guidingIds);
            $ids = array_filter(array_map('intval', $ids));
            Log::info('SpecialOffersController::syncSpecialOfferRelationships - Syncing guidings', [
                'ids' => $ids,
                'special_offer_id' => $specialOffer->id,
            ]);
            $specialOffer->guidings()->sync($ids);
        } elseif ($request->has('guidings') && is_array($request->input('guidings'))) {
            // Fallback to old format
            Log::info('SpecialOffersController::syncSpecialOfferRelationships - Using fallback guidings');
            $specialOffer->guidings()->sync($request->input('guidings', []));
        } else {
            // Clear if neither format is present
            Log::info('SpecialOffersController::syncSpecialOfferRelationships - Clearing guidings');
            $specialOffer->guidings()->sync([]);
        }
    }
}
