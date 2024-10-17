<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGuidingRequest;
use App\Http\Requests\StoreNewGuidingRequest;
use App\Models\FishType;
use App\Models\Gallery;
use App\Models\Guiding;
use App\Models\Target;
use App\Models\Method;
use App\Models\Water;
use App\Models\GuidingRequest;
use App\Models\Inclussion;
use App\Models\GuidingExtra;
use App\Models\GuidingPrice;
use App\Models\GuidingTargetFish;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Auth;
use Config;
use Illuminate\Support\Facades\Mail;
use App\Mail\GuidingRequestMail;
use App\Mail\SearchRequestUserMail;
use Illuminate\Support\Facades\Log;
use App\Models\BlockedEvent;

class GuidingsController extends Controller
{
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

        $guiding = Guiding::where('id',$id)->where('slug',$slug)->where('status',1)->first();
        $targetFish = $guiding->is_newguiding ? json_decode($guiding->target_fish, true) : $guiding->guidingTargets->pluck('id')->toArray();
        $fishingFrom = $guiding->fishing_from_id;
        $fishingType = $guiding->fishing_type_id;

        $ratings = $guiding->user->received_ratings;
        $ratingCount = $ratings->count();
        $averageRating = $ratingCount > 0 ? $ratings->avg('rating') : 0;
        
        $otherGuidings = Guiding::where('status', 1)
            ->where('id', '!=', $guiding->id)
            ->where(function($query) use ($guiding, $targetFish, $fishingFrom, $fishingType) {
                $query->where(function($q) use ($guiding, $targetFish) {
                    if ($guiding->is_newguiding) {
                        $q->where('is_newguiding', 1)
                          ->where(function($subQ) use ($targetFish) {
                              foreach ($targetFish as $fish) {
                                  $subQ->orWhereJsonContains('target_fish', $fish);
                              }
                          });
                    } else {
                        $q->whereHas('guidingTargets', function($subQ) use ($targetFish) {
                            $subQ->whereIn('target_id', $targetFish);
                        });
                    }
                })
                ->where(function($q) use ($guiding, $fishingFrom) {
                    $q->where('fishing_from_id', $fishingFrom)
                      ->orWhereHas('fishingFrom', function($subQ) use ($fishingFrom) {
                          $subQ->where('id', $fishingFrom);
                      });
                })
                ->where(function($q) use ($guiding, $fishingType) {
                    $q->where('fishing_type_id', $fishingType)
                      ->orWhereHas('fishingTypes', function($subQ) use ($fishingType) {
                          $subQ->where('id', $fishingType);
                      });
                });
            })
            ->limit(4)
            ->get();

        $sameGuidings = Guiding::where('user_id', $guiding->user_id)
            ->where('id', '!=', $guiding->id)
            ->limit(10)
            ->get();

        return view('pages.guidings.newIndex', [
            'guiding' => $guiding,
            'same_guiding' => $sameGuidings,
            'ratings' => $ratings,
            'other_guidings' => $otherGuidings,
            'average_rating' => $averageRating,
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
    
    public function guidingsStore(StoreNewGuidingRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $isDraft = $data['is_draft'] ?? false;

            if ($request->input('is_update') == '1') {
                $guiding = Guiding::findOrFail($request->input('guiding_id'));
            } else {
                $guiding = new Guiding();
                $guiding->user_id = auth()->id();
            }

            $guiding->is_newguiding = 1;
            $guiding->slug = slugify($request->input('title') . "-" . $request->input('location') . "-" . auth()->id());

            //step 1
            $guiding->location = $request->has('location') ? $request->input('location') : '';
            $guiding->title = $request->has('title') ? $request->input('title') : '';
            $guiding->lat = $request->has('latitude') ? $request->input('latitude') : '';
            $guiding->lng = $request->has('longitude') ? $request->input('longitude') : '';
            $guiding->country = $request->has('country') ? $request->input('country') : '';
            
            $galeryImages = [];
            if ($request->has('title_image')) {
                foreach($request->file('title_image') as $index => $image){
                    $webp_path = media_upload($image, 'guidings-images', $guiding->slug. "-". $index);
    
                    if ($index == $request->input('primaryImage', 0)) {
                        $guiding->thumbnail_path = $webp_path;
                    }
                    $galeryImages[] = $webp_path;
                }
            }
            $guiding->galery_images = json_encode($galeryImages);

            //step 2
            $guiding->is_boat = $request->has('type_of_fishing') ? ($request->input('type_of_fishing') == 'boat' ? 1 : 0) : 0;
            $guiding->fishing_from_id = $request->has('type_of_fishing') ? ($request->input('type_of_fishing') == 'boat' ? 1 : 2) : 2;
            if ($guiding->is_boat) {
                $guiding->boat_type = $request->has('type_of_boat') ? $request->input('type_of_boat') : '';
                $guiding->boat_information = $this->saveDescriptions($request);
                $guiding->boat_extras = $request->has('extras') ? $request->input('extras') : '';
            }

            //step 3
            $guiding->target_fish = $request->has('target_fish') ? $request->input('target_fish') : '';
            $guiding->fishing_methods = $request->has('methods') ? $request->input('methods') : '';
            $guiding->water_types = $request->has('water_types') ? $request->input('water_types') : '';

            //step 4
            $guiding->experience_level = $request->has('experience_level') ? json_encode($request->input('experience_level')) : '';
            $guiding->inclusions = $request->has('inclussions') ? $request->input('inclussions') : '';
            if ($request->has('type_of_fishing')) {
                $guiding->style_of_fishing = $request->input('style_of_fishing');

                if ($request->input('type_of_fishing') == 'active') {
                    $guiding->fishing_type_id = 1;
                } else if ($request->input('type_of_fishing') == 'passive') {
                    $guiding->fishing_type_id = 2;
                } else {
                    $guiding->fishing_type_id = 3;
                }
            }

            //step 5
            $guiding->desc_course_of_action = $request->has('desc_course_of_action') ? $request->input('desc_course_of_action') : '';
            $guiding->desc_meeting_point = $request->has('desc_meeting_point') ? $request->input('desc_meeting_point') : '';
            $guiding->meeting_point = $request->has('meeting_point') ? $request->input('desc_meeting_point') : '';
            $guiding->desc_starting_time = $request->has('desc_starting_time') ? $request->input('desc_starting_time') : '';
            $guiding->desc_tour_unique = $request->has('desc_tour_unique') ? $request->input('desc_tour_unique') : '';
            $guiding->description = $this->generateLongDescription($request);

            //step 6
            $guiding->requirements = $this->saveRequirements($request);
            $guiding->recommendations = $this->saveRecommendations($request);   
            $guiding->other_information = $this->saveOtherInformation($request);  

            //step 7
            $guiding->tour_type = $request->has('tour_type') ? $request->input('tour_type') : '';

            $guiding->duration_type = $request->has('duration') ? $request->input('duration') : '';
            if ($request->input('duration') == 'multi_day') {
                $guiding->duration = $request->input('duration_days');
            } else {
                $guiding->duration = $request->input('duration_hours');
            }

            $guiding->max_guests = $request->has('no_guest') ? $request->input('no_guest') : 0;

            if ($request->has('price_type') ) {
                $guiding->price_type = $request->input('price_type');
                if ( $request->input('price_type') === 'per_person') {
                    $pricePerPerson = [];
                    foreach ($request->all() as $key => $value) {
                        if (strpos($key, 'price_per_person_') === 0) {
                            $guestNumber = substr($key, strlen('price_per_person_'));
                            $pricePerPerson[] = [
                                'person' => $guestNumber,
                                'amount' => $value
                            ];
                        }
                    }
                    $guiding->prices = json_encode($pricePerPerson);
                    $guiding->price = 0;
                } else {
                    $guiding->prices = json_encode([ 'person' => 0, 'amount' => $request->input('price_per_boat')]);
                    $guiding->price = $request->input('price_per_boat');
                }
            }
            
            $pricingExtras = [];
            $i = 1;
            while (true) {
                $nameKey = "extra_name_" . $i;
                $priceKey = "extra_price_" . $i;
                
                if ($request->has($nameKey) && $request->has($priceKey)) {
                    $pricingExtras[] = [
                        'name' => $request->input($nameKey),
                        'price' => $request->input($priceKey)
                    ];
                    if ($i == 1) {
                        $guiding->price = $request->input($priceKey);
                    }
                    $i++;
                } else {
                    break;
                }
            }
            $guiding->pricing_extra = json_encode($pricingExtras);

            //step 8    
            $guiding->allowed_booking_advance = $request->has('allowed_booking_advance') ? $request->input('allowed_booking_advance') : '';
            $guiding->booking_window = $request->has('booking_window') ? $request->input('booking_window') : '';

            if ($request->has('seasonal_trip')) {
                $guiding->seasonal_trip = $request->input('seasonal_trip');
                $allMonths = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];
                
                if ($request->input('seasonal_trip') == "season_monthly") {
                    $selectedMonths = $request->input('months');
                    $guiding->months = json_encode($selectedMonths);
                } else {
                    $selectedMonths = $allMonths;
                    $guiding->months = json_encode($selectedMonths);
                }

                if ($request->input('is_update') == '1') {
                    BlockedEvent::where('guiding_id', $guiding->id)
                                ->where('type', 'blockiert')
                                ->delete();
                }

                foreach ($allMonths as $index => $month) {
                    if (!in_array($month, $selectedMonths)) {
                        $year = date('Y');
                        $monthNumber = str_pad($index + 1, 2, '0', STR_PAD_LEFT);
                        $blockedFrom = date('Y-m-d', strtotime("$year-$monthNumber-01"));
                        $blockedTo = date('Y-m-t', strtotime($blockedFrom));
                        
                        BlockedEvent::create([
                            'user_id' => $guiding->user_id,
                            'type' => 'blockiert',
                            'guiding_id' => $guiding->id,
                            'from' => $blockedFrom,
                            'due' => $blockedTo,
                        ]);
                    }
                }
            }

            $guiding->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $isDraft ? 'Draft saved successfully!' : 'Guiding created successfully!',
                'redirect_url' => route('profile.myguidings'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in guidingsStore: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['error' => 'An error occurred while processing your request.'], 500);
        }
    }

    public function saveDraft(Request $request)
    {
        $user = Auth::user();
        $data = $request->all();
        $currentStep = $request->input('current_step', 1);

        return response()->json(['success' => true, 'message' => 'Draft saved successfully']);
    }

    private function generateLongDescription($request)
    {
        $longDescriptions = json_decode(file_get_contents(public_path('assets/prompts/long_description.json')), true);
        $randomDescription = $longDescriptions['options'][array_rand($longDescriptions['options'])];

        $description = str_replace(
            ['{course_of_action}', '{meeting_point}', '{special_about}', '{tour_unique}', '{starting_time}'],
            [$request->desc_course_of_action, $request->desc_meeting_point, "", $request->desc_tour_unique, $request->desc_starting_time],
            $randomDescription['text']
        );

        return $description;
    }

    private function saveDescriptions( $request)
    {
        $descriptions = $request->input('descriptions', []);
        $descriptionData = [];

        foreach ($descriptions as $description) {
            $descriptionData[$description] = $request->input($description);
        }

        return json_encode($descriptionData);
    }

    private function saveOtherInformation($request)
    {
        $otherInformations = $request->input('other_information', []);
        $otherInformationData = [];

        foreach ($otherInformations as $otherInformation) {
            $otherInformationData[$otherInformation] = $request->input($otherInformation);
        }

        return json_encode($otherInformationData);
    }

    private function saveRequirements($request)
    {
        $requirements = $request->input('requiements_taking_part', []);
        $requirementData = [];

        foreach ($requirements as $requirement) {
            $requirementData[$requirement] = $request->input($requirement);
        }

        return json_encode($requirementData);
    }

    private function saveRecommendations($request)
    {
        $recommendations = $request->input('recommended_preparation', []);
        $recommendationData = [];

        foreach ($recommendations as $recommendation) {
            $recommendationData[$recommendation] = $request->input($recommendation);
        }

        return json_encode($recommendationData);
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

    public function edit_newguiding(Guiding $guiding)
    {
        // Ensure the user has permission to edit this guiding
        if ($guiding->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

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
            'galery_images' => $guiding->galery_images,
            'thumbnail_path' => $guiding->thumbnail_path,

            //step 2
            'type_of_fishing' => $guiding->is_boat ? 'boat' : 'shore',
            'boat_type' => $guiding->boat_type,
            'boat_information' => json_decode($guiding->boat_information, true),
            'boat_extras' => json_decode($guiding->boat_extras, true),

            //step 3
            'target_fish' => json_decode($guiding->target_fish, true),
            'methods' => json_decode($guiding->fishing_methods, true),
            'water_types' => json_decode($guiding->water_types, true),

            //step 4
            'experience_level' => json_decode($guiding->experience_level, true),
            'inclussions' => json_decode($guiding->inclusions, true),
            'style_of_fishing' => $guiding->style_of_fishing,

            //step 5
            'long_description' => $guiding->description,
            'desc_course_of_action' => $guiding->desc_course_of_action,
            'desc_starting_time' => $guiding->desc_starting_time,
            'desc_meeting_point' => $guiding->desc_meeting_point,
            'desc_tour_unique' => $guiding->desc_tour_unique,
            
            //step 6
            'requirements' => json_decode($guiding->requirements, true),
            'recommendations' => json_decode($guiding->recommendations, true),
            'other_information' => json_decode($guiding->other_information, true),

            //step 7
            'tour_type' => $guiding->tour_type,
            'duration' => $guiding->duration,
            'duration_type' => $guiding->duration_type,
            'no_guest' => $guiding->max_guests,
            'price_type' => $guiding->price_type,
            'prices' => json_decode($guiding->prices, true),
            'pricing_extra' => json_decode($guiding->pricing_extra, true),

            //step 8
            'allowed_booking_advance' => $guiding->allowed_booking_advance,
            'booking_window' => $guiding->booking_window,
            'seasonal_trip' => $guiding->seasonal_trip,
            'months' => json_decode($guiding->months, true),
        ];

        // Get necessary data for dropdowns
        $targets = Target::all();
        $waters = Water::all();
        $methods = Method::all();
        $inclussions = Inclussion::all();

        $locale = Config::get('app.locale');
        if($locale == 'en') {
            $targets = $targets->map(function ($item) {
                return ['value' => $item->name_en, 'id' => $item->id];
            });
            $methods = $methods->map(function ($item) {
                return ['value' => $item->name_en, 'id' => $item->id];
            });
            $waters = $waters->map(function ($item) {
                return ['value' => $item->name_en, 'id' => $item->id];
            });
            $inclussions = $inclussions->map(function ($item) {
                return ['value' => $item->name_en, 'id' => $item->id];
            });
        } else {
            $targets = $targets->map(function ($item) {
                return ['value' => $item->name, 'id' => $item->id];
            });
            $methods = $methods->map(function ($item) {
                return ['value' => $item->name, 'id' => $item->id];
            });
            $waters = $waters->map(function ($item) {
                return ['value' => $item->name, 'id' => $item->id];
            });
            $inclussions = $inclussions->map(function ($item) {
                return ['value' => $item->name, 'id' => $item->id];
            });
        }

        return view('pages.profile.newguiding', compact('formData', 'waters', 'methods', 'targets', 'inclussions'));
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
