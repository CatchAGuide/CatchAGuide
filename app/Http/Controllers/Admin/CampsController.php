<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Camp;
use App\Models\CampFacility;
use App\Models\Accommodation;
use App\Models\RentalBoat;
use App\Models\Guiding;
use App\Models\Target;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CampsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $camps = Camp::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.pages.camps.index', compact('camps'));
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
        
        $campFacilities = CampFacility::where('is_active', true)->orderBy('name')->get();
        $accommodations = Accommodation::where('status', 'active')->orderBy('title')->get();
        $rentalBoats = RentalBoat::where('status', 'active')->orderBy('title')->get();
        $guidings = Guiding::where('status', 'active')->orderBy('title')->get();
        $targetFish = Target::orderBy('name')->get();
        
        $targetRedirect = route('admin.camps.index');
        
        return view('admin.pages.camps.create', compact(
            'formData',
            'targetRedirect',
            'campFacilities',
            'accommodations',
            'rentalBoats',
            'guidings',
            'targetFish'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $isDraft = $request->input('is_draft') == '1';
        
        // Different validation rules for draft vs final submission
        if ($isDraft) {
            // Minimal validation for drafts
            $validated = $request->validate([
                'title' => 'nullable|string|max:255',
                'location' => 'nullable|string|max:255',
                'status' => 'nullable|string|in:active,inactive,draft',
            ]);
        } else {
            // Full validation for final submission
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'location' => 'required|string|max:255',
                'description_camp' => 'required|string',
                'description_area' => 'required|string',
                'description_fishing' => 'required|string',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'country' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'region' => 'nullable|string|max:255',
                'distance_to_store' => 'nullable|string|max:255',
                'distance_to_nearest_town' => 'nullable|string|max:255',
                'distance_to_airport' => 'nullable|string|max:255',
                'distance_to_ferry_port' => 'nullable|string|max:255',
                'policies_regulations' => 'nullable|string',
                'target_fish' => 'nullable|string',
                'best_travel_times' => 'nullable|string',
                'travel_information' => 'nullable|string',
                'extras' => 'nullable|string',
                'status' => 'nullable|string|in:active,inactive,draft',
                'camp_facility_checkboxes' => 'nullable|array',
                'camp_facility_checkboxes.*' => 'exists:camp_facilities,id',
                'accommodations' => 'nullable|array',
                'accommodations.*' => 'exists:accommodations,id',
                'rental_boats' => 'nullable|array',
                'rental_boats.*' => 'exists:rental_boats,id',
                'guidings' => 'nullable|array',
                'guidings.*' => 'exists:guidings,id',
                'target_fish' => 'nullable|array',
                'target_fish.*' => 'exists:targets,id',
            ]);
        }

        try {
            DB::beginTransaction();

            // Handle image uploads
            $galleryImages = $this->handleImageUploads($request);
            $thumbnailPath = $galleryImages[0] ?? null;

            // Create camp
            $camp = Camp::create([
                'title' => $validated['title'] ?? '',
                'location' => $validated['location'] ?? '',
                'description_camp' => $request->input('description_camp', ''),
                'description_area' => $request->input('description_area', ''),
                'description_fishing' => $request->input('description_fishing', ''),
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
                'country' => $request->input('country'),
                'city' => $request->input('city'),
                'region' => $request->input('region'),
                'distance_to_store' => $request->input('distance_to_store'),
                'distance_to_nearest_town' => $request->input('distance_to_nearest_town'),
                'distance_to_airport' => $request->input('distance_to_airport'),
                'distance_to_ferry_port' => $request->input('distance_to_ferry_port'),
                'policies_regulations' => $request->input('policies_regulations'),
                'best_travel_times' => $request->input('best_travel_times'),
                'travel_information' => $request->input('travel_information'),
                'extras' => $request->input('extras'),
                'thumbnail_path' => $thumbnailPath,
                'gallery_images' => $galleryImages,
                'status' => $isDraft ? 'draft' : ($validated['status'] ?? 'active'),
                'user_id' => Auth::id(),
            ]);

            // Sync relationships
            if ($request->has('camp_facility_checkboxes')) {
                $camp->facilities()->sync($request->input('camp_facility_checkboxes'));
            }

            if ($request->has('accommodations')) {
                $camp->accommodations()->sync($request->input('accommodations'));
            }

            if ($request->has('rental_boats')) {
                $camp->rentalBoats()->sync($request->input('rental_boats'));
            }

            if ($request->has('guidings')) {
                $camp->guidings()->sync($request->input('guidings'));
            }

            DB::commit();

            $message = $isDraft ? 'Camp draft saved successfully!' : 'Camp created successfully!';
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'redirect' => $request->input('target_redirect', route('admin.camps.index'))
                ]);
            }

            return redirect($request->input('target_redirect', route('admin.camps.index')))
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while saving the camp: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()->with('error', 'An error occurred while saving the camp.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $camp = Camp::with(['user', 'facilities', 'accommodations', 'rentalBoats', 'guidings'])
            ->findOrFail($id);

        return view('admin.pages.camps.show', compact('camp'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $camp = Camp::with(['facilities', 'accommodations', 'rentalBoats', 'guidings'])
            ->findOrFail($id);

        $formData = $camp->toArray();
        $formData['is_update'] = 1;
        $formData['lat'] = $camp->latitude;
        $formData['lng'] = $camp->longitude;
        
        $campFacilities = CampFacility::where('is_active', true)->orderBy('name')->get();
        $accommodations = Accommodation::where('status', 'active')->orderBy('title')->get();
        $rentalBoats = RentalBoat::where('status', 'active')->orderBy('title')->get();
        $guidings = Guiding::where('status', 'active')->orderBy('title')->get();
        $targetFish = Target::orderBy('name')->get();
        
        $targetRedirect = route('admin.camps.index');
        
        return view('admin.pages.camps.edit', compact(
            'formData',
            'targetRedirect',
            'campFacilities',
            'accommodations',
            'rentalBoats',
            'guidings',
            'targetFish'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $camp = Camp::findOrFail($id);
        $isDraft = $request->input('is_draft') == '1';
        
        // Different validation rules for draft vs final submission
        if ($isDraft) {
            // Minimal validation for drafts
            $validated = $request->validate([
                'title' => 'nullable|string|max:255',
                'location' => 'nullable|string|max:255',
                'status' => 'nullable|string|in:active,inactive,draft',
            ]);
        } else {
            // Full validation for final submission
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'location' => 'required|string|max:255',
                'description_camp' => 'required|string',
                'description_area' => 'required|string',
                'description_fishing' => 'required|string',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'country' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'region' => 'nullable|string|max:255',
                'distance_to_store' => 'nullable|string|max:255',
                'distance_to_nearest_town' => 'nullable|string|max:255',
                'distance_to_airport' => 'nullable|string|max:255',
                'distance_to_ferry_port' => 'nullable|string|max:255',
                'policies_regulations' => 'nullable|string',
                'target_fish' => 'nullable|string',
                'best_travel_times' => 'nullable|string',
                'travel_information' => 'nullable|string',
                'extras' => 'nullable|string',
                'status' => 'nullable|string|in:active,inactive,draft',
                'camp_facility_checkboxes' => 'nullable|array',
                'camp_facility_checkboxes.*' => 'exists:camp_facilities,id',
                'accommodations' => 'nullable|array',
                'accommodations.*' => 'exists:accommodations,id',
                'rental_boats' => 'nullable|array',
                'rental_boats.*' => 'exists:rental_boats,id',
                'guidings' => 'nullable|array',
                'guidings.*' => 'exists:guidings,id',
                'target_fish' => 'nullable|array',
                'target_fish.*' => 'exists:targets,id',
            ]);
        }

        try {
            DB::beginTransaction();

            // Handle image uploads
            $galleryImages = $this->handleImageUploads($request, $camp);
            $thumbnailPath = $galleryImages[0] ?? $camp->thumbnail_path;

            // Update camp
            $camp->update([
                'title' => $validated['title'] ?? $camp->title,
                'location' => $validated['location'] ?? $camp->location,
                'description_camp' => $request->input('description_camp', $camp->description_camp),
                'description_area' => $request->input('description_area', $camp->description_area),
                'description_fishing' => $request->input('description_fishing', $camp->description_fishing),
                'latitude' => $request->input('latitude', $camp->latitude),
                'longitude' => $request->input('longitude', $camp->longitude),
                'country' => $request->input('country', $camp->country),
                'city' => $request->input('city', $camp->city),
                'region' => $request->input('region', $camp->region),
                'distance_to_store' => $request->input('distance_to_store', $camp->distance_to_store),
                'distance_to_nearest_town' => $request->input('distance_to_nearest_town', $camp->distance_to_nearest_town),
                'distance_to_airport' => $request->input('distance_to_airport', $camp->distance_to_airport),
                'distance_to_ferry_port' => $request->input('distance_to_ferry_port', $camp->distance_to_ferry_port),
                'policies_regulations' => $request->input('policies_regulations', $camp->policies_regulations),
                'best_travel_times' => $request->input('best_travel_times', $camp->best_travel_times),
                'travel_information' => $request->input('travel_information', $camp->travel_information),
                'extras' => $request->input('extras', $camp->extras),
                'thumbnail_path' => $thumbnailPath,
                'gallery_images' => $galleryImages,
                'status' => $isDraft ? 'draft' : ($validated['status'] ?? $camp->status),
            ]);

            // Sync relationships
            if ($request->has('camp_facility_checkboxes')) {
                $camp->facilities()->sync($request->input('camp_facility_checkboxes'));
            }

            if ($request->has('accommodations')) {
                $camp->accommodations()->sync($request->input('accommodations'));
            }

            if ($request->has('rental_boats')) {
                $camp->rentalBoats()->sync($request->input('rental_boats'));
            }

            if ($request->has('guidings')) {
                $camp->guidings()->sync($request->input('guidings'));
            }

            DB::commit();

            $message = $isDraft ? 'Camp draft updated successfully!' : 'Camp updated successfully!';
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'redirect' => $request->input('target_redirect', route('admin.camps.index'))
                ]);
            }

            return redirect($request->input('target_redirect', route('admin.camps.index')))
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while updating the camp: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()->with('error', 'An error occurred while updating the camp.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $camp = Camp::findOrFail($id);
            
            // Delete associated images
            if ($camp->gallery_images) {
                foreach ($camp->gallery_images as $image) {
                    if (Storage::disk('public')->exists($image)) {
                        Storage::disk('public')->delete($image);
                    }
                }
            }
            
            $camp->delete();

            return redirect()->route('admin.camps.index')
                ->with('success', 'Camp deleted successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while deleting the camp.');
        }
    }

    /**
     * Change camp status
     */
    public function changeStatus($id)
    {
        try {
            $camp = Camp::findOrFail($id);
            
            $newStatus = $camp->status === 'active' ? 'inactive' : 'active';
            $camp->update(['status' => $newStatus]);
            
            $message = $newStatus === 'active' ? 'Camp activated successfully!' : 'Camp deactivated successfully!';
            
            return redirect()->route('admin.camps.index')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while changing camp status.');
        }
    }

    /**
     * Handle image uploads for camps
     */
    private function handleImageUploads(Request $request, $existingCamp = null)
    {
        $galleryImages = [];
        
        // Handle new image uploads
        if ($request->hasFile('title_image')) {
            foreach ($request->file('title_image') as $image) {
                $path = $image->store('camps/gallery', 'public');
                $galleryImages[] = $path;
            }
        }
        
        // Handle existing images
        if ($existingCamp && $existingCamp->gallery_images) {
            $galleryImages = array_merge($galleryImages, $existingCamp->gallery_images);
        }
        
        // Handle cropped images
        if ($request->hasFile('cropped_image')) {
            foreach ($request->file('cropped_image') as $image) {
                $path = $image->store('camps/gallery', 'public');
                $galleryImages[] = $path;
            }
        }
        
        return $galleryImages;
    }
}
