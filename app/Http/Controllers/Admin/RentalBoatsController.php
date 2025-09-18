<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RentalBoat;
use App\Models\User;
use App\Models\BoatExtras;
use App\Models\Inclussion;
use App\Models\GuidingBoatType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\GuidingBoatDescription;
use Illuminate\Support\Facades\Config;

class RentalBoatsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $rentalBoats = RentalBoat::with('user')->get();
        return view('admin.pages.rental-boats.index', compact('rentalBoats'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $formData = [
            'is_update' => 0,
            'user_id' => Auth::id(),
            'status' => 'active'
        ];
        
        return view('admin.pages.rental-boats.create', array_merge([
            'pageTitle' => 'Create Rental Boat',
            'formData' => $formData,
            'targetRedirect' => route('admin.rental-boats.index'),
        ], $this->getFormData()));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $isDraft = $request->input('is_draft') == '1';
        
        // Different validation rules for draft vs final submission
        if ($isDraft) {
            // Minimal validation for drafts
            $request->validate([
                'title' => 'nullable|string|max:255',
                'location' => 'nullable|string|max:255',
                'boat_type' => 'nullable|string|max:255',
                'desc_of_boat' => 'nullable|string',
                'price_type' => 'nullable|string|in:per_hour,per_day,per_week',
                'base_price' => 'nullable|numeric|min:0',
                'status' => 'nullable|string|in:active,inactive,draft',
            ]);
        } else {
            // Full validation for final submission
            $request->validate([
                'title' => 'required|string|max:255',
                'location' => 'required|string|max:255',
                'boat_type' => 'required|string|max:255',
                'desc_of_boat' => 'required|string',
                'price_type' => 'required|string|in:per_hour,per_day,per_week',
                'base_price' => 'required|numeric|min:0',
                'status' => 'required|string|in:active,inactive,draft',
            ]);
        }

        try {
            $slug = $this->generateUniqueSlug($request->title ?? 'Untitled');

            // Process form data
            $rentalBoatData = [
                'user_id' => $request->user_id ?? Auth::id(),
                'title' => $request->title ?? 'Untitled',
                'slug' => $slug,
                'location' => $request->location ?? '',
                'city' => $request->city ?? '',
                'country' => $request->country ?? '',
                'region' => $request->region ?? '',
                'lat' => $request->latitude ?? null,
                'lng' => $request->longitude ?? null,
                'boat_type' => $this->processBoatType($request->boat_type),
                'desc_of_boat' => $request->desc_of_boat ?? '',
                'requirements' => $request->requirements ?? '',
                'status' => $isDraft ? 'draft' : ($request->status ?? 'active'),
                'price_type' => $request->price_type ?? null,
                'boat_information' => $this->processBoatInformation($request),
                'boat_extras' => $this->processBoatExtras($request),
                'inclusions' => $this->processInclusions($request),
                'prices' => $this->processPricing($request),
            ];

            // Process images
            \Log::info('RentalBoatsController::store - Starting image processing', [
                'has_files' => $request->hasFile('title_image'),
                'file_count' => $request->hasFile('title_image') ? count($request->file('title_image')) : 0,
                'slug' => $slug
            ]);
            
            $imageData = $this->processImageUploads($request, $slug, null);
            \Log::info('RentalBoatsController::store - Image processing result', [
                'image_data' => $imageData,
                'has_thumbnail' => !empty($imageData['thumbnail_path']),
                'gallery_count' => is_array($imageData['gallery_images']) ? count($imageData['gallery_images']) : 0
            ]);
            
            if ($imageData) {
                $rentalBoatData['thumbnail_path'] = $imageData['thumbnail_path'];
                $rentalBoatData['gallery_images'] = $imageData['gallery_images'];
                \Log::info('RentalBoatsController::store - Added image data to rental boat data', [
                    'thumbnail_path' => $rentalBoatData['thumbnail_path'],
                    'gallery_images' => $rentalBoatData['gallery_images']
                ]);
            }

            $rentalBoat = RentalBoat::create($rentalBoatData);
            \Log::info('RentalBoatsController::store - Rental boat created', [
                'rental_boat_id' => $rentalBoat->id,
                'thumbnail_path' => $rentalBoat->thumbnail_path,
                'gallery_images' => $rentalBoat->gallery_images
            ]);

            // Move images from temp directory to final directory if they were created
            if ($imageData && $rentalBoat->id) {
                \Log::info('RentalBoatsController::store - Moving images to final directory', [
                    'rental_boat_id' => $rentalBoat->id,
                    'image_data' => $imageData
                ]);
                $this->moveImagesToFinalDirectory($rentalBoat->id, $rentalBoat->slug, $imageData);
                
                // Log final state after moving
                $rentalBoat->refresh();
                \Log::info('RentalBoatsController::store - Final rental boat state after image move', [
                    'rental_boat_id' => $rentalBoat->id,
                    'thumbnail_path' => $rentalBoat->thumbnail_path,
                    'gallery_images' => $rentalBoat->gallery_images
                ]);
            }

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
     *
     * @param  \App\Models\RentalBoat  $rentalBoat
     * @return \Illuminate\Http\Response
     */
    public function show(RentalBoat $rentalBoat)
    {
        $rentalBoat->load('user');
        return view('admin.pages.rental-boats.show', compact('rentalBoat'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\RentalBoat  $rentalBoat
     * @return \Illuminate\Http\Response
     */
    public function edit(RentalBoat $rentalBoat)
    {
        return view('admin.pages.rental-boats.edit', array_merge([
            'pageTitle' => 'Edit Rental Boat',
            'formData' => $this->prepareEditFormData($rentalBoat),
            'targetRedirect' => route('admin.rental-boats.index'),
        ], $this->getFormData()));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RentalBoat  $rentalBoat
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RentalBoat $rentalBoat)
    {
        $isDraft = $request->input('is_draft') == '1';
        
        // Different validation rules for draft vs final submission
        if ($isDraft) {
            // Minimal validation for drafts
            $request->validate([
                'title' => 'nullable|string|max:255',
                'location' => 'nullable|string|max:255',
                'boat_type' => 'nullable|string|max:255',
                'desc_of_boat' => 'nullable|string',
                'price_type' => 'nullable|string|in:per_hour,per_day,per_week',
                'base_price' => 'nullable|numeric|min:0',
                'status' => 'nullable|string|in:active,inactive,draft',
            ]);
        } else {
            // Full validation for final submission
            $request->validate([
                'title' => 'required|string|max:255',
                'location' => 'required|string|max:255',
                'boat_type' => 'required|string|max:255',
                'desc_of_boat' => 'required|string',
                'price_type' => 'required|string|in:per_hour,per_day,per_week',
                'base_price' => 'required|numeric|min:0',
                'status' => 'required|string|in:active,inactive,draft',
            ]);
        }

        try {
            // Generate slug from title if title changed
            if ($rentalBoat->title !== $request->title && $request->title) {
                $rentalBoat->slug = $this->generateUniqueSlug($request->title, $rentalBoat->id);
            }

            // Update basic fields
            if ($request->title) $rentalBoat->title = $request->title;
            if ($request->location) $rentalBoat->location = $request->location;
            if ($request->city) $rentalBoat->city = $request->city;
            if ($request->country) $rentalBoat->country = $request->country;
            if ($request->region) $rentalBoat->region = $request->region;
            if ($request->latitude) $rentalBoat->lat = $request->latitude;
            if ($request->longitude) $rentalBoat->lng = $request->longitude;
            if ($request->boat_type) $rentalBoat->boat_type = $this->processBoatType($request->boat_type);
            if ($request->desc_of_boat) $rentalBoat->desc_of_boat = $request->desc_of_boat;
            if ($request->requirements !== null) $rentalBoat->requirements = $request->requirements;
            if ($request->price_type) $rentalBoat->price_type = $request->price_type;
            
            // Handle status - only update if not a draft or if explicitly provided
            if (!$isDraft && $request->status) {
                $rentalBoat->status = $request->status;
            } elseif ($isDraft) {
                $rentalBoat->status = 'draft';
            }

            // Update boat information and other fields
            $rentalBoat->boat_information = $this->processBoatInformation($request);
            $rentalBoat->boat_extras = $this->processBoatExtras($request);
            $rentalBoat->inclusions = $this->processInclusions($request);
            $rentalBoat->prices = $this->processPricing($request);

            // Process images for updates
            \Log::info('RentalBoatsController::update - Starting image processing', [
                'rental_boat_id' => $rentalBoat->id,
                'has_files' => $request->hasFile('title_image'),
                'file_count' => $request->hasFile('title_image') ? count($request->file('title_image')) : 0,
                'slug' => $rentalBoat->slug
            ]);
            
            $imageData = $this->processImageUploads($request, $rentalBoat->slug, $rentalBoat->id);
            \Log::info('RentalBoatsController::update - Image processing result', [
                'image_data' => $imageData,
                'has_thumbnail' => !empty($imageData['thumbnail_path']),
                'gallery_count' => is_array($imageData['gallery_images']) ? count($imageData['gallery_images']) : 0
            ]);
            
            if ($imageData) {
                $rentalBoat->thumbnail_path = $imageData['thumbnail_path'];
                $rentalBoat->gallery_images = $imageData['gallery_images'];
                \Log::info('RentalBoatsController::update - Updated rental boat with image data', [
                    'thumbnail_path' => $rentalBoat->thumbnail_path,
                    'gallery_images' => $rentalBoat->gallery_images
                ]);
            }

            $rentalBoat->save();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $isDraft ? 'Draft saved successfully!' : 'Rental boat updated successfully!',
                    'rental_boat_id' => $rentalBoat->id,
                    'redirect_url' => $request->input('target_redirect') ?? route('admin.rental-boats.index'),
                ]);
            }

            return redirect()->route('admin.rental-boats.index')
                ->with('success', 'Rental boat updated successfully.');
        } catch (\Exception $e) {
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
     *
     * @param  \App\Models\RentalBoat  $rentalBoat
     * @return \Illuminate\Http\Response
     */
    public function destroy(RentalBoat $rentalBoat)
    {
        $rentalBoat->delete();
        
        return redirect()->route('admin.rental-boats.index')
            ->with('success', 'Rental boat deleted successfully.');
    }

    /**
     * Change the status of a rental boat
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus($id)
    {
        $rentalBoat = RentalBoat::findOrFail($id);
        
        $rentalBoat->status = $rentalBoat->status === 'active' ? 'inactive' : 'active';
        $rentalBoat->save();
        
        $statusText = $rentalBoat->status === 'active' ? 'activated' : 'deactivated';
        
        return back()->with('success', "Rental boat {$statusText} successfully.");
    }

    private function getFormData()
    {
        $locale = Config::get('app.locale');
        $nameField = $locale == 'en' ? 'name_en' : 'name';

        return [
            'rentalBoatTypes' => GuidingBoatType::all()->map(function($item) use ($nameField) {
                return [
                    'value' => $item->$nameField,
                    'id' => $item->id
                ];
            }),
            'boatExtras' => BoatExtras::all()->map(function($item) use ($nameField) {
                return [
                    'value' => $item->$nameField,
                    'id' => $item->id
                ];
            }),
            'inclusions' => Inclussion::all()->map(function($item) use ($nameField) {
                return [
                    'value' => $item->$nameField,
                    'id' => $item->id
                ];
            }),
            'guiding_boat_descriptions' => GuidingBoatDescription::all()->map(function($item) use ($nameField) {
                return [
                    'value' => $item->$nameField,
                    'id' => $item->id
                ];
            })
        ];
    }

    private function processBoatType($boatTypeValue)
    {
        // If it's already a numeric ID, return it
        if (is_numeric($boatTypeValue)) {
            return $boatTypeValue;
        }
        
        // If it's a name, find the corresponding ID
        $boatType = GuidingBoatType::where('name', $boatTypeValue)
            ->orWhere('name_en', $boatTypeValue)
            ->first();
            
        return $boatType ? $boatType->id : $boatTypeValue;
    }

    private function processBoatInformation($request)
    {
        $boatInformation = [];
        
        // Add boat info checkboxes (Length, Capacity, Engine, etc.)
        if ($request->has('boat_info_checkboxes')) {
            $boatInfoCheckboxes = $request->input('boat_info_checkboxes', []);
            $boatInfoData = [];

            foreach ($boatInfoCheckboxes as $checkbox) {
                $boatInfoData[$checkbox] = $request->input("boat_info_".$checkbox);
            }

            $boatInformation = array_merge($boatInformation, $boatInfoData);
        }


        return $boatInformation;
    }

    private function processBoatExtras($request)
    {
        if (!$request->has('boat_extras')) {
            return null;
        }

        return is_string($request->boat_extras) 
            ? explode(',', $request->boat_extras) 
            : $request->boat_extras;
    }

    private function processInclusions($request)
    {
        if (!$request->has('inclusions')) {
            return null;
        }

        return is_string($request->inclusions) 
            ? explode(',', $request->inclusions) 
            : $request->inclusions;
    }

    private function processPricing($request)
    {
        $pricing = [
            'base_price' => $request->base_price
        ];

        if ($request->has('extra_pricing')) {
            $pricing['pricing_extra'] = $request->extra_pricing;
        }

        return $pricing;
    }

    private function prepareEditFormData($rentalBoat)
    {

        // Debug: Check what's actually in the database
        \Log::info('Raw database data:', [
            'boat_extras_raw' => $rentalBoat->getRawOriginal('boat_extras'),
            'inclusions_raw' => $rentalBoat->getRawOriginal('inclusions'),
            'boat_extras_casted' => $rentalBoat->boat_extras,
            'inclusions_casted' => $rentalBoat->inclusions
        ]);

        // Fix broken JSON arrays
        $boatInformation = $rentalBoat->boat_information;
        
        // Fix boat_extras - reconstruct JSON from array fragments
        $boatExtras = $rentalBoat->boat_extras;
        if (is_array($boatExtras) && !empty($boatExtras)) {
            $firstItem = reset($boatExtras);
            if (is_string($firstItem) && strpos($firstItem, '[{"value"') === 0) {
                // Reconstruct the JSON by joining all fragments
                $jsonString = implode('', $boatExtras);
                // Fix missing commas between key-value pairs and objects
                $jsonString = preg_replace('/"value":"([^"]+)""id":(\d+)/', '"value":"\1","id":\2', $jsonString); // Fix missing comma between value and id
                $jsonString = preg_replace('/\}\{/', '},{', $jsonString); // Fix missing comma between objects
                \Log::info('Reconstructed boat_extras JSON:', [
                    'original' => $boatExtras,
                    'reconstructed' => $jsonString,
                    'decoded' => json_decode($jsonString, true)
                ]);
                $decoded = json_decode($jsonString, true);
                if (is_array($decoded)) {
                    $boatExtras = array_column($decoded, 'value');
                }
            }
        }
        
        // Fix inclusions - reconstruct JSON from array fragments
        $inclusions = $rentalBoat->inclusions;
        if (is_array($inclusions) && !empty($inclusions)) {
            $firstItem = reset($inclusions);
            if (is_string($firstItem) && strpos($firstItem, '[{"value"') === 0) {
                // Reconstruct the JSON by joining all fragments
                $jsonString = implode('', $inclusions);
                // Fix missing commas between key-value pairs and objects
                $jsonString = preg_replace('/"value":"([^"]+)""id":(\d+)/', '"value":"\1","id":\2', $jsonString); // Fix missing comma between value and id
                $jsonString = preg_replace('/\}\{/', '},{', $jsonString); // Fix missing comma between objects
                $decoded = json_decode($jsonString, true);
                if (is_array($decoded)) {
                    $inclusions = array_column($decoded, 'value');
                }
            }
        }

        // Convert boat_type ID to name for form display
        $boatTypeName = $rentalBoat->boat_type;
        if (is_numeric($rentalBoat->boat_type)) {
            $boatType = GuidingBoatType::find($rentalBoat->boat_type);
            if ($boatType) {
                $locale = Config::get('app.locale');
                $nameField = $locale == 'en' ? 'name_en' : 'name';
                $boatTypeName = $boatType->$nameField;
            }
        }

        // Log the fixed data
        \Log::info('Fixed data:', [
            'boat_extras' => $boatExtras,
            'inclusions' => $inclusions
        ]);

        return [
            'id' => $rentalBoat->id,
            'is_update' => 1,
            'user_id' => $rentalBoat->user_id,
            'title' => $rentalBoat->title,
            'slug' => $rentalBoat->slug,
            'location' => $rentalBoat->location,
            'city' => $rentalBoat->city,
            'country' => $rentalBoat->country,
            'region' => $rentalBoat->region,
            'lat' => $rentalBoat->lat,
            'lng' => $rentalBoat->lng,
            'boat_type' => $boatTypeName,
            'desc_of_boat' => $rentalBoat->desc_of_boat,
            'requirements' => $rentalBoat->requirements,
            'boat_information' => $boatInformation,
            'boat_extras' => $boatExtras,
            'inclusions' => $inclusions,
            'price_type' => $rentalBoat->price_type,
            'prices' => $rentalBoat->prices,
            'pricing_extra' => $rentalBoat->pricing_extra,
            'status' => $rentalBoat->status,
            'thumbnail_path' => $rentalBoat->thumbnail_path,
            'gallery_images' => $rentalBoat->gallery_images,
        ];
    }

    /**
     * Generate unique slug for rental boat
     *
     * @param string $title
     * @param int|null $excludeId
     * @return string
     */
    private function generateUniqueSlug($title, $excludeId = null)
    {
        $slug = \Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        $query = RentalBoat::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
            
            $query = RentalBoat::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }

    /**
     * Process image uploads with isolated storage strategy
     *
     * @param \Illuminate\Http\Request $request
     * @param string $slug
     * @param int|null $rentalBoatId
     * @return array|null
     */
    private function processImageUploads(Request $request, string $slug, ?int $rentalBoatId = null)
    {
        \Log::info('RentalBoatsController::processImageUploads - Starting', [
            'slug' => $slug,
            'rental_boat_id' => $rentalBoatId,
            'is_update' => $request->input('is_update'),
            'has_title_image' => $request->hasFile('title_image'),
            'image_list' => $request->input('image_list'),
            'existing_images' => $request->input('existing_images')
        ]);

        $galleryImages = [];
        $thumbnailPath = '';
        $imageList = json_decode($request->input('image_list', '[]')) ?? [];
        $processedFilenames = [];

        // Handle existing images for updates
        if ($request->input('is_update') == '1' && $rentalBoatId) {
            $existingImagesJson = $request->input('existing_images');
            $existingImages = json_decode($existingImagesJson, true) ?? [];
            $keepImages = array_filter($imageList);

            \Log::info('RentalBoatsController::processImageUploads - Processing existing images', [
                'existing_images' => $existingImages,
                'keep_images' => $keepImages
            ]);

            foreach ($existingImages as $existingImage) {
                $imagePath = $existingImage;
                $imagePathWithSlash = '/' . $existingImage;
                if (in_array($imagePath, $keepImages) || in_array($imagePathWithSlash, $keepImages)) {
                    $galleryImages[] = $existingImage;
                    $processedFilenames[] = basename($existingImage);
                    \Log::info('RentalBoatsController::processImageUploads - Keeping existing image', [
                        'image' => $existingImage
                    ]);
                } else {
                    // Delete removed images
                    \Log::info('RentalBoatsController::processImageUploads - Deleting removed image', [
                        'image' => $existingImage
                    ]);
                    media_delete($existingImage);
                }
            }
        }

        // Process new file uploads
        if ($request->hasFile('title_image')) {
            $imageCount = count($galleryImages);
            $tempSlug = $slug ?: 'temp-rental-boat';
            
            \Log::info('RentalBoatsController::processImageUploads - Processing new file uploads', [
                'file_count' => count($request->file('title_image')),
                'current_gallery_count' => $imageCount,
                'temp_slug' => $tempSlug
            ]);
            
            foreach($request->file('title_image') as $index => $image) {
                $originalFilename = $image->getClientOriginalName();
                $filename = 'rental-boats-images/' . $originalFilename;
                
                \Log::info('RentalBoatsController::processImageUploads - Processing file', [
                    'index' => $index,
                    'original_filename' => $originalFilename,
                    'filename' => $filename,
                    'already_processed' => in_array($originalFilename, $processedFilenames),
                    'original_in_image_list' => in_array($originalFilename, $imageList),
                    'prefixed_in_image_list' => in_array($filename, $imageList),
                    'slash_prefixed_in_image_list' => in_array('/' . $filename, $imageList),
                    'will_process' => in_array($originalFilename, $imageList) || in_array($filename, $imageList) || in_array('/' . $filename, $imageList)
                ]);
                
                // Check if this image was already processed (existing image)
                if (in_array($originalFilename, $processedFilenames)) {
                    \Log::info('RentalBoatsController::processImageUploads - Skipping already processed file', [
                        'filename' => $originalFilename
                    ]);
                    continue;
                }
                
                // Check if this image is in the image_list (new image that should be kept)
                // Check both the original filename and the prefixed filename
                if (in_array($originalFilename, $imageList) || in_array($filename, $imageList) || in_array('/' . $filename, $imageList)) {
                    $index = $index + $imageCount;
                    $timestamp = time();
                    $filename = $tempSlug . "-" . $index . "-" . $timestamp;
                    
                    // Use isolated directory structure: rental-boats/{id}/gallery/
                    $directory = $rentalBoatId ? "rental-boats/{$rentalBoatId}/gallery" : "rental-boats/temp/gallery";
                    
                    \Log::info('RentalBoatsController::processImageUploads - Uploading file', [
                        'directory' => $directory,
                        'filename' => $filename,
                        'rental_boat_id' => $rentalBoatId
                    ]);
                    
                    $webp_path = media_upload($image, $directory, $filename, 75, $rentalBoatId);
                    $galleryImages[] = $webp_path;
                    $processedFilenames[] = $originalFilename;
                    
                    \Log::info('RentalBoatsController::processImageUploads - File uploaded successfully', [
                        'webp_path' => $webp_path,
                        'gallery_images_count' => count($galleryImages)
                    ]);
                } else {
                    \Log::info('RentalBoatsController::processImageUploads - Skipping file not in image list', [
                        'original_filename' => $originalFilename,
                        'filename' => $filename,
                        'image_list' => $imageList
                    ]);
                }
            }
        } else {
            \Log::info('RentalBoatsController::processImageUploads - No title_image files found');
        }

        // Set the primary image if available
        $primaryImageIndex = $request->input('primaryImage', 0);
        if (isset($galleryImages[$primaryImageIndex])) {
            $thumbnailPath = $galleryImages[$primaryImageIndex];
            \Log::info('RentalBoatsController::processImageUploads - Set thumbnail from gallery', [
                'primary_image_index' => $primaryImageIndex,
                'thumbnail_path' => $thumbnailPath
            ]);
        } else {
            \Log::info('RentalBoatsController::processImageUploads - No thumbnail set', [
                'primary_image_index' => $primaryImageIndex,
                'gallery_images_count' => count($galleryImages)
            ]);
        }

        $result = [
            'gallery_images' => $galleryImages,
            'thumbnail_path' => $thumbnailPath
        ];

        \Log::info('RentalBoatsController::processImageUploads - Final result', [
            'gallery_images' => $galleryImages,
            'thumbnail_path' => $thumbnailPath,
            'gallery_count' => count($galleryImages)
        ]);

        return $result;
    }

    /**
     * Move images from temp directory to final directory after rental boat creation
     *
     * @param int $rentalBoatId
     * @param string $slug
     * @param array $imageData
     * @return void
     */
    private function moveImagesToFinalDirectory(int $rentalBoatId, string $slug, array $imageData)
    {
        \Log::info('RentalBoatsController::moveImagesToFinalDirectory - Starting', [
            'rental_boat_id' => $rentalBoatId,
            'slug' => $slug,
            'image_data' => $imageData
        ]);

        $tempDirectory = "rental-boats/temp/gallery";
        $finalDirectory = "rental-boats/{$rentalBoatId}/gallery";
        
        \Log::info('RentalBoatsController::moveImagesToFinalDirectory - Directory setup', [
            'temp_directory' => $tempDirectory,
            'final_directory' => $finalDirectory
        ]);
        
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

        // Update the rental boat record with final paths
        $rentalBoat = RentalBoat::find($rentalBoatId);
        if ($rentalBoat) {
            \Log::info('RentalBoatsController::moveImagesToFinalDirectory - Updating rental boat record', [
                'rental_boat_id' => $rentalBoatId,
                'updated_gallery_images' => $updatedGalleryImages,
                'updated_thumbnail_path' => $updatedThumbnailPath
            ]);
            
            $rentalBoat->gallery_images = $updatedGalleryImages;
            $rentalBoat->thumbnail_path = $updatedThumbnailPath;
            $rentalBoat->save();
            
            \Log::info('RentalBoatsController::moveImagesToFinalDirectory - Rental boat updated successfully', [
                'rental_boat_id' => $rentalBoatId,
                'final_gallery_images' => $rentalBoat->gallery_images,
                'final_thumbnail_path' => $rentalBoat->thumbnail_path
            ]);
        } else {
            \Log::error('RentalBoatsController::moveImagesToFinalDirectory - Rental boat not found', [
                'rental_boat_id' => $rentalBoatId
            ]);
        }
    }
}
