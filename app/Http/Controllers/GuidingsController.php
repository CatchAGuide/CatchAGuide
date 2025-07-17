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
use App\Traits\GuidingFilterOptimization;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Auth;
use Config;
use Illuminate\Support\Facades\Mail;
use App\Mail\GuidingRequestMail;
use App\Mail\SearchRequestUserMail;
use Illuminate\Support\Facades\Log;
use App\Models\ExtrasPrice;
use App\Services\CalendarScheduleService;
use App\Models\BoatExtras;
use App\Models\Destination;
use App\Models\Review;
use Illuminate\Support\Facades\Cache;
use App\Services\GuidingFilterService;
use App\Services\ImageOptimizationService;

class GuidingsController extends Controller
{
    use GuidingFilterOptimization;
    
    /**
     * Guiding Status Values:
     * 0 = Disabled (manually disabled by user via profile page)
     * 1 = Active/Published (completed and live)
     * 2 = Draft (not completed or work in progress)
     * 
     * Status Logic:
     * - New guidings start as draft (2)
     * - When completed, they become published (1)
     * - Status 0 or 1 guidings NEVER become draft (2) when edited
     * - Only guidings that were never published can be in draft status
     * - Manual disable/enable toggles between 0 and 1
     */

    public function __construct()
    {
        $this->initializeOptimizationServices();
    }

    public function index(Request $request)
    {
        $locale = Config::get('app.locale');
        $destination = null;

        // Get random seed
        $randomSeed = $this->getRandomSeed();
       
        // Clean up request parameters before processing
        $cleanedRequest = $this->cleanRequestParameters($request);
        
        // Emergency fallback for staging environment
        if (app()->environment('staging') && config('app.simple_mode', false)) {
            return $this->indexSimpleMode($cleanedRequest, $locale, $randomSeed, $destination);
        }
        
        // Check if we actually need the filter service
        $hasCheckboxFilters = $this->hasActiveCheckboxFilters($cleanedRequest);
        
        if ($hasCheckboxFilters) {
            // Use filter service for checkbox filters
            return $this->indexWithFilterService($cleanedRequest, $locale, $randomSeed, $destination);
        } else {
            // Use direct database queries for location-only or no-filter searches
            return $this->indexWithDirectQuery($cleanedRequest, $locale, $randomSeed, $destination);
        }
    }

    private function indexSimpleMode($request, $locale, $randomSeed, $destination)
    {
        // Ultra-simple mode for staging performance issues
        $guidings = Guiding::where('status', 1)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        $guidings->appends($request->except('page'));
        
        return view('pages.guidings.index-simple', [
            'guidings' => $guidings,
            'allGuidings' => $guidings->getCollection(),
            'otherguidings' => collect(),
            'title' => 'All Guidings',
            'filter_title' => '',
            'searchMessage' => '',
            'destination' => null,
            'targetFishOptions' => collect(),
            'methodOptions' => collect(),
            'waterTypeOptions' => collect(),
            'alltargets' => collect(),
            'guiding_waters' => collect(),
            'guiding_methods' => collect(),
            'targetFishCounts' => [],
            'methodCounts' => [],
            'waterTypeCounts' => [],
            'durationCounts' => [],
            'personCounts' => [],
            'isMobile' => false,
            'total' => $guidings->total(),
            'filterCounts' => [],
            'maxPrice' => 1000,
            'overallMaxPrice' => 1000,
        ]);
    }

    private function indexWithDirectQuery($request, $locale, $randomSeed, $destination)
    {
        $searchMessage = "";
        
        // Build base query without filter service
        $baseQuery = Guiding::with(['boatType', 'user.reviews'])->where('status', 1);

        // Handle destination filtering
        if ($request->has('from_destination')) {
            $destination = Destination::where('id', $request->input('destination_id'))->first();
            
            if ($destination) {
                switch ($destination->type) {
                    case 'country':
                        $baseQuery->where('country', $destination->name);
                        break;
                    case 'region':
                        $baseQuery->where('region', $destination->name)
                              ->where('country', $destination->country_name);
                        break;
                    case 'city':
                        $baseQuery->where('city', $destination->name)
                              ->where('region', $destination->region_name)
                              ->where('country', $destination->country_name);
                        break;
                }
            }
        }

        // Apply location filtering if present
        if ($this->hasLocationFilter($request)) {
            $guidingFilter = Guiding::locationFilter(
                $request->get('city'), 
                $request->get('country'), 
                $request->get('region'), 
                $request->get('radius'), 
                $request->get('placeLat'), 
                $request->get('placeLng')
            );
            
            $searchMessage = $guidingFilter['message'];
            
            // Apply location filter by restricting to location-filtered IDs
            if (!empty($guidingFilter['ids'])) {
                $baseQuery->whereIn('id', $guidingFilter['ids']);
                
                // Add location-based ordering
                $orderByCase = 'CASE id ';
                foreach($guidingFilter['ids'] as $position => $id) {
                    $orderByCase .= "WHEN $id THEN $position ";
                }
                $orderByCase .= 'ELSE ' . count($guidingFilter['ids']) . ' END';
                $baseQuery->orderByRaw($orderByCase);
            } else {
                // No location matches found, return empty results
                $allGuidings = collect();
                $guidings = new \Illuminate\Pagination\LengthAwarePaginator(
                    collect(), 0, 20, 1, 
                    ['path' => request()->url(), 'pageName' => 'page']
                );
            }
        } else {
            $this->applySorting($baseQuery, $request, $randomSeed);
        }

        // Execute queries if we haven't already set empty results
        if (!isset($allGuidings)) {
            $allGuidings = $baseQuery->get();
            $guidings = $baseQuery->paginate(20);
            $guidings->appends($request->except('page'));
            
            // Batch fetch all needed related models for all guidings
            $allTargetIds = $allGuidings->flatMap(function($g) { return json_decode($g->target_fish, true) ?: []; })->unique()->filter()->values();
            $allMethodIds = $allGuidings->flatMap(function($g) { return json_decode($g->fishing_methods, true) ?: []; })->unique()->filter()->values();
            $allWaterIds = $allGuidings->flatMap(function($g) { return json_decode($g->water_types, true) ?: []; })->unique()->filter()->values();
            $allInclussionIds = $allGuidings->flatMap(function($g) { return json_decode($g->inclusions, true) ?: []; })->unique()->filter()->values();

            $targetsMap = $allTargetIds->isNotEmpty() ? Target::whereIn('id', $allTargetIds)->get()->keyBy('id') : collect();
            $methodsMap = $allMethodIds->isNotEmpty() ? Method::whereIn('id', $allMethodIds)->get()->keyBy('id') : collect();
            $watersMap = $allWaterIds->isNotEmpty() ? Water::whereIn('id', $allWaterIds)->get()->keyBy('id') : collect();
            $inclussionsMap = $allInclussionIds->isNotEmpty() ? Inclussion::whereIn('id', $allInclussionIds)->get()->keyBy('id') : collect();

            $this->preComputeGuidingData($allGuidings, $targetsMap, $methodsMap, $watersMap, $inclussionsMap);
            $this->preComputeGuidingData($guidings->items(), $targetsMap, $methodsMap, $watersMap, $inclussionsMap);
        } else {
            // Ensure $guidings is always defined
            if (!isset($guidings)) {
                $guidings = new \Illuminate\Pagination\LengthAwarePaginator(
                    collect(), 0, 20, 1, 
                    ['path' => request()->url(), 'pageName' => 'page']
                );
            }
        }

        // Calculate filter counts based on current result set
        if ($allGuidings->isNotEmpty()) {
            // Use the current result IDs for accurate counts
            $currentResultIds = $allGuidings->pluck('id')->toArray();
            $filterCounts = $this->getFilterService()->getFilterCounts($currentResultIds);
        } else {
            // Get basic filter counts if no current results
            $filterCounts = $this->getFilterService()->getFilterCounts();
        }
        
        // Ensure all filter count arrays have default values to prevent undefined key errors
        $filterCounts = array_merge([
            'targets' => [],
            'methods' => [],
            'water_types' => [],
            'duration_types' => [
                'half_day' => 0,
                'full_day' => 0,
                'multi_day' => 0
            ],
            'person_ranges' => []
        ], $filterCounts);

        // Get other guidings if needed (only for very small result sets)
        $otherguidings = [];
        if($allGuidings->isEmpty() || count($allGuidings) <= 3){
            if($request->has('placeLat') && $request->has('placeLng') && !empty($request->get('placeLat')) && !empty($request->get('placeLng')) ){
                $otherguidings = $this->getOtherGuidingsBasedByLocation($request->get('placeLat'), $request->get('placeLng'), $allGuidings);
            } else {
                $otherguidings = $this->getOtherGuidings();
            }
        }

        // Get filter options for display
        $targetFishOptions = Target::whereIn('id', array_keys($filterCounts['targets'] ?? []))->get();
        $methodOptions = Method::whereIn('id', array_keys($filterCounts['methods'] ?? []))->get();
        $waterTypeOptions = Water::whereIn('id', array_keys($filterCounts['water_types'] ?? []))->get();

        // Build title and filter title
        $titleData = $this->buildTitleAndFilterTitle($request, $locale, $targetFishOptions, $methodOptions, $waterTypeOptions);

        // Get all options for filters (for the filter dropdowns)
        $alltargets = Target::select('id', 'name', 'name_en')->orderBy('name')->get();
        $guiding_waters = Water::select('id', 'name', 'name_en')->orderBy('name')->get();
        $guiding_methods = Method::select('id', 'name', 'name_en')->orderBy('name')->get();

        $isMobile = $request->get('ismobile') == 'true' || app('agent')->isMobile();

        // Get max price (use cached value)
        $overallMaxPrice = $this->getMaxPriceFromFilterData();

        $responseData = [
            'title' => $titleData['title'],
            'filter_title' => $titleData['filter_title'],
            'guidings' => $guidings,
            'radius' => $request->get('radius'),
            'allGuidings' => $allGuidings,
            'searchMessage' => $searchMessage ?? '',
            'otherguidings' => $otherguidings,
            'alltargets' => $alltargets,
            'guiding_waters' => $guiding_waters,
            'guiding_methods' => $guiding_methods,
            'destination' => $destination,
            'targetFishOptions' => $targetFishOptions,
            'methodOptions' => $methodOptions,
            'waterTypeOptions' => $waterTypeOptions,
            'targetFishCounts' => $filterCounts['targets'] ?? [],
            'methodCounts' => $filterCounts['methods'] ?? [],
            'waterTypeCounts' => $filterCounts['water_types'] ?? [],
            'durationCounts' => $filterCounts['duration_types'] ?? [],
            'personCounts' => $filterCounts['person_ranges'] ?? [],
            'isMobile' => $isMobile,
            'total' => is_object($guidings) ? $guidings->total() : count($guidings),
            'filterCounts' => [
                'targetFish' => $filterCounts['targets'] ?? [],
                'methods' => $filterCounts['methods'] ?? [],
                'waters' => $filterCounts['water_types'] ?? [],
                'durations' => $filterCounts['duration_types'] ?? [],
                'persons' => $filterCounts['person_ranges'] ?? []
            ],
            'maxPrice' => $overallMaxPrice,
            'overallMaxPrice' => $overallMaxPrice,
            'targetsMap' => $targetsMap ?? collect(),
            'methodsMap' => $methodsMap ?? collect(),
            'watersMap' => $watersMap ?? collect(),
            'inclussionsMap' => $inclussionsMap ?? collect(),
        ];
        // Handle AJAX requests
        if ($request->ajax()) {
            $view = view('pages.guidings.partials.guiding-list', $responseData)->render();
            
            $guidingsData = $allGuidings->map(function($guiding) {
                return [
                    'id' => $guiding->id,
                    'slug' => $guiding->slug,
                    'title' => $guiding->title,
                    'location' => $guiding->location,
                    'lat' => $guiding->lat,
                    'lng' => $guiding->lng
                ];
            });
           
            return response()->json(array_merge($responseData, [
                'html' => $view,
                'guidings' => $guidingsData,
            ]));
        }

        return view('pages.guidings.index', $responseData);
    }

    private function indexWithFilterService($request, $locale, $randomSeed, $destination)
    {
        $searchMessage = "";
        
        // Start with all active guidings if no checkbox filters, otherwise use filtered IDs
        $hasCheckboxFilters = $this->hasActiveCheckboxFilters($request);
        
        // Only call filter service if we actually have checkbox filters
        $checkboxFilteredIds = [];
        if ($hasCheckboxFilters) {
            $checkboxFilteredIds = $this->getFilterService()->getFilteredGuidingIds($request);
        }
        
        if ($hasCheckboxFilters && empty($checkboxFilteredIds)) {
            // If checkbox filters are applied but no results, return empty
            $guidings = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(), 0, 20, 1, 
                ['path' => request()->url(), 'pageName' => 'page']
            );
            $allGuidings = collect();
        } else {
            // Build base query with minimal eager loading
            if ($hasCheckboxFilters) {
                // Use filtered IDs from JSON service
                $baseQuery = Guiding::with(['boatType', 'user.reviews'])
                    ->whereIn('id', $checkboxFilteredIds)
                    ->where('status', 1);
            } else {
                // No checkbox filters - get all active guidings
                $baseQuery = Guiding::with(['boatType', 'user.reviews'])
                    ->where('status', 1);
            }

            // Handle destination filtering
            if ($request->has('from_destination')) {
                $destination = Destination::where('id', $request->input('destination_id'))->first();
                
                if ($destination) {
                    switch ($destination->type) {
                        case 'country':
                            $baseQuery->where('country', $destination->name);
                            break;
                        case 'region':
                            $baseQuery->where('region', $destination->name)
                                  ->where('country', $destination->country_name);
                            break;
                        case 'city':
                            $baseQuery->where('city', $destination->name)
                                  ->where('region', $destination->region_name)
                                  ->where('country', $destination->country_name);
                            break;
                    }
                }
            }

            // Apply location filtering if present (this can't be pre-computed)
            if ($this->hasLocationFilter($request)) {
                $guidingFilter = Guiding::locationFilter(
                    $request->get('city'), 
                    $request->get('country'), 
                    $request->get('region'), 
                    $request->get('radius'), 
                    $request->get('placeLat'), 
                    $request->get('placeLng')
                );
                
                $searchMessage = $guidingFilter['message'];
                
                // Apply location filter by restricting to location-filtered IDs
                if (!empty($guidingFilter['ids'])) {
                    $baseQuery->whereIn('id', $guidingFilter['ids']);
                    
                    // Add location-based ordering
                    $orderByCase = 'CASE id ';
                    foreach($guidingFilter['ids'] as $position => $id) {
                        $orderByCase .= "WHEN $id THEN $position ";
                    }
                    $orderByCase .= 'ELSE ' . count($guidingFilter['ids']) . ' END';
                    $baseQuery->orderByRaw($orderByCase);
                } else {
                    // No location matches found, return empty results
                    $allGuidings = collect();
                    $guidings = new \Illuminate\Pagination\LengthAwarePaginator(
                        collect(), 0, 20, 1, 
                        ['path' => request()->url(), 'pageName' => 'page']
                    );
                }
            } else {
                $this->applySorting($baseQuery, $request, $randomSeed);
            }

            // Only execute queries if we haven't already set empty results
            if (!isset($allGuidings)) {
                $allGuidings = $baseQuery->get();
                $guidings = $baseQuery->paginate(20);
                $guidings->appends($request->except('page'));
                
                // Batch fetch all needed related models for all guidings
                $allTargetIds = $allGuidings->flatMap(function($g) { return json_decode($g->target_fish, true) ?: []; })->unique()->filter()->values();
                $allMethodIds = $allGuidings->flatMap(function($g) { return json_decode($g->fishing_methods, true) ?: []; })->unique()->filter()->values();
                $allWaterIds = $allGuidings->flatMap(function($g) { return json_decode($g->water_types, true) ?: []; })->unique()->filter()->values();
                $allInclussionIds = $allGuidings->flatMap(function($g) { return json_decode($g->inclusions, true) ?: []; })->unique()->filter()->values();

                $targetsMap = $allTargetIds->isNotEmpty() ? Target::whereIn('id', $allTargetIds)->get()->keyBy('id') : collect();
                $methodsMap = $allMethodIds->isNotEmpty() ? Method::whereIn('id', $allMethodIds)->get()->keyBy('id') : collect();
                $watersMap = $allWaterIds->isNotEmpty() ? Water::whereIn('id', $allWaterIds)->get()->keyBy('id') : collect();
                $inclussionsMap = $allInclussionIds->isNotEmpty() ? Inclussion::whereIn('id', $allInclussionIds)->get()->keyBy('id') : collect();

                $this->preComputeGuidingData($allGuidings, $targetsMap, $methodsMap, $watersMap, $inclussionsMap);
                $this->preComputeGuidingData($guidings->getCollection(), $targetsMap, $methodsMap, $watersMap, $inclussionsMap);
            }
        }

        // Get filter counts - optimize based on whether we have checkbox filters
        if ($hasCheckboxFilters) {
            // Use the final result IDs for accurate counts when checkbox filters are applied
            $finalResultIds = $allGuidings->pluck('id')->toArray();
            $filterCounts = $this->getFilterService()->getFilterCounts($finalResultIds);
        } else {
            // For location-only searches, use basic counts or skip entirely
            $filterCounts = [
                'targets' => [],
                'methods' => [],
                'water_types' => [],
                'duration_types' => [
                    'half_day' => 0,
                    'full_day' => 0,
                    'multi_day' => 0
                ],
                'person_ranges' => []
            ];
        }
        
        // Ensure all filter count arrays have default values to prevent undefined key errors
        $filterCounts = array_merge([
            'targets' => [],
            'methods' => [],
            'water_types' => [],
            'duration_types' => [
                'half_day' => 0,
                'full_day' => 0,
                'multi_day' => 0
            ],
            'person_ranges' => []
        ], $filterCounts);

        // Get other guidings if needed (only for very small result sets)
        $otherguidings = [];
        if($allGuidings->isEmpty() || count($allGuidings) <= 3){
            if($request->has('placeLat') && $request->has('placeLng') && !empty($request->get('placeLat')) && !empty($request->get('placeLng')) ){
                $otherguidings = $this->getOtherGuidingsBasedByLocation($request->get('placeLat'), $request->get('placeLng'), $allGuidings);
            } else {
                $otherguidings = $this->getOtherGuidings();
            }
        }

        // Get filter options for display
        $targetFishOptions = Target::whereIn('id', array_keys($filterCounts['targets'] ?? []))->get();
        $methodOptions = Method::whereIn('id', array_keys($filterCounts['methods'] ?? []))->get();
        $waterTypeOptions = Water::whereIn('id', array_keys($filterCounts['water_types'] ?? []))->get();

        // Build title and filter title
        $titleData = $this->buildTitleAndFilterTitle($request, $locale, $targetFishOptions, $methodOptions, $waterTypeOptions);

        // Get all options for filters (for the filter dropdowns)
        $alltargets = Target::select('id', 'name', 'name_en')->orderBy('name')->get();
        $guiding_waters = Water::select('id', 'name', 'name_en')->orderBy('name')->get();
        $guiding_methods = Method::select('id', 'name', 'name_en')->orderBy('name')->get();

        $isMobile = $request->get('ismobile') == 'true' || app('agent')->isMobile();

        // Get max price from filter service metadata (only if needed)
        $overallMaxPrice = $this->getMaxPriceFromFilterData();

        $responseData = [
            'title' => $titleData['title'],
            'filter_title' => $titleData['filter_title'],
            'guidings' => $guidings,
            'radius' => $request->get('radius'),
            'allGuidings' => $allGuidings,
            'searchMessage' => $searchMessage ?? '',
            'otherguidings' => $otherguidings,
            'alltargets' => $alltargets,
            'guiding_waters' => $guiding_waters,
            'guiding_methods' => $guiding_methods,
            'destination' => $destination,
            'targetFishOptions' => $targetFishOptions,
            'methodOptions' => $methodOptions,
            'waterTypeOptions' => $waterTypeOptions,
            'targetFishCounts' => $filterCounts['targets'] ?? [],
            'methodCounts' => $filterCounts['methods'] ?? [],
            'waterTypeCounts' => $filterCounts['water_types'] ?? [],
            'durationCounts' => $filterCounts['duration_types'] ?? [],
            'personCounts' => $filterCounts['person_ranges'] ?? [],
            'isMobile' => $isMobile,
            'total' => is_object($guidings) ? $guidings->total() : count($guidings),
            'filterCounts' => [
                'targetFish' => $filterCounts['targets'] ?? [],
                'methods' => $filterCounts['methods'] ?? [],
                'waters' => $filterCounts['water_types'] ?? [],
                'durations' => $filterCounts['duration_types'] ?? [],
                'persons' => $filterCounts['person_ranges'] ?? []
            ],
            'maxPrice' => $overallMaxPrice,
            'overallMaxPrice' => $overallMaxPrice,
            'targetsMap' => $targetsMap ?? collect(),
            'methodsMap' => $methodsMap ?? collect(),
            'watersMap' => $watersMap ?? collect(),
            'inclussionsMap' => $inclussionsMap ?? collect(),
        ];

        // Handle AJAX requests
        if ($request->ajax()) {
            $view = view('pages.guidings.partials.guiding-list', $responseData)->render();
            
            $guidingsData = $allGuidings->map(function($guiding) {
                return [
                    'id' => $guiding->id,
                    'slug' => $guiding->slug,
                    'title' => $guiding->title,
                    'location' => $guiding->location,
                    'lat' => $guiding->lat,
                    'lng' => $guiding->lng
                ];
            });
            
            return response()->json(array_merge($responseData, [
                'html' => $view,
                'guidings' => $guidingsData,
            ]));
        }

        return view('pages.guidings.index', $responseData);
    }

    public function otherGuidings(){
        return $this->getOtherGuidings();
    }

    public function otherGuidingsBasedByLocation($latitude, $longitude, $allGuidings)
    {
        return $this->getOtherGuidingsBasedByLocation($latitude, $longitude, $allGuidings);
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
            ->where('status', 1)
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

    public function guidingsStore(StoreNewGuidingRequest $request)
    {
        try {
            DB::beginTransaction();

            $isDraft = $request->input('is_draft', 0) == 1;
            $isUpdate = $request->input('is_update', 0) == 1;
            $guiding = $isUpdate
                ? Guiding::findOrFail($request->input('guiding_id'))
                : new Guiding(['user_id' => auth()->id()]);

            // Store original status for updates
            $originalStatus = $isUpdate ? $guiding->status : null;

            $this->fillGuidingFromRequest($guiding, $request, $isDraft);

            // Slug generation (always for new, or if title/location changed)
            if (!$isUpdate) {
                $guiding->slug = slugify($guiding->title . "-in-" . $guiding->location);
            }

            // Only enforce strict requirements if not draft
            $galleryImages = json_decode($guiding->gallery_images, true) ?? [];
            if (!$isDraft && (empty($galleryImages) || count($galleryImages) < 5)) {
                throw new \Exception('Please upload at least 5 images');
            }

            $guiding->is_newguiding = 1;
            
            // Smart status management
            if ($isDraft) {
                // Preserve status if it was ever published (1) or disabled (0)
                // Only set to draft (2) if it's new or was already a draft
                if ($isUpdate && ((int)$originalStatus === 1 || (int)$originalStatus === 0)) {
                    $guiding->status = $originalStatus; // Keep original status (0 or 1)
                } else {
                    $guiding->status = 2; // New guiding or was already draft
                }
            } else {
                // Final submission - set to published
                $guiding->status = 1;
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

    public function saveDraft(StoreNewGuidingRequest $request)
    {
        try {
            // Handle file uploads synchronously first
            $processedData = $this->processFileUploads($request);
            
            // Prepare data for the job
            $guidingData = $this->prepareGuidingDataForJob($request, $processedData);
            
            $isUpdate = $request->input('is_update') == '1';
            $guidingId = $isUpdate ? $request->input('guiding_id') : null;
            
            // Add original status to guiding data for the job to use
            if ($isUpdate && $guidingId) {
                $existingGuiding = Guiding::find($guidingId);
                $originalStatus = $existingGuiding ? $existingGuiding->status : null;
                $guidingData['original_status'] = $originalStatus;
            }
            
            // Dispatch the job for database operations
            \App\Jobs\SaveGuidingDraftJob::dispatch(
                $guidingData, 
                $request->has('user_id') && $request->input('user_id') != null && $request->input('user_id') != '' ? $request->input('user_id') : auth()->id(), 
                $isUpdate, 
                $guidingId
            );

            return response()->json([
                'success' => true,
                'guiding_id' => $guidingId,
                'message' => 'Draft is being saved...'
            ]);

        } catch (\Exception $e) {
            Log::error('Error in saveDraft: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save draft.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process file uploads synchronously and return processed data
     */
    private function processFileUploads(StoreNewGuidingRequest $request): array
    {
        $galeryImages = [];
        $imageList = json_decode($request->input('image_list', '[]')) ?? [];
        $thumbnailPath = '';

        // Handle existing images for updates
        if ($request->input('is_update') == '1') {
            $existingImagesJson = $request->input('existing_images');
            $existingImages = json_decode($existingImagesJson, true) ?? [];
            $keepImages = array_filter($imageList);

            foreach ($existingImages as $existingImage) {
                $imagePath = $existingImage;
                $imagePathWithSlash = '/' . $existingImage;
                if (in_array($imagePath, $keepImages) || in_array($imagePathWithSlash, $keepImages)) {
                    $galeryImages[] = $existingImage;
                } else {
                    media_delete($existingImage);
                }
            }
        }

        // Process new file uploads
        if ($request->has('title_image')) {
            $imageCount = count($galeryImages);
            $tempSlug = slugify(($request->input('title') ?? 'temp') . "-in-" . ($request->input('location') ?? 'location'));
            
            foreach($request->file('title_image') as $index => $image) {
                $filename = 'guidings-images/'.$image->getClientOriginalName();
                if (in_array($filename, $imageList) || in_array('/' . $filename, $imageList)) {
                    continue;
                }
                $index = $index + $imageCount;
                $webp_path = media_upload($image, 'guidings-images', $tempSlug. "-". $index . "-" . time());
                $galeryImages[] = $webp_path;
            }
        }

        // Set the primary image if available
        $primaryImageIndex = $request->input('primaryImage', 0);
        if (isset($galeryImages[$primaryImageIndex])) {
            $thumbnailPath = $galeryImages[$primaryImageIndex];
        }

        return [
            'gallery_images' => $galeryImages,
            'thumbnail_path' => $thumbnailPath
        ];
    }

    /**
     * Prepare data for the job from request
     */
    private function prepareGuidingDataForJob(StoreNewGuidingRequest $request, array $processedData): array
    {
        // Basic fields
        $data = [
            'location' => $request->input('location', ''),
            'title' => $request->input('title', ''),
            'latitude' => $request->input('latitude', ''),
            'longitude' => $request->input('longitude', ''),
            'country' => $request->input('country', ''),
            'city' => $request->input('city', ''),
            'region' => $request->input('region', ''),
            
            // Processed images
            'gallery_images' => $processedData['gallery_images'],
            'thumbnail_path' => $processedData['thumbnail_path'],
            
            // Boat and fishing info
            'is_boat' => $request->has('type_of_fishing') ? ($request->input('type_of_fishing') == 'boat' ? 1 : 0) : 0,
            'fishing_from_id' => (int) $request->has('type_of_fishing') ? ($request->input('type_of_fishing') == 'boat' ? 1 : 2) : 2,
            'other_boat_info' => $request->input('other_boat_info', ''),
            'boat_type' => $request->input('type_of_boat', ''),
            
            // Process boat information
            'boat_information' => $this->prepareDescriptionsForJob($request),
            'boat_extras' => $this->prepareJsonDataForJob($request->input('boat_extras') ?? '[]'),
            
            // Target fish, methods, water types
            'target_fish' => $this->prepareJsonDataForJob($request->input('target_fish') ?? '[]'),
            'methods' => $this->prepareJsonDataForJob($request->input('methods') ?? '[]'),
            'style_of_fishing' => (int) $request->input('style_of_fishing', 3),
            'water_types' => $this->prepareJsonDataForJob($request->input('water_types') ?? '[]'),
            
            // Descriptions
            'desc_course_of_action' => $request->input('desc_course_of_action', ''),
            'desc_meeting_point' => $request->input('desc_meeting_point', ''),
            'meeting_point' => $request->input('meeting_point', ''),
            'desc_starting_time' => $request->input('desc_starting_time', ''),
            'desc_departure_time' => $request->input('desc_departure_time', []),
            'desc_tour_unique' => $request->input('desc_tour_unique', ''),
            'description' => $request->input('desc_course_of_action', $this->generateLongDescription($request)),
            
            // Requirements, recommendations, other info
            'requirements' => $this->prepareRequirementsForJob($request),
            'recommendations' => $this->prepareRecommendationsForJob($request),
            'other_information' => $this->prepareOtherInformationForJob($request),
            
            // Tour details
            'tour_type' => $request->input('tour_type', ''),
            'duration' => $request->input('duration', ''),
            'duration_value' => $request->input('duration') == 'multi_day' 
                ? (int) $request->input('duration_days', 0) 
                : (int) $request->input('duration_hours', 0),
            'no_guest' => (int) $request->input('no_guest', 0),
            'min_guests' => $request->has('has_min_guests') ? (int) $request->input('min_guests') : null,
            
            // Pricing
            'price_type' => $request->input('price_type', ''),
            'price' => $this->calculatePrice($request),
            'prices' => $this->preparePricesForJob($request),
            'inclusions' => $this->prepareJsonDataForJob($request->input('inclusions') ?? '[]'),
            'pricing_extra' => $this->preparePricingExtrasForJob($request),
            
            // Booking settings
            'allowed_booking_advance' => $request->input('allowed_booking_advance', ''),
            'booking_window' => $request->input('booking_window', ''),
            'seasonal_trip' => $request->input('seasonal_trip', ''),
            'months' => $request->input('months', []),
            'weekday_availability' => $request->input('weekday_availability', 'all_week'),
            'weekdays' => $request->input('weekday_availability') === 'all_week' 
                ? ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']
                : $request->input('weekdays', []),
        ];

        return $data;
    }

    /**
     * Helper methods for data preparation
     */
    private function prepareJsonDataForJob(?string $jsonString): array
    {
        if ($jsonString === null) {
            return [];
        }
        
        $data = collect(json_decode($jsonString, true) ?? []);
        return $data->map(function($item) {
            return $item['id'] ?? $item['value'] ?? $item;
        })->toArray();
    }

    private function prepareDescriptionsForJob($request): array
    {
        $descriptions = $request->input('descriptions', []);
        $descriptionData = [];

        foreach ($descriptions as $description) {
            $descriptionData[$description] = $request->input("boat_description_".$description);
        }

        return $descriptionData;
    }

    private function prepareRequirementsForJob($request): array
    {
        $requirements = $request->input('requiements_taking_part', []);
        $requirementData = [];

        foreach ($requirements as $requirement) {
            $requirementData[$requirement] = $request->input("requiements_taking_part_".$requirement);
        }

        return $requirementData;
    }

    private function prepareRecommendationsForJob($request): array
    {
        $recommendations = $request->input('recommended_preparation', []);
        $recommendationData = [];

        foreach ($recommendations as $recommendation) {
            $recommendationData[$recommendation] = $request->input("recommended_preparation_".$recommendation);
        }

        return $recommendationData;
    }

    private function prepareOtherInformationForJob($request): array
    {
        $otherInformations = $request->input('other_information', []);
        $otherInformationData = [];

        foreach ($otherInformations as $otherInformation) {
            $otherInformationData[$otherInformation] = $request->input("other_information_".$otherInformation);
        }

        return $otherInformationData;
    }

    private function calculatePrice($request): float
    {
        if ($request->input('price_type') === 'per_person') {
            return 0;
        }
        
        return (float) $request->input('price_per_boat', 0);
    }

    private function preparePricesForJob($request): array
    {
        $pricePerPerson = [];
        
        if ($request->input('price_type') === 'per_person') {
            foreach ($request->all() as $key => $value) {
                if (strpos($key, 'price_per_person_') === 0) {
                    $guestNumber = substr($key, strlen('price_per_person_'));
                    $pricePerPerson[] = [
                        'person' => $guestNumber,
                        'amount' => $value
                    ];
                }
            }
        } else {
            if($request->has('no_guest')){
                for ($i = 1; $i <= $request->input('no_guest', 0); $i++) {
                    $pricePerPerson[] = [
                        'person' => $i,
                        'amount' => (float) ($request->input('price_per_boat', 0) / max(1, $request->input('no_guest', 1))) * $i
                    ];
                }
            }
        }
        
        return $pricePerPerson;
    }

    private function preparePricingExtrasForJob($request): array
    {
        $pricingExtras = [];
        $i = 1;
        
        while (true) {
            $nameKey = "extra_name_" . $i;
            $priceKey = "extra_price_" . $i;

            if ($request->has($nameKey) && $request->has($priceKey)) {
                $extraPrice = \App\Models\ExtrasPrice::where('name', $request->input($nameKey))
                    ->orWhere('name_en', $request->input($nameKey))
                    ->first();
                $extraname = $extraPrice ? $extraPrice->name : $request->input($nameKey);
                
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
        
        return $pricingExtras;
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

    /**
     * Pre-compute expensive view data to avoid N+1 queries and repeated function calls
     */
    private function preComputeGuidingData($guidings, $targetsMap = null, $methodsMap = null, $watersMap = null, $inclussionsMap = null)
    {
        if (empty($guidings)) {
            return;
        }
        foreach ($guidings as $guiding) {
            $galleryImages = json_decode($guiding->gallery_images, true) ?? [];
            $optimizedImages = [];
            foreach ($galleryImages as $imagePath) {
                $optimizedImages[] = $this->imageOptimizationService->getOptimizedThumbnail($imagePath);
            }
            $guiding->cached_gallery_images = $optimizedImages;
            $guiding->cached_target_fish_names = $guiding->getTargetFishNames($targetsMap);
            $guiding->cached_inclusion_names = $guiding->getInclusionNames($inclussionsMap);
            $guiding->cached_review_count = $guiding->user->reviews->count();
            $guiding->cached_average_rating = $guiding->user->average_rating();
            $guiding->cached_boat_type_name = $guiding->is_boat ? 
                ($guiding->boatType && $guiding->boatType->name !== null ? $guiding->boatType->name : __('guidings.boat')) : 
                __('guidings.shore');
        }
    }

    /**
     * Legacy method maintained for compatibility with guidingsStore
     * Fill a Guiding model from request data.
     * Handles both draft and final save logic.
     */
    private function fillGuidingFromRequest(Guiding $guiding, StoreNewGuidingRequest $request, bool $isDraft)
    {
        // Step 1: Basic fields
        $guiding->location = $request->input('location', '');
        $guiding->title = $request->input('title', '');
        $guiding->lat = $request->input('latitude', '');
        $guiding->lng = $request->input('longitude', '');
        $guiding->country = $request->input('country', '');
        $guiding->city = $request->input('city', '');
        $guiding->region = $request->input('region', '');

        // Step 1: Images
        $galeryImages = [];
        $imageList = json_decode($request->input('image_list', '[]')) ?? [];

        if ($request->input('is_update') == '1') {
            $existingImagesJson = $request->input('existing_images');
            $existingImages = json_decode($existingImagesJson, true) ?? [];
            $keepImages = array_filter($imageList);

            foreach ($existingImages as $existingImage) {
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
                $filename = 'guidings-images/'.$image->getClientOriginalName();
                if (in_array($filename, $imageList) || in_array('/' . $filename, $imageList)) {
                    continue;
                }
                $index = $index + $imageCount;
                $webp_path = media_upload($image, 'guidings-images', $guiding->slug. "-". $index . "-" . time());
                $galeryImages[] = $webp_path;
            }
        }

        // Set the primary image if available
        foreach($galeryImages as $index => $image) {
            if($index == $request->input('primaryImage', 0)) {
                $guiding->thumbnail_path = $image;
            }
        }
        $guiding->gallery_images = json_encode($galeryImages);

        // Step 2: Boat and fishing info
        $guiding->is_boat = $request->has('type_of_fishing') ? ($request->input('type_of_fishing') == 'boat' ? 1 : 0) : 0;
        $guiding->fishing_from_id = (int) $request->has('type_of_fishing') ? ($request->input('type_of_fishing') == 'boat' ? 1 : 2) : 2;
        $guiding->additional_information = $request->input('other_boat_info', '');
        if ($guiding->is_boat) {
            $guiding->boat_type = $request->input('type_of_boat', '');
            $guiding->boat_information = $this->saveDescriptions($request);

            $boatExtrasData = collect(json_decode($request->input('boat_extras', '[]')));
            $boatExtras = $boatExtrasData->map(function($item) {
                return $item->id ?? $item->value;
            })->toArray();
            $guiding->boat_extras = json_encode($boatExtras);
        }

        // Step 3: Target fish, methods, water types
        if ($request->has('target_fish')) {
            $targetFishData = collect(json_decode($request->input('target_fish', '[]')));
            $targetFish = $targetFishData->map(function($item) {
                return $item->id ?? $item->value;
            })->toArray();
            $guiding->target_fish = json_encode($targetFish);
        }

        $methodsData = collect(json_decode($request->input('methods', '[]')));
        $methods = $methodsData->map(function($item) {
            return $item->id ?? $item->value;
        })->toArray();
        $guiding->fishing_methods = json_encode($methods);

        $guiding->fishing_type_id = (int) $request->input('style_of_fishing', 3);

        $waterTypesData = collect(json_decode($request->input('water_types', '[]')));
        $waterTypes = $waterTypesData->map(function($item) {
            return $item->id ?? $item->value;
        })->toArray();
        $guiding->water_types = json_encode($waterTypes);

        // Step 4: Descriptions
        $guiding->desc_course_of_action = $request->input('desc_course_of_action', '');
        $guiding->desc_meeting_point = $request->input('desc_meeting_point', '');
        $guiding->meeting_point = $request->input('meeting_point', '');
        $guiding->desc_starting_time = $request->input('desc_starting_time', '');
        $guiding->desc_departure_time = $request->has('desc_departure_time') ? json_encode($request->input('desc_departure_time')) : json_encode([]);
        $guiding->desc_tour_unique = $request->input('desc_tour_unique', '');
        $guiding->description = $request->input('desc_course_of_action', $this->generateLongDescription($request));

        // Step 5: Requirements, recommendations, other info
        $guiding->requirements = $this->saveRequirements($request);
        $guiding->recommendations = $this->saveRecommendations($request);
        $guiding->other_information = $this->saveOtherInformation($request);

        // Step 6: Tour type, duration, guests, price
        $guiding->tour_type = $request->input('tour_type', '');
        $guiding->duration_type = $request->input('duration', '');
        if ($request->input('duration') == 'multi_day') {
            $guiding->duration = (int) $request->input('duration_days', 0);
        } else {
            $guiding->duration = (int) $request->input('duration_hours', 0);
        }
        $guiding->max_guests = (int) $request->input('no_guest', 0);

        if ($request->has('price_type')) {
            $guiding->price_type = $request->input('price_type');
            $pricePerPerson = [];
            if ($request->input('price_type') === 'per_person') {
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
                if($request->has('no_guest')){
                    for ($i = 1; $i <= $request->input('no_guest', 0); $i++) {
                        $pricePerPerson[] = [
                            'person' => $i,
                            'amount' => (float) ($request->input('price_per_boat', 0) / max(1, $request->input('no_guest', 1))) * $i
                        ];
                    }
                }
                $guiding->price = (float) $request->input('price_per_boat', 0);
            }
            $guiding->prices = json_encode($pricePerPerson);
        }

        $inclusionsData = collect(json_decode($request->input('inclusions', '[]')));
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

        // Step 7: Booking/seasonal info
        $guiding->allowed_booking_advance = $request->input('allowed_booking_advance', '');
        $guiding->booking_window = $request->input('booking_window', '');

        if ($request->has('seasonal_trip')) {
            $guiding->seasonal_trip = $request->input('seasonal_trip');
            $allMonths = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];

            if ($request->input('seasonal_trip') == "season_monthly") {
                $selectedMonths = $request->input('months', []);
                $guiding->months = json_encode($selectedMonths);
            } else {
                $selectedMonths = $allMonths;
                $guiding->months = json_encode($selectedMonths);
            }

            // Generate complete calendar schedule
            CalendarScheduleService::generateCompleteSchedule(
                $guiding,
                $selectedMonths,
                $request->input('weekdays', []),
                $request->input('is_update') == '1' // shouldCleanup
            );
        }

        $guiding->weekday_availability = $request->input('weekday_availability', 'all_week');
        if ($request->input('weekday_availability') === 'all_week') {
            $guiding->weekdays = json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
        } else {
            $guiding->weekdays = $request->has('weekdays') ? json_encode($request->input('weekdays')) : json_encode([]);
        }
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
            'status' => $guiding->status,
            'user_id' => $guiding->user_id,
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

    /**
     * Alternative synchronous version of saveDraft for immediate feedback
     * Use this if you need immediate response with guiding_id
     */
    public function saveDraftSync(StoreNewGuidingRequest $request)
    {
        try {
            DB::beginTransaction();

            // Handle file uploads first
            $processedData = $this->processFileUploads($request);

            $isUpdate = $request->input('is_update') == '1';
            $originalStatus = null;

            // Try to find an existing draft for this user and (optionally) title/location
            if ($isUpdate && $request->input('guiding_id')) {
                $guiding = Guiding::findOrFail($request->input('guiding_id'));
                $originalStatus = $guiding->status;
                Log::info('SaveDraftSync: Found existing guiding', [
                    'guiding_id' => $guiding->id,
                    'original_status' => $originalStatus,
                    'is_update' => $isUpdate
                ]);
            } else {
                $guiding = Guiding::where('user_id', auth()->id())
                    ->where('status', 2)
                    ->where('title', $request->input('title'))
                    ->where('city', $request->input('city'))
                    ->where('country', $request->input('country'))
                    ->where('region', $request->input('region'))
                    ->first();

                if (!$guiding) {
                    $guiding = new Guiding(['user_id' => auth()->id()]);
                    Log::info('SaveDraftSync: Creating new guiding');
                } else {
                    Log::info('SaveDraftSync: Found existing draft guiding', [
                        'guiding_id' => $guiding->id,
                        'status' => $guiding->status
                    ]);
                }
            }

            // Use the legacy method for consistency
            $this->fillGuidingFromRequest($guiding, $request, true);

            // Slug generation (always for new, or if title/location changed)
            if (!$isUpdate) {
                $guiding->slug = slugify($guiding->title . "-in-" . $guiding->location);
            }

            $guiding->is_newguiding = 1;
            
            // Smart status management for drafts
            if ($isUpdate && ((int)$originalStatus === 1 || (int)$originalStatus === 0)) {
                // Preserve original status if it was published (1) or disabled (0)
                $guiding->status = $originalStatus;
                Log::info('SaveDraftSync: Preserving original status', [
                    'guiding_id' => $guiding->id,
                    'original_status' => $originalStatus,
                    'preserved_status' => $guiding->status
                ]);
            } else {
                // Set to draft for new guidings or guidings that were already drafts
                $guiding->status = 2;
                Log::info('SaveDraftSync: Setting to draft status', [
                    'guiding_id' => $guiding->id,
                    'original_status' => $originalStatus,
                    'new_status' => $guiding->status,
                    'reason' => $isUpdate ? 'was already draft (status 2)' : 'new guiding'
                ]);
            }

            $guiding->save();
            DB::commit();

            Log::info('SaveDraftSync: Successfully saved', [
                'guiding_id' => $guiding->id,
                'final_status' => $guiding->status
            ]);

            return response()->json([
                'success' => true,
                'guiding_id' => $guiding->id,
                'message' => 'Draft saved successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in saveDraftSync: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save draft.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


}
