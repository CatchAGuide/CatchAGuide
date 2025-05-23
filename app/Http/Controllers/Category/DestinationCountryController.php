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
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Spatie\Geocoder\Geocoder;
use Illuminate\Support\Facades\Log;
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
        $searchMessage = "";
        $title = '';
        $filter_title = '';
        $destination = null;

        // Get or generate a random seed and store it in the session
        $randomSeed = Session::get('random_seed');
        if (!$randomSeed) {
            $randomSeed = rand();
            Session::put('random_seed', $randomSeed);
        }

        // Clean up request parameters before processing
        $cleanedRequest = $this->cleanRequestParameters($request);
        
        // Eager load relationships to avoid N+1 problem
        $baseQuery = Guiding::with(['target_fish', 'methods', 'water_types', 'boatType'])
            ->select(['*', DB::raw('(
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
        
        // Use caching for price ranges
        $cacheKey = 'guiding_price_ranges';
        $cacheDuration = 60 * 24; // Cache for 24 hours

        if (Cache::has($cacheKey)) {
            $priceRangeData = Cache::get($cacheKey);
            $priceRanges = $priceRangeData['ranges'];
            $overallMaxPrice = $priceRangeData['maxPrice'];
        } else {
            // Optimize max price query
            $maxPriceResult = DB::table('guidings')
                ->selectRaw('MAX(
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
                    END
                ) as max_price')
                ->where('status', 1)
                ->first();

            $overallMaxPrice = ceil(($maxPriceResult->max_price ?? 5000) / 50) * 50;
            
            // Define price ranges
            $priceRanges = [];
            $minPrice = 50;
            $step = 50;
            
            for ($i = $minPrice; $i <= $overallMaxPrice; $i += $step) {
                $rangeEnd = min($i + $step, $overallMaxPrice);
                $priceRanges[] = [
                    'min' => $i,
                    'max' => $rangeEnd,
                    'range' => "€{$i}-€{$rangeEnd}",
                    'count' => 0
                ];
            }
            
            // Count guidings in each price range
            $priceResults = DB::table('guidings')
                ->select('id', DB::raw('
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
                    END as lowest_price
                '))
                ->where('status', 1)
                ->get();
            
            foreach ($priceResults as $guiding) {
                $price = $guiding->lowest_price;
                if ($price >= $minPrice && $price <= $overallMaxPrice) {
                    foreach ($priceRanges as &$range) {
                        if ($price >= $range['min'] && $price < $range['max']) {
                            $range['count']++;
                            break;
                        }
                    }
                }
            }
            
            // Cache the results
            Cache::put($cacheKey, [
                'ranges' => $priceRanges,
                'maxPrice' => $overallMaxPrice
            ], $cacheDuration);
        }
        
        // 2. Apply filters to the query
        $filteredQuery = clone $baseQuery;
        
        // If coming from destination page, get the destination context
        if ($cleanedRequest->has('from_destination')) {
            $destination = Destination::where('id', $cleanedRequest->input('destination_id'))->first();
            
            if ($destination) {
                switch ($destination->type) {
                    case 'country':
                        $filteredQuery->where('country', $destination->name);
                        break;
                    case 'region':
                        $filteredQuery->where('region', $destination->name)
                              ->where('country', $destination->country_name);
                        break;
                    case 'city':
                        $filteredQuery->where('city', $destination->name)
                              ->where('region', $destination->region_name)
                              ->where('country', $destination->country_name);
                        break;
                }
            }
        }

        // Apply sorting
        $hasOnlyPageParam = count(array_diff(array_keys($cleanedRequest->all()), ['page'])) === 0;
        $isFirstPage = !$cleanedRequest->has('page') || $cleanedRequest->get('page') == 1;

        if ($hasOnlyPageParam && $isFirstPage) {
            $filteredQuery->orderByRaw("RAND($randomSeed)");
        } else {
            // Default ordering for all other cases
            if ($cleanedRequest->has('sortby') && !empty($cleanedRequest->get('sortby'))) {
                switch ($cleanedRequest->get('sortby')) {
                    case 'newest':
                        $filteredQuery->orderBy('created_at', 'desc');
                        break;
                    case 'price-asc':
                        $filteredQuery->orderBy('lowest_price', 'asc');
                        break;
                    case 'price-desc':
                        $filteredQuery->orderBy('lowest_price', 'desc');
                        break;
                    case 'long-duration':
                        $filteredQuery->orderBy('duration', 'desc');
                        break;
                    case 'short-duration':
                        $filteredQuery->orderBy('duration', 'asc');
                        break;
                    default:
                        // Default to sorting by lowest price if no valid sort option is provided
                        $filteredQuery->orderBy('lowest_price', 'asc');
                }
            } else {
                // Default to sorting by lowest price if no sort option is provided
                $filteredQuery->orderBy('lowest_price', 'asc');
            }
        }
        
        // Apply title and filter title
        if($cleanedRequest->has('page')){
            $title .= __('guidings.Page') . ' ' . $cleanedRequest->page . ' - ';
        }

        // Apply method filters
        if($cleanedRequest->has('methods') && !empty($cleanedRequest->get('methods'))){
            $requestMethods = array_filter($cleanedRequest->get('methods'));

            if(count($requestMethods)) {
                $title .= __('guidings.Method') . ' (';
                $filter_title .= __('guidings.Method') . ' (';
                $method_rows = Method::whereIn('id', $cleanedRequest->methods)->get();
                $title_row = '';
                foreach ($method_rows as $row) {
                    $title_row .= (($locale == 'en')? $row->name_en : $row->name) . ', ';
                }
                $title .= substr($title_row, 0, -2);
                $title .= '), ';
                $filter_title .= substr($title_row, 0, -2);
                $filter_title .= '), ';

                $filteredQuery->where(function($query) use ($requestMethods) {
                    foreach($requestMethods as $methodId) {
                        $query->whereJsonContains('fishing_methods', (int)$methodId);
                    }
                });
            }
        }

        // Apply water filters
        if($cleanedRequest->has('water') && !empty($cleanedRequest->get('water'))){
            $requestWater = array_filter($cleanedRequest->get('water'));

            if(count($requestWater)){
                $title .= __('guidings.Water') . ' (';
                $filter_title .= __('guidings.Water') . ' (';
                $method_rows = Water::whereIn('id', $cleanedRequest->water)->get();
                $title_row = '';
                foreach ($method_rows as $row) {
                    $title_row .= (($locale == 'en')? $row->name_en : $row->name) . ', ';
                }
                $title .= substr($title_row, 0, -2);
                $title .= ') | ';
                $filter_title .= substr($title_row, 0, -2);
                $filter_title .= '), ';

                $filteredQuery->where(function($query) use ($requestWater) {
                    foreach($requestWater as $waterId) {
                        $query->whereJsonContains('water_types', (int)$waterId);
                    }
                });
            }
        }

        // Apply target fish filters
        if($cleanedRequest->has('target_fish')){
            $requestFish = array_filter($cleanedRequest->target_fish);

            if(count($requestFish)){
                $title .= __('guidings.Target_Fish') . ' (';
                $filter_title .= __('guidings.Target_Fish') . ' (';
                $method_rows = Target::whereIn('id', $requestFish)->get();
                $title_row = '';
                foreach ($method_rows as $row) {
                    $title_row .= (($locale == 'en')? $row->name_en : $row->name) . ', ';
                }
                $title .= substr($title_row, 0, -2);
                $title .= ') | ';
                $filter_title .= substr($title_row, 0, -2);
                $filter_title .= '), ';

                $filteredQuery->where(function($query) use ($requestFish) {
                    foreach($requestFish as $fishId) {
                        $query->whereJsonContains('target_fish', (int)$fishId);
                    }
                });
            }
        }

        // Apply price filters
        if(($cleanedRequest->has('price_min') && $cleanedRequest->get('price_min') !== "") && ($cleanedRequest->has('price_max') && $cleanedRequest->get('price_max') !== "")){
            // if ($minPrice != $request->get('price_min') || $overallMaxPrice != $request->get('price_max')){
                $min_price = $cleanedRequest->get('price_min');
                $max_price = $cleanedRequest->get('price_max');

                $title .= 'Price ' . $min_price . '€ - ' . $max_price . '€ | ';
                $filter_title .= 'Price ' . $min_price . '€ - ' . $max_price . '€, ';

                // Use the lowest_price field we calculated in the main query
                $filteredQuery->havingRaw('lowest_price >= ? AND lowest_price <= ?', [$min_price, $max_price]);
            // }
        }

        // Apply duration filters
        if ($cleanedRequest->has('duration_types') && !empty($cleanedRequest->get('duration_types'))) {
            $filteredQuery->whereIn('duration_type', $cleanedRequest->get('duration_types'));
        }

        // Apply person filters
        if($cleanedRequest->has('num_persons')){
            $title .= __('guidings.Number of People') . ' ' . $cleanedRequest->get('num_persons') . ' | ';
            $filter_title .= __('guidings.Number of People') . ' ' . $cleanedRequest->get('num_persons') . ', ';
            
            // For single selection, we just need to check if the guiding supports at least this many people
            $minPersons = $cleanedRequest->get('num_persons');
            $filteredQuery->where('max_guests', '>=', $minPersons);
        }
        
        // Apply radius filters
        $radius = null; // Radius in miles
        if($cleanedRequest->has('radius')){
            $title .= __('guidings.Radius') . ' ' . $cleanedRequest->radius . 'km | ';
            $filter_title .= __('guidings.Radius') . ' ' . $cleanedRequest->radius . 'km, ';
            $radius = $cleanedRequest->get('radius');
        }

        // Set default values if $filterData is null
        $filterData = json_decode($row_data->filters, true);
        $placeLat = $filterData['placeLat'] ?? null;
        $placeLng = $filterData['placeLng'] ?? null;
        $city = $filterData['city'] ?? null;
        $country = $filterData['country'] ?? null;
        $region = $filterData['region'] ?? null;

        if(!empty($placeLat) && !empty($placeLng)){
            $title .= __('guidings.Coordinates') . ' Lat ' . $placeLat . ' Lang ' . $placeLng . ' | ';
            $filter_title .= __('guidings.Coordinates') . ' Lat ' . $placeLat . ' Lang ' . $placeLng . ', ';
            $guidingFilter = Guiding::locationFilter($city, $country, $region, $radius, $placeLat, $placeLng);
            $searchMessage = $guidingFilter['message'];
            
            // Add a subquery to order by the position in the filtered IDs array
            $orderByCase = 'CASE guidings.id ';
            foreach($guidingFilter['ids'] as $position => $id) {
                $orderByCase .= "WHEN $id THEN $position ";
            }
            $orderByCase .= 'ELSE ' . count($guidingFilter['ids']) . ' END';
            
            $filteredQuery->whereIn('guidings.id', $guidingFilter['ids'])
                  ->orderByRaw($orderByCase);
        }

        
        // 3. Get all filtered guidings (for counts and filter options)
        $allGuidings = $filteredQuery->get();
        
        // 4. Extract available filter options from filtered results
        $availableTargetFish = collect();
        $availableMethods = collect();
        $availableWaterTypes = collect();
        $durationCounts = [
            'multi_day' => 0,
            'half_day' => 0,
            'full_day' => 0
        ];
        $targetFishCounts = [];
        $methodCounts = [];
        $waterTypeCounts = [];
        $personCounts = [];

        foreach ($allGuidings as $guiding) {
            // For target fish
            $targetFish = json_decode($guiding->target_fish, true) ?? [];
            $availableTargetFish = $availableTargetFish->concat($targetFish)->unique();
            foreach ($targetFish as $fishId) {
                $targetFishCounts[$fishId] = ($targetFishCounts[$fishId] ?? 0) + 1;
            }
            
            // For methods
            $methods = json_decode($guiding->fishing_methods, true) ?? [];
            $availableMethods = $availableMethods->concat($methods)->unique();
            foreach ($methods as $methodId) {
                $methodCounts[$methodId] = ($methodCounts[$methodId] ?? 0) + 1;
            }
            
            // For water types
            $waterTypes = json_decode($guiding->water_types, true) ?? [];
            $availableWaterTypes = $availableWaterTypes->concat($waterTypes)->unique();
            foreach ($waterTypes as $waterId) {
                $waterTypeCounts[$waterId] = ($waterTypeCounts[$waterId] ?? 0) + 1;
            }

            // Count durations
            if (isset($guiding->duration_type)) {
                $durationCounts[$guiding->duration_type] = ($durationCounts[$guiding->duration_type] ?? 0) + 1;
            }
            
            // Count persons
            if (isset($guiding->max_guests)) {
                // Count all guidings that support at least this number of persons
                for ($i = 1; $i <= min(8, $guiding->max_guests); $i++) {
                    $personCounts[$i] = ($personCounts[$i] ?? 0) + 1;
                }
            }
        }

        // Sort person counts
        ksort($personCounts);
        
        // Get the models for these IDs, only including items with counts > 0
        $targetFishOptions = Target::whereIn('id', array_keys(array_filter($targetFishCounts)))->get();
        $methodOptions = Method::whereIn('id', array_keys(array_filter($methodCounts)))->get();
        $waterTypeOptions = Water::whereIn('id', array_keys(array_filter($waterTypeCounts)))->get();

        // 5. Get other guidings if needed
        $otherguidings = [];
        if($allGuidings->isEmpty() || count($allGuidings) <= 10){
            if($cleanedRequest->has('placeLat') && $cleanedRequest->has('placeLng') && !empty($cleanedRequest->get('placeLat')) && !empty($cleanedRequest->get('placeLng')) ){
                $latitude = $cleanedRequest->get('placeLat');
                $longitude = $cleanedRequest->get('placeLng');
                $otherguidings = $this->otherGuidingsBasedByLocation($latitude, $longitude, $allGuidings);
            } else {
                $otherguidings = $this->otherGuidings();
            }
        }

        // 6. Get paginated results
        $guidings = $filteredQuery->paginate(20);
        $guidings->appends(request()->except('page'));

        // Finalize filter title
        $filter_title = substr($filter_title, 0, -2);

        // Get all options for filters
        $alltargets = Target::select('id', 'name', 'name_en')->orderBy('name')->get();
        $guiding_waters = Water::select('id', 'name', 'name_en')->orderBy('name')->get();
        $guiding_methods = Method::select('id', 'name', 'name_en')->orderBy('name')->get();

        // Check if mobile
        $isMobile = $cleanedRequest->get('ismobile') == 'true' || app('agent')->isMobile();

        // Ensure personCounts is always an array
        if (empty($personCounts)) {
            $personCounts = [];
        }
        
        // Handle AJAX requests
        if ($cleanedRequest->ajax()) {
            Log::info('AJAX request received'); 
            $view = view('pages.guidings.partials.guiding-list', [
                'title' => $title,
                'filter_title' => $filter_title,
                'guidings' => $guidings,
                'radius' => $radius,
                'allGuidings' => $allGuidings,
                'searchMessage' => $searchMessage,
                'otherguidings' => $otherguidings,
                'alltargets' => $alltargets,
                'guiding_waters' => $guiding_waters,
                'guiding_methods' => $guiding_methods,
                'destination' => $destination,
                'targetFishOptions' => $targetFishOptions,
                'methodOptions' => $methodOptions,
                'waterTypeOptions' => $waterTypeOptions,
                'targetFishCounts' => $targetFishCounts,
                'methodCounts' => $methodCounts,
                'waterTypeCounts' => $waterTypeCounts,
                'durationCounts' => $durationCounts,
                'personCounts' => $personCounts,
                'isMobile' => $isMobile,
                // 'priceHistogramData' => $priceHistogramData,
                'maxPrice' => $overallMaxPrice,
                'overallMaxPrice' => $overallMaxPrice,
            ])->render();
            
            // Add guiding data for map updates
            $guidingsData = $guidings->map(function($guiding) {
                return [
                    'id' => $guiding->id,
                    'slug' => $guiding->slug,
                    'title' => $guiding->title,
                    'location' => $guiding->location,
                    'lat' => $guiding->lat,
                    'lng' => $guiding->lng
                ];
            });
            
            return response()->json([
                'html' => $view,
                'guidings' => $guidingsData,
                'allGuidings' => $allGuidings,
                'searchMessage' => $searchMessage,
                'isMobile' => $isMobile,
                'total' => $guidings->total(),
                'filterCounts' => [
                    'targetFish' => $targetFishCounts,
                    'methods' => $methodCounts,
                    'waters' => $waterTypeCounts,
                    'durations' => $durationCounts,
                    'persons' => $personCounts
                ],
                // 'priceHistogramData' => $priceHistogramData,
                'maxPrice' => $overallMaxPrice,
                'overallMaxPrice' => $overallMaxPrice,
            ]);
        }

        // Return full view for non-AJAX requests
        return view('pages.category.country', [ 
            'guidings_total' => $filteredQuery->count(),
            'row_data' => $row_data,
            'regions' => $regions,
            'cities' => $cities,
            'faq' => $faq,
            'fish_chart' => $fish_chart,
            'fish_size_limit' => $fish_size_limit,
            'fish_time_limit' => $fish_time_limit,
            'title' => $title,
            'filter_title' => $filter_title,
            'guidings' => $guidings,
            'radius' => $radius,
            'allGuidings' => $allGuidings,
            'searchMessage' => $searchMessage,
            'otherguidings' => $otherguidings,
            'alltargets' => $alltargets,
            'guiding_waters' => $guiding_waters,
            'guiding_methods' => $guiding_methods,
            'destination' => $destination,
            'targetFishOptions' => $targetFishOptions,
            'methodOptions' => $methodOptions,
            'waterTypeOptions' => $waterTypeOptions,
            'targetFishCounts' => $targetFishCounts,
            'methodCounts' => $methodCounts,
            'waterTypeCounts' => $waterTypeCounts,
            'durationCounts' => $durationCounts,
            'personCounts' => $personCounts,
            'isMobile' => $isMobile,
            'total' => $guidings->total(),
            'filterCounts' => [
                'targetFish' => $targetFishCounts,
                'methods' => $methodCounts,
                'waters' => $waterTypeCounts,
                'durations' => $durationCounts,
                'persons' => $personCounts
            ],
            // 'priceHistogramData' => $priceHistogramData,
            'maxPrice' => $overallMaxPrice,
            'overallMaxPrice' => $overallMaxPrice,
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

    public function getCoordinates($country, $region=null, $city=null)
    {
        $client = new \GuzzleHttp\Client();
        $geocoder = new Geocoder($client);

        $geocoder->setApiKey(env('GOOGLE_MAPS_API_KEY'));

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
    

    /**
     * Clean up request parameters before processing
     */
    private function cleanRequestParameters(Request $request)
    {
        // Only process if price parameters exist
        if (!$request->has('price_min') && !$request->has('price_max')) {
            return $request; // Return original request if no price parameters
        }
        
        // Clone the request only if we need to modify it
        $cleanedRequest = clone $request;
        $requestData = $cleanedRequest->all();
        
        // Get default values (use cached value for max price)
        $defaultMinPrice = 50;
        $cacheKey = 'guiding_price_ranges';
        $defaultMaxPrice = Cache::has($cacheKey) ? Cache::get($cacheKey)['maxPrice'] : 1000;
        
        // Check and remove price parameters if they match defaults
        $modified = false;
        
        if (isset($requestData['price_min']) && (int)$requestData['price_min'] === $defaultMinPrice) {
            unset($requestData['price_min']);
            $modified = true;
        }
        
        if (isset($requestData['price_max']) && (int)$requestData['price_max'] === $defaultMaxPrice) {
            unset($requestData['price_max']);
            $modified = true;
        }
        
        // Only replace if we actually modified something
        if ($modified) {
            $cleanedRequest->replace($requestData);
            return $cleanedRequest;
        }
        
        // Return original request if no changes needed
        return $request;
    }
}
