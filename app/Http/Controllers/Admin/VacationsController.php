<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vacation;
use Illuminate\Http\Request;

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

    public function store(Request $request)
    {
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
            'status' => 'required|boolean'
        ]);

        $vacation = Vacation::create($validated);
        
        return redirect()->route('admin.vacations.index')
            ->with('success', 'Vacation created successfully');
    }

    public function edit($id)
    {
        $vacation = Vacation::findOrFail($id);
        return response()->json($vacation);
    }

    public function update(Request $request, $id)
    {
        $vacation = Vacation::findOrFail($id);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:vacations,slug,' . $id,
            // ... same validation rules as store()
        ]);

        $vacation->update($validated);
        
        return redirect()->route('admin.vacations.index')
            ->with('success', 'Vacation updated successfully');
    }

    public function destroy($id)
    {
        $vacation = Vacation::find($id);
        $vacation->delete();
        return redirect()->route('admin.vacations.index')->with('success', 'Vacation deleted successfully');
    }
}
