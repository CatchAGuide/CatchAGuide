<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGuidingRequest;
use App\Http\Requests\StoreNewGuidingRequest;
use App\Models\Gallery;
use App\Models\Guiding;
use App\Models\Target;
use App\Models\Method;
use App\Models\Water;
use App\Models\GuidingRequest;
use App\Models\Inclussion;
use App\Models\GuidingBoatType;
use App\Models\GuidingBoatDescription;
use App\Models\GuidingAdditionalInformation;
use App\Models\GuidingRequirements;
use App\Models\GuidingRecommendations;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Auth;
use Config;
use Illuminate\Support\Facades\Mail;
use App\Mail\GuidingRequestMail;
use App\Mail\SearchRequestUserMail;
use Illuminate\Support\Facades\Log;
use App\Models\BlockedEvent;
use App\Models\ExtrasPrice;
use App\Models\BoatExtras;
use App\Models\Destination;
use App\Models\Review;
use Illuminate\Support\Facades\Cache;

class GuidingsController extends Controller
{
    public function index(Request $request)
    {
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
            ) AS lowest_price')])
            ->where('status', 1);
        
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

            if(count($requestMethods)){
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

        // Apply location filters
        $placeLat = $cleanedRequest->get('placeLat');
        $placeLng = $cleanedRequest->get('placeLng');

        if(!empty($placeLat) && !empty($placeLng) && !empty($cleanedRequest->get('place'))){
            $title .= __('guidings.Coordinates') . ' Lat ' . $placeLat . ' Lang ' . $placeLng . ' | ';
            $filter_title .= __('guidings.Coordinates') . ' Lat ' . $placeLat . ' Lang ' . $placeLng . ', ';
            $guidingFilter = Guiding::locationFilter($cleanedRequest->get('city'), $cleanedRequest->get('country'), $cleanedRequest->get('region') ?? null, $radius, $placeLat, $placeLng);
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

        // Generate price histogram data for JavaScript
        // $priceHistogramData = array_map(function($range) {
        //     return [
        //         'min' => $range['min'],
        //         'max' => $range['max'],
        //         'count' => $range['count']
        //     ];
        // }, $priceRanges);

        // Handle AJAX requests
        if ($cleanedRequest->ajax()) {
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
        return view('pages.guidings.index', [            
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

    public function newShow($id, $slug, Request $request)
    {
        $locale = Config::get('app.locale');
        
        $query = Guiding::where('id', $id)->where('slug', $slug);
        
        $destination = null;

        // If coming from destination page, get the destination context
        if ($request->has('from_destination')) {
            $destination = Destination::where('id', $request->input('destination_id'))->first();
        }

        if (!Auth::check()) {
            $query = $query->where('status', 1);
        }

        $guiding = $query->first();

        if (is_null($guiding)) {
            abort(404);
        }
        // $targetFish = $guiding->is_newguiding ? json_decode($guiding->target_fish, true) : $guiding->guidingTargets->pluck('id')->toArray();
        $targetFish = json_decode($guiding->target_fish, true);
        $fishingFrom = $guiding->fishing_from_id;
        $fishingType = $guiding->fishing_type_id;

        // Get reviews instead of ratings
        // $reviews = $guiding->reviews;
        $reviews = Review::where('guide_id', $guiding->user_id)->with('booking', 'booking.user')->get();
        $reviews_count = $reviews->count();

        // Calculate average scores
        $average_overall_score = $reviews_count > 0 ? $reviews->avg('overall_score') : 0;
        $average_guide_score = $reviews_count > 0 ? $reviews->avg('guide_score') : 0;
        $average_region_water_score = $reviews_count > 0 ? $reviews->avg('region_water_score') : 0;
        $average_grandtotal_score = $reviews_count > 0 ? $reviews->avg('grandtotal_score') : 0;

        $otherGuidings = Guiding::where('status', 1)
            ->where('id', '!=', $guiding->id)
            ->where(function($query) use ($guiding, $targetFish, $fishingFrom, $fishingType) {
                $query->where(function($q) use ($guiding, $targetFish) {
                    $q->where(function($subQ) use ($targetFish) {
                        foreach ($targetFish as $fish) {
                            $subQ->orWhereJsonContains('target_fish', $fish);
                        }
                    });
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
            'reviews' => $reviews,
            'reviews_count' => $reviews_count,
            'average_overall_score' => $average_overall_score,
            'average_guide_score' => $average_guide_score,
            'average_region_water_score' => $average_region_water_score,
            'average_grandtotal_score' => $average_grandtotal_score,
            'other_guidings' => $otherGuidings,
            'destination' => $destination,
            'blocked_events' => $guiding->getBlockedEvents(),
        ]);
    }

    public function otherGuidings(){
        $otherguidings = Guiding::inRandomOrder('1234')->where('status',1)->limit(10)->get();

        return $otherguidings;
    }

    public function otherGuidingsBasedByLocation($latitude, $longitude, $allGuidings)
    {
        // Get IDs of guidings that are already in allGuidings
        $existingGuidingIds = $allGuidings->pluck('id')->toArray();

        // Calculate distance using ST_Distance_Sphere and filter within 200km radius
        $nearestListings = Guiding::select(['guidings.*'])
            ->selectRaw("ST_Distance_Sphere(
                point(lng, lat),
                point(?, ?)
            ) as distance", [
                $longitude,
                $latitude
            ])
            ->whereRaw("ST_Distance_Sphere(
                point(lng, lat),
                point(?, ?)
            ) <= ?", [
                $longitude,
                $latitude,
                200 * 1000 // 200km converted to meters
            ])
            ->whereNotIn('id', $existingGuidingIds) // Exclude existing guidings
            ->where('status', 1)
            ->orderByRaw('CASE WHEN distance IS NULL THEN 1 ELSE 0 END')
            ->orderBy('distance') // Sort by nearest first
            ->limit(10)
            ->get();

        return $nearestListings;
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
                $guiding->slug = slugify($request->input('title') . "-in-" . $request->input('location'));
            }

            Log::info($guiding);

            $guiding->is_newguiding = 1;

            //step 1
            $guiding->location = $request->has('location') ? $request->input('location') : '';
            $guiding->title = $request->has('title') ? $request->input('title') : '';
            $guiding->lat = $request->has('latitude') ? $request->input('latitude') : '';
            $guiding->lng = $request->has('longitude') ? $request->input('longitude') : '';
            $guiding->country = $request->has('country') ? $request->input('country') : '';
            $guiding->city = $request->has('city') ? $request->input('city') : '';
            $guiding->region = $request->has('region') ? $request->input('region') : '';
            
            $galeryImages = [];
            $imageList = json_decode($request->input('image_list')) ?? [];

            if ($request->input('is_update') == '1') {
                // Get existing images from request
                $existingImagesJson = $request->input('existing_images');
                $existingImages = json_decode($existingImagesJson, true);
                
                // Get image list that should be kept
                $keepImages = array_filter($imageList);
                
                // Only keep images that are in the image_list
                foreach ($existingImages as $existingImage) {
                    // Check if the image path is in the keepImages list
                    // Handle both formats: with and without leading slash
                    $imagePath = $existingImage;
                    $imagePathWithSlash = '/' . $existingImage;
                    
                    if (in_array($imagePath, $keepImages) || in_array($imagePathWithSlash, $keepImages)) {
                        $galeryImages[] = $existingImage;
                    } else {
                        media_delete($existingImage);
                    }
                }
            }

            if ($request->has('title_image')) {
                $imageCount = count($galeryImages);
                foreach($request->file('title_image') as $index => $image) {
                    // Extract just the filename without path
                    $filename = 'guidings-images/'.$image->getClientOriginalName();
                    
                    // Check if this file is already in our list (avoid duplicates)
                    if (in_array($filename, $imageList) || in_array('/' . $filename, $imageList)) {
                        continue;
                    }
                    
                    $index = $index + $imageCount;
                    $webp_path = media_upload($image, 'guidings-images', $guiding->slug. "-". $index . "-" . time());
                    $galeryImages[] = $webp_path;
                }
            }

            if (count($galeryImages) < 5) {
                throw new \Exception('Please upload at least 5 images');
            }

            // Set the primary image
            foreach($galeryImages as $index => $image) {
                if($index == $request->input('primaryImage', 0)) {
                    $guiding->thumbnail_path = $image;
                }
            }

            $guiding->gallery_images = json_encode($galeryImages);

            //step 2
            $guiding->is_boat = $request->has('type_of_fishing') ? ($request->input('type_of_fishing') == 'boat' ? 1 : 0) : 0;
            $guiding->fishing_from_id = (int) $request->has('type_of_fishing') ? ($request->input('type_of_fishing') == 'boat' ? 1 : 2) : 2;
            $guiding->additional_information = $request->has('other_boat_info') ? $request->input('other_boat_info') : '';
            if ($guiding->is_boat) {
                $guiding->boat_type = $request->has('type_of_boat') ? $request->input('type_of_boat') : '';
                $guiding->boat_information = $this->saveDescriptions($request);

                $boatExtrasData = collect(json_decode($request->input('boat_extras')));
                $boatExtras = $boatExtrasData->map(function($item) {
                    return $item->id ?? $item->value;
                })->toArray();
                $guiding->boat_extras = json_encode($boatExtras);
            }

            //step 3
            if ($request->has('target_fish')) {
                $targetFishData = collect(json_decode($request->input('target_fish')));
                $targetFish = $targetFishData->map(function($item) {
                    return $item->id ?? $item->value;
                })->toArray();
                $guiding->target_fish = json_encode($targetFish);
            }
            
            $methodsData = collect(json_decode($request->input('methods')));
            $methods = $methodsData->map(function($item) {
                return $item->id ?? $item->value;
            })->toArray();
            $guiding->fishing_methods = json_encode($methods);
            
            $guiding->fishing_type_id = (int) $request->has('style_of_fishing') ? $request->input('style_of_fishing') : 3; 
            
            $waterTypesData = collect(json_decode($request->input('water_types')));
            $waterTypes = $waterTypesData->map(function($item) {
                return $item->id ?? $item->value;
            })->toArray();
            $guiding->water_types = json_encode($waterTypes);

            //step 4
            $guiding->desc_course_of_action = $request->has('desc_course_of_action') ? $request->input('desc_course_of_action') : '';
            $guiding->desc_meeting_point = $request->has('desc_meeting_point') ? $request->input('desc_meeting_point') : '';
            $guiding->meeting_point = $request->has('meeting_point') ? $request->input('desc_meeting_point') : '';
            $guiding->desc_starting_time = $request->has('desc_starting_time') ? $request->input('desc_starting_time') : '';
            $guiding->desc_departure_time = $request->has('desc_departure_time') ? json_encode($request->input('desc_departure_time')) : json_encode([]);
            $guiding->desc_tour_unique = $request->has('desc_tour_unique') ? $request->input('desc_tour_unique') : '';
            $guiding->description = $request->has('desc_course_of_action') ? $request->input('desc_course_of_action') : $this->generateLongDescription($request);

            //step 5
            $guiding->requirements = $this->saveRequirements($request);
            $guiding->recommendations = $this->saveRecommendations($request);   
            $guiding->other_information = $this->saveOtherInformation($request);  

            //step 6
            $guiding->tour_type = $request->has('tour_type') ? $request->input('tour_type') : '';

            $guiding->duration_type = $request->has('duration') ? $request->input('duration') : '';
            if ($request->input('duration') == 'multi_day') {
                $guiding->duration = (int) $request->input('duration_days');
            } else {
                $guiding->duration = (int) $request->input('duration_hours');
            }

            $guiding->max_guests = (int) $request->has('no_guest') ? $request->input('no_guest') : 0;

            if ($request->has('price_type') ) {
                $guiding->price_type = $request->input('price_type');
                $pricePerPerson = [];
                if ( $request->input('price_type') === 'per_person') {
                    foreach ($request->all() as $key => $value) {
                        if (strpos($key, 'price_per_person_') === 0) {
                            $guestNumber = substr($key, strlen('price_per_person_'));
                            $pricePerPerson[] = [
                                'person' => $guestNumber,
                                'amount' => $value
                            ];
                        }
                    }
                    $guiding->price = 0;
                    
                    $has_min_guests = $request->has('has_min_guests') ? 1 : 0;
                    if ($has_min_guests) {
                        $guiding->min_guests = (int) $request->input('min_guests');
                    } else {
                        $guiding->min_guests = null;
                    }
                } else {
                    for ($i = 1; $i <= $request->input('no_guest'); $i++) {
                        $pricePerPerson[] = [
                            'person' => $i,
                            'amount' => (float) ( $request->input('price_per_boat') / $request->input('no_guest') ) * $i
                        ];
                    }
                    $guiding->price = (float) $request->input('price_per_boat');
                }
                $guiding->prices = json_encode($pricePerPerson);
            }
            
            $inclusionsData = collect(json_decode($request->input('inclusions')));
            $inclusions = $inclusionsData->map(function($item) {
                return $item->id ?? $item->value;
            })->toArray();
            $guiding->inclusions = json_encode($inclusions);
            
            $pricingExtras = [];
            $i = 1;
            while (true) {
                $nameKey = "extra_name_" . $i;
                $priceKey = "extra_price_" . $i;
                
                $extraPrice = ExtrasPrice::where('name', $request->input($nameKey))
                                       ->orWhere('name_en', $request->input($nameKey))
                                       ->first();
                $extraname = $extraPrice ? $extraPrice->name : $request->input($nameKey);
                if ($request->has($nameKey) && $request->has($priceKey)) {

                    if ($extraname && $request->input($priceKey)) {
                        $pricingExtras[] = [
                            'name' => $extraname,
                            'price' => $request->input($priceKey)
                        ];
                    }
                    $i++;
                } else {
                    break;
                }
            }
            $guiding->pricing_extra = json_encode($pricingExtras);

            //step 7   
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
                        $currentMonth = date('m');
                        $year = date('Y');
                        
                        if ($monthNumber < $currentMonth) {
                            $year++;
                        }
                        
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

            $guiding->weekday_availability = $request->input('weekday_availability', 'all_week');
            if ($request->input('weekday_availability') === 'all_week') {
                $guiding->weekdays = json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            } else {
                $guiding->weekdays = $request->has('weekdays') ? json_encode($request->input('weekdays')) : json_encode([]);
            }

            $guiding->save();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $isDraft ? 'Draft saved successfully!' : 'Guiding created successfully!',
                'redirect_url' => $request->input('target_redirect') ?? route('profile.myguidings'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in guidingsStore: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['error' => 'An error occurred while processing your request.' . $e->getMessage()], 500);
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
            $descriptionData[$description] = $request->input("boat_description_".$description);
        }

        return json_encode($descriptionData);

    }

    private function saveOtherInformation($request)
    {
        $otherInformations = $request->input('other_information', []);
        $otherInformationData = [];

        foreach ($otherInformations as $otherInformation) {
            $otherInformationData[$otherInformation] = $request->input("other_information_".$otherInformation);
        }

        return json_encode($otherInformationData);
    }

    private function saveRequirements($request)
    {
        $requirements = $request->input('requiements_taking_part', []);
        $requirementData = [];

        foreach ($requirements as $requirement) {
            $requirementData[$requirement] = $request->input("requiements_taking_part_".$requirement);
        }

        return json_encode($requirementData);
    }

    private function saveRecommendations($request)
    {
        $recommendations = $request->input('recommended_preparation', []);
        $recommendationData = [];

        foreach ($recommendations as $recommendation) {
            $recommendationData[$recommendation] = $request->input("recommended_preparation_".$recommendation);
        }

        return json_encode($recommendationData);
    }

    public function store(StoreGuidingRequest $request)
    {
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
            'desc_departure_time' => json_decode($guiding->desc_departure_time, true),
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
            'min_guests' => $guiding->min_guests,
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
            'weekday_availability' => $guiding->weekday_availability,
            'weekdays' => json_decode($guiding->weekdays, true),
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

        return view('pages.profile.newguiding', array_merge(
            ['formData' => $formData, 'pageTitle' => $pageTitle],
            $collections
        ));
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
        $email = env('TO_CEO','info@catchaguide.com');
        if (!CheckEmailLog('guiding_request_mail', 'guiding_request_mail', $email)) {
            Mail::to($email)->queue(new GuidingRequestMail($guideRequest));
        }
        if (!CheckEmailLog('search_request_user_mail', 'search_request_user_mail', $request->email)) {
            Mail::to($request->email)->queue(new SearchRequestUserMail($guideRequest));
        }

        return redirect()->back()->with('message', "Email Has been Sent");
    }
}
