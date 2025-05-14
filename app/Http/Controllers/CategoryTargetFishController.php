<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CategoryPage;
use App\Models\Guiding;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Models\Method;
use App\Models\Water;
use App\Models\Target;
use Illuminate\Support\Facades\Cache;

class CategoryTargetFishController extends Controller
{
    public function index()
    {
        $language = app()->getLocale();
        $allTargets = CategoryPage::where('type', 'Targets')
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
        
        $introduction = __('category.introduction');
        $title = __('category.title');
        $route = 'target-fish.targets';

        $data = compact('favories', 'allTargets', 'introduction', 'title', 'route');
        return view('pages.category.category-index', $data);
    }

    public function targets($slug, Request $request)
    {
        $language = app()->getLocale();
        $row_data = CategoryPage::whereSlug($slug)->whereType('Targets')->with('language', 'faq')->first();

        if (!$row_data) {
            abort(404);
        }

        $row_data->language = $row_data->language($language);
        $row_data->faq = $row_data->faq($language);

        $title = 'Target Fish';
        $filter_title = '';
        $searchMessage = '';
        $radius = null;

        // Get or generate a random seed and store it in the session
        $randomSeed = Session::get('random_seed');
        if (!$randomSeed) {
            $randomSeed = rand();
            Session::put('random_seed', $randomSeed);
        }

        // Eager load relationships to avoid N+1 problem
        $query = Guiding::with(['target_fish', 'methods', 'water_types', 'boatType'])
            ->select(['guidings.*', DB::raw('(
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
            ->where('status', 1)
            ->whereNotNull('lat')
            ->whereNotNull('lng');
        
        // Apply target fish filter based on the category page
        $query->where(function($query) use ($row_data) {
            $query->whereJsonContains('target_fish', (int)$row_data->source_id);
        });

        // Check if we only have page parameter
        $hasOnlyPageParam = count(array_diff(array_keys($request->all()), ['page'])) === 0;
        $isFirstPage = !$request->has('page') || $request->get('page') == 1;

        // Apply sorting
        if ($hasOnlyPageParam && $isFirstPage) {
            $query->orderByRaw("RAND($randomSeed)");
        } else {
            // Apply sorting based on user selection
            if ($request->has('sortby') && !empty($request->get('sortby'))) {
                switch ($request->get('sortby')) {
                    case 'newest':
                        $query->orderBy('created_at', 'desc');
                        break;
                    case 'price-asc':
                        $query->orderBy('lowest_price', 'asc');
                        break;
                    case 'price-desc':
                        $query->orderBy('lowest_price', 'desc');
                        break;
                    case 'long-duration':
                        $query->orderBy('duration', 'desc');
                        break;
                    case 'short-duration':
                        $query->orderBy('duration', 'asc');
                        break;
                    default:
                        $query->orderBy('lowest_price', 'asc');
                }
            } else {
                // Default to sorting by lowest price if no sort option is provided
                $query->orderBy('lowest_price', 'asc');
            }
        }

        // Build title based on filters
        if($request->has('page')){
            $title .= __('vacations.Page') . ' ' . $request->page . ' - ';
        }

        // Apply method filters
        if($request->has('methods') && !empty($request->get('methods'))){
            $requestMethods = array_filter($request->get('methods'));

            if(count($requestMethods)){
                $title .= __('guidings.Method') . ' (';
                $filter_title .= __('guidings.Method') . ' (';
                $method_rows = Method::whereIn('id', $request->methods)->get();
                $title_row = '';
                foreach ($method_rows as $row) {
                    $title_row .= (($language == 'en')? $row->name_en : $row->name) . ', ';
                }
                $title .= substr($title_row, 0, -2);
                $title .= '), ';
                $filter_title .= substr($title_row, 0, -2);
                $filter_title .= '), ';

                $query->where(function($query) use ($requestMethods) {
                    foreach($requestMethods as $methodId) {
                        $query->whereJsonContains('fishing_methods', (int)$methodId);
                    }
                });
            }
        }

        // Apply water filters
        if($request->has('water') && !empty($request->get('water'))){
            $requestWater = array_filter($request->get('water'));

            if(count($requestWater)){
                $title .= __('guidings.Water') . ' (';
                $filter_title .= __('guidings.Water') . ' (';
                $water_rows = Water::whereIn('id', $request->water)->get();
                $title_row = '';
                foreach ($water_rows as $row) {
                    $title_row .= (($language == 'en')? $row->name_en : $row->name) . ', ';
                }
                $title .= substr($title_row, 0, -2);
                $title .= ') | ';
                $filter_title .= substr($title_row, 0, -2);
                $filter_title .= '), ';

                $query->where(function($query) use ($requestWater) {
                    foreach($requestWater as $waterId) {
                        $query->whereJsonContains('water_types', (int)$waterId);
                    }
                });
            }
        }

        // Apply price filters
        if(($request->has('price_min') && $request->get('price_min') !== "") && ($request->has('price_max') && $request->get('price_max') !== "")){
            $min_price = $request->get('price_min');
            $max_price = $request->get('price_max');

            $title .= 'Price ' . $min_price . '€ - ' . $max_price . '€ | ';
            $filter_title .= 'Price ' . $min_price . '€ - ' . $max_price . '€, ';

            // Use the lowest_price field we calculated in the main query
            $query->havingRaw('lowest_price >= ? AND lowest_price <= ?', [$min_price, $max_price]);
        }

        // Apply duration filters
        if ($request->has('duration_types') && !empty($request->get('duration_types'))) {
            $query->whereIn('duration_type', $request->get('duration_types'));
        }

        // Apply person filters
        if($request->has('num_guests') && !empty($request->get('num_guests'))){
            $title .= __('guidings.Number of People') . ' ' . $request->get('num_guests') . ' | ';
            $filter_title .= __('guidings.Number of People') . ' ' . $request->get('num_guests') . ', ';
            
            // For single selection, we just need to check if the guiding supports at least this many people
            $minPersons = $request->get('num_guests');
            $query->where('max_guests', '>=', $minPersons);
        }

        // Get all guidings for filter options
        $allGuidings = $query->get();
        $category_total = $allGuidings->count();
        
        // Extract available filter options from filtered results
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

        // Get other guidings if needed
        $otherguidings = [];
        if($allGuidings->isEmpty() || count($allGuidings) <= 10){
            $otherguidings = Guiding::inRandomOrder('1234')->where('status',1)->limit(10)->get();
        }

        // Get all options for filters
        $alltargets = Target::select('id', 'name', 'name_en')->orderBy('name')->get();
        $guiding_waters = Water::select('id', 'name', 'name_en')->orderBy('name')->get();
        $guiding_methods = Method::select('id', 'name', 'name_en')->orderBy('name')->get();

        // Check if mobile
        $isMobile = $request->get('ismobile') == 'true' || app('agent')->isMobile();

        // Ensure personCounts is always an array
        if (empty($personCounts)) {
            $personCounts = [];
        }

        // Finalize filter title
        $filter_title = substr($filter_title, 0, -2);
        
        // Apply pagination
        $guides = $query->paginate(10)->appends(request()->except('page'));

        // Use caching for price ranges
        $cacheKey = 'guiding_price_ranges';
        $cacheDuration = 60 * 24; // Cache for 24 hours

        if (Cache::has($cacheKey)) {
            $priceRangeData = Cache::get($cacheKey);
            $priceRanges = $priceRangeData['ranges'];
            $overallMaxPrice = $priceRangeData['maxPrice'];
        } else {
            // Default max price if cache not available
            $overallMaxPrice = 5000;
        }

        // Handle AJAX requests
        if ($request->ajax()) {
            $view = view('pages.category.partials.category-list', [
                'title' => $title,
                'filter_title' => $filter_title,
                'guides' => $guides,
                'row_data' => $row_data,
                'category_total' => $category_total,
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
                'maxPrice' => $overallMaxPrice,
                'overallMaxPrice' => $overallMaxPrice,
            ])->render();
            
            // Add guiding data for map updates
            $guidingsData = $guides->map(function($guiding) {
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
                'total' => $guides->total(),
                'filterCounts' => [
                    'targetFish' => $targetFishCounts,
                    'methods' => $methodCounts,
                    'waters' => $waterTypeCounts,
                    'durations' => $durationCounts,
                    'persons' => $personCounts
                ],
                'maxPrice' => $overallMaxPrice,
                'overallMaxPrice' => $overallMaxPrice,
            ]);
        }

        // Return full view for non-AJAX requests
        return view('pages.category.category-show', [
            'row_data' => $row_data,
            'guides' => $guides,
            'title' => $title,
            'filter_title' => $filter_title,
            'category_total' => $category_total,
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
            'total' => $guides->total(),
            'filterCounts' => [
                'targetFish' => $targetFishCounts,
                'methods' => $methodCounts,
                'waters' => $waterTypeCounts,
                'durations' => $durationCounts,
                'persons' => $personCounts
            ],
            'maxPrice' => $overallMaxPrice,
            'overallMaxPrice' => $overallMaxPrice,
            'radius' => $radius,
        ]);
    }
}
