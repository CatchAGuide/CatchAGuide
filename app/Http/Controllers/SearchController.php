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

        if (! is_numeric($latitude) || ! is_numeric($longitude)) {
            return view('pages.search.search');
        }

        $latitude = (float) $latitude;
        $longitude = (float) $longitude;

        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            return view('pages.search.search');
        }

        $radius = 500;

        if($request->has('radius') && is_numeric($request->get('radius'))){
            $radius = (float) $request->get('radius');
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


        $query->selectRaw(
            '(3959 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance',
            [$latitude, $longitude, $latitude]
        )
        ->having('distance', '<=', $radius)
        ->orderBy('distance');


        $guidings = $query->paginate(24);

        // dd($guidings);

 

        return view('pages.search.search');
    }

}
