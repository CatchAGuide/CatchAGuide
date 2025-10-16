<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\AccommodationType;
use App\Models\AccommodationDetail;
use App\Models\RoomConfiguration;
use App\Models\Facility;
use App\Models\KitchenEquipment;
use App\Models\BathroomAmenity;
use App\Models\AccommodationPolicy;
use App\Models\AccommodationRentalCondition;
use App\Models\AccommodationExtra;
use App\Models\AccommodationInclusive;
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
        $formData = [
            'is_update' => 0,
            'user_id' => Auth::id(),
            'status' => 'active'
        ];
        
        // Use proper Eloquent relationships and scopes - transform data like rental boat form
        $accommodationTypes = AccommodationType::active()->ordered()->get();
        $accommodationDetails = AccommodationDetail::active()->ordered()->get();
        $roomConfigurations = RoomConfiguration::active()->ordered()->get();
        
        // Transform data to have 'value' field for Tagify compatibility
        $facilities = Facility::active()->ordered()->get()->map(function($item) {
            return [
                'value' => $item->name,
                'id' => $item->id
            ];
        });
        
        $kitchenEquipment = KitchenEquipment::active()->ordered()->get()->map(function($item) {
            return [
                'value' => $item->name,
                'id' => $item->id
            ];
        });
        
        $bathroomAmenities = BathroomAmenity::active()->ordered()->get()->map(function($item) {
            return [
                'value' => $item->name,
                'id' => $item->id
            ];
        });
        
        // Step 6 data
        $accommodationPolicies = AccommodationPolicy::active()->ordered()->get();
        $accommodationRentalConditions = AccommodationRentalCondition::active()->ordered()->get();
        $accommodationExtras = AccommodationExtra::active()->ordered()->get();
        $accommodationInclusives = AccommodationInclusive::active()->ordered()->get();
        
        $targetRedirect = route('admin.accommodations.index');
        
        return view('admin.pages.accommodations.create', compact(
            'formData',
            'targetRedirect',
            'accommodationTypes',
            'accommodationDetails', 
            'roomConfigurations',
            'facilities',
            'kitchenEquipment',
            'bathroomAmenities',
            'accommodationPolicies',
            'accommodationRentalConditions',
            'accommodationExtras',
            'accommodationInclusives'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $isDraft = $request->input('is_draft') == '1';
        
        // Different validation rules for draft vs final submission
        // if ($isDraft) {
        //     // Minimal validation for drafts
        //     $validated = $request->validate([
        //         'title' => 'nullable|string|max:255',
        //         'location' => 'nullable|string|max:255',
        //         'accommodation_type' => 'nullable|string|max:255',
        //         'description' => 'nullable|string',
        //         'price_type' => 'nullable|string|in:per_accommodation,per_person',
        //         'status' => 'nullable|string|in:active,inactive,draft',
        //     ]);
        // } else {
        //     // Full validation for final submission
        //     $validated = $request->validate([
        //         'title' => 'required|string|max:255',
        //         'location' => 'required|string|max:255',
        //         'city' => 'nullable|string|max:255',
        //         'country' => 'nullable|string|max:255',
        //         'region' => 'nullable|string|max:255',
        //         'accommodation_type' => 'required|string|max:255',
        //         'description' => 'nullable|string',
        //         'condition_or_style' => 'nullable|string|max:255',
        //         'living_area_sqm' => 'nullable|integer|min:0',
        //         'floor_layout' => 'nullable|string|max:255',
        //         'max_occupancy' => 'nullable|integer|min:1',
        //         'number_of_bedrooms' => 'nullable|integer|min:0',
        //         'kitchen_type' => 'nullable|string|max:255',
        //         'bathroom' => 'nullable|integer|min:0',
        //         'location_description' => 'nullable|string',
        //         'distance_to_water_m' => 'nullable|integer|min:0',
        //         'distance_to_boat_berth_m' => 'nullable|integer|min:0',
        //         'distance_to_shop_km' => 'nullable|numeric|min:0',
        //         'distance_to_parking_m' => 'nullable|integer|min:0',
        //         'distance_to_nearest_town_km' => 'nullable|numeric|min:0',
        //         'distance_to_airport_km' => 'nullable|numeric|min:0',
        //         'distance_to_ferry_port_km' => 'nullable|numeric|min:0',
        //         'changeover_day' => 'nullable|string|max:255',
        //         'minimum_stay_nights' => 'nullable|integer|min:1',
        //         'price_type' => 'required|string|in:per_accommodation,per_person',
        //         'price_per_night' => 'nullable|numeric|min:0',
        //         'price_per_week' => 'nullable|numeric|min:0',
        //         'currency' => 'nullable|string|max:3',
        //         'lat' => 'nullable|numeric|between:-90,90',
        //         'lng' => 'nullable|numeric|between:-180,180',
        //         'status' => 'nullable|string|in:active,inactive,draft',
        //     ]);
        // }

        try {
            $slug = $this->generateUniqueSlug($request->title ?? 'Untitled');

            // Process form data
            $accommodationData = [
                'user_id' => $request->user_id ?? Auth::id(),
                'title' => $request->title ?? 'Untitled',
                'slug' => $slug,
                'location' => $request->location ?? '',
                'city' => $request->city ?? '',
                'country' => $request->country ?? '',
                'region' => $request->region ?? '',
                'lat' => $request->latitude ?? null,
                'lng' => $request->longitude ?? null,
                'accommodation_type' => $request->accommodation_type ?? '',
                'description' => $request->description ?? '',
                'condition_or_style' => $request->condition_or_style ?? '',
                'living_area_sqm' => $request->living_area_sqm ?? null,
                'floor_layout' => $request->floor_layout ?? '',
                'max_occupancy' => $request->max_occupancy ?? null,
                'number_of_bedrooms' => $request->number_of_bedrooms ?? null,
                'kitchen_type' => $request->kitchen_type ?? '',
                'bathroom' => $request->bathroom ?? null,
                'location_description' => $request->location_description ?? '',
                'distance_to_water_m' => $request->distance_to_water_m ?? null,
                'distance_to_boat_berth_m' => $request->distance_to_boat_berth_m ?? null,
                'distance_to_shop_km' => $request->distance_to_shop_km ?? null,
                'distance_to_parking_m' => $request->distance_to_parking_m ?? null,
                'distance_to_nearest_town_km' => $request->distance_to_nearest_town_km ?? null,
                'distance_to_airport_km' => $request->distance_to_airport_km ?? null,
                'distance_to_ferry_port_km' => $request->distance_to_ferry_port_km ?? null,
                'changeover_day' => $request->changeover_day ?? '',
                'minimum_stay_nights' => $request->minimum_stay_nights ?? null,
                'status' => $isDraft ? 'draft' : ($request->status ?? 'active'),
                'price_type' => $request->price_type ?? null,
                'price_per_night' => $request->price_per_night ?? null,
                'price_per_week' => $request->price_per_week ?? null,
                'currency' => $request->currency ?? 'EUR',
                'amenities' => $this->processFacilities($request),
                'kitchen_equipment' => $this->processKitchenEquipmentTagify($request),
                'bathroom_amenities' => $this->processBathroomAmenitiesTagify($request),
                'accommodation_details' => $this->processAccommodationDetails($request),
                'room_configurations' => $this->processRoomConfigurations($request),
                'policies' => $this->processPoliciesTagify($request),
                'rental_conditions' => $this->processRentalConditionsTagify($request),
                'extras' => $this->processExtrasTagify($request),
                'inclusives' => $this->processInclusivesTagify($request),
                'bed_types' => $this->processBedTypes($request),
                'per_person_pricing' => $this->processPerPersonPricing($request),
            ];
            
            $imageData = $this->processImageUploads($request, $slug, null);
            
            if ($imageData) {
                $accommodationData['thumbnail_path'] = $imageData['thumbnail_path'];
                $accommodationData['gallery_images'] = $imageData['gallery_images'];
            }

            $accommodation = Accommodation::create($accommodationData);

            // Move images from temp directory to final directory if they were created
            if ($imageData && $accommodation->id) {
                $this->moveImagesToFinalDirectory($accommodation->id, $accommodation->slug, $imageData);
                
                // Log final state after moving
                $accommodation->refresh();
            }

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
        // Prepare form data similar to create method
        $accommodationTypes = AccommodationType::active()->ordered()->get();
        $accommodationDetails = AccommodationDetail::active()->ordered()->get();
        $roomConfigurations = RoomConfiguration::active()->ordered()->get();
        
        // Transform data to have 'value' field for Tagify compatibility
        $facilities = Facility::active()->ordered()->get()->map(function($item) {
            return [
                'value' => $item->name,
                'id' => $item->id
            ];
        });
        
        $kitchenEquipment = KitchenEquipment::active()->ordered()->get()->map(function($item) {
            return [
                'value' => $item->name,
                'id' => $item->id
            ];
        });
        
        $bathroomAmenities = BathroomAmenity::active()->ordered()->get()->map(function($item) {
            return [
                'value' => $item->name,
                'id' => $item->id
            ];
        });
        
        // Step 6 data
        $accommodationPolicies = AccommodationPolicy::active()->ordered()->get();
        $accommodationRentalConditions = AccommodationRentalCondition::active()->ordered()->get();
        $accommodationExtras = AccommodationExtra::active()->ordered()->get();
        $accommodationInclusives = AccommodationInclusive::active()->ordered()->get();
        
        return view('admin.pages.accommodations.edit', compact(
            'accommodation',
            'accommodationTypes',
            'accommodationDetails',
            'roomConfigurations',
            'facilities',
            'kitchenEquipment',
            'bathroomAmenities',
            'accommodationPolicies',
            'accommodationRentalConditions',
            'accommodationExtras',
            'accommodationInclusives'
        ));
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

        // Process Tagify fields
        $validated['amenities'] = $this->processFacilities($request);
        $validated['kitchen_equipment'] = $this->processKitchenEquipmentTagify($request);
        $validated['bathroom_amenities'] = $this->processBathroomAmenitiesTagify($request);
        $validated['accommodation_details'] = $this->processAccommodationDetails($request);
        $validated['room_configurations'] = $this->processRoomConfigurations($request);
        $validated['policies'] = $this->processPoliciesTagify($request);
        $validated['rental_conditions'] = $this->processRentalConditionsTagify($request);
        $validated['extras'] = $this->processExtrasTagify($request);
        $validated['inclusives'] = $this->processInclusivesTagify($request);

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

    /**
     * Generate unique slug for accommodation
     */
    private function generateUniqueSlug($title, $excludeId = null)
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        $query = Accommodation::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
            
            $query = Accommodation::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }

    /**
     * Process amenities data
     */
    private function processAmenities($request)
    {
        $amenities = [];
        
        $amenityFields = [
            'terrace', 'garden', 'swimming_pool', 'private_jetty_boat_dock', 
            'fish_cleaning_station', 'smoker', 'barbecue_area', 'lockable_storage_fishing',
            'wifi', 'fireplace_stove', 'sauna', 'pool_hot_tub', 'games_corner',
            'balcony', 'garden_furniture_sun_loungers', 'parking_spaces',
            'charging_station_electric_cars', 'boat_ramp_nearby', 'tv',
            'sound_system', 'reception', 'keybox', 'heating', 'air_conditioning'
        ];

        foreach ($amenityFields as $field) {
            $amenities[$field] = $request->has($field);
        }

        return $amenities;
    }

    /**
     * Process kitchen equipment data
     */
    private function processKitchenEquipment($request)
    {
        $kitchenEquipment = [];
        
        $kitchenFields = [
            'oven', 'stove_or_ceramic_hob', 'microwave', 'dishwasher', 'coffee_machine',
            'cookware_and_dishes', 'refrigerator', 'freezer', 'kettle', 'toaster',
            'blender_hand_mixer', 'basic_cooking_supplies', 'kitchen_utensils',
            'baking_equipment', 'dishwashing_items', 'wine_glasses'
        ];

        foreach ($kitchenFields as $field) {
            $kitchenEquipment[$field] = $request->has($field);
        }

        return $kitchenEquipment;
    }

    /**
     * Process bathroom amenities data
     */
    private function processBathroomAmenities($request)
    {
        $bathroomAmenities = [];
        
        $bathroomFields = [
            'iron_and_ironing_board', 'clothes_drying_rack', 'toilet', 'own_shower'
        ];

        foreach ($bathroomFields as $field) {
            $bathroomAmenities[$field] = $request->has($field);
        }

        return $bathroomAmenities;
    }

    /**
     * Process policies data
     */
    private function processPolicies($request)
    {
        $policies = [];
        
        // Handle policy checkboxes with textarea inputs (like boat information)
        if ($request->has('policy_checkboxes')) {
            $policyCheckboxes = $request->input('policy_checkboxes', []);
            
            foreach ($policyCheckboxes as $checkbox) {
                $policies[$checkbox] = [
                    'enabled' => true,
                    'details' => $request->input("policy_{$checkbox}", '')
                ];
            }
        }
        
        // Also handle any remaining individual policy fields for backward compatibility
        $policyFields = [
            'bed_linen_included',
            'pets_allowed',
            'smoking_allowed',
            'towels_included',
            'quiet_hours_no_parties',
            'checkin_checkout_times',
            'children_allowed_child_friendly',
            'accessible_barrier_free',
            'energy_usage_included',
            'water_usage_included',
            'parking_availability'
        ];
        
        foreach ($policyFields as $field) {
            if (!isset($policies[$field])) {
                $policies[$field] = [
                    'enabled' => $request->has($field) ? (bool)$request->input($field) : false,
                    'details' => ''
                ];
            }
        }
        
        return $policies;
    }

    /**
     * Process rental conditions data
     */
    private function processRentalConditions($request)
    {
        return [
            'checkin_checkout_time' => $request->checkin_checkout_time ?? '',
            'self_checkin' => $request->has('self_checkin'),
            'quiet_times' => $request->quiet_times ?? '',
            'waste_disposal_recycling_rules' => $request->waste_disposal_recycling_rules ?? ''
        ];
    }

    /**
     * Process bed types data
     */
    private function processBedTypes($request)
    {
        $bedTypes = [];
        
        // Handle new bed type checkboxes with quantity inputs (like policies)
        if ($request->has('bed_type_checkboxes')) {
            $bedTypeCheckboxes = $request->input('bed_type_checkboxes', []);
            
            foreach ($bedTypeCheckboxes as $bedType) {
                $bedTypes[$bedType] = [
                    'enabled' => true,
                    'quantity' => (int)($request->input("bed_type_{$bedType}", 0))
                ];
            }
        }
        
        // Handle old bed_types structure for backward compatibility
        if ($request->has('bed_types')) {
            foreach ($request->bed_types as $type => $data) {
                if (!isset($bedTypes[$type])) { // Don't override new structure
                    if (isset($data['enabled']) && $data['enabled']) {
                        $bedTypes[$type] = [
                            'enabled' => true,
                            'quantity' => (int)($data['quantity'] ?? 0)
                        ];
                    }
                }
            }
        }

        return $bedTypes;
    }

    /**
     * Process per-person pricing data
     */
    private function processPerPersonPricing($request)
    {
        if ($request->price_type !== 'per_person') {
            return [];
        }

        $pricing = [];
        
        // Process dynamic pricing tiers
        foreach ($request->all() as $key => $value) {
            if (strpos($key, 'guest_count_') === 0) {
                $tierId = str_replace('guest_count_', '', $key);
                $guestCount = (int)$value;
                
                if ($guestCount > 0) {
                    $pricing[$tierId] = [
                        'guest_count' => $guestCount,
                        'price_per_night' => (float)($request->input("price_per_person_night_{$tierId}") ?? 0),
                        'price_per_week' => (float)($request->input("price_per_person_week_{$tierId}") ?? 0)
                    ];
                }
            }
        }

        return $pricing;
    }

    /**
     * Process image uploads with isolated storage strategy
     */
    private function processImageUploads(Request $request, string $slug, ?int $accommodationId = null)
    {
        $galleryImages = [];
        $thumbnailPath = '';
        $imageList = json_decode($request->input('image_list', '[]')) ?? [];
        $processedFilenames = [];

        // Handle existing images for updates
        if ($request->input('is_update') == '1' && $accommodationId) {
            $existingImagesJson = $request->input('existing_images');
            $existingImages = json_decode($existingImagesJson, true) ?? [];
            $keepImages = array_filter($imageList);

            foreach ($existingImages as $existingImage) {
                $imagePath = $existingImage;
                $imagePathWithSlash = '/' . $existingImage;
                if (in_array($imagePath, $keepImages) || in_array($imagePathWithSlash, $keepImages)) {
                    $galleryImages[] = $existingImage;
                } else {
                    // Delete unused images
                    if (file_exists(public_path($existingImage))) {
                        unlink(public_path($existingImage));
                    }
                }
            }
        }

        // Process new file uploads
        if ($request->hasFile('title_image')) {
            $imageCount = count($galleryImages);
            $tempSlug = $slug ?: 'temp-accommodation';

            foreach($request->file('title_image') as $index => $image) {
                $originalFilename = $image->getClientOriginalName();
                $filename = 'accommodations-images/' . $originalFilename;
                
                // Check if this image was already processed
                if (in_array($originalFilename, $processedFilenames)) continue;
                
                // Check if this image is in the image_list
                if (in_array($originalFilename, $imageList) || in_array($filename, $imageList) || in_array('/' . $filename, $imageList)) {
                    $index = $index + $imageCount;
                    $timestamp = time();
                    $filename = $tempSlug . "-" . $index . "-" . $timestamp;
                    
                    // Use isolated directory structure: accommodations/{id}/gallery/
                    $directory = $accommodationId ? "accommodations/{$accommodationId}/gallery" : "accommodations/temp/gallery";
                    
                    // Store the image
                    $path = $image->storeAs($directory, $filename . '.webp', 'public');
                    $galleryImages[] = $path;
                    $processedFilenames[] = $originalFilename;
                }
            }
        }

        // Set the primary image if available
        $primaryImageIndex = $request->input('primaryImage', 0);
        if (isset($galleryImages[$primaryImageIndex])) {
            $thumbnailPath = $galleryImages[$primaryImageIndex];
        }

        return [
            'gallery_images' => $galleryImages,
            'thumbnail_path' => $thumbnailPath
        ];
    }

    /**
     * Move images from temp directory to final directory after accommodation creation
     */
    private function moveImagesToFinalDirectory(int $accommodationId, string $slug, array $imageData)
    {
        $tempDirectory = "accommodations/temp/gallery";
        $finalDirectory = "accommodations/{$accommodationId}/gallery";

        // Ensure final directory exists
        \Storage::disk('public')->makeDirectory($finalDirectory);
        if (!file_exists(public_path($finalDirectory))) {
            mkdir(public_path($finalDirectory), 0755, true);
        }

        $updatedGalleryImages = [];
        $updatedThumbnailPath = '';

        // Move gallery images
        foreach ($imageData['gallery_images'] as $imagePath) {
            if (strpos($imagePath, $tempDirectory) === 0) {
                $filename = basename($imagePath);
                $newPath = $finalDirectory . '/' . $filename;
                
                // Move file from temp to final directory
                if (\Storage::disk('public')->exists($imagePath)) {
                    \Storage::disk('public')->move($imagePath, $newPath);
                }
                
                // Move in public directory as well
                $oldPublicPath = public_path($imagePath);
                $newPublicPath = public_path($newPath);
                if (file_exists($oldPublicPath)) {
                    rename($oldPublicPath, $newPublicPath);
                }
                
                $updatedGalleryImages[] = $newPath;
            } else {
                $updatedGalleryImages[] = $imagePath;
            }
        }

        // Move thumbnail
        if ($imageData['thumbnail_path'] && strpos($imageData['thumbnail_path'], $tempDirectory) === 0) {
            $filename = basename($imageData['thumbnail_path']);
            $newThumbnailPath = $finalDirectory . '/' . $filename;
            
            // Move file from temp to final directory
            if (\Storage::disk('public')->exists($imageData['thumbnail_path'])) {
                \Storage::disk('public')->move($imageData['thumbnail_path'], $newThumbnailPath);
            }
            
            // Move in public directory as well
            $oldPublicPath = public_path($imageData['thumbnail_path']);
            $newPublicPath = public_path($newThumbnailPath);
            if (file_exists($oldPublicPath)) {
                rename($oldPublicPath, $newPublicPath);
            }
            
            $updatedThumbnailPath = $newThumbnailPath;
        } else {
            $updatedThumbnailPath = $imageData['thumbnail_path'];
        }

        // Update the accommodation record with final paths
        $accommodation = Accommodation::find($accommodationId);
        if ($accommodation) {
            $accommodation->gallery_images = $updatedGalleryImages;
            $accommodation->thumbnail_path = $updatedThumbnailPath;
            $accommodation->save();
        }
    }

    /**
     * Process facilities Tagify data
     */
    private function processFacilities($request)
    {
        if (!$request->has('facilities')) {
            return null;
        }

        return is_string($request->facilities) 
            ? explode(',', $request->facilities) 
            : $request->facilities;
    }

    /**
     * Process kitchen equipment Tagify data
     */
    private function processKitchenEquipmentTagify($request)
    {
        if (!$request->has('kitchen_equipment')) {
            return null;
        }

        return is_string($request->kitchen_equipment) 
            ? explode(',', $request->kitchen_equipment) 
            : $request->kitchen_equipment;
    }

    /**
     * Process bathroom amenities Tagify data
     */
    private function processBathroomAmenitiesTagify($request)
    {
        if (!$request->has('bathroom_amenities')) {
            return null;
        }

        return is_string($request->bathroom_amenities) 
            ? explode(',', $request->bathroom_amenities) 
            : $request->bathroom_amenities;
    }

    /**
     * Process accommodation details checkbox data with input fields
     */
    private function processAccommodationDetails($request)
    {
        $accommodationDetails = [];
        
        // Get selected accommodation detail checkboxes
        $selectedDetails = $request->input('accommodation_detail_checkboxes', []);
        
        foreach ($selectedDetails as $detailId) {
            $detailData = [
                'id' => $detailId,
                'value' => $request->input("accommodation_detail_{$detailId}", '')
            ];
            $accommodationDetails[] = $detailData;
        }
        
        return empty($accommodationDetails) ? null : $accommodationDetails;
    }

    /**
     * Process room configurations checkbox data with input fields
     */
    private function processRoomConfigurations($request)
    {
        $roomConfigurations = [];
        
        // Get selected room configuration checkboxes
        $selectedConfigs = $request->input('room_config_checkboxes', []);
        
        foreach ($selectedConfigs as $configId) {
            $configData = [
                'id' => $configId,
                'value' => $request->input("room_config_{$configId}", '')
            ];
            $roomConfigurations[] = $configData;
        }
        
        return empty($roomConfigurations) ? null : $roomConfigurations;
    }

    /**
     * Process policies Tagify data
     */
    private function processPoliciesTagify($request)
    {
        if (!$request->has('policies')) {
            return null;
        }

        return is_string($request->policies) 
            ? explode(',', $request->policies) 
            : $request->policies;
    }

    /**
     * Process rental conditions checkbox data with input fields
     */
    private function processRentalConditionsTagify($request)
    {
        $rentalConditions = [];
        
        // Get selected rental condition checkboxes
        $selectedConditions = $request->input('rental_condition_checkboxes', []);
        
        foreach ($selectedConditions as $conditionId) {
            $conditionData = [
                'id' => $conditionId,
                'value' => $request->input("rental_condition_{$conditionId}", '')
            ];
            $rentalConditions[] = $conditionData;
        }
        
        return empty($rentalConditions) ? null : $rentalConditions;
    }

    /**
     * Process extras Tagify data
     */
    private function processExtrasTagify($request)
    {
        if (!$request->has('extras')) {
            return null;
        }

        return is_string($request->extras) 
            ? explode(',', $request->extras) 
            : $request->extras;
    }

    /**
     * Process inclusives Tagify data
     */
    private function processInclusivesTagify($request)
    {
        if (!$request->has('inclusives')) {
            return null;
        }

        return is_string($request->inclusives) 
            ? explode(',', $request->inclusives) 
            : $request->inclusives;
    }
}
