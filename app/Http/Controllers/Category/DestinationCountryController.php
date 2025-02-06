<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\Guiding;
use App\Models\Method;
use App\Models\Target;
use App\Models\Water;
use Config;
use DB;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Spatie\Geocoder\Geocoder;

class DestinationCountryController extends Controller
{
    public function index()
    {
        $countries = Destination::whereType('country')->get();
        return view('pages.countries.index', compact('countries'));
    }

    public function country(Request $request, $country, $region = null, $city = null)
    {
        // Validate existence of parent records first
        $country_row = Destination::whereSlug($country)
            ->whereType('country')
            ->firstOrFail(); // Use firstOrFail instead of first()

        $region_row = null;
        $city_row = null;

        if ($region) {
            $region_row = Destination::whereSlug($region)
                ->whereType('region')
                ->whereCountryId($country_row->id)
                ->firstOrFail();
        }

        if ($city) {
            $city_row = Destination::whereSlug($city)
                ->whereType('city')
                ->whereCountryId($country_row->id)
                ->whereRegionId($region_row->id)
                ->firstOrFail();
        }

        $place_location = $country;
        $query = Destination::with(['faq', 'fish_chart', 'fish_size_limit', 'fish_time_limit']);

        if ($city_row) {
            $query->whereType('city')->whereId($city_row->id);
        } elseif ($region_row) {
            $query->whereType('region')->whereId($region_row->id);
        } else {
            $query->whereType('country')->whereId($country_row->id);
        }

        $row_data = $query->firstOrFail();

        $regions = Destination::with(['faq', 'fish_chart', 'fish_size_limit', 'fish_time_limit'])->whereType('region')->whereCountryId($row_data->id)->get();
        $cities = Destination::with(['faq', 'fish_chart', 'fish_size_limit', 'fish_time_limit'])->whereType('city')->whereCountryId($row_data->id)->get();

        $faq = $row_data->faq;
        $fish_chart = $row_data->fish_chart;
        $fish_size_limit = $row_data->fish_size_limit;
        $fish_time_limit = $row_data->fish_time_limit;

        $locale = Config::get('app.locale');

        $title = '';
        $randomSeed = Session::get('random_seed');
        if (!$randomSeed) {
            $randomSeed = rand();
            Session::put('random_seed', $randomSeed);
        }

        $query = Guiding::select(['*', DB::raw('(
            WITH price_per_person AS (
                SELECT 
                    CASE 
                        WHEN JSON_VALID(prices) THEN (
                            SELECT MIN(
                                CASE 
                                    WHEN person > 1 THEN CAST(amount AS DECIMAL(10,2)) / person
                                    ELSE CAST(amount AS DECIMAL(10,2))
                                END
                            )
                            FROM JSON_TABLE(
                                prices,
                                "$[*]" COLUMNS(
                                    person INT PATH "$.person",
                                    amount DECIMAL(10,2) PATH "$.amount"
                                )
                            ) as price_data
                        )
                        ELSE price
                    END as lowest_pp
            )
            SELECT COALESCE(lowest_pp, price) 
            FROM price_per_person
        ) AS lowest_price')])->where('status',1)->whereNotNull('lat')->whereNotNull('lng');
        
        // Get or generate a random seed and store it in the session
        $randomSeed = Session::get('random_seed');
        if (!$randomSeed) {
            $randomSeed = rand();
            Session::put('random_seed', $randomSeed);
        }

        // Only apply random ordering if:
        // 1. There are no query parameters except page
        // 2. We're on page 1 or no page is specified
        $hasOnlyPageParam = count(array_diff(array_keys($request->all()), ['page'])) === 0;
        $isFirstPage = !$request->has('page') || $request->get('page') == 1;

        if ($hasOnlyPageParam && $isFirstPage) {
            $query->orderByRaw("RAND($randomSeed)");
        } else {
            // Default ordering for all other cases
            if (!$request->has('sortby')) {
                $query->latest();
            }
        }

        if($request->has('page')){
            $title .= __('guidings.Page') . ' ' . $request->page . ' - ';
        }
        if($request->has('num_guests') && !empty($request->get('num_guests'))){
            $title .= __('guidings.Guest') . ' ' . $request->num_guests . ' | ';
            $q = $query->where('max_guests','>=',$request->get('num_guests'));
        }

        // Apply sorting if specified
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

                $query->where(function($query) use ($requestMethods) {
                    foreach($requestMethods as $methodId) {
                        $query->orWhereJsonContains('fishing_methods', (int)$methodId);
                    }
                });
            }
        }

        if($request->has('water') && !empty($request->get('water'))){
            $requestWater = array_filter($request->get('water'));

            if(count($requestWater)){
                $title .= __('guidings.Water') . ' (';
                $water_rows = Water::whereIn('id', $request->water)->get();
                $title_row = '';
                foreach ($water_rows as $row) {
                    $title_row .= (($locale == 'en')? $row->name_en : $row->name) . ', ';
                }
                $title .= substr($title_row, 0, -2);
                $title .= ') | ';

                $query->where(function($query) use ($requestWater) {
                    foreach($requestWater as $waterId) {
                        $query->orWhereJsonContains('water_types', (int)$waterId);
                    }
                });
            }
        }

        if($request->has('target_fish')){
            $requestFish = array_filter($request->get('target_fish'));

            if(count($requestFish)){
                $title .= __('guidings.Target_Fish') . ' (';
                $target_rows = Target::whereIn('id', $requestFish)->get();
                $title_row = '';
                foreach ($target_rows as $row) {
                    $title_row .= (($locale == 'en')? $row->name_en : $row->name) . ', ';
                }
                $title .= substr($title_row, 0, -2);
                $title .= ') | ';

                $query->where(function($query) use ($requestFish) {
                    foreach($requestFish as $fishId) {
                        $query->orWhereJsonContains('target_fish', (int)$fishId);
                    }
                });
            }
        }

        $radius = null; // Radius in miles
        if($request->has('radius')){
            $title .= __('guidings.Radius') . ' ' . $request->radius . 'km | ';
            $radius = $request->get('radius');
        }

        $filterData = json_decode($row_data->filters, true);

        $placeLat = $filterData['placeLat'];
        $placeLng = $filterData['placeLng'];
        $city = $filterData['city'] ?? null;
        $country = $filterData['country'] ?? null;
        $region = $filterData['region'] ?? null;

        $title .= __('guidings.Country') . ' ' . $country . ' | ';

        if(!empty($placeLat) && !empty($placeLng)){
            $title .= __('guidings.Coordinates') . ' Lat ' . $placeLat . ' Lang ' . $placeLng . ' | ';
            $guidingFilter = Guiding::locationFilter($city, $country, $region, $radius, $placeLat, $placeLng);
            $searchMessage = $guidingFilter['message'];
            
            // Add a subquery to order by the position in the filtered IDs array
            $orderByCase = 'CASE guidings.id ';
            foreach($guidingFilter['ids'] as $position => $id) {
                $orderByCase .= "WHEN $id THEN $position ";
            }
            $orderByCase .= 'ELSE ' . count($guidingFilter['ids']) . ' END';
            
            $query->whereIn('guidings.id', $guidingFilter['ids'])
                  ->orderByRaw($orderByCase);
        }

        if($request->has('price_range') && !empty($request->get('price_range'))){
            $price_range = explode('-', $request->get('price_range'));
            
            if(count($price_range) == 2) {
                $min_price = $price_range[0];
                $max_price = $price_range[1];
                $title .= __('guidings.Price') . ' ' . $min_price . '€ - ' . $max_price . '€ | ';
                
                $query->having(DB::raw('lowest_price'), '>=', $min_price)
                      ->having(DB::raw('lowest_price'), '<=', $max_price);
            } elseif(count($price_range) == 1) {
                // Handle single value (350 and more)
                $min_price = $price_range[0];
                $title .= __('guidings.Price') . ' ' . $min_price . '€+ | ';
                
                $query->having(DB::raw('lowest_price'), '>=', $min_price);
            }
        }

        $guidings_total = $query->count();
        $allGuidings = $query->get();

        $otherguidings = array();

        if($allGuidings->isEmpty()){
            if($request->has('placeLat') && $request->has('placeLng') && !empty($request->get('placeLat')) && !empty($request->get('placeLng'))){
                $latitude = $request->get('placeLat');
                $longitude = $request->get('placeLng');
                $otherguidings = $this->otherGuidingsBasedByLocation($latitude,$longitude);
            } else {
                $otherguidings = $this->otherGuidings();
            }
        }

       
        $guidings = $query->paginate(6);
        $guidings->appends(request()->except('page'));

        $data = compact('row_data', 'regions', 'cities', 'faq', 'fish_chart', 'fish_size_limit', 'fish_time_limit', 'guidings', 'radius', 'allGuidings', 'otherguidings', 'title', 'guidings_total');

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

        $geocoder->setApiKey(env('GOOGLE_MAP_API_KEY'));

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
