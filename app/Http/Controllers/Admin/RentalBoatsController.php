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
                'boat_type' => $request->boat_type ?? null,
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
            if ($request->hasFile('title_image')) {
                // Handle image upload logic here
                // This would typically involve storing images and updating paths
            }

            $rentalBoat = RentalBoat::create($rentalBoatData);

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
            if ($request->boat_type) $rentalBoat->boat_type = $request->boat_type;
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
            'boat_type' => $rentalBoat->boat_type,
            'desc_of_boat' => $rentalBoat->desc_of_boat,
            'requirements' => $rentalBoat->requirements,
            'boat_information' => $rentalBoat->boat_information,
            'boat_extras' => $rentalBoat->boat_extras,
            'inclusions' => $rentalBoat->inclusions,
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
}
