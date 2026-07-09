<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccommodationRequest;
use App\Models\Accommodation;
use App\Support\AdminListingStats;
use App\Services\Accommodation\AccommodationDataProcessor;
use App\Services\Accommodation\AccommodationSeoService;
use App\Services\Accommodation\AccommodationCacheService;
use App\Services\Media\ListingGalleryImageProcessor;
use App\Services\Media\ListingMediaRelocator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AccommodationsController extends Controller
{
    public function __construct(
        private AccommodationDataProcessor $dataProcessor,
        private ListingGalleryImageProcessor $galleryProcessor,
        private ListingMediaRelocator $mediaRelocator,
        private AccommodationSeoService $seoService,
        private AccommodationCacheService $cacheService
    ) {}
    /**
     * Display a listing of the resource with caching
     */
    public function index()
    {
        $accommodations = Accommodation::with(['user', 'accommodationType'])
            ->orderByDesc('id')
            ->get();
        $listingStats = AdminListingStats::cardsForStatusListings($accommodations, 'Total accommodations');

        return view('admin.pages.accommodations.index', compact('accommodations', 'listingStats'));
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
        
        return view('admin.pages.accommodations.create', array_merge([
            'formData' => $formData,
            'targetRedirect' => route('admin.accommodations.index'),
        ], $cachedFormData));
    }

    /**
     * Store a newly created resource in storage using services
     */
    public function store(AccommodationRequest $request)
    {
        $isDraft = $request->input('is_draft') == '1';

        try {
            // Process form data using service
            $accommodationData = $this->dataProcessor->processRequestData($request);
            $accommodationData['status'] = $isDraft ? 'draft' : 'active';
            $accommodationData['slug'] = $this->seoService->generateSlug($request->title ?? 'Untitled');

            // Process images
            $imageData = $this->galleryProcessor->process($request, 'accommodation', $accommodationData['slug'], null);
            
            if ($imageData) {
                $accommodationData['thumbnail_path'] = $imageData['thumbnail_path'];
                $accommodationData['gallery_images'] = $imageData['gallery_images'];
            }

            // Create accommodation
            $accommodation = Accommodation::create($accommodationData);

            // Move images from temp directory to final directory if they were created
            if ($imageData && $accommodation->id) {
                $updatedImageData = $this->mediaRelocator->promoteForListing('accommodation', $accommodation->id, $imageData);
                $accommodation->update([
                    'gallery_images' => $updatedImageData['gallery_images'],
                    'thumbnail_path' => $updatedImageData['thumbnail_path']
                ]);
                $accommodation->refresh();
            }

            // Clear caches
            $this->cacheService->clearAllCaches();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $isDraft ? 'Draft saved successfully!' : 'Accommodation created successfully!',
                    'accommodation_id' => $accommodation->id,
                    'redirect_url' => $request->input('target_redirect') ?? route('admin.accommodations.index'),
                ]);
            }

            return redirect()->route('admin.accommodations.index')
                ->with('success', 'Accommodation created successfully.');
        } catch (\Exception $e) {
            Log::error('AccommodationsController::store - Error creating accommodation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating accommodation: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating accommodation: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource with caching
     */
    public function show(Accommodation $accommodation)
    {
        $accommodation = $this->cacheService->getAccommodation($accommodation->id);
        if (!$accommodation) {
            abort(404);
        }
        
        return view('admin.pages.accommodations.show', compact('accommodation'));
    }

    /**
     * Show the form for editing the specified resource with cached data
     */
    public function edit(Accommodation $accommodation)
    {
        $formData = $this->dataProcessor->prepareEditFormData($accommodation);
        $cachedFormData = $this->cacheService->getFormData();
        
        return view('admin.pages.accommodations.edit', array_merge([
            'accommodation' => $accommodation,
            'formData' => $formData,
            'targetRedirect' => route('admin.accommodations.index'),
        ], $cachedFormData));
    }

    /**
     * Update the specified resource in storage using services
     */
    public function update(AccommodationRequest $request, Accommodation $accommodation)
    {
        $isDraft = $request->input('is_draft') == '1';

        try {
            // Process form data using service
            $accommodationData = $this->dataProcessor->processRequestData($request, $accommodation);
            $accommodationData['status'] = $isDraft ? 'draft' : 'active';
            
            // Generate slug from title if title changed
            if ($accommodation->title !== $request->title && $request->title) {
                $accommodationData['slug'] = $this->seoService->generateSlug($request->title, $accommodation->id);
            }

            // Process images
            $imageData = $this->galleryProcessor->process($request, 'accommodation', $accommodation->slug, $accommodation->id);
            
            if ($imageData) {
                $accommodationData['thumbnail_path'] = $imageData['thumbnail_path'];
                $accommodationData['gallery_images'] = $imageData['gallery_images'];
            }

            // Update accommodation
            $accommodation->update($accommodationData);

            // Move images from temp directory to final directory if they were created
            if ($imageData) {
                $updatedImageData = $this->mediaRelocator->promoteForListing('accommodation', $accommodation->id, $imageData);
                $accommodation->update([
                    'gallery_images' => $updatedImageData['gallery_images'],
                    'thumbnail_path' => $updatedImageData['thumbnail_path']
                ]);
            }

            // Clear caches
            $this->cacheService->clearAccommodationCache($accommodation->id);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $isDraft ? 'Draft updated successfully!' : 'Accommodation updated successfully!',
                    'accommodation_id' => $accommodation->id,
                    'redirect_url' => $request->input('target_redirect') ?? route('admin.accommodations.index'),
                ]);
            }

            return redirect()->route('admin.accommodations.index')
                ->with('success', 'Accommodation updated successfully.');
        } catch (\Exception $e) {
            Log::error('AccommodationsController::update - Error updating accommodation', [
                'accommodation_id' => $accommodation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating accommodation: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating accommodation: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage with cache clearing
     */
    public function destroy(Accommodation $accommodation)
    {
        $accommodation->delete();
        
        // Clear caches
        $this->cacheService->clearAccommodationCache($accommodation->id);

        return redirect()->route('admin.accommodations.index')
            ->with('success', 'Accommodation deleted successfully.');
    }

    /**
     * Activate or deactivate an accommodation (draft <-> active).
     */
    public function changeStatus(Request $request, $id)
    {
        $accommodation = Accommodation::findOrFail($id);
        $requested = $request->input('status');
        $accommodation->status = in_array($requested, ['active', 'draft'], true)
            ? $requested
            : ($accommodation->status === 'active' ? 'draft' : 'active');
        $accommodation->save();

        $this->cacheService->clearAccommodationCache($accommodation->id);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully.',
                'status' => $accommodation->status,
            ]);
        }

        return redirect()->back()->with(
            'success',
            $accommodation->status === 'active'
                ? 'Accommodation activated successfully.'
                : 'Accommodation set to draft successfully.'
        );
    }
}
