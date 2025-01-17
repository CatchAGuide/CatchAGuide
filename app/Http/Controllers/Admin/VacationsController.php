<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vacation;
use App\Models\VacationBooking;
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

    public function bookings()
    {
        $bookings = VacationBooking::with('vacation', 'package', 'accommodation', 'boat', 'guiding')->orderBy('created_at', 'desc')->get();
        return view('admin.pages.vacations.bookings',compact('bookings'));
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
                    // Check if the value is a string (from tagify) and convert to array
                    if (is_string($data[$field])) {
                        try {
                            $decodedValue = json_decode($data[$field], true);
                            if (is_array($decodedValue)) {
                                // Extract only the 'value' property from each tag if it exists
                                $data[$field] = array_map(function($item) {
                                    return is_array($item) && isset($item['value']) ? $item['value'] : $item;
                                }, $decodedValue);
                            } else {
                                $data[$field] = [];
                            }
                        } catch (\Exception $e) {
                            Log::error("Error processing JSON field {$field}:", [
                                'error' => $e->getMessage(),
                                'value' => $data[$field]
                            ]);
                            $data[$field] = [];
                        }
                    }
                    // Only encode if we have a valid array
                    if (is_array($data[$field])) {
                        $data[$field] = json_encode($data[$field]);
                    } else {
                        $data[$field] = json_encode([]);
                    }
                }
            }

            // Handle boolean fields
            $booleanFields = ['pets_allowed', 'smoking_allowed', 'disability_friendly'];
            foreach ($booleanFields as $field) {
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
                            'title' => $accommodation['title'] ?? null,
                            'description' => $accommodation['description'],
                            'capacity' => $accommodation['capacity'],
                            'dynamic_fields' => json_encode([
                                'prices' => array_values($accommodation['prices'] ?? []),
                                'bed_count' => $accommodation['bed_count'] ?? '',
                                'living_area' => $accommodation['living_area'] ?? '',
                                'min_rental_days' => $accommodation['min_rental_days'] ?? '',
                                'facilities' => $accommodation['facilities'] ?? ''
                            ])
                        ]);
                    }
                }

                // Handle Boats
                if ($request->has('boats')) {
                    foreach ($request->boats as $boat) {
                        $vacation->boats()->create([
                            'title' => $boat['title'] ?? null,
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
                            'title' => $package['title'] ?? null,
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
                            'title' => $guiding['title'] ?? null,
                            'description' => $guiding['description'],
                            'capacity' => $guiding['capacity'],
                            'dynamic_fields' => json_encode([
                                'prices' => array_values($guiding['prices'] ?? [])
                            ])
                        ]);
                    }
                }

                // Handle Extras
                if ($request->has('extras')) {
                    foreach ($request->extras as $extra) {
                        $vacation->extras()->create([
                            'description' => $extra['description'],
                            'price' => $extra['price'],
                            'type' => $extra['price_type']
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
            $vacation = Vacation::with(['accommodations', 'boats', 'packages', 'guidings', 'extras'])->findOrFail($id);
            
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
                        $data = $vacation->accommodations()->create([
                            'title' => $accommodation['title'] ?? null,
                            'description' => $accommodation['description'],
                            'capacity' => $accommodation['capacity'],
                            'dynamic_fields' => json_encode([
                                'prices' => array_values($accommodation['prices'] ?? []),
                                'bed_count' => $accommodation['bed_count'] ?? '',
                                'living_area' => $accommodation['living_area'] ?? '',
                                'min_rental_days' => $accommodation['min_rental_days'] ?? '',
                                'facilities' => $accommodation['facilities'] ?? ''
                            ])
                        ]);
                    }
                }

                // Update Boats
                $vacation->boats()->delete(); // Remove existing
                if ($request->has('boats')) {
                    foreach ($request->boats as $boat) {
                        $vacation->boats()->create([
                            'title' => $boat['title'] ?? null,
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
                            'title' => $package['title'] ?? null,
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
                            'title' => $guiding['title'] ?? null,
                            'description' => $guiding['description'],
                            'capacity' => $guiding['capacity'],
                            'dynamic_fields' => json_encode([
                                'prices' => array_values($guiding['prices'] ?? [])
                            ])
                        ]);
                    }
                }

                // Update Extras
                $vacation->extras()->delete(); // Remove existing
                if ($request->has('extras')) {
                    foreach ($request->extras as $extra) {
                        $vacation->extras()->create([
                            'description' => $extra['description'],
                            'price' => $extra['price'],
                            'type' => $extra['price_type']
                        ]);
                    }
                }

                DB::commit();

                return redirect()
                    ->route('admin.vacations.index')
                    ->with('success', 'Vacation updated successfully');

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error in vacation update:', [
                    'error' => $e->getMessage(),
                    'stack_trace' => $e->getTraceAsString()
                ]);
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

    public function show(VacationBooking $booking)
    {
        try {
            // Eager load all the relationships we need
            $booking->load([
                'vacation',
                'package',
                'accommodation',
                'boat',
                'guiding'
            ]);

            return view('admin.pages.vacations.show', compact('booking'));
        } catch (\Exception $e) {
            Log::error('Error in vacation booking show:', [
                'error' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            return redirect()
                ->route('admin.vacations.bookings')
                ->with('error', 'An error occurred while loading the booking details: ' . $e->getMessage());
        }
    }
}
