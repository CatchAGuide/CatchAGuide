<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class AccommodationsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $accommodations = Accommodation::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.pages.accommodations.index', compact('accommodations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.pages.accommodations.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'accommodation_type' => 'required|string|max:255',
            'description' => 'nullable|string',
            'condition_or_style' => 'nullable|string|max:255',
            'living_area_sqm' => 'nullable|integer|min:0',
            'floor_layout' => 'nullable|string|max:255',
            'max_occupancy' => 'nullable|integer|min:1',
            'number_of_bedrooms' => 'nullable|integer|min:0',
            'number_of_beds' => 'nullable|integer|min:0',
            'kitchen_type' => 'nullable|string|max:255',
            'bathroom' => 'nullable|integer|min:0',
            'location_description' => 'nullable|string',
            'distance_to_water_m' => 'nullable|integer|min:0',
            'distance_to_boat_berth_m' => 'nullable|integer|min:0',
            'distance_to_shop_km' => 'nullable|numeric|min:0',
            'distance_to_parking_m' => 'nullable|integer|min:0',
            'distance_to_nearest_town_km' => 'nullable|numeric|min:0',
            'distance_to_airport_km' => 'nullable|numeric|min:0',
            'distance_to_ferry_port_km' => 'nullable|numeric|min:0',
            'changeover_day' => 'nullable|string|max:255',
            'minimum_stay_nights' => 'nullable|integer|min:1',
            'price_per_night' => 'nullable|numeric|min:0',
            'price_per_week' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        // Generate slug from title
        $validated['slug'] = Str::slug($validated['title']);
        
        // Ensure slug is unique
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (Accommodation::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Add user_id
        $validated['user_id'] = Auth::id();

        // Handle boolean fields
        $booleanFields = [
            'living_room', 'dining_room_or_area', 'terrace', 'garden', 'swimming_pool',
            'refrigerator_freezer', 'oven', 'stove_or_ceramic_hob', 'microwave',
            'dishwasher', 'coffee_machine', 'cookware_and_dishes', 'washing_machine',
            'dryer', 'separate_laundry_room', 'freezer_room', 'filleting_house',
            'wifi_or_internet', 'bed_linen_included', 'utilities_included',
            'pets_allowed', 'smoking_allowed', 'reception_available'
        ];

        foreach ($booleanFields as $field) {
            $validated[$field] = $request->has($field);
        }

        // Handle array fields
        if ($request->has('bed_types')) {
            $validated['bed_types'] = is_array($request->bed_types) ? $request->bed_types : [];
        }

        if ($request->has('rental_includes')) {
            $validated['rental_includes'] = is_array($request->rental_includes) ? $request->rental_includes : [];
        }

        // Handle file uploads
        if ($request->hasFile('thumbnail_path')) {
            $validated['thumbnail_path'] = $request->file('thumbnail_path')->store('accommodations/thumbnails', 'public');
        }

        if ($request->hasFile('gallery_images')) {
            $galleryPaths = [];
            foreach ($request->file('gallery_images') as $file) {
                $galleryPaths[] = $file->store('accommodations/gallery', 'public');
            }
            $validated['gallery_images'] = $galleryPaths;
        }

        $accommodation = Accommodation::create($validated);

        return redirect()->route('admin.accommodations.show', $accommodation)
            ->with('success', 'Accommodation created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Accommodation $accommodation)
    {
        $accommodation->load('user');
        return view('admin.pages.accommodations.show', compact('accommodation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Accommodation $accommodation)
    {
        return view('admin.pages.accommodations.edit', compact('accommodation'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Accommodation $accommodation)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'accommodation_type' => 'required|string|max:255',
            'description' => 'nullable|string',
            'condition_or_style' => 'nullable|string|max:255',
            'living_area_sqm' => 'nullable|integer|min:0',
            'floor_layout' => 'nullable|string|max:255',
            'max_occupancy' => 'nullable|integer|min:1',
            'number_of_bedrooms' => 'nullable|integer|min:0',
            'number_of_beds' => 'nullable|integer|min:0',
            'kitchen_type' => 'nullable|string|max:255',
            'bathroom' => 'nullable|integer|min:0',
            'location_description' => 'nullable|string',
            'distance_to_water_m' => 'nullable|integer|min:0',
            'distance_to_boat_berth_m' => 'nullable|integer|min:0',
            'distance_to_shop_km' => 'nullable|numeric|min:0',
            'distance_to_parking_m' => 'nullable|integer|min:0',
            'distance_to_nearest_town_km' => 'nullable|numeric|min:0',
            'distance_to_airport_km' => 'nullable|numeric|min:0',
            'distance_to_ferry_port_km' => 'nullable|numeric|min:0',
            'changeover_day' => 'nullable|string|max:255',
            'minimum_stay_nights' => 'nullable|integer|min:1',
            'price_per_night' => 'nullable|numeric|min:0',
            'price_per_week' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        // Generate slug from title if title changed
        if ($accommodation->title !== $validated['title']) {
            $validated['slug'] = Str::slug($validated['title']);
            
            // Ensure slug is unique
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Accommodation::where('slug', $validated['slug'])->where('id', '!=', $accommodation->id)->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        // Handle boolean fields
        $booleanFields = [
            'living_room', 'dining_room_or_area', 'terrace', 'garden', 'swimming_pool',
            'refrigerator_freezer', 'oven', 'stove_or_ceramic_hob', 'microwave',
            'dishwasher', 'coffee_machine', 'cookware_and_dishes', 'washing_machine',
            'dryer', 'separate_laundry_room', 'freezer_room', 'filleting_house',
            'wifi_or_internet', 'bed_linen_included', 'utilities_included',
            'pets_allowed', 'smoking_allowed', 'reception_available'
        ];

        foreach ($booleanFields as $field) {
            $validated[$field] = $request->has($field);
        }

        // Handle array fields
        if ($request->has('bed_types')) {
            $validated['bed_types'] = is_array($request->bed_types) ? $request->bed_types : [];
        }

        if ($request->has('rental_includes')) {
            $validated['rental_includes'] = is_array($request->rental_includes) ? $request->rental_includes : [];
        }

        // Handle file uploads
        if ($request->hasFile('thumbnail_path')) {
            $validated['thumbnail_path'] = $request->file('thumbnail_path')->store('accommodations/thumbnails', 'public');
        }

        if ($request->hasFile('gallery_images')) {
            $galleryPaths = [];
            foreach ($request->file('gallery_images') as $file) {
                $galleryPaths[] = $file->store('accommodations/gallery', 'public');
            }
            $validated['gallery_images'] = $galleryPaths;
        }

        $accommodation->update($validated);

        return redirect()->route('admin.accommodations.show', $accommodation)
            ->with('success', 'Accommodation updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Accommodation $accommodation)
    {
        $accommodation->delete();

        return redirect()->route('admin.accommodations.index')
            ->with('success', 'Accommodation deleted successfully.');
    }
}
