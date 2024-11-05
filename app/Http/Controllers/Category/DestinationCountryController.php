<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\Guiding;
use App\Models\Method;
use App\Models\Target;
use App\Models\Water;
use Config;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Spatie\Geocoder\Geocoder;

class DestinationCountryController extends Controller
{
    public function index()
    {
        return view('pages.countries.index');
    }

    public function country(Request $request, $country, $region=null, $city=null)
    {
        $query = Destination::with(['faq', 'fish_chart', 'fish_size_limit', 'fish_time_limit']);

        $country_row = Destination::whereSlug($country)->whereType('country')->first();
        $region_row = Destination::whereSlug($region)->whereType('region')->first();
        $city_row = Destination::whereSlug($city)->whereType('city')->first();


        if (!is_null($city)) {
            if (is_null($city_row)) {
                abort(404);
            } elseif (is_null($region_row)) {
                abort(404);
            } elseif (is_null($country_row)) {
                abort(404);
            }
            $query = $query->whereType('city')->whereCountryId($country_row->id)->whereRegionId($region_row->id)->whereId($city_row->id);
        } elseif (!is_null($region)) {
            if (is_null($region_row)) {
                abort(404);
            } elseif (is_null($country_row)) {
                abort(404);
            }
            $query = $query->whereType('region')->whereCountryId($country_row->id)->whereId($region_row->id);
        } else {
            if (is_null($country_row)) {
                abort(404);
            }
            $query = $query->whereType('country')->whereId($country_row->id);
        }

        $row_data = $query->first();

        if (is_null($row_data)) {
            abort(404);
        }
        
        $regions = Destination::with(['faq', 'fish_chart', 'fish_size_limit', 'fish_time_limit'])->whereType('region')->whereCountryId($row_data->id)->get();
        $cities = Destination::with(['faq', 'fish_chart', 'fish_size_limit', 'fish_time_limit'])->whereType('city')->whereCountryId($row_data->id)->get();

        
        $faq = $row_data->faq;
        $fish_chart = $row_data->fish_chart;
        $fish_size_limit = $row_data->fish_size_limit;
        $fish_time_limit = $row_data->fish_time_limit;

        //$guidings = Guiding::where('status', 1)->whereNotNull('lat')->whereNotNull('lng')->paginate(10);

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

        $radius = null; // Radius in miles
        if($request->has('radius')){

            $title .= __('guidings.Radius') . ' ' . $request->radius . 'km | ';
            $radius = $request->get('radius');
        }
        $geocode = $this->getCoordinates($country, $region, $city);
        
        $placeLat = $geocode['lat'];
        $placeLng = $geocode['lng'];

        $title .= __('guidings.Country') . ' ' . $country . ' | ';
        //$query->where('country',$country);

        if(!empty($placeLat) && !empty($placeLng)){

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

       
        $guidings = $query->paginate(6);


        $guidings->appends(request()->except('page'));

        $data = compact('row_data', 'regions', 'cities', 'faq', 'fish_chart', 'fish_size_limit', 'fish_time_limit', 'guidings', 'radius', 'allGuidings', 'otherguidings', 'title');

        return view('pages.category.country', $data);
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

    public function getCoordinates($country, $region=null, $city=null)
    {
        $client = new \GuzzleHttp\Client();
        $geocoder = new Geocoder($client);

        $geocoder->setApiKey('AIzaSyBiGuDOg_5yhHeoRz-7bIkc9T1egi1fA7Q');

        $geocoder->setCountry($country);

        $address = $country;

        if (!is_null($city)) {
            $address = $city . ', ' . $region . ', ' . $country;
        } elseif (!is_null($region)) {
            $address = $region . ', ' .$country;
        }

        $coordinates = $geocoder->getCoordinatesForAddress($address);

        return $coordinates;
    }
}
