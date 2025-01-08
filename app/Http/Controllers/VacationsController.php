<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vacation;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class VacationsController extends Controller
{
    public function index(Request $request)
    {
        $locale = Config::get('app.locale');

        $title = 'Holidays';
        $filter_title = 'Holidays';
        $randomSeed = Session::get('random_seed');
        if (!$randomSeed) {
            $randomSeed = rand();
            Session::put('random_seed', $randomSeed);
        }

        $query = Vacation::where('status',1);

        if (empty($request->all())) {
            $query->orderByRaw("RAND($randomSeed)");
        }

        if($request->has('page')){
            $title .= __('vacations.Page') . ' ' . $request->page . ' - ';
        }

        if($request->has('num_guests') && !empty($request->get('num_guests'))){
            $title .= __('vacations.Guest') . ' ' . $request->num_guests . ' | ';
            $filter_title .= __('vacations.Guest') . ' ' . $request->num_guests . ', ';
            $q = $query->where('max_guests','>=',$request->get('num_guests'));
        }

        if($request->has('sortby') && !empty($request->get('sortby'))){
            switch ($request->get('sortby')) {
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;

                case 'price-asc':
                    $query->orderBy(DB::raw('lowest_price'), 'asc');
                    break;

                case 'price-desc':
                    $query->orderBy(DB::raw('lowest_price'), 'desc');
                    break;

                case 'long-duration':
                    $query->orderBy('duration', 'desc');
                    break;

                case 'short-duration':
                    $query->orderBy('duration', 'asc');
                    break;

                default:
                    if (empty($request->all())) {
                        $query->orderByRaw("RAND($randomSeed)");
                    }
            }
        }

        $searchMessage = '';
        $placeLat = $request->get('placeLat');
        $placeLng = $request->get('placeLng');

        if(!empty($placeLat) && !empty($placeLng) && !empty($request->get('place'))){

            $title .= __('vacations.Coordinates') . ' Lat ' . $placeLat . ' Lang ' . $placeLng . ' | ';
            $filter_title .= __('vacations.Coordinates') . ' Lat ' . $placeLat . ' Lang ' . $placeLng . ', ';
            $vacationFilter = Vacation::locationFilter($request->get('place'), $radius, $placeLat, $placeLng);
            $searchMessage = $vacationFilter['message'];
            $query->whereIn('id', $vacationFilter['ids']);
        }

        $allVacations = $query->get();

        $othervacations = array();

        if($allVacations->isEmpty()){

            if($request->has('placeLat') && $request->has('placeLng') && !empty($request->get('placeLat')) && !empty($request->get('placeLng')) ){
                $latitude = $request->get('placeLat');
                $longitude = $request->get('placeLng');
            
                $othervacations = $this->otherVacationsBasedByLocation($latitude,$longitude);

            }else{

                $othervacations = $this->otherVacations();

            }
        }

        $vacations = $query->paginate(20);

        $filter_title = substr($filter_title, 0, -2);

        $vacations->appends(request()->except('page'));

        return view('pages.vacations.index', compact('vacations', 'othervacations', 'allVacations', 'title', 'filter_title', 'searchMessage'));
    }

    public function show($id)
    {
        $vacation = Vacation::find($id);
        return view('pages.vacations.show', compact('vacation'));
    }

    private function otherVacations(){
        $othervacations = Vacation::inRandomOrder('1234')->where('status',1)->limit(10)->get();

        return $othervacations;
    }

    private function otherVacationsBasedByLocation($latitude,$longitude){
        $nearestlisting = Vacation::select(['vacations.*']) // Include necessary attributes here
        ->selectRaw("(6371 * acos(cos(radians($latitude)) * cos(radians(lat)) * cos(radians(lng) - radians($longitude)) + sin(radians($latitude)) * sin(radians(lat)))) AS distance")
        ->orderBy('distance')
        ->where('status',1)
        ->limit(10)
        ->get();

        return $nearestlisting;
    }
}