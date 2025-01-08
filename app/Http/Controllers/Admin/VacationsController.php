<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vacation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
            $galleryImages = $this->saveImages($request);
            $formattedData['gallery'] = json_encode($galleryImages);

            $vacation = Vacation::create($formattedData);

            if($vacation) {
                return redirect()->route('admin.vacations.index')
                    ->with('success', 'Vacation created successfully');
            }
            
            return redirect()->route('admin.vacations.index')
                ->with('error', 'Failed to create vacation')
                ->withInput();
                
        } catch (\Exception $e) {
            \Log::error('Vacation creation failed: ' . $e->getMessage());
            return redirect()->route('admin.vacations.index')
                ->with('error', 'An error occurred while creating the vacation')
                ->withInput();
        }
    }

    public function edit($id)
    {
        $vacation = Vacation::findOrFail($id);
        
        // Transform the data if needed
        $vacation->target_fish = is_string($vacation->target_fish) ? json_decode($vacation->target_fish) : $vacation->target_fish;
        $vacation->amenities = is_string($vacation->amenities) ? json_decode($vacation->amenities) : $vacation->amenities;
        $vacation->equipment = is_string($vacation->equipment) ? json_decode($vacation->equipment) : $vacation->equipment;
        $vacation->additional_services = is_string($vacation->additional_services) ? json_decode($vacation->additional_services) : $vacation->additional_services;
        $vacation->included_services = is_string($vacation->included_services) ? json_decode($vacation->included_services) : $vacation->included_services;
        $vacation->pets_allowed = $vacation->pets_allowed == "1" ? true : false;
        $vacation->smoking_allowed = $vacation->smoking_allowed == "1" ? true : false;
        $vacation->disability_friendly = $vacation->disability_friendly == "1" ? true : false;
        
        return response()->json($vacation);
    }

    public function update(Request $request, $id)
    {
        try {
            $vacation = Vacation::findOrFail($id);
            
            // Log the incoming request data
            Log::info('Updating vacation #' . $id . ' - Incoming request data:', [
                'request_data' => $request->all()
            ]);

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'slug' => 'required|string|unique:vacations,slug,' . $id,
                'location' => 'required|string',
                'city' => 'required|string',
                'country' => 'required|string',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'region' => 'required|string',
                'gallery' => 'sometimes|array',
                'best_travel_times' => 'required|string',
                'surroundings_description' => 'required|string',
                'target_fish' => 'required',
                'airport_distance' => 'required|string',
                'water_distance' => 'required|string',
                'shopping_distance' => 'required|numeric',
                'pets_allowed' => 'boolean',
                'smoking_allowed' => 'boolean',
                'disability_friendly' => 'boolean',
                'accommodation_description' => 'required|string',
                'living_area' => 'required|numeric',
                'bedroom_count' => 'required|numeric',
                'bed_count' => 'required|numeric',
                'max_persons' => 'required|string',
                'min_rental_days' => 'required|numeric',
                'amenities' => 'required',
                'boat_description' => 'nullable|string',
                'equipment' => 'required',
                'basic_fishing_description' => 'required|string',
                'catering_info' => 'nullable|string',
                'package_price_per_person' => 'required|string',
                'accommodation_price' => 'required|string',
                'boat_rental_price' => 'nullable|numeric',
                'guiding_price' => 'nullable|numeric',
                'additional_services' => 'nullable',
                'included_services' => 'required',
                'travel_included' => 'required|string',
                'travel_options' => 'required',
            ]);

            // Log successful validation
            Log::info('Validation passed for vacation #' . $id);

            // Format the request data
            $formattedData = $this->formatRequestData($request);
            
            // Log the formatted data
            Log::info('Formatted data for vacation #' . $id . ':', [
                'formatted_data' => $formattedData
            ]);
            
            // Handle gallery images only if new images are uploaded
            if ($request->hasFile('gallery')) {
                Log::info('Processing new gallery images for vacation #' . $id);
                $galleryImages = $this->saveImages($request);
                $formattedData['gallery'] = json_encode($galleryImages);
                
                // Log gallery processing results
                Log::info('Gallery images processed:', [
                    'images' => $galleryImages
                ]);
            }

            // Log update attempt
            Log::info('Attempting to update vacation #' . $id);

            if($vacation->update($formattedData)) {
                Log::info('Successfully updated vacation #' . $id);
                return redirect()->route('admin.vacations.index')
                    ->with('success', 'Vacation updated successfully');
            }
            
            Log::warning('Failed to update vacation #' . $id . ' - Database update returned false');
            return redirect()->route('admin.vacations.index')
                ->with('error', 'Failed to update vacation')
                ->withInput();
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Specific logging for validation errors
            Log::error('Validation failed for vacation #' . $id . ':', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            // Detailed logging for other exceptions
            Log::error('Unexpected error updating vacation #' . $id . ':', [
                'error' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return redirect()->route('admin.vacations.index')
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
