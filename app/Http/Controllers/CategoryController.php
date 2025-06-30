<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CategoryPage;
use App\Models\Guiding;
use App\Models\Method;
use App\Models\Water;
use App\Models\Target;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function index($type)
    {
        $language = app()->getLocale();
        $allTargets = CategoryPage::where('type', $type)
            ->get()
            ->map(function($item) use ($language) {
                $item->language = $item->language($language);
                return $item;
            })
            ->filter(function($item) {
                return $item->language !== null;
            });

        $favories = $allTargets->filter(function($item) {
            return $item->is_favorite === true || $item->is_favorite === 1;
        });
        
        $introduction = __('category.' . $type . '.introduction');
        $title = __('category.' . $type . '.title');

        $data = compact('favories', 'allTargets', 'introduction', 'title', 'type');
        return view('pages.category.category-index', $data);
    }

    public function targets($type, $slug, Request $request)
    {
        $language = app()->getLocale();
        $row_data = CategoryPage::whereSlug($slug)->whereType($type)->with('language', 'faq')->first();

        if (!$row_data) {
            abort(404);
        }

        $row_data->language = $row_data->language($language);
        $row_data->faq = $row_data->faq($language);

        $title = $row_data->language->title;
        $filter_title = '';
        $searchMessage = '';
        $radius = null;
        $locale = app()->getLocale();

        $queryType = $type === 'targets' ? 'target_fish' : 'fishing_methods';

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

        $baseQuery->where(function($query) use ($row_data, $queryType) {
            $query->whereJsonContains($queryType, (int)$row_data->source_id);
        });
        
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
            $view = view('pages.guidings.partials.guiding-list', [
                'title' => $title,
                'row_data' => $row_data,
                'filter_title' => $filter_title,
                'guidings' => $guidings,
                'guides' => $guidings,
                'otherguidings' => $otherguidings,
                'radius' => $radius,
                'allGuidings' => $allGuidings,
                'searchMessage' => $searchMessage,
                'alltargets' => $alltargets,
                'guiding_waters' => $guiding_waters,
                'guiding_methods' => $guiding_methods,
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
        return view('pages.category.category-show', [ 
            'guidings_total' => $filteredQuery->count(),
            'row_data' => $row_data,
            'title' => $title,
            'filter_title' => $filter_title,
            'guidings' => $guidings,
            'guides' => $guidings,
            'radius' => $radius,
            'allGuidings' => $allGuidings,
            'searchMessage' => $searchMessage,
            'otherguidings' => $otherguidings,
            'alltargets' => $alltargets,
            'guiding_waters' => $guiding_waters,
            'guiding_methods' => $guiding_methods,
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
