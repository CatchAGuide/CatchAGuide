<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vacation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class VacationsController extends Controller
{
    public function index()
    {
        $vacations = Vacation::all();
        return view('admin.pages.vacations.index',compact('vacations'));
    }

    public function create()
    {
        return view('admin.pages.vacations.create');
    }

    private function formatRequestData(Request $request)
    {
        try {
            $data = $request->all();
            
            // Log initial data
            Log::info('Starting formatRequestData - Initial data:', [
                'initial_data' => $data
            ]);
            
            // Handle JSON fields
            $jsonFields = ['target_fish', 'amenities', 'equipment', 'additional_services', 'included_services', 'travel_options'];
            foreach ($jsonFields as $field) {
                if (isset($data[$field])) {
                    Log::info("Processing JSON field: {$field}", [
                        'original_value' => $data[$field]
                    ]);
                    
                    // Check if the value is a string (from tagify) and convert to array
                    if (is_string($data[$field])) {
                        try {
                            $data[$field] = json_decode($data[$field], true);
                            // Extract only the 'value' property from each tag if it exists
                            $data[$field] = array_map(function($item) {
                                return is_array($item) && isset($item['value']) ? $item['value'] : $item;
                            }, $data[$field]);
                            
                            Log::info("Successfully processed JSON field: {$field}", [
                                'processed_value' => $data[$field]
                            ]);
                        } catch (\Exception $e) {
                            Log::error("Error processing JSON field {$field}:", [
                                'error' => $e->getMessage(),
                                'value' => $data[$field]
                            ]);
                            $data[$field] = [];
                        }
                    }
                    $data[$field] = json_encode($data[$field]);
                }
            }

            // Handle boolean fields
            $booleanFields = ['pets_allowed', 'smoking_allowed', 'disability_friendly', 'status'];
            foreach ($booleanFields as $field) {
                Log::info("Processing boolean field: {$field}", [
                    'original_value' => $data[$field] ?? null
                ]);
                $data[$field] = isset($data[$field]) && ($data[$field] === '1' || $data[$field] === true);
            }

            Log::info('Completed formatRequestData', [
                'final_data' => $data
            ]);

            return $data;
        } catch (\Exception $e) {
            Log::error('Error in formatRequestData:', [
                'error' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'slug' => 'required|string|unique:vacations',
                'location' => 'required|string',
                'city' => 'required|string',
                'country' => 'required|string',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'region' => 'required|string',
                'gallery' => 'required|array',
                'best_travel_times' => 'required|string',
                'surroundings_description' => 'required|string',
                'target_fish' => 'required|array',
                'airport_distance' => 'required|numeric',
                'water_distance' => 'required|numeric',
                'shopping_distance' => 'required|numeric',
                'pets_allowed' => 'boolean',
                'smoking_allowed' => 'boolean',
                'disability_friendly' => 'boolean',
                'accommodation_description' => 'required|string',
                'living_area' => 'required|numeric',
                'bedroom_count' => 'required|integer',
                'bed_count' => 'required|integer',
                'max_persons' => 'required|integer',
                'min_rental_days' => 'required|integer',
                'amenities' => 'required|array',
                'boat_description' => 'nullable|string',
                'equipment' => 'required|array',
                'basic_fishing_description' => 'required|string',
                'catering_info' => 'nullable|string',
                'package_price_per_person' => 'required|numeric',
                'accommodation_price' => 'required|numeric',
                'boat_rental_price' => 'nullable|numeric',
                'guiding_price' => 'nullable|numeric',
                'additional_services' => 'nullable|array',
                'included_services' => 'required|array',
                'status' => 'required|boolean',

            ]);

            // Format the request data
            $formattedData = $this->formatRequestData($request);
            
            // Handle gallery images
            if ($request->hasFile('gallery')) {
                $galleryImages = $this->saveImages($request);
                $formattedData['gallery'] = json_encode($galleryImages);
            }

            // Begin transaction
            DB::beginTransaction();

            try {
                // Create the vacation
                $vacation = Vacation::create($formattedData);

                // Handle Accommodations
                if ($request->has('accommodations')) {
                    foreach ($request->accommodations as $accommodation) {
                        $vacation->accommodations()->create([
                            'description' => $accommodation['description'],
                            'capacity' => $accommodation['capacity'],
                            'dynamic_fields' => json_encode([
                                'prices' => array_values($accommodation['prices'] ?? []),
                                'living_area' => $accommodation['living_area'] ?? '',
                                'bed_count' => $accommodation['bed_count'] ?? '',
                                'facilities' => $accommodation['facilities'] ?? '',
                                'min_rental_days' => $accommodation['min_rental_days'] ?? ''
                            ])
                        ]);
                    }
                }

                // Handle Boats
                if ($request->has('boats')) {
                    foreach ($request->boats as $boat) {
                        $vacation->boats()->create([
                            'description' => $boat['description'],
                            'capacity' => $boat['capacity'],
                            'dynamic_fields' => json_encode([
                                'prices' => array_values($boat['prices'] ?? []),
                                'facilities' => $boat['facilities'] ?? ''
                            ])
                        ]);
                    }
                }

                // Handle Packages
                if ($request->has('packages')) {
                    foreach ($request->packages as $package) {
                        $vacation->packages()->create([
                            'description' => $package['description'],
                            'capacity' => $package['capacity'],
                            'dynamic_fields' => json_encode([
                                'prices' => array_values($package['prices'] ?? [])
                            ])
                        ]);
                    }
                }

                // Handle Guidings
                if ($request->has('guidings')) {
                    foreach ($request->guidings as $guiding) {
                        $vacation->guidings()->create([
                            'description' => $guiding['description'],
                            'capacity' => $guiding['capacity'],
                            'dynamic_fields' => json_encode([
                                'prices' => array_values($guiding['prices'] ?? [])
                            ])
                        ]);
                    }
                }

                DB::commit();

                return redirect()
                    ->route('admin.vacations.index')
                    ->with('success', 'Vacation created successfully');

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error creating vacation:', [
                    'error' => $e->getMessage(),
                    'stack_trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error in vacation store:', [
                'error' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            return redirect()
                ->back()
                ->with('error', 'An error occurred while creating the vacation: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit($id)
    {
        try {
            $vacation = Vacation::with(['accommodations', 'boats', 'packages', 'guidings'])->findOrFail($id);
            
            // Transform the data to include parsed dynamic_fields
            $data = $vacation->toArray();
            
            // Parse dynamic fields for each relation
            $data['accommodations'] = $vacation->accommodations->map(function($item) {
                $item->dynamic_fields = json_decode($item->dynamic_fields, true);
                return $item;
            });
            
            $data['boats'] = $vacation->boats->map(function($item) {
                $item->dynamic_fields = json_decode($item->dynamic_fields, true);
                return $item;
            });
            
            $data['packages'] = $vacation->packages->map(function($item) {
                $item->dynamic_fields = json_decode($item->dynamic_fields, true);
                return $item;
            });
            
            $data['guidings'] = $vacation->guidings->map(function($item) {
                $item->dynamic_fields = json_decode($item->dynamic_fields, true);
                return $item;
            });

            // Debug log
            \Log::info('Edit vacation data:', ['data' => $data]);

            return response()->json($data);
        } catch (\Exception $e) {
            \Log::error('Error in vacation edit:', [
                'error' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $vacation = Vacation::findOrFail($id);
            
            // Format the request data
            $formattedData = $this->formatRequestData($request);
            
            // Handle gallery images
            if ($request->hasFile('gallery')) {
                $galleryImages = $this->saveImages($request);
                
                // Merge with existing images if any
                if ($request->has('existing_gallery')) {
                    $existingGallery = json_decode($request->existing_gallery, true) ?? [];
                    $galleryImages = array_merge($existingGallery, $galleryImages);
                }
                
                $formattedData['gallery'] = json_encode($galleryImages);
            } else if ($request->has('existing_gallery')) {
                // Only update with existing gallery
                $formattedData['gallery'] = $request->existing_gallery;
            }

            DB::beginTransaction();

            try {
                // Update the vacation
                $vacation->update($formattedData);

                // Update Accommodations
                $vacation->accommodations()->delete(); // Remove existing
                if ($request->has('accommodations')) {
                    foreach ($request->accommodations as $accommodation) {
                        $vacation->accommodations()->create([
                            'description' => $accommodation['description'],
                            'capacity' => $accommodation['capacity'],
                            'dynamic_fields' => json_encode([
                                'prices' => array_values($accommodation['prices'] ?? []),
                                'living_area' => $accommodation['living_area'] ?? '',
                                'bed_count' => $accommodation['bed_count'] ?? '',
                                'facilities' => $accommodation['facilities'] ?? '',
                                'min_rental_days' => $accommodation['min_rental_days'] ?? ''
                            ])
                        ]);
                    }
                }

                // Update Boats
                $vacation->boats()->delete(); // Remove existing
                if ($request->has('boats')) {
                    foreach ($request->boats as $boat) {
                        $vacation->boats()->create([
                            'description' => $boat['description'],
                            'capacity' => $boat['capacity'],
                            'dynamic_fields' => json_encode([
                                'prices' => array_values($boat['prices'] ?? []),
                                'facilities' => $boat['facilities'] ?? ''
                            ])
                        ]);
                    }
                }

                // Update Packages
                $vacation->packages()->delete(); // Remove existing
                if ($request->has('packages')) {
                    foreach ($request->packages as $package) {
                        $vacation->packages()->create([
                            'description' => $package['description'],
                            'capacity' => $package['capacity'],
                            'dynamic_fields' => json_encode([
                                'prices' => array_values($package['prices'] ?? []),
                                'catering_info' => $package['catering_info'] ?? ''
                            ])
                        ]);
                    }
                }

                // Update Guidings
                $vacation->guidings()->delete(); // Remove existing
                if ($request->has('guidings')) {
                    foreach ($request->guidings as $guiding) {
                        $vacation->guidings()->create([
                            'description' => $guiding['description'],
                            'capacity' => $guiding['capacity'],
                            'dynamic_fields' => json_encode([
                                'prices' => array_values($guiding['prices'] ?? [])
                            ])
                        ]);
                    }
                }

                DB::commit();

                return redirect()
                    ->route('admin.vacations.index')
                    ->with('success', 'Vacation updated successfully');

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error in vacation update:', [
                'error' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            return redirect()
                ->back()
                ->with('error', 'An error occurred while updating the vacation: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $vacation = Vacation::find($id);
        $vacation->delete();
        return redirect()->route('admin.vacations.index')->with('success', 'Vacation deleted successfully');
    }

    public function changeVacationStatus($id)
    {
        $vacation = Vacation::find($id);
        $vacation->status = !$vacation->status;
        $vacation->save();
        return redirect()->route('admin.vacations.index')->with('success', 'Vacation status changed successfully');
    }

    private function saveImages($request)
    {
        $galeryImages = [];
        if ($request->has('gallery')) {
            $imageCount = count($galeryImages);
            foreach($request->file('gallery') as $index => $image){
                $index = $index + $imageCount;
                $webp_path = media_upload($image, 'vacations-images', $request->slug. "-". $index . "-" . time());
                $galeryImages[] = $webp_path;
            }
        }

        return $galeryImages;
    }
}
