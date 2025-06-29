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
        // Don't load data in constructor - load it lazily when needed
    }

    /**
     * Ensure filter data is loaded (lazy loading)
     */
    private function ensureDataLoaded()
    {
        if ($this->filterData === null) {
            $this->loadFilterData();
        }
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
        $this->ensureDataLoaded();
        
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
        $this->ensureDataLoaded();
        
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

        // Ensure filteredIds is an array
        if (!is_array($filteredIds)) {
            $filteredIds = [];
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

        // Process targets
        if (isset($this->filterData['targets'])) {
            foreach ($this->filterData['targets'] as $targetId => $guidingIds) {
                $intersect = array_intersect($guidingIds, $filteredIds);
                $count = count($intersect);
                if ($count > 0) {
                    $counts['targets'][$targetId] = $count;
                }
            }
        }

        // Process methods
        if (isset($this->filterData['methods'])) {
            foreach ($this->filterData['methods'] as $methodId => $guidingIds) {
                $intersect = array_intersect($guidingIds, $filteredIds);
                $count = count($intersect);
                if ($count > 0) {
                    $counts['methods'][$methodId] = $count;
                }
            }
        }

        // Process water types
        if (isset($this->filterData['water_types'])) {
            foreach ($this->filterData['water_types'] as $waterId => $guidingIds) {
                $intersect = array_intersect($guidingIds, $filteredIds);
                $count = count($intersect);
                if ($count > 0) {
                    $counts['water_types'][$waterId] = $count;
                }
            }
        }

        // Process duration types
        if (isset($this->filterData['duration_types'])) {
            foreach ($this->filterData['duration_types'] as $type => $guidingIds) {
                $intersect = array_intersect($guidingIds, $filteredIds);
                $count = count($intersect);
                $counts['duration_types'][$type] = $count;
            }
        }

        // Process person ranges
        if (isset($this->filterData['person_ranges'])) {
            foreach ($this->filterData['person_ranges'] as $range => $guidingIds) {
                $intersect = array_intersect($guidingIds, $filteredIds);
                $count = count($intersect);
                if ($count > 0) {
                    $counts['person_ranges'][$range] = $count;
                }
            }
        }

        // Process price ranges
        if (isset($this->filterData['price_ranges'])) {
            foreach ($this->filterData['price_ranges'] as $range => $guidingIds) {
                $intersect = array_intersect($guidingIds, $filteredIds);
                $count = count($intersect);
                if ($count > 0) {
                    $counts['price_ranges'][$range] = $count;
                }
            }
        }

        return $counts;
    }

    /**
     * Get interactive filter counts for better user experience
     * This method calculates what the counts would be if each filter was selected/deselected
     */
    public function getInteractiveFilterCounts($request, $currentResultIds = null)
    {
        $this->ensureDataLoaded();
        
        if (!$this->filterData || empty($currentResultIds)) {
            return $this->getFilterCounts($currentResultIds);
        }

        $interactiveCounts = [
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

        // Get currently active filters
        $activeTargets = $request->get('target_fish', []);
        $activeMethods = $request->get('methods', []);
        $activeWaters = $request->get('water', []);
        $activeDurations = $request->get('duration_types', []);
        $activePersons = $request->get('num_persons', []);

        // For each filter type, calculate what the counts would be with current filters
        $this->calculateInteractiveTargetCounts($interactiveCounts, $currentResultIds, $activeTargets, $activeMethods, $activeWaters, $activeDurations, $activePersons, $request);
        $this->calculateInteractiveMethodCounts($interactiveCounts, $currentResultIds, $activeTargets, $activeMethods, $activeWaters, $activeDurations, $activePersons, $request);
        $this->calculateInteractiveWaterCounts($interactiveCounts, $currentResultIds, $activeTargets, $activeMethods, $activeWaters, $activeDurations, $activePersons, $request);
        $this->calculateInteractiveDurationCounts($interactiveCounts, $currentResultIds, $activeTargets, $activeMethods, $activeWaters, $activeDurations, $activePersons, $request);
        $this->calculateInteractivePersonCounts($interactiveCounts, $currentResultIds, $activeTargets, $activeMethods, $activeWaters, $activeDurations, $activePersons, $request);
        $this->calculateInteractivePriceCounts($interactiveCounts, $currentResultIds, $activeTargets, $activeMethods, $activeWaters, $activeDurations, $activePersons, $request);

        return $interactiveCounts;
    }

    private function calculateInteractiveTargetCounts(&$counts, $currentResultIds, $activeTargets, $activeMethods, $activeWaters, $activeDurations, $activePersons, $request)
    {
        if (!isset($this->filterData['targets'])) return;

        foreach ($this->filterData['targets'] as $targetId => $guidingIds) {
            // Calculate what results would be if this target was selected (ignoring current target filters)
            $tempIds = $this->calculateFilteredIds($request, [$targetId], $activeMethods, $activeWaters, $activeDurations, $activePersons);
            $intersect = array_intersect($tempIds, $currentResultIds);
            
            if (count($intersect) > 0) {
                $counts['targets'][$targetId] = count($intersect);
            }
        }
    }

    private function calculateInteractiveMethodCounts(&$counts, $currentResultIds, $activeTargets, $activeMethods, $activeWaters, $activeDurations, $activePersons, $request)
    {
        if (!isset($this->filterData['methods'])) return;

        foreach ($this->filterData['methods'] as $methodId => $guidingIds) {
            $tempIds = $this->calculateFilteredIds($request, $activeTargets, [$methodId], $activeWaters, $activeDurations, $activePersons);
            $intersect = array_intersect($tempIds, $currentResultIds);
            
            if (count($intersect) > 0) {
                $counts['methods'][$methodId] = count($intersect);
            }
        }
    }

    private function calculateInteractiveWaterCounts(&$counts, $currentResultIds, $activeTargets, $activeMethods, $activeWaters, $activeDurations, $activePersons, $request)
    {
        if (!isset($this->filterData['water_types'])) return;

        foreach ($this->filterData['water_types'] as $waterId => $guidingIds) {
            $tempIds = $this->calculateFilteredIds($request, $activeTargets, $activeMethods, [$waterId], $activeDurations, $activePersons);
            $intersect = array_intersect($tempIds, $currentResultIds);
            
            if (count($intersect) > 0) {
                $counts['water_types'][$waterId] = count($intersect);
            }
        }
    }

    private function calculateInteractiveDurationCounts(&$counts, $currentResultIds, $activeTargets, $activeMethods, $activeWaters, $activeDurations, $activePersons, $request)
    {
        if (!isset($this->filterData['duration_types'])) return;

        foreach ($this->filterData['duration_types'] as $durationType => $guidingIds) {
            $tempIds = $this->calculateFilteredIds($request, $activeTargets, $activeMethods, $activeWaters, [$durationType], $activePersons);
            $intersect = array_intersect($tempIds, $currentResultIds);
            
            $counts['duration_types'][$durationType] = count($intersect);
        }
    }

    private function calculateInteractivePersonCounts(&$counts, $currentResultIds, $activeTargets, $activeMethods, $activeWaters, $activeDurations, $activePersons, $request)
    {
        if (!isset($this->filterData['person_ranges'])) return;

        foreach ($this->filterData['person_ranges'] as $personRange => $guidingIds) {
            $tempIds = $this->calculateFilteredIds($request, $activeTargets, $activeMethods, $activeWaters, $activeDurations, [$personRange]);
            $intersect = array_intersect($tempIds, $currentResultIds);
            
            if (count($intersect) > 0) {
                $counts['person_ranges'][$personRange] = count($intersect);
            }
        }
    }

    private function calculateInteractivePriceCounts(&$counts, $currentResultIds, $activeTargets, $activeMethods, $activeWaters, $activeDurations, $activePersons, $request)
    {
        if (!isset($this->filterData['price_ranges'])) return;

        foreach ($this->filterData['price_ranges'] as $priceRange => $guidingIds) {
            // For price ranges, we need to consider the current price filter settings
            $tempIds = $this->calculateFilteredIds($request, $activeTargets, $activeMethods, $activeWaters, $activeDurations, $activePersons, $priceRange);
            $intersect = array_intersect($tempIds, $currentResultIds);
            
            if (count($intersect) > 0) {
                $counts['price_ranges'][$priceRange] = count($intersect);
            }
        }
    }

    /**
     * Calculate filtered IDs for specific filter combinations
     */
    private function calculateFilteredIds($request, $targetIds = [], $methodIds = [], $waterIds = [], $durationTypes = [], $personRanges = [], $priceRange = null)
    {
        $filteredIds = null;

        // Apply target fish filter (AND logic for multiple selections)
        if (!empty($targetIds)) {
            $targetFilterIds = $this->getGuidingIdsForTargets($targetIds);
            $filteredIds = $this->intersectIds($filteredIds, $targetFilterIds);
        }

        // Apply methods filter (AND logic for multiple selections)
        if (!empty($methodIds)) {
            $methodFilterIds = $this->getGuidingIdsForMethods($methodIds);
            $filteredIds = $this->intersectIds($filteredIds, $methodFilterIds);
        }

        // Apply water types filter (AND logic for multiple selections)
        if (!empty($waterIds)) {
            $waterFilterIds = $this->getGuidingIdsForWaterTypes($waterIds);
            $filteredIds = $this->intersectIds($filteredIds, $waterFilterIds);
        }

        // Apply duration types filter (OR logic for multiple selections)
        if (!empty($durationTypes)) {
            $durationFilterIds = $this->getGuidingIdsForDurationTypes($durationTypes);
            $filteredIds = $this->intersectIds($filteredIds, $durationFilterIds);
        }

        // Apply person ranges filter (OR logic for multiple selections)
        if (!empty($personRanges)) {
            $personFilterIds = [];
            foreach ($personRanges as $personRange) {
                if (isset($this->filterData['person_ranges'][$personRange])) {
                    $personFilterIds = array_merge($personFilterIds, $this->filterData['person_ranges'][$personRange]);
                }
            }
            $filteredIds = $this->intersectIds($filteredIds, array_unique($personFilterIds));
        }

        // Apply price range filter
        if ($priceRange && isset($this->filterData['price_ranges'][$priceRange])) {
            $filteredIds = $this->intersectIds($filteredIds, $this->filterData['price_ranges'][$priceRange]);
        } elseif ($this->hasPriceFilter($request)) {
            $priceIds = $this->getGuidingIdsForPriceRange($request);
            $filteredIds = $this->intersectIds($filteredIds, $priceIds);
        }

        return $filteredIds ?? [];
    }

    private function getGuidingIdsForTargets($targetIds)
    {
        if (empty($targetIds)) {
            return [];
        }

        // For multiple target fish selections, use AND logic (intersection)
        // Show only guidings that have ALL selected target fish
        $resultIds = null;
        
        foreach ($targetIds as $targetId) {
            if (isset($this->filterData['targets'][$targetId])) {
                $currentIds = $this->filterData['targets'][$targetId];
                
                if ($resultIds === null) {
                    // First iteration - set the initial result
                    $resultIds = $currentIds;
                } else {
                    // Subsequent iterations - intersect with previous results
                    $resultIds = array_intersect($resultIds, $currentIds);
                }
            } else {
                // If any target fish has no guidings, result should be empty
                return [];
            }
        }
        
        return $resultIds ?? [];
    }

    private function getGuidingIdsForMethods($methodIds)
    {
        if (empty($methodIds)) {
            return [];
        }

        // For multiple method selections, use AND logic (intersection)
        // Show only guidings that have ALL selected methods
        $resultIds = null;
        
        foreach ($methodIds as $methodId) {
            if (isset($this->filterData['methods'][$methodId])) {
                $currentIds = $this->filterData['methods'][$methodId];
                
                if ($resultIds === null) {
                    $resultIds = $currentIds;
                } else {
                    $resultIds = array_intersect($resultIds, $currentIds);
                }
            } else {
                return [];
            }
        }
        
        return $resultIds ?? [];
    }

    private function getGuidingIdsForWaterTypes($waterIds)
    {
        if (empty($waterIds)) {
            return [];
        }

        // For multiple water type selections, use AND logic (intersection)
        // Show only guidings that have ALL selected water types
        $resultIds = null;
        
        foreach ($waterIds as $waterId) {
            if (isset($this->filterData['water_types'][$waterId])) {
                $currentIds = $this->filterData['water_types'][$waterId];
                
                if ($resultIds === null) {
                    $resultIds = $currentIds;
                } else {
                    $resultIds = array_intersect($resultIds, $currentIds);
                }
            } else {
                return [];
            }
        }
        
        return $resultIds ?? [];
    }

    private function getGuidingIdsForDurationTypes($durationTypes)
    {
        // For duration types, OR logic makes more sense (half_day OR full_day OR multi_day)
        // Keep the original logic here
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
        
        // Check if price_min is set and different from default
        $hasMinFilter = $priceMin && (int)$priceMin !== $defaultMinPrice && (int)$priceMin > 0;
        
        // Check if price_max is set and different from default
        $hasMaxFilter = $priceMax && (int)$priceMax !== $defaultMaxPrice && (int)$priceMax < $defaultMaxPrice;
        
        return $hasMinFilter || $hasMaxFilter;
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
        $this->ensureDataLoaded();
        return $this->filterData['metadata'] ?? [];
    }
} 