<?php

namespace App\Traits;

use App\Models\Guiding;
use App\Models\Target;
use App\Models\Method;
use App\Models\Water;
use App\Models\Inclussion;
use App\Services\GuidingFilterService;
use App\Services\ImageOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

trait GuidingFilterOptimization
{
    protected $filterService;
    protected $imageOptimizationService;

    /**
     * Initialize the optimization services
     */
    protected function initializeOptimizationServices()
    {
        if (!$this->imageOptimizationService) {
            $this->imageOptimizationService = new ImageOptimizationService();
        }
    }

    /**
     * Get or create filter service instance
     */
    private function getFilterService()
    {
        if (!$this->filterService) {
            $this->filterService = new GuidingFilterService();
        }
        return $this->filterService;
    }

    /**
     * Check if request has active checkbox filters
     */
    protected function hasActiveCheckboxFilters($request)
    {
        // Check target_fish - ignore if null or empty array
        $targetFish = $request->get('target_fish');
        if ($targetFish && is_array($targetFish) && !empty(array_filter($targetFish))) {
            return true;
        }
        
        // Check methods
        $methods = $request->get('methods');
        if ($methods && is_array($methods) && !empty(array_filter($methods))) {
            return true;
        }
        
        // Check water types
        $water = $request->get('water');
        if ($water && is_array($water) && !empty(array_filter($water))) {
            return true;
        }
        
        // Check duration types
        $durationTypes = $request->get('duration_types');
        if ($durationTypes && is_array($durationTypes) && !empty(array_filter($durationTypes))) {
            return true;
        }
        
        // Check number of persons
        $numPersons = $request->get('num_persons');
        if ($numPersons && is_array($numPersons) && !empty(array_filter($numPersons))) {
            return true;
        }
        
        // Check price filters
        if ($this->hasPriceFilter($request)) {
            return true;
        }
        
        return false;
    }

    /**
     * Check if request has location-based filters
     */
    protected function hasLocationFilter($request)
    {
        // Check for coordinate-based location search (from map/autocomplete)
        $hasCoordinateSearch = !empty($request->get('placeLat')) && 
                              !empty($request->get('placeLng')) && 
                              !empty($request->get('place'));
        
        // Check for text-based location search (from header search)
        $hasTextSearch = !empty($request->get('city')) || 
                        !empty($request->get('country')) || 
                        !empty($request->get('region'));
        
        return $hasCoordinateSearch || $hasTextSearch;
    }

    /**
     * Check if request has price filters
     */
    protected function hasPriceFilter($request)
    {
        $priceMin = $request->get('price_min');
        $priceMax = $request->get('price_max');
        $defaultMinPrice = 50;
        $defaultMaxPrice = $this->getMaxPriceFromFilterData();
        
        // Check if price_min is set and different from default
        $hasMinFilter = $priceMin && (int)$priceMin !== $defaultMinPrice && (int)$priceMin > 0;
        
        // Check if price_max is set and different from default
        $hasMaxFilter = $priceMax && (int)$priceMax !== $defaultMaxPrice && (int)$priceMax < $defaultMaxPrice;
        
        return $hasMinFilter || $hasMaxFilter;
    }

    /**
     * Apply sorting to query
     */
    protected function applySorting($query, $request, $randomSeed)
    {
        $hasOnlyPageParam = count(array_diff(array_keys($request->all()), ['page'])) === 0;
        $isFirstPage = !$request->has('page') || $request->get('page') == 1;

        if ($hasOnlyPageParam && $isFirstPage) {
            $query->orderByRaw("RAND($randomSeed)");
        } else {
            if ($request->has('sortby') && !empty($request->get('sortby'))) {
                switch ($request->get('sortby')) {
                    case 'newest':
                        $query->orderBy('created_at', 'desc');
                        break;
                    case 'price-asc':
                        $query->orderBy('price', 'asc');
                        break;
                    case 'price-desc':
                        $query->orderBy('price', 'desc');
                        break;
                    case 'long-duration':
                        $query->orderBy('duration', 'desc');
                        break;
                    case 'short-duration':
                        $query->orderBy('duration', 'asc');
                        break;
                    default:
                        $query->orderBy('price', 'asc');
                }
            } else {
                $query->orderBy('price', 'asc');
            }
        }
    }

    /**
     * Get max price from filter data
     */
    protected function getMaxPriceFromFilterData()
    {
        // Use cached value if available to avoid loading filter service
        $cacheKey = 'guiding_price_ranges';
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey)['maxPrice'];
        }

        // Only load filter service if cache miss
        $metadata = $this->getFilterService()->getMetadata();
        
        if (isset($metadata['counts']['price_ranges'])) {
            $maxPrice = 0;
            foreach (array_keys($metadata['counts']['price_ranges']) as $range) {
                if (strpos($range, '-') !== false) {
                    list($min, $max) = explode('-', $range);
                    if ((int)$max > $maxPrice) {
                        $maxPrice = (int)$max;
                    }
                }
            }
            return $maxPrice ?: 1000;
        }

        return 1000; // Default fallback
    }

    /**
     * Clean up request parameters before processing
     */
    protected function cleanRequestParameters(Request $request)
    {
        // Clone the request for potential modifications
        $cleanedRequest = clone $request;
        $requestData = $cleanedRequest->all();
        $modified = false;
        
        // Step 1: Fix HTML-encoded parameter names (amp;paramName -> paramName)
        $cleanedData = [];
        foreach ($requestData as $key => $value) {
            // Remove 'amp;' prefix that appears from HTML entity encoding issues
            $cleanedKey = preg_replace('/^(amp;)+/', '', $key);
            
            if ($cleanedKey !== $key) {
                $modified = true;
            }
            
            // If the cleaned key already exists, prefer the non-prefixed version
            if (!isset($cleanedData[$cleanedKey]) || empty($cleanedData[$cleanedKey])) {
                $cleanedData[$cleanedKey] = $value;
            }
        }
        
        $requestData = $cleanedData;
        
        // Step 2: Clean price parameters if they match defaults
        if (isset($requestData['price_min']) || isset($requestData['price_max'])) {
            $defaultMinPrice = 50;
            $defaultMaxPrice = $this->getMaxPriceFromFilterData();
            
            if (isset($requestData['price_min'])) {
                $priceMin = (int)$requestData['price_min'];
                if ($priceMin === $defaultMinPrice || $priceMin <= 0) {
                    unset($requestData['price_min']);
                    $modified = true;
                }
            }
            
            if (isset($requestData['price_max'])) {
                $priceMax = (int)$requestData['price_max'];
                if ($priceMax === $defaultMaxPrice || $priceMax >= $defaultMaxPrice) {
                    unset($requestData['price_max']);
                    $modified = true;
                }
            }
        }
        
        // Only replace if we actually modified something
        if ($modified) {
            $cleanedRequest->replace($requestData);
            return $cleanedRequest;
        }
        
        // Return original request if no changes needed
        return $request;
    }

    /**
     * Pre-compute expensive view data to avoid N+1 queries and repeated function calls
     */
    protected function preComputeGuidingData($guidings, $targetsMap = null, $methodsMap = null, $watersMap = null, $inclussionsMap = null)
    {
        if (empty($guidings)) {
            return;
        }

        $this->initializeOptimizationServices();

        foreach ($guidings as $guiding) {
            $galleryImages = json_decode($guiding->gallery_images, true) ?? [];
            $optimizedImages = [];
            
            if (!empty($guiding->thumbnail_path)) {
                $optimizedImages[] = $this->imageOptimizationService->getOptimizedThumbnail($guiding->thumbnail_path);
            }
            
            foreach ($galleryImages as $imagePath) {
                if ($guiding->thumbnail_path && $imagePath === $guiding->thumbnail_path) {
                    continue;
                }
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
     * Build title and filter title
     */
    protected function buildTitleAndFilterTitle($request, $locale, $targetFishOptions, $methodOptions, $waterTypeOptions)
    {
        $title = '';
        $filter_title = '';

        if($request->has('page')){
            $title .= __('guidings.Page') . ' ' . $request->page . ' - ';
        }

        // Apply method filters
        if($request->has('methods') && !empty($request->get('methods'))){
            $requestMethods = array_filter($request->get('methods'));
            if(count($requestMethods)){
                $title .= __('guidings.Method') . ' (';
                $filter_title .= __('guidings.Method') . ' (';
                $title_row = '';
                foreach ($methodOptions as $row) {
                    if (in_array($row->id, $requestMethods)) {
                        $title_row .= (($locale == 'en')? $row->name_en : $row->name) . ', ';
                    }
                }
                $title .= substr($title_row, 0, -2);
                $title .= '), ';
                $filter_title .= substr($title_row, 0, -2);
                $filter_title .= '), ';
            }
        }

        // Apply water filters
        if($request->has('water') && !empty($request->get('water'))){
            $requestWater = array_filter($request->get('water'));
            if(count($requestWater)){
                $title .= __('guidings.Water') . ' (';
                $filter_title .= __('guidings.Water') . ' (';
                $title_row = '';
                foreach ($waterTypeOptions as $row) {
                    if (in_array($row->id, $requestWater)) {
                        $title_row .= (($locale == 'en')? $row->name_en : $row->name) . ', ';
                    }
                }
                $title .= substr($title_row, 0, -2);
                $title .= '), ';
                $filter_title .= substr($title_row, 0, -2);
                $filter_title .= '), ';
            }
        }

        // Apply target fish filters
        if($request->has('target_fish') && !empty($request->get('target_fish'))){
            $requestFish = array_filter($request->get('target_fish'));
            if(count($requestFish)){
                $title .= __('guidings.Target_Fish') . ' (';
                $filter_title .= __('guidings.Target_Fish') . ' (';
                $title_row = '';
                foreach ($targetFishOptions as $row) {
                    if (in_array($row->id, $requestFish)) {
                        $title_row .= (($locale == 'en')? $row->name_en : $row->name) . ', ';
                    }
                }
                $title .= substr($title_row, 0, -2);
                $title .= '), ';
                $filter_title .= substr($title_row, 0, -2);
                $filter_title .= '), ';
            }
        }

        $filter_title = substr($filter_title, 0, -2);

        return [
            'title' => $title,
            'filter_title' => $filter_title
        ];
    }

    /**
     * Get other guidings (fallback when no results)
     */
    protected function getOtherGuidings()
    {
        // Cache random guidings for 30 minutes to improve performance
        $cacheKey = 'other_guidings_random';
        
        return Cache::remember($cacheKey, 1800, function() {
            return Guiding::inRandomOrder('1234')->where('status', 1)->limit(10)->get();
        });
    }

    /**
     * Get other guidings based on location
     */
    protected function getOtherGuidingsBasedByLocation($latitude, $longitude, $allGuidings)
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

    /**
     * Get random seed for consistent ordering
     */
    protected function getRandomSeed()
    {
        $randomSeed = Session::get('random_seed');
        if (!$randomSeed) {
            $randomSeed = rand();
            Session::put('random_seed', $randomSeed);
        }
        return $randomSeed;
    }
} 