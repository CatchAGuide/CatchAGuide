<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateGuidingRequest;
use App\Models\Guiding;
use App\Models\Method;
use App\Models\Target;
use App\Models\Water;
use App\Models\Inclussion;
use App\Models\BoatExtras;
use App\Models\ExtrasPrice;
use App\Models\GuidingBoatType;
use App\Models\GuidingBoatDescription;
use App\Models\GuidingAdditionalInformation;
use App\Models\GuidingRequirements;
use App\Models\GuidingRecommendations;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;

class GuidingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $guidings = Guiding::all();
        return view('admin.pages.guidings.index',compact('guidings'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.pages.guidings.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Guiding  $guiding
     * @return \Illuminate\Http\Response
     */
    public function show(Guiding $guiding)
    {
        $targets = Target::all();
        $methods = Method::all();
        $waters = Water::all();
        return view('admin.pages.guidings.show', compact('guiding', 'waters', 'methods', 'targets'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Guiding  $guiding
     * @return \Illuminate\Http\Response
     */
    public function edit(Guiding $guiding)
    {
        // Load necessary relationships
        $guiding->load([
            'guidingTargets', 'guidingWaters', 'guidingMethods', 
            'fishingTypes', 'fishingFrom'
        ]);

        // Prepare data for the form
        $formData = [
            'id' => $guiding->id,
            'is_update' => 1,
            //step1
            'title' => $guiding->title,
            'location' => $guiding->location,
            'latitude' => $guiding->lat,
            'longitude' => $guiding->lng,
            'country' => $guiding->country,
            'city' => $guiding->city,
            'region' => $guiding->region,
            'gallery_images' => $guiding->gallery_images,
            'thumbnail_path' => $guiding->thumbnail_path,

            //step 2
            'type_of_fishing' => $guiding->is_boat ? 'boat' : 'shore',
            'boat_type' => $guiding->boat_type,
            'boat_information' => $guiding->getBoatInformationAttribute(),
            'boat_extras' => $guiding->getBoatExtras(),

            //step 3
            'target_fish' => $guiding->getTargetFishNames(),
            'methods' => $guiding->getFishingMethodNames(),
            'water_types' => $guiding->getWaterNames(),

            //step 4
            'inclusions' => $guiding->getInclusionNames(),
            'fishing_type' => $guiding->fishing_type_id,

            //step 5
            'long_description' => $guiding->description,
            'desc_course_of_action' => $guiding->desc_course_of_action,
            'desc_starting_time' => $guiding->desc_starting_time,
            'desc_meeting_point' => $guiding->desc_meeting_point,
            'desc_tour_unique' => $guiding->desc_tour_unique,
            
            //step 6
            'requirements' => $guiding->getRequirementsAttribute(),
            'recommendations' => $guiding->getRecommendationsAttribute(),
            'other_information' => $guiding->getOtherInformationAttribute(),

            //step 7
            'tour_type' => trim($guiding->tour_type),
            'duration' => $guiding->duration,
            'duration_type' => $guiding->duration_type,
            'no_guest' => $guiding->max_guests,
            'price_type' => $guiding->price_type,
            'price' => $guiding->price,
            'prices' => json_decode($guiding->prices, true),
            'pricing_extra' => $guiding->getPricingExtraAttribute(),

            //step 8
            'allowed_booking_advance' => $guiding->allowed_booking_advance,
            'booking_window' => $guiding->booking_window,
            'seasonal_trip' => $guiding->seasonal_trip,
            'months' => json_decode($guiding->months, true),
            'other_boat_info' => $guiding->additional_information,
        ];

        $locale = Config::get('app.locale');
        $nameField = $locale == 'en' ? 'name_en' : 'name';

        $modelClasses = [
            'targets' => Target::class,
            'methods' => Method::class,
            'waters' => Water::class,
            'inclusions' => Inclussion::class,
            'boat_extras' => BoatExtras::class,
            'extras_prices' => ExtrasPrice::class,
            'guiding_boat_types' => GuidingBoatType::class,
            'guiding_boat_descriptions' => GuidingBoatDescription::class,
            'guiding_additional_infos' => GuidingAdditionalInformation::class,
            'guiding_requirements' => GuidingRequirements::class,
            'guiding_recommendations' => GuidingRecommendations::class
        ];

        $collections = [];
        foreach ($modelClasses as $key => $modelClass) {
            $collections[$key] = $modelClass::all()
            ->map(function($item) use ($nameField, $key) {
                return [
                    'value' => $item->$nameField,
                    'id' => $item->id
                ];

            });
        }

        $pageTitle = __('profile.editguiding');

        return view('admin.pages.guidings.edit', array_merge(
            ['formData' => $formData, 'pageTitle' => $pageTitle],
            $collections
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Guiding  $guiding
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateGuidingRequest $request, Guiding $guiding)
    {
       $data = $request->validated();

       $guiding->update($data);

       return redirect()->route('admin.guidings.index');
    }

    public function changeGuidingStatus($id)
    {
        $guiding = Guiding::find($id);
        if($guiding->status === 1) {
            $guiding->status = 0;
        } else {
            $guiding->status = 1;
        }
        $guiding->save();
        return back()->with('success', 'Der Status wurde erfolgreich geändert');
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Guiding  $guiding
     * @return \Illuminate\Http\Response
     */
    public function destroy(Guiding $guiding)
    {
        //
    }
}
