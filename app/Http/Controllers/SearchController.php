<?php

namespace App\Http\Controllers;

use App\Models\Guiding;
use App\Models\Method;
use App\Models\Target;
use App\Models\Water;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {


        $query = Guiding::query();

        $latitude = $request->get('placeLat');
        $longitude = $request->get('placeLng');

        $radius = 500;

        if($request->has('radius') && !empty($request->get('radius'))){
            $radius = $request->get('radius');
        }
        if($request->has('methods') && !empty($request->get('methods'))){
            $methods = $request->get('methods');

            $query->whereHas('guidingMethods', function ($query) use ($methods) {
                $query->where('method_id', $methods);
            });
        }

        if($request->has('water') && !empty($request->get('water'))){
            $water = $request->get('water');

            $query->whereHas('guidingWaters', function ($query) use ($water) {
                $query->where('water_id', $water);
            });
        }


        if($request->has('target_fish') && !empty($request->get('target_fish'))){
            $target_fish = $request->get('target_fish');

            $query->whereHas('guidingTargets', function ($query) use ($target_fish) {
                $query->where('target_id', $target_fish);
            });
        }


        $query->selectRaw("(3959 * acos(cos(radians($latitude)) * cos(radians(lat)) * cos(radians(lng) - radians($longitude)) + sin(radians($latitude)) * sin(radians(lat)))) AS distance")
        ->having('distance', '<=', $radius)
        ->orderBy('distance');


        $guidings = $query->paginate(24);

        dd($guidings);

 

        return view('pages.search.search');
    }

}
