<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGuidingRequest;
use App\Models\FishType;
use App\Models\Gallery;
use App\Models\Guiding;
use App\Models\Method;
use App\Models\Target;
use App\Models\Water;
use Auth;
use Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

class GuidingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $locale = Config::get('app.locale');

        $title = '';
        $randomSeed = Session::get('random_seed');
        if (!$randomSeed) {
            $randomSeed = rand();
            Session::put('random_seed', $randomSeed);
        }

        $query = Guiding::query()->where('status',1)->whereNotNull('lat')->whereNotNull('lng');

        if (empty($request->all())) {
            $query->orderByRaw("RAND($randomSeed)");
            // Request has at least one parameter (input data)
        } 
          
        if($request->has('page')){
            $title .= __('guidings.Page') . ' ' . $request->page . ' - ';
        }

        if($request->has('num_guests') && !empty($request->get('num_guests'))){
            $title .= __('guidings.Guest') . ' ' . $request->num_guests . ' | ';
            $q = $query->where('max_guests','>=',$request->get('num_guests'));
        }

        if($request->has('sortby') && !empty($request->get('sortby'))){
            switch ($request->get('sortby')) {
                case 'newest':
                    $query->orderBy('created_at','desc');
                  break;

                case 'price-asc':
                    $query->orderBy('price','asc');
                  break;

                case 'price-desc':
                    $query->orderBy('price','desc');
                  break;

                case 'long-duration':
                    $query->orderBy('duration','desc');
                break;

                case 'short-duration':
                    $query->orderBy('duration','asc');
                break;

                default:
                    $query;
            }
        }


        if($request->has('methods') && !empty($request->get('methods'))){
   
            $requestMethods = array_filter($request->get('methods'));

            if(count($requestMethods)){
                $title .= __('guidings.Method') . ' (';
                $method_rows = Method::whereIn('id', $request->methods)->get();
                $title_row = '';
                foreach ($method_rows as $row) {
                    $title_row .= (($locale == 'en')? $row->name_en : $row->name) . ', ';
                }
                $title .= substr($title_row, 0, -2);
                $title .= ') | ';
                $query->whereHas('guidingMethods', function ($query) use ($requestMethods) {
                    $query->whereIn('method_id', $requestMethods);
                });
            }
            
        }

        if($request->has('fishing_type') && !empty($request->get('fishing_type'))){
   
            $requestFishingTypes = $request->get('fishing_type');

            if($requestFishingTypes){

                $title .= __('guidings.Fishing_Type') . 'Fishing Type (';
                $method_rows = FishType::whereIn('id', $request->fishing_type)->get();
                $title_row = '';
                foreach ($method_rows as $row) {
                    $title_row .= (($locale == 'en')? $row->name_en : $row->name) . ', ';
                }
                $title .= substr($title_row, 0, -2);
                $title .= ') | ';

                $query->whereHas('fishingTypes', function ($query) use ($requestFishingTypes) {
                    $query->where('id', $requestFishingTypes);
                });
            }
        }

        if($request->has('duration') && !empty($request->get('duration'))){
            $title .= __('guidings.Duration') . ' ' . $request->duration . ' | ';

            $q = $query->where('duration','>=',$request->get('duration'));
        }

        if($request->has('fishingfrom') && !empty($request->get('fishingfrom'))){
   
            $requestFishingFrom = array_filter($request->get('fishingfrom'));

            if(count($requestFishingFrom)){
                $query->whereHas('fishingFrom', function ($query) use ($requestFishingFrom) {
                    $query->whereIn('id', $requestFishingFrom);
                });
            }
            
        }

        if($request->has('water') && !empty($request->get('water'))){

            $requestWater = array_filter($request->get('water'));

            if(count($requestWater)){

                $title .= __('guidings.Water') . ' (';
                $method_rows = Water::whereIn('id', $request->water)->get();
                $title_row = '';
                foreach ($method_rows as $row) {
                    $title_row .= (($locale == 'en')? $row->name_en : $row->name) . ', ';
                }
                $title .= substr($title_row, 0, -2);
                $title .= ') | ';

                $query->whereHas('guidingWaters', function ($query) use ($requestWater) {
                    $query->whereIn('water_id', $requestWater);
                });
            }
          
        }

        if($request->has('target_fish')){
            $requestFish = array_filter($request->get('target_fish'));

            if(count($requestFish)){

                $title .= __('guidings.Target_Fish') . ' (';
                $method_rows = Target::whereIn('id', $request->target_fish)->get();
                $title_row = '';
                foreach ($method_rows as $row) {
                    $title_row .= (($locale == 'en')? $row->name_en : $row->name) . ', ';
                }
                $title .= substr($title_row, 0, -2);
                $title .= ') | ';

                $query->whereHas('guidingTargets', function ($query) use ($requestFish) {
                    $query->whereIn('target_id', $requestFish);
                });
            }

        }

        if($request->has('country')){
            $title .= __('guidings.Country') . ' ' . $request->country . ' | ';
            $query->where('country',$request->get('country'));
        }

        $radius = null; // Radius in miles
        if($request->has('radius')){

            $title .= __('guidings.Radius') . ' ' . $request->radius . 'km | ';
            $radius = $request->get('radius');
        }

        $placeLat = $request->get('placeLat');
        $placeLng = $request->get('placeLng');

        if($request->has('place') && empty($request->get('place'))){
            $title .= __('guidings.Place') . ' ' . $request->place . ' | ';
            return redirect()->route('guidings.index', $request->except([
                'placeLng',
                'placeLat'
            ]));
        }

        if(!empty($placeLat) && !empty($placeLng) && !empty($request->get('place'))){

            $title .= __('guidings.Coordinates') . ' Lat ' . $placeLat . ' Lang ' . $placeLng . ' | ';
            $query->select(['guidings.*'])
            ->selectRaw("(6371 * acos(cos(radians($placeLat)) * cos(radians(lat)) * cos(radians(lng) - radians($placeLng)) + sin(radians($placeLat)) * sin(radians(lat)))) AS distance")
            ->where('status', 1)
            ->orderBy('distance') // Sort the results by distance in ascending order
            ->get();

        }

        $allGuidings = $query->get();


        $otherguidings = array();

        if($allGuidings->isEmpty()){

            if($request->has('placeLat') && $request->has('placeLng') && !empty($request->get('placeLat')) && !empty($request->get('placeLng')) ){
                $latitude = $request->get('placeLat');
                $longitude = $request->get('placeLng');
            
                $otherguidings = $this->otherGuidingsBasedByLocation($latitude,$longitude);

            }else{

                $otherguidings = $this->otherGuidings();

            }
        }

       
        $guidings = $query->paginate(20);


        $guidings->appends(request()->except('page'));

        return view('pages.guidings.index', [
            'title' => $title,
            'guidings' => $guidings,
            'radius' => $radius,
            'allGuidings' => $allGuidings,
            'otherguidings' => $otherguidings,
        ]);
        
    }

    public function newShow($id,$slug)
    {
        $locale = Config::get('app.locale');

        $title = '';
        
        return view('pages.guidings.newIndex', [
            'title' => $title,
        ]);
    }

    public function otherGuidings(){
        $otherguidings = Guiding::inRandomOrder('1234')->where('status',1)->limit(10)->get();

        return $otherguidings;
    }

    public function otherGuidingsBasedByLocation($latitude,$longitude){
        $nearestlisting = Guiding::select(['guidings.*']) // Include necessary attributes here
        ->selectRaw("(6371 * acos(cos(radians($latitude)) * cos(radians(lat)) * cos(radians(lng) - radians($longitude)) + sin(radians($latitude)) * sin(radians(lat)))) AS distance")
        ->orderBy('distance')
        ->where('status',1)
        ->limit(10)
        ->get();

        return $nearestlisting;
    }

    public function store(StoreGuidingRequest $request)
    {
        //dd($request->all());
        $data = $request->validated();

        $data['user_id'] = auth()->id();
        $data['slug'] = slugify($data['title'] . "-in-" . $data['location']);
        // TODO Hier muss mehr abgefangen werden und umgebaut werden!
        $waters = $request->water;
        array_unshift($waters, 'alle');
        $data['water'] = serialize($waters);
        $targets = $request->targets;
        array_unshift($targets, 'alle');
        $data['targets'] = serialize($targets);
        $methods = $request->methods;
        array_unshift($methods, 'alle');
        $data['methods'] = serialize($methods);

        // Add Gebühren
        if($data['price_two_persons'] > 0) {
            $data['price_two_persons'];
        }
        if($data['price_three_persons'] > 0) {
            $data['price_three_persons'];
        }
        if($data['price_four_persons'] > 0) {
            $data['price_four_persons'];
        }
        if($data['price_five_persons'] > 0) {
            $data['price_five_persons'];
        }
        $guiding = Guiding::create($data);


        if($request->gallery) {
            foreach ($request->gallery as $key => $file) {

     
                if(isset($file['image_name'])) {
                    $maxFileSizeInBytes = 20971520;
                    $fileSizeInBytes = $file['image_name']->getSize();
                    if ($fileSizeInBytes > $maxFileSizeInBytes) {
                        return redirect()->back()->withErrors(['file' => 'Die Datei ist zu groß. Maximalgröße: 20MB']);
                    }
                    $name = time().rand(1,50).'.'.$file['image_name']->extension();
                    $file['image_name']->move(public_path('files'), $name);
                    $gallery = new Gallery();
                    $gallery->image_name = $name;
                    $gallery->user_id = auth()->user()->id;
                    $gallery->avatar = isset($file['avatar']) && $file['avatar'] == "on" ? 1 : 0;
                    $gallery->guiding_id = $guiding->id;
                    $gallery->save();
                }
            }
        }

        return redirect()->route('profile.myguidings')->with(['message' => 'Das Guiding wurde erfolgreich erstellt!']);
    }
    
    public function show($id,$slug)
    {   
        $guiding = Guiding::where('id',$id)->where('slug',$slug)->where('status',1)->first();

        if(!$guiding){
            abort(404);
        }
        

        $targetId = $guiding->guidingTargets->pluck('id')->toArray();
        $fishingfrom = $guiding->fishingFrom->id;
        $fishingtype = $guiding->fishingTypes->id;



        $ratings = $guiding->user->received_ratings;
        $ratingCount = $ratings->count();
        $averageRating = $ratingCount > 0 ? $ratings->avg('rating') : 0;
        $otherGuidings = Guiding::whereHas('guidingTargets',function($query) use ($targetId){
            $query->wherein('target_id',$targetId);
        })->whereHas('fishingFrom',function($query) use($fishingfrom){
            $query->where('id',$fishingfrom);
        })->whereHas('fishingTypes',function($query) use($fishingtype){
            $query->where('id',$fishingtype);
        })
        ->where('status', 1)
        ->limit(4)
        ->get();

        // $otherGuidings = Guiding::where('id', '!=', $guiding->id)
        //     ->where('status', 1)
        //     ->limit(3)
        //     ->get();


            
        return view('pages.guidings.show', [
            'guiding' => $guiding,
            'ratings' => $ratings,
            'other_guidings' => $otherGuidings,
            'average_rating' => $averageRating,
        ]);
    }

    public function redirectToNewFormat($slug)
    {

        $guiding = Guiding::where('slug',$slug)->first();

        if(!$guiding){
            abort(404);
        }

        return redirect(route('guidings.show',[$guiding->id, $slug]), 301);

     
    }


    public function edit(Guiding $guiding)
    {           
    
        $targets = Target::all();
        $methods = Method::all();
        $waters = Water::all();
        

        return view('pages.guidings.edit', compact('guiding','targets', 'methods', 'waters'));
    }


    public function update(StoreGuidingRequest $request, Guiding $guiding)
    {


        $files = $request->files;


        $guiding->update([
            'title' => $request->title,
            'slug' => slugify($request->title),
            'location' => $request->location,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'recommended_for_anfaenger' => $request->recommended_for_anfaenger,
            'recommended_for_fortgeschrittene' => $request->recommended_for_fortgeschrittene,
            'recommended_for_profis' => $request->recommended_for_profis,
            'duration' => $request->duration,
            'special_license_needed' => $request->special_license_needed,
            'required_special_license' => $request->required_special_license,
            'fishing_type' => $request->fishing_type,
            'fishing_from' => $request->fishing_from,
            'water_sonstiges' => $request->water_sonstiges,
            'target_fish_sonstiges' => $request->target_fish_sonstiges,
            'water' => serialize($request->water),
            'methods' => serialize($request->methods),
            'targets' => serialize($request->targets),
            'methods_sonstiges' => $request->methods_sonstiges,
            'water_name' => $request->water_name,
            'description' => $request->description,
            'required_equipment' => $request->required_equipment,
            'needed_equipment' => $request->needed_equipment,
            'meeting_point' => $request->meeting_point,
            'additional_information' => $request->additional_information,
            'catering' => $request->catering,
            'max_guests' => $request->max_guests,
            'price' => $request->price,
            'price_two_persons' => $request->price_two_persons,
            'price_three_persons' => $request->price_three_persons,
            'price_four_persons' => $request->price_four_persons,
            'price_five_persons' => $request->price_five_persons,
        ]);

        $images = app('guiding')->getImagesUrl($guiding);
        $imgKey = 'image_0';
        for($i=0;$i<=4;$i++){
            if(!isset($images['image_'.$i])){
                $imgKey = 'image_'.$i;
                break;
            }
        }
    
        foreach($files as $file){
            foreach($file as $index => $f){
                app('asset')->uploadImage($guiding,$imgKey,$f);
            }
  
        }

        return redirect()->back()->with(['message' => 'Das Guiding wurde erfolgreich bearbeitet!']);
    }

    public function deleteImage(Guiding $guiding, $img){

        app('asset')->deleteThumbnails($guiding, $img);
        app('asset')->deleteImage($guiding, $img);
 
     }

    public function deleteguiding($id)
    {
        $guiding = Guiding::find($id);
        if ($guiding->user_id == Auth::user()->id) {
            if ($guiding->status == 1)
                $guiding->status = 0;
            else {
                $guiding->status = 1;
            }
            $guiding->save();
            return redirect()->back()->with('message', "Das Guiding wurde erfolgreich deaktiviert");
        } else {
            return redirect()->back()->with('error', 'Du hast keine Berechtigung das Guiding zu löschen.. bitte wende Dich an einen Administrator');
        }
    }

    // request
    public function bookingrequest(){

        return view('pages.guidings.search-request');
    }

    

    public function bookingRequestStore(Request $request){
        // dd($request);
        $guideRequest = new GuidingRequest;
        
        $guideRequest->guide_type = $request->guideType;
        $guideRequest->rentaboat = $request->rentaboat;
        $guideRequest->fishing_duration = $request->fishingDuration;

        $guideRequest->country = $request->country;
        $guideRequest->city = $request->city;
        $guideRequest->days_of_tour = $request->tripDays;
        $guideRequest->days_of_fishing = $request->daysOfFishing;
        $guideRequest->accomodation = $request->accomodation;
        $guideRequest->targets = json_encode($request->target_fish);
        $guideRequest->methods = json_encode($request->methods);
        $guideRequest->fishing_from = $request->fishing_from;
        $guideRequest->number_of_guest = $request->numberofguest;
        $guideRequest->from_date = date("Y-m-d", strtotime($request->date_of_tour_from));  
        $guideRequest->to_date = date("Y-m-d", strtotime($request->date_of_tour_to));  
        $guideRequest->name = $request->name;
        $guideRequest->phone = $request->phone;
        $guideRequest->email = $request->email;

        $guideRequest->save();

        if($request->locale == 'en'){
            \App::setLocale('en');
        }else{
            \App::setLocale('de');
        }
        
        Mail::to(env('TO_CEO'))->queue(new GuidingRequestMail($guideRequest));
        Mail::to($request->email)->queue(new SearchRequestUserMail($guideRequest));

        return redirect()->back()->with('message', "Email Has been Sent");
    }
}
