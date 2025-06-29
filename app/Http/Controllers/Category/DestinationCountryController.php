<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\Guiding;
use App\Models\Method;
use App\Models\Target;
use App\Models\Water;
use App\Models\Inclussion;
use App\Traits\GuidingFilterOptimization;
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
    use GuidingFilterOptimization;

    public function index()
    {
        $countries = Destination::whereType('country')->whereLanguage(app()->getLocale())->get();
        return view('pages.countries.index', compact('countries'));
    }

    public function country(Request $request, $country, $region = null, $city = null)
    {
        // Validate existence of parent records first
        $country_row = Destination::whereSlug($country)
            ->whereType('country')
            ->whereLanguage(app()->getLocale())
            ->firstOrFail();

        $region_row = null;
        $city_row = null;

        if ($region) {
            $region_row = Destination::whereSlug($region)
                ->whereType('region')
                ->whereCountryId($country_row->id)
                ->whereLanguage(app()->getLocale())
                ->firstOrFail();
        }

        if ($city) {
            $city_row = Destination::whereSlug($city)
                ->whereType('city')
                ->whereCountryId($country_row->id)
                ->whereRegionId($region_row->id)
                ->whereLanguage(app()->getLocale())
                ->firstOrFail();
        }

        // Get destination data
        $query = Destination::with(['faq', 'fish_chart', 'fish_size_limit', 'fish_time_limit']);

        if ($city_row) {
            $query->whereType('city')->whereId($city_row->id);
        } elseif ($region_row) {
            $query->whereType('region')->whereId($region_row->id);
        } else {
            $query->whereType('country')->whereId($country_row->id);
        }

        $row_data = $query->firstOrFail();
        $regions = Destination::with(['faq', 'fish_chart', 'fish_size_limit', 'fish_time_limit'])
            ->whereType('region')
            ->whereCountryId($row_data->id)
            ->whereLanguage(app()->getLocale())
            ->get();
        
        $cities = Destination::with(['faq', 'fish_chart', 'fish_size_limit', 'fish_time_limit'])
            ->whereType('city')
            ->whereCountryId($row_data->id)
            ->whereLanguage(app()->getLocale())
            ->get();

        $faq = $row_data->faq;
        $fish_chart = $row_data->fish_chart;
        $fish_size_limit = $row_data->fish_size_limit;
        $fish_time_limit = $row_data->fish_time_limit;

        $locale = Config::get('app.locale');
        $destination = null;

        // Get random seed
        $randomSeed = $this->getRandomSeed();

        // Clean up request parameters before processing
        $cleanedRequest = $this->cleanRequestParameters($request);
        
        // Emergency fallback for staging environment
        if (app()->environment('staging') && config('app.simple_mode', false)) {
            return $this->countrySimpleMode($cleanedRequest, $locale, $randomSeed, $destination, $row_data, $regions, $cities, $faq, $fish_chart, $fish_size_limit, $fish_time_limit);
        }
        
        // Check if we actually need the filter service
        $hasCheckboxFilters = $this->hasActiveCheckboxFilters($cleanedRequest);
        
        if ($hasCheckboxFilters) {
            // Use filter service for checkbox filters
            return $this->countryWithFilterService($cleanedRequest, $locale, $randomSeed, $destination, $row_data, $regions, $cities, $faq, $fish_chart, $fish_size_limit, $fish_time_limit, $country_row, $region_row, $city_row);
        } else {
            // Use direct database queries for location-only or no-filter searches
            return $this->countryWithDirectQuery($cleanedRequest, $locale, $randomSeed, $destination, $row_data, $regions, $cities, $faq, $fish_chart, $fish_size_limit, $fish_time_limit, $country_row, $region_row, $city_row);
        }
    }

    private function countrySimpleMode($request, $locale, $randomSeed, $destination, $row_data, $regions, $cities, $faq, $fish_chart, $fish_size_limit, $fish_time_limit)
    {
        // Ultra-simple mode for staging performance issues
        $guidings = Guiding::where('status', 1)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        $guidings->appends($request->except('page'));
        
        return view('pages.category.country', [
            'guidings_total' => $guidings->total(),
            'row_data' => $row_data,
            'regions' => $regions,
            'cities' => $cities,
            'faq' => $faq,
            'fish_chart' => $fish_chart,
            'fish_size_limit' => $fish_size_limit,
            'fish_time_limit' => $fish_time_limit,
            'title' => 'All Guidings',
            'filter_title' => '',
            'guidings' => $guidings,
            'radius' => null,
            'allGuidings' => $guidings->getCollection(),
            'searchMessage' => '',
            'otherguidings' => collect(),
            'alltargets' => collect(),
            'guiding_waters' => collect(),
            'guiding_methods' => collect(),
            'destination' => null,
            'targetFishOptions' => collect(),
            'methodOptions' => collect(),
            'waterTypeOptions' => collect(),
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

    private function countryWithDirectQuery($request, $locale, $randomSeed, $destination, $row_data, $regions, $cities, $faq, $fish_chart, $fish_size_limit, $fish_time_limit, $country_row, $region_row, $city_row)
    {
        $searchMessage = "";
        
        // Build base query with location pre-filtering (this is the key difference from GuidingsController)
        $baseQuery = Guiding::with(['boatType', 'user.reviews'])->where('status', 1);
        
        // Apply destination-based location filtering FIRST
        $this->applyDestinationLocationFilter($baseQuery, $country_row, $region_row, $city_row);
        
        // Debug: Log the destination filtering
        if (config('app.debug')) {
            $testCount = Guiding::where('status', 1)->count();
            Log::info('Destination filtering applied', [
                'total_active_guidings' => $testCount,
                'country' => $country_row ? $country_row->name : null,
                'region' => $region_row ? $region_row->name : null,
                'city' => $city_row ? $city_row->name : null,
                'sql' => $baseQuery->toSql(),
                'bindings' => $baseQuery->getBindings()
            ]);
        }

        // Handle destination filtering from request
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

        // Apply additional location filtering if present
        if ($this->hasAdditionalLocationFilter($request)) {
            $additionalFilter = $this->applyAdditionalLocationFilter($request);
            $searchMessage = $additionalFilter['message'];
            
            if (!empty($additionalFilter['ids'])) {
                $baseQuery->whereIn('id', $additionalFilter['ids']);
                
                // Add location-based ordering
                $orderByCase = 'CASE id ';
                foreach($additionalFilter['ids'] as $position => $id) {
                    $orderByCase .= "WHEN $id THEN $position ";
                }
                $orderByCase .= 'ELSE ' . count($additionalFilter['ids']) . ' END';
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
            $currentResultIds = $allGuidings->pluck('id')->toArray();
            $filterCounts = $this->getFilterService()->getFilterCounts($currentResultIds);
        } else {
            $filterCounts = $this->getFilterService()->getFilterCounts();
        }
        
        // Ensure all filter count arrays have default values
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

        // Get other guidings if needed
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

        // Get all options for filters
        $alltargets = Target::select('id', 'name', 'name_en')->orderBy('name')->get();
        $guiding_waters = Water::select('id', 'name', 'name_en')->orderBy('name')->get();
        $guiding_methods = Method::select('id', 'name', 'name_en')->orderBy('name')->get();

        $isMobile = $request->get('ismobile') == 'true' || app('agent')->isMobile();
        $overallMaxPrice = $this->getMaxPriceFromFilterData();

        $responseData = [
            'guidings_total' => is_object($guidings) ? $guidings->total() : count($guidings),
            'row_data' => $row_data,
            'regions' => $regions,
            'cities' => $cities,
            'faq' => $faq,
            'fish_chart' => $fish_chart,
            'fish_size_limit' => $fish_size_limit,
            'fish_time_limit' => $fish_time_limit,
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

        return view('pages.category.country', $responseData);
    }

    private function countryWithFilterService($request, $locale, $randomSeed, $destination, $row_data, $regions, $cities, $faq, $fish_chart, $fish_size_limit, $fish_time_limit, $country_row, $region_row, $city_row)
    {
        $searchMessage = "";
        
        // Start with location-filtered guidings (this is different from GuidingsController)
        $locationFilteredIds = $this->getLocationFilteredIds($country_row, $region_row, $city_row);
        
        // Debug: Log the location filtering results
        if (config('app.debug')) {
            Log::info('Location filtered IDs', [
                'country' => $country_row ? $country_row->name : null,
                'region' => $region_row ? $region_row->name : null,
                'city' => $city_row ? $city_row->name : null,
                'location_filtered_count' => count($locationFilteredIds),
                'location_filtered_ids' => $locationFilteredIds
            ]);
        }
        
        // Get checkbox filter results and intersect with location filter
        $checkboxFilteredIds = $this->getFilterService()->getFilteredGuidingIds($request);
        $finalFilteredIds = array_intersect($locationFilteredIds, $checkboxFilteredIds);
        
        // Debug: Log the final filtering results
        if (config('app.debug')) {
            Log::info('Final filtered IDs', [
                'checkbox_filtered_count' => count($checkboxFilteredIds),
                'final_filtered_count' => count($finalFilteredIds),
                'final_filtered_ids' => $finalFilteredIds
            ]);
        }
        
        if (empty($finalFilteredIds)) {
            // If no results after filtering, return empty
            $guidings = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(), 0, 20, 1, 
                ['path' => request()->url(), 'pageName' => 'page']
            );
            $allGuidings = collect();
        } else {
            // Build base query with filtered IDs
            $baseQuery = Guiding::with(['boatType', 'user.reviews'])
                ->whereIn('id', $finalFilteredIds)
                ->where('status', 1);

            // Handle destination filtering from request
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

            // Apply additional location filtering if present
            if ($this->hasAdditionalLocationFilter($request)) {
                $additionalFilter = $this->applyAdditionalLocationFilter($request);
                $searchMessage = $additionalFilter['message'];
                
                if (!empty($additionalFilter['ids'])) {
                    $baseQuery->whereIn('id', $additionalFilter['ids']);
                    
                    // Add location-based ordering
                    $orderByCase = 'CASE id ';
                    foreach($additionalFilter['ids'] as $position => $id) {
                        $orderByCase .= "WHEN $id THEN $position ";
                    }
                    $orderByCase .= 'ELSE ' . count($additionalFilter['ids']) . ' END';
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

        // Get filter counts - use the final result IDs for accurate counts
        $finalResultIds = $allGuidings->pluck('id')->toArray();
        $filterCounts = $this->getFilterService()->getFilterCounts($finalResultIds);
        
        // Ensure all filter count arrays have default values
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

        // Get other guidings if needed
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

        // Get all options for filters
        $alltargets = Target::select('id', 'name', 'name_en')->orderBy('name')->get();
        $guiding_waters = Water::select('id', 'name', 'name_en')->orderBy('name')->get();
        $guiding_methods = Method::select('id', 'name', 'name_en')->orderBy('name')->get();

        $isMobile = $request->get('ismobile') == 'true' || app('agent')->isMobile();
        $overallMaxPrice = $this->getMaxPriceFromFilterData();

        $responseData = [
            'guidings_total' => is_object($guidings) ? $guidings->total() : count($guidings),
            'row_data' => $row_data,
            'regions' => $regions,
            'cities' => $cities,
            'faq' => $faq,
            'fish_chart' => $fish_chart,
            'fish_size_limit' => $fish_size_limit,
            'fish_time_limit' => $fish_time_limit,
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

        return view('pages.category.country', $responseData);
    }

    /**
     * Apply destination-based location filtering to query
     */
    private function applyDestinationLocationFilter($query, $country_row, $region_row, $city_row)
    {
        if ($city_row) {
            // For city filtering, we need the actual parent names
            $region_name = $region_row ? $region_row->name : null;
            $country_name = $country_row ? $country_row->name : null;
            
            // Use case-insensitive matching for better coverage
            $query->whereRaw('LOWER(city) = LOWER(?)', [$city_row->name]);
            if ($region_name && $region_name !== 'N/A') {
                $query->whereRaw('LOWER(region) = LOWER(?)', [$region_name]);
            }
            if ($country_name && $country_name !== 'N/A') {
                $query->whereRaw('LOWER(country) = LOWER(?)', [$country_name]);
            }
        } elseif ($region_row) {
            // For region filtering
            $country_name = $country_row ? $country_row->name : null;
            
            $query->whereRaw('LOWER(region) = LOWER(?)', [$region_row->name]);
            if ($country_name && $country_name !== 'N/A') {
                $query->whereRaw('LOWER(country) = LOWER(?)', [$country_name]);
            }
        } else {
            // For country filtering
            $query->whereRaw('LOWER(country) = LOWER(?)', [$country_row->name]);
        }
    }

    /**
     * Get location-filtered IDs for filter service approach
     */
    private function getLocationFilteredIds($country_row, $region_row, $city_row)
    {
        $query = Guiding::where('status', 1);
        $this->applyDestinationLocationFilter($query, $country_row, $region_row, $city_row);
        return $query->pluck('id')->toArray();
    }

    /**
     * Check if request has additional location filters beyond URL params
     */
    private function hasAdditionalLocationFilter($request)
    {
        // Check for coordinate-based location search (from map/autocomplete)
        $hasCoordinateSearch = !empty($request->get('placeLat')) && 
                              !empty($request->get('placeLng'));
        
        return $hasCoordinateSearch;
    }

    /**
     * Apply additional location filtering
     */
    private function applyAdditionalLocationFilter($request)
    {
        $filterData = [];
        
        // Get filter data from the destination if available
        if ($request->has('placeLat') && $request->has('placeLng')) {
            $filterData = [
                'placeLat' => $request->get('placeLat'),
                'placeLng' => $request->get('placeLng'),
                'city' => $request->get('city', ''),
                'country' => $request->get('country', ''),
                'region' => $request->get('region', ''),
            ];
        }

        $placeLat = $filterData['placeLat'] ?? null;
        $placeLng = $filterData['placeLng'] ?? null;
        $city = $filterData['city'] ?? null;
        $country = $filterData['country'] ?? null;
        $region = $filterData['region'] ?? null;
        $radius = $request->get('radius');

        if (!empty($placeLat) && !empty($placeLng)) {
            return Guiding::locationFilter($city, $country, $region, $radius, $placeLat, $placeLng);
        }

        return ['ids' => [], 'message' => ''];
    }

    public function otherGuidings(){
        return $this->getOtherGuidings();
    }

    public function otherGuidingsBasedByLocation($latitude, $longitude){
        // This method signature is different from trait, so we keep this wrapper
        $allGuidings = collect(); // Empty collection since we don't have existing guidings context
        return $this->getOtherGuidingsBasedByLocation($latitude, $longitude, $allGuidings);
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
