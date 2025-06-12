<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class GuidingFilterService
{
    private $filterData = null;
    private $cacheKey = 'guiding_filter_data';
    private $cacheTimeout = 3600; // 1 hour

    public function __construct()
    {
        $this->loadFilterData();
    }

    /**
     * Load filter data from cache or file
     */
    private function loadFilterData()
    {
        // Try to get from cache first
        $this->filterData = Cache::get($this->cacheKey);
        
        if (!$this->filterData) {
            // Load from file if not in cache
            if (Storage::disk('local')->exists('cache/guiding-filters.json')) {
                $jsonContent = Storage::disk('local')->get('cache/guiding-filters.json');
                $this->filterData = json_decode($jsonContent, true);
                
                // Cache it for faster subsequent access
                Cache::put($this->cacheKey, $this->filterData, $this->cacheTimeout);
            } else {
                // Return empty structure if file doesn't exist
                $this->filterData = $this->getEmptyFilterStructure();
            }
        }
    }

    /**
     * Get filtered guiding IDs based on request parameters
     */
    public function getFilteredGuidingIds($request)
    {
        if (!$this->filterData) {
            return [];
        }

        $filteredIds = null; // Will store the intersection of all filters

        // Apply target fish filter
        if ($request->has('target_fish') && !empty($request->get('target_fish'))) {
            $targetFishIds = $this->getGuidingIdsForTargets($request->get('target_fish'));
            $filteredIds = $this->intersectIds($filteredIds, $targetFishIds);
        }

        // Apply methods filter
        if ($request->has('methods') && !empty($request->get('methods'))) {
            $methodIds = $this->getGuidingIdsForMethods($request->get('methods'));
            $filteredIds = $this->intersectIds($filteredIds, $methodIds);
        }

        // Apply water types filter
        if ($request->has('water') && !empty($request->get('water'))) {
            $waterIds = $this->getGuidingIdsForWaterTypes($request->get('water'));
            $filteredIds = $this->intersectIds($filteredIds, $waterIds);
        }

        // Apply duration types filter
        if ($request->has('duration_types') && !empty($request->get('duration_types'))) {
            $durationIds = $this->getGuidingIdsForDurationTypes($request->get('duration_types'));
            $filteredIds = $this->intersectIds($filteredIds, $durationIds);
        }

        // Apply person count filter
        if ($request->has('num_persons') && !empty($request->get('num_persons'))) {
            $personIds = $this->getGuidingIdsForPersonCount($request->get('num_persons'));
            $filteredIds = $this->intersectIds($filteredIds, $personIds);
        }

        // Apply price range filter
        if ($this->hasPriceFilter($request)) {
            $priceIds = $this->getGuidingIdsForPriceRange($request);
            $filteredIds = $this->intersectIds($filteredIds, $priceIds);
        }

        return $filteredIds ?? [];
    }

    /**
     * Get available filter options with counts based on current filters
     */
    public function getAvailableFilterOptions($request)
    {
        // Get IDs that match all current filters except the one we're calculating options for
        $baseIds = $this->getFilteredGuidingIds($request);

        return [
            'targets' => $this->getTargetOptions($request, $baseIds),
            'methods' => $this->getMethodOptions($request, $baseIds),
            'water_types' => $this->getWaterTypeOptions($request, $baseIds),
            'duration_types' => $this->getDurationTypeOptions($request, $baseIds),
            'person_ranges' => $this->getPersonRangeOptions($request, $baseIds),
            'price_ranges' => $this->getPriceRangeOptions($request, $baseIds),
        ];
    }

    /**
     * Get filter counts for display
     */
    public function getFilterCounts($filteredIds = null)
    {
        if (!$this->filterData) {
            return [
                'targets' => [],
                'methods' => [],
                'water_types' => [],
                'duration_types' => [
                    'half_day' => 0,
                    'full_day' => 0,
                    'multi_day' => 0
                ],
                'person_ranges' => [],
                'price_ranges' => []
            ];
        }

        if ($filteredIds === null) {
            // Return all counts if no specific filtering
            return $this->filterData['metadata']['counts'] ?? [];
        }

        // Calculate counts based on filtered IDs
        $counts = [
            'targets' => [],
            'methods' => [],
            'water_types' => [],
            'duration_types' => [
                'half_day' => 0,
                'full_day' => 0,
                'multi_day' => 0
            ],
            'person_ranges' => [],
            'price_ranges' => []
        ];

        if (isset($this->filterData['targets'])) {
            foreach ($this->filterData['targets'] as $targetId => $guidingIds) {
                $intersect = array_intersect($guidingIds, $filteredIds);
                if (!empty($intersect)) {
                    $counts['targets'][$targetId] = count($intersect);
                }
            }
        }

        if (isset($this->filterData['methods'])) {
            foreach ($this->filterData['methods'] as $methodId => $guidingIds) {
                $intersect = array_intersect($guidingIds, $filteredIds);
                if (!empty($intersect)) {
                    $counts['methods'][$methodId] = count($intersect);
                }
            }
        }

        if (isset($this->filterData['water_types'])) {
            foreach ($this->filterData['water_types'] as $waterId => $guidingIds) {
                $intersect = array_intersect($guidingIds, $filteredIds);
                if (!empty($intersect)) {
                    $counts['water_types'][$waterId] = count($intersect);
                }
            }
        }

        if (isset($this->filterData['duration_types'])) {
            foreach ($this->filterData['duration_types'] as $type => $guidingIds) {
                $intersect = array_intersect($guidingIds, $filteredIds);
                $counts['duration_types'][$type] = count($intersect);
            }
        }

        if (isset($this->filterData['person_ranges'])) {
            foreach ($this->filterData['person_ranges'] as $range => $guidingIds) {
                $intersect = array_intersect($guidingIds, $filteredIds);
                if (!empty($intersect)) {
                    $counts['person_ranges'][$range] = count($intersect);
                }
            }
        }

        if (isset($this->filterData['price_ranges'])) {
            foreach ($this->filterData['price_ranges'] as $range => $guidingIds) {
                $intersect = array_intersect($guidingIds, $filteredIds);
                if (!empty($intersect)) {
                    $counts['price_ranges'][$range] = count($intersect);
                }
            }
        }

        return $counts;
    }

    private function getGuidingIdsForTargets($targetIds)
    {
        $allIds = [];
        foreach ($targetIds as $targetId) {
            if (isset($this->filterData['targets'][$targetId])) {
                $allIds = array_merge($allIds, $this->filterData['targets'][$targetId]);
            }
        }
        return array_unique($allIds);
    }

    private function getGuidingIdsForMethods($methodIds)
    {
        $allIds = [];
        foreach ($methodIds as $methodId) {
            if (isset($this->filterData['methods'][$methodId])) {
                $allIds = array_merge($allIds, $this->filterData['methods'][$methodId]);
            }
        }
        return array_unique($allIds);
    }

    private function getGuidingIdsForWaterTypes($waterIds)
    {
        $allIds = [];
        foreach ($waterIds as $waterId) {
            if (isset($this->filterData['water_types'][$waterId])) {
                $allIds = array_merge($allIds, $this->filterData['water_types'][$waterId]);
            }
        }
        return array_unique($allIds);
    }

    private function getGuidingIdsForDurationTypes($durationTypes)
    {
        $allIds = [];
        foreach ($durationTypes as $durationType) {
            if (isset($this->filterData['duration_types'][$durationType])) {
                $allIds = array_merge($allIds, $this->filterData['duration_types'][$durationType]);
            }
        }
        return array_unique($allIds);
    }

    private function getGuidingIdsForPersonCount($personCount)
    {
        return $this->filterData['person_ranges'][$personCount] ?? [];
    }

    private function getGuidingIdsForPriceRange($request)
    {
        $priceMin = $request->get('price_min');
        $priceMax = $request->get('price_max');
        
        if (!$priceMin && !$priceMax) {
            return [];
        }

        $allIds = [];
        foreach ($this->filterData['price_ranges'] as $range => $guidingIds) {
            list($rangeMin, $rangeMax) = explode('-', $range);
            $rangeMin = (int)$rangeMin;
            $rangeMax = (int)$rangeMax;

            $matches = true;
            if ($priceMin && $rangeMax < $priceMin) {
                $matches = false;
            }
            if ($priceMax && $rangeMin > $priceMax) {
                $matches = false;
            }

            if ($matches) {
                $allIds = array_merge($allIds, $guidingIds);
            }
        }

        return array_unique($allIds);
    }

    private function hasPriceFilter($request)
    {
        $priceMin = $request->get('price_min');
        $priceMax = $request->get('price_max');
        
        $defaultMinPrice = 50;
        $defaultMaxPrice = $this->getMaxPriceFromData();
        
        return ($priceMin && $priceMin != $defaultMinPrice) || 
               ($priceMax && $priceMax != $defaultMaxPrice);
    }

    private function getMaxPriceFromData()
    {
        if (!$this->filterData || !isset($this->filterData['price_ranges'])) {
            return 1000;
        }

        $maxPrice = 0;
        foreach (array_keys($this->filterData['price_ranges']) as $range) {
            list($min, $max) = explode('-', $range);
            if ((int)$max > $maxPrice) {
                $maxPrice = (int)$max;
            }
        }

        return $maxPrice ?: 1000;
    }

    private function intersectIds($existingIds, $newIds)
    {
        if ($existingIds === null) {
            return $newIds;
        }
        
        return array_intersect($existingIds, $newIds);
    }

    private function getTargetOptions($request, $baseIds)
    {
        // Implementation for getting available target options with counts
        return [];
    }

    private function getMethodOptions($request, $baseIds)
    {
        // Implementation for getting available method options with counts
        return [];
    }

    private function getWaterTypeOptions($request, $baseIds)
    {
        // Implementation for getting available water type options with counts
        return [];
    }

    private function getDurationTypeOptions($request, $baseIds)
    {
        // Implementation for getting available duration type options with counts
        return [];
    }

    private function getPersonRangeOptions($request, $baseIds)
    {
        // Implementation for getting available person range options with counts
        return [];
    }

    private function getPriceRangeOptions($request, $baseIds)
    {
        // Implementation for getting available price range options with counts
        return [];
    }

    private function getEmptyFilterStructure()
    {
        return [
            'targets' => [],
            'methods' => [],
            'water_types' => [],
            'duration_types' => [],
            'person_ranges' => [],
            'price_ranges' => [],
            'metadata' => [
                'generated_at' => now()->toISOString(),
                'total_guidings' => 0,
                'counts' => []
            ]
        ];
    }

    /**
     * Clear the cache - useful when data is regenerated
     */
    public function clearCache()
    {
        Cache::forget($this->cacheKey);
        $this->filterData = null;
        $this->loadFilterData();
    }

    /**
     * Get metadata about the filter data
     */
    public function getMetadata()
    {
        return $this->filterData['metadata'] ?? [];
    }
} 