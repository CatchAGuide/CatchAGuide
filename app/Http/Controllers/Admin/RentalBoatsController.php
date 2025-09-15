<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RentalBoat;
use App\Models\User;
use App\Models\BoatExtras;
use App\Models\Inclussion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $pageTitle = 'Create Rental Boat';
        $locale = Config::get('app.locale');
        $nameField = $locale == 'en' ? 'name_en' : 'name';
        
        $formData = [
            'is_update' => 0,
            'user_id' => Auth::id(),
            'status' => 'active'
        ];
        
        // Get predefined options for boat extras and inclusions
        $boatExtras = BoatExtras::all()->map(function($item) use ($nameField) {
            return [
                'value' => $item->$nameField,
                'id' => $item->id
            ];
        });
        
        $inclusions = Inclussion::all()->map(function($item) use ($nameField) {
            return [
                'value' => $item->$nameField,
                'id' => $item->id
            ];
        });
        
        return view('admin.pages.rental-boats.create', [
            'pageTitle' => $pageTitle,
            'formData' => $formData,
            'targetRedirect' => route('admin.rental-boats.index'),
            'boatExtras' => $boatExtras,
            'inclusions' => $inclusions
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'boat_type' => 'required|string|max:255',
            'desc_of_boat' => 'required|string',
            'price_type' => 'required|string|in:per_hour,per_day,per_week',
            'base_price' => 'required|numeric|min:0',
            'status' => 'required|string|in:active,inactive',
            'booking_advance' => 'required|string|in:same_day,one_day,three_days,one_week',
        ]);

        // Generate slug from title
        $slug = \Str::slug($request->title);
        
        // Ensure slug is unique
        $originalSlug = $slug;
        $counter = 1;
        while (RentalBoat::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Process form data
        $rentalBoatData = [
            'user_id' => $request->user_id ?? Auth::id(),
            'title' => $request->title,
            'slug' => $slug,
            'location' => $request->location,
            'city' => $request->city,
            'country' => $request->country,
            'region' => $request->region,
            'lat' => $request->latitude,
            'lng' => $request->longitude,
            'boat_type' => $request->boat_type,
            'desc_of_boat' => $request->desc_of_boat,
            'requirements' => $request->requirements,
            'status' => $request->status,
            'price_type' => $request->price_type,
        ];

        // Process boat information
        if ($request->has('boat_info')) {
            $rentalBoatData['boat_information'] = $request->boat_info;
        }

        // Process boat extras
        if ($request->has('boat_extras')) {
            $rentalBoatData['boat_extras'] = is_string($request->boat_extras) 
                ? explode(',', $request->boat_extras) 
                : $request->boat_extras;
        }

        // Process inclusions
        if ($request->has('inclusions')) {
            $rentalBoatData['inclusions'] = is_string($request->inclusions) 
                ? explode(',', $request->inclusions) 
                : $request->inclusions;
        }

        // Process pricing
        $rentalBoatData['prices'] = [
            'base_price' => $request->base_price
        ];

        // Process extra pricing
        if ($request->has('extra_pricing')) {
            $rentalBoatData['pricing_extra'] = $request->extra_pricing;
        }

        // Process images
        if ($request->hasFile('title_image')) {
            // Handle image upload logic here
            // This would typically involve storing images and updating paths
        }

        $rentalBoat = RentalBoat::create($rentalBoatData);

        return redirect()->route('admin.rental-boats.index')
            ->with('success', 'Rental boat created successfully.');
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
        $pageTitle = 'Edit Rental Boat';
        $locale = Config::get('app.locale');
        $nameField = $locale == 'en' ? 'name_en' : 'name';
        
        // Prepare form data for edit mode
        $formData = [
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
        
        // Get predefined options for boat extras and inclusions
        $boatExtras = BoatExtras::all()->map(function($item) use ($nameField) {
            return [
                'value' => $item->$nameField,
                'id' => $item->id
            ];
        });
        
        $inclusions = Inclussion::all()->map(function($item) use ($nameField) {
            return [
                'value' => $item->$nameField,
                'id' => $item->id
            ];
        });
        
        return view('admin.pages.rental-boats.edit', [
            'pageTitle' => $pageTitle,
            'formData' => $formData,
            'targetRedirect' => route('admin.rental-boats.index'),
            'boatExtras' => $boatExtras,
            'inclusions' => $inclusions
        ]);
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
        $request->validate([
            'title' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'boat_type' => 'required|string|max:255',
            'desc_of_boat' => 'required|string',
            'price_type' => 'required|string|in:per_hour,per_day,per_week',
            'base_price' => 'required|numeric|min:0',
            'status' => 'required|string|in:active,inactive',
            'booking_advance' => 'required|string|in:same_day,one_day,three_days,one_week',
        ]);

        // Generate slug from title if title changed
        if ($rentalBoat->title !== $request->title) {
            $slug = \Str::slug($request->title);
            
            // Ensure slug is unique
            $originalSlug = $slug;
            $counter = 1;
            while (RentalBoat::where('slug', $slug)->where('id', '!=', $rentalBoat->id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            $rentalBoat->slug = $slug;
        }

        // Update basic fields
        $rentalBoat->title = $request->title;
        $rentalBoat->location = $request->location;
        $rentalBoat->city = $request->city;
        $rentalBoat->country = $request->country;
        $rentalBoat->region = $request->region;
        $rentalBoat->lat = $request->latitude;
        $rentalBoat->lng = $request->longitude;
        $rentalBoat->boat_type = $request->boat_type;
        $rentalBoat->desc_of_boat = $request->desc_of_boat;
        $rentalBoat->requirements = $request->requirements;
        $rentalBoat->status = $request->status;
        $rentalBoat->price_type = $request->price_type;

        // Process boat information
        if ($request->has('boat_info')) {
            $rentalBoat->boat_information = $request->boat_info;
        }

        // Process boat extras
        if ($request->has('boat_extras')) {
            $rentalBoat->boat_extras = is_string($request->boat_extras) 
                ? explode(',', $request->boat_extras) 
                : $request->boat_extras;
        }

        // Process inclusions
        if ($request->has('inclusions')) {
            $rentalBoat->inclusions = is_string($request->inclusions) 
                ? explode(',', $request->inclusions) 
                : $request->inclusions;
        }

        // Process pricing
        $rentalBoat->prices = [
            'base_price' => $request->base_price
        ];

        // Process extra pricing
        if ($request->has('extra_pricing')) {
            $rentalBoat->pricing_extra = $request->extra_pricing;
        }

        $rentalBoat->save();

        return redirect()->route('admin.rental-boats.index')
            ->with('success', 'Rental boat updated successfully.');
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
}
