<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGuidingRequest;
use App\Models\Gallery;
use App\Models\Guiding;
use App\Models\Method;
use App\Models\Target;
use App\Models\Water;
use Auth;
use Illuminate\Http\Request;

class GuidingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $query = Guiding::query();
    
        if ($request->has('num_guests')) {
            $query->where('max_guests', $request->get('num_guests'));
        }
    
        if ($request->has('sortby') && !empty($request->get('sortby'))) {
            switch ($request->get('sortby')) {
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'price-asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price-desc':
                    $query->orderBy('price', 'desc');
                    break;
                default:
                    $query;
            }
        }
    
        if ($request->has('methods') && !empty($request->get('methods'))) {
            $requestMethods = $request->get('methods');
    
            $query->whereHas('guidingMethods', function ($query) use ($requestMethods) {
                $query->whereIn('method_id', $requestMethods);
            });
        }
    
        if ($request->has('water') && !empty($request->get('water'))) {
            $requestWater = $request->get('water');
    
            $query->whereHas('guidingWaters', function ($query) use ($requestWater) {
                $query->whereIn('water_id', $requestWater);
            });
        }
    
        if ($request->has('target_fish') && !empty($request->get('target_fish'))) {
            $requestFish = $request->get('target_fish');
    
            $query->whereHas('guidingTargets', function ($query) use ($requestFish) {
                $query->whereIn('target_id', $requestFish);
            });
        }
    
        $radius = $request->get('radius') ? $request->get('radius') : 300; // Radius in miles
    
        if ($request->has('placeLat') && $request->has('placeLng') && !empty($request->get('placeLat')) && !empty($request->get('placeLng'))) {
            $latitude = $request->get('placeLat');
            $longitude = $request->get('placeLng');
    
            $query->select(['guidings.*']) // Include necessary attributes here
                ->selectRaw("(3959 * acos(cos(radians($latitude)) * cos(radians(lat)) * cos(radians(lng) - radians($longitude)) + sin(radians($latitude)) * sin(radians(lat)))) AS distance")
                ->having('distance', '<=', $radius)
                ->orderBy('distance');
        }
    
        $allGuidings = $query->get();
    
        $guidings = $query->paginate(24);
    
        if ($request->ajax()) {

                return response()->json([
                    'guidings' => view('pages.guidings.partials.guidingcontainer', ['guidings' => $guidings])->render(),
                    'allGuidings' => $allGuidings, // Include the full guiding data in the response
                ]);

        }
    
        $guidings->appends(request()->except('page'));
    
        return view('pages.guidings.index', [
            'guidings' => $guidings,
            'radius' => $radius,
            'allGuidings' => $allGuidings,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
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

        /*
        $file = $request->file('thumbnail');
        $file_name = md5($file->getClientOriginalName() . microtime()) . '.' . $file->getClientOriginalExtension();
        $thumbnail = $file->move(public_path('images'), $file_name);
        $data['thumbnail_path'] = $file_name;
        */



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

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Guiding $guiding
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function show($id,$slug)
    {
        $guiding = Guiding::where('id',$id)->where('slug',$slug)->first();

        if(!$guiding){
            abort(404);
        }
        
        $amountShowingOtherGuides = 3;
        $ratings = $guiding->user->received_ratings;
        $ratingCount = $ratings->count();
        $averageRating = $ratingCount > 0 ? $ratings->avg('rating') : 0;
        $otherGuidings = Guiding::where('id', '!=', $guiding->id)
            ->where('status', 1)
            ->get()
            ->take($amountShowingOtherGuides);
            
        return view('pages.guidings.show', [
            'guiding' => $guiding,
            'ratings' => $ratings,
            'other_guidings' => $otherGuidings,
            'average_rating' => $averageRating,
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Guiding $guiding
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit(Guiding $guiding)
    {
        $targets = Target::all();
        $methods = Method::all();
        $waters = Water::all();
        

        return view('pages.guidings.edit', compact('guiding','targets', 'methods', 'waters'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Guiding $guiding
     * @return \Illuminate\Http\Response
     */
    public function update(StoreGuidingRequest $request, Guiding $guiding)
    {


        $files = $request->files;
  
        // TODO prüfen ob Admin oder Besitzer des Guidings Dinge ändert
        // $data = $request->validated();
        // if ($request->user_id) {
        //     $user = $request->user_id;
        // } else {
        //     $user = auth()->user()->id;
        // }

     

        // $data['user_id'] = $guiding->id;
        // $data['slug'] = slugify($data['title'] . "-in-" . $data['location']);

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

        // $waters = $request->water;
        // array_unshift($waters, 'alle');
        // $data['water'] = serialize($waters);
        // $targets = $request->targets;
        // array_unshift($targets, 'alle');
        // $data['targets'] = serialize($targets);
        // $methods = $request->methods;
        // array_unshift($methods, 'alle');
        // $data['methods'] = serialize($methods);

        // if ($request->hasFile('thumbnail')) {
        //     $file = $request->file('thumbnail');
        //     $file_name = md5($file->getClientOriginalName() . microtime()) . '.' . $file->getClientOriginalExtension();
        //     $thumbnail = $file->move(public_path('images'), $file_name);
        //     $data['thumbnail_path'] = $file_name;
        // }

        // Add Gebühren
        // if($data['price_two_persons'] > 0) {
        //     $data['price_two_persons'];
        // }
        // if($data['price_three_persons'] > 0) {
        //     $data['price_three_persons'];
        // }
        // if($data['price_four_persons'] > 0) {
        //     $data['price_four_persons'];
        // }
        // if($data['price_five_persons'] > 0) {
        //     $data['price_five_persons'];
        // }


        // $x = Guiding::find($guiding->id);
        // $guiding->update($data);

        // Old Pictures Delete
        // if(isset($request->deleteimage)) {
        //     foreach ($request->deleteimage as $imageId => $file) {
        //         Gallery::find($imageId)->delete();
        //     }
        // }

        // NEW Picture
        // if(isset($request->gallery) && !empty($request->gallery)) {
        //     foreach ($request->gallery as $key => $file) {
        //         if(isset($file['image_name'])) {
        //             $maxFileSizeInBytes = 20971520;
        //             $fileSizeInBytes = $file['image_name']->getSize();
        //             if ($fileSizeInBytes > $maxFileSizeInBytes) {
        //                 return redirect()->back()->withErrors(['file' => 'Die Datei ist zu groß. Maximalgröße: 20MB']);
        //             }
        //             $name = time() . rand(1, 50) . '.' . $file['image_name']->extension();
        //             $file['image_name']->move(public_path('files'), $name);
        //             $gallery = new Gallery();
        //             $gallery->image_name = $name;
        //             $gallery->user_id = auth()->user()->id;
        //             $gallery->avatar = isset($file['avatar']) && $file['avatar'] == "on" ? 1 : 0;
        //             $gallery->guiding_id = $guiding->id;
        //             $gallery->save();
        //         }
        //     }
        // }

        // Update
        // if(isset($request['gallery']) && !isset($request->deleteimage)) {
        //         foreach($request['gallery'] as $key => $value) {
        //             if($key != 0) {
        //                 $gallery = Gallery::find($key);
        //                 if($gallery != NULL) {
        //                     $gallery->avatar = 1;
        //                     $gallery->update();
        //                     $otherPictures = Gallery::all()
        //                         ->where('guiding_id', '=', $id)
        //                         ->where('id', '!=', $gallery->id);
        //                     foreach($otherPictures as $otherPicture) {
        //                         $otherPicture->avatar = 0;
        //                         $otherPicture->update();
        //                     }
        //                 }
        //             continue;
        //         }
        //     }
        // }
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
}
