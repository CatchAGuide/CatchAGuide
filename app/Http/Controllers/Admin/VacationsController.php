<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vacation;
use App\Models\VacationBooking;
use App\Services\AdminChangeTracker;
use App\Services\Media\ListingMediaRelocator;
use App\Services\Vacation\VacationComponentSync;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class VacationsController extends Controller
{
    public function __construct(
        private ListingMediaRelocator $mediaRelocator,
        private VacationComponentSync $componentSync,
    ) {}

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
            $booleanFields = ['pets_allowed', 'smoking_allowed', 'disability_friendly', 'has_boat', 'has_guiding'];
            foreach ($booleanFields as $field) {
                $data[$field] = isset($data[$field]) && ($data[$field] === '1' || $data[$field] === true);
            }

            return $data;
        } catch (\Exception $e) {
            Log::error('Error in formatRequestData:', [
                'error' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * City, country, region and coordinates are only filled by picking a Google suggestion.
     * When the admin keeps the stored location, they arrive empty (null after the request
     * middleware), and country/latitude/longitude are NOT NULL, so the whole save failed.
     * Keep what is stored instead.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function keepStoredLocation(array $data, ?Vacation $vacation = null): array
    {
        foreach (['location', 'city', 'country', 'region', 'latitude', 'longitude'] as $field) {
            if (! array_key_exists($field, $data) || filled($data[$field])) {
                continue;
            }

            $data[$field] = $vacation?->getRawOriginal($field) ?? '';
        }

        return $data;
    }

    /**
     * @return list<string>
     */
    private function galleryPaths(mixed $existingGallery): array
    {
        $paths = is_string($existingGallery) ? json_decode($existingGallery, true) : $existingGallery;

        return array_values(array_filter(
            is_array($paths) ? $paths : [],
            static fn ($path) => is_string($path) && trim($path) !== ''
        ));
    }

    public function store(Request $request)
    {
        try {
            $formattedData = $this->keepStoredLocation($this->formatRequestData($request));

            // Handle gallery images
            $galleryImages = null;
            if ($request->hasFile('gallery')) {
                $galleryImages = $this->saveImages($request);
                $formattedData['gallery'] = json_encode($galleryImages);
            }

            Log::info($formattedData);
            // Begin transaction
            DB::beginTransaction();

            try {
                // Create the vacation
                $vacation = Vacation::create($formattedData);

                if ($galleryImages !== null) {
                    $this->relocateVacationMediaFromTemp($vacation, $galleryImages);
                }

                $this->componentSync->sync($vacation, $request);

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
                $item->dynamic_fields = is_string($item->dynamic_fields) ? json_decode($item->dynamic_fields, true) : $item->dynamic_fields;
                return $item;
            });
            
            $data['boats'] = $vacation->boats->map(function($item) {
                $item->dynamic_fields = is_string($item->dynamic_fields) ? json_decode($item->dynamic_fields, true) : $item->dynamic_fields;
                return $item;
            });
            
            $data['packages'] = $vacation->packages->map(function($item) {
                $item->dynamic_fields = is_string($item->dynamic_fields) ? json_decode($item->dynamic_fields, true) : $item->dynamic_fields;
                return $item;
            });
            
            $data['guidings'] = $vacation->guidings->map(function($item) {
                $item->dynamic_fields = is_string($item->dynamic_fields) ? json_decode($item->dynamic_fields, true) : $item->dynamic_fields;
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
            $formattedData = $this->keepStoredLocation($this->formatRequestData($request), $vacation);

            // Handle gallery images
            if ($request->hasFile('gallery')) {
                $galleryImages = array_merge(
                    $this->galleryPaths($request->input('existing_gallery')),
                    $this->saveImages($request, (int) $id)
                );

                $formattedData['gallery'] = json_encode($galleryImages);
            } else if ($request->has('existing_gallery')) {
                // Only update with existing gallery
                $formattedData['gallery'] = json_encode($this->galleryPaths($request->input('existing_gallery')));
            }

            DB::beginTransaction();

            try {
                // Track changes before updating
                $changeTracker = new AdminChangeTracker();
                $changeTracker->trackVacationChanges($vacation, $formattedData);
                
                // Update the vacation
                $vacation->update($formattedData);

                $this->componentSync->sync($vacation, $request);

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

    private function saveImages($request, ?int $vacationId = null)
    {
        $galeryImages = [];
        if ($request->has('gallery')) {
            $directory = media_listing_directory('vacation', $vacationId);
            $imageCount = count($galeryImages);
            foreach($request->file('gallery') as $index => $image){
                $index = $index + $imageCount;
                $webp_path = media_upload($image, $directory, $request->slug. "-". $index . "-" . time(), 75, $vacationId);
                $galeryImages[] = $webp_path;
            }
        }

        return $galeryImages;
    }

    private function relocateVacationMediaFromTemp(Vacation $vacation, array $galleryImages): void
    {
        if ($vacation->id <= 0 || empty($galleryImages)) {
            return;
        }

        $relocated = $this->mediaRelocator->promoteForListing(
            'vacation',
            (int) $vacation->id,
            [
                'gallery_images' => $galleryImages,
                'thumbnail_path' => '',
            ]
        );

        $vacation->update(['gallery' => json_encode($relocated['gallery_images'])]);
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
