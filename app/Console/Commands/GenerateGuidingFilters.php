<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Guiding;
use App\Models\Target;
use App\Models\Method;
use App\Models\Water;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class GenerateGuidingFilters extends Command
{
    protected $signature = 'guidings:generate-filters';
    protected $description = 'Generate pre-computed filter mappings for guidings';

    public function handle()
    {
        $this->info('Generating guiding filter mappings...');
        
        $filterMapping = [
            'targets' => [],
            'methods' => [],
            'water_types' => [],
            'duration_types' => [],
            'person_ranges' => [],
            'price_ranges' => [],
            'metadata' => [
                'generated_at' => now()->toISOString(),
                'total_guidings' => 0,
            ]
        ];

        // Get all active guidings with necessary data
        $guidings = Guiding::where('status', 1)
            ->select([
                'id', 'target_fish', 'fishing_methods', 'water_types', 
                'duration_type', 'max_guests', 'min_guests', 'price', 'prices'
            ])
            ->get();

        $filterMapping['metadata']['total_guidings'] = $guidings->count();
        $this->info("Processing {$guidings->count()} guidings...");

        // Initialize arrays for all possible filter values
        $this->initializeFilterArrays($filterMapping, $guidings);

        // Process each guiding
        foreach ($guidings as $guiding) {
            $guidingId = $guiding->id;
            
            // Process target fish
            $targetFish = json_decode($guiding->target_fish, true) ?? [];
            foreach ($targetFish as $fishId) {
                if (!in_array($guidingId, $filterMapping['targets'][$fishId] ?? [])) {
                    $filterMapping['targets'][$fishId][] = $guidingId;
                }
            }

            // Process methods
            $methods = json_decode($guiding->fishing_methods, true) ?? [];
            foreach ($methods as $methodId) {
                if (!in_array($guidingId, $filterMapping['methods'][$methodId] ?? [])) {
                    $filterMapping['methods'][$methodId][] = $guidingId;
                }
            }

            // Process water types
            $waterTypes = json_decode($guiding->water_types, true) ?? [];
            foreach ($waterTypes as $waterId) {
                if (!in_array($guidingId, $filterMapping['water_types'][$waterId] ?? [])) {
                    $filterMapping['water_types'][$waterId][] = $guidingId;
                }
            }

            // Process duration types
            if ($guiding->duration_type) {
                $durationType = $guiding->duration_type;
                if (!in_array($guidingId, $filterMapping['duration_types'][$durationType] ?? [])) {
                    $filterMapping['duration_types'][$durationType][] = $guidingId;
                }
            }

            // Process person ranges (1 to max_guests)
            if ($guiding->max_guests) {
                for ($i = 1; $i <= min(8, $guiding->max_guests); $i++) {
                    if (!in_array($guidingId, $filterMapping['person_ranges'][$i] ?? [])) {
                        $filterMapping['person_ranges'][$i][] = $guidingId;
                    }
                }
            }

            // Process price ranges
            $lowestPrice = $this->calculateLowestPrice($guiding);
            if ($lowestPrice > 0) {
                $priceRange = $this->getPriceRange($lowestPrice);
                if ($priceRange && !in_array($guidingId, $filterMapping['price_ranges'][$priceRange] ?? [])) {
                    $filterMapping['price_ranges'][$priceRange][] = $guidingId;
                }
            }
        }

        // Add counts for each filter
        $this->addFilterCounts($filterMapping);

        // Save to storage
        $jsonContent = json_encode($filterMapping, JSON_PRETTY_PRINT);
        Storage::disk('local')->put('cache/guiding-filters.json', $jsonContent);

        $this->info('Filter mappings generated and saved successfully!');
        $this->info('Targets: ' . count(array_filter($filterMapping['targets'])));
        $this->info('Methods: ' . count(array_filter($filterMapping['methods'])));
        $this->info('Water Types: ' . count(array_filter($filterMapping['water_types'])));
        $this->info('Duration Types: ' . count(array_filter($filterMapping['duration_types'])));
        $this->info('Person Ranges: ' . count(array_filter($filterMapping['person_ranges'])));
        $this->info('Price Ranges: ' . count(array_filter($filterMapping['price_ranges'])));

        return 0;
    }

    private function initializeFilterArrays(&$filterMapping, $guidings)
    {
        // Initialize all target fish IDs
        $targetIds = Target::pluck('id')->toArray();
        foreach ($targetIds as $id) {
            $filterMapping['targets'][$id] = [];
        }

        // Initialize all method IDs
        $methodIds = Method::pluck('id')->toArray();
        foreach ($methodIds as $id) {
            $filterMapping['methods'][$id] = [];
        }

        // Initialize all water type IDs
        $waterIds = Water::pluck('id')->toArray();
        foreach ($waterIds as $id) {
            $filterMapping['water_types'][$id] = [];
        }

        // Initialize duration types
        $durationTypes = ['half_day', 'full_day', 'multi_day'];
        foreach ($durationTypes as $type) {
            $filterMapping['duration_types'][$type] = [];
        }

        // Initialize person ranges (1-8)
        for ($i = 1; $i <= 8; $i++) {
            $filterMapping['person_ranges'][$i] = [];
        }

        $priceBounds = $this->getPriceBounds($guidings);
        $filterMapping['metadata']['minPrice'] = $priceBounds['min'];
        $filterMapping['metadata']['maxPrice'] = $priceBounds['max'];

        // Price filter buckets always start at €50 (UI default)
        $bucketFloor = 50;
        for ($i = $bucketFloor; $i <= $priceBounds['max']; $i += 50) {
            $rangeKey = $i . '-' . min($i + 50, $priceBounds['max']);
            $filterMapping['price_ranges'][$rangeKey] = [];
        }
    }

    /**
     * Per-guiding lowest price — mirrors the SQL CASE + JSON_TABLE + MIN logic:
     * valid JSON prices → min(per-person tier), else fallback to `price` column.
     */
    private function resolveLowestPrice($guiding): ?float
    {
        if ($guiding->prices) {
            $prices = json_decode($guiding->prices, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($prices) && count($prices) > 0) {
                $lowestPrice = null;

                foreach ($prices as $priceData) {
                    if (!isset($priceData['person'], $priceData['amount'])) {
                        continue;
                    }

                    $person = (int) $priceData['person'];
                    $amount = (float) $priceData['amount'];
                    $pricePerPerson = $person > 1 ? $amount / $person : $amount;

                    if ($lowestPrice === null || $pricePerPerson < $lowestPrice) {
                        $lowestPrice = $pricePerPerson;
                    }
                }

                if ($lowestPrice !== null) {
                    return $lowestPrice;
                }
            }
        }

        return $guiding->price !== null ? (float) $guiding->price : null;
    }

    private function calculateLowestPrice($guiding)
    {
        return $this->resolveLowestPrice($guiding) ?? 0;
    }

    private function getPriceRange($price)
    {
        $rangeSize = 50;
        $rangeStart = floor($price / $rangeSize) * $rangeSize;
        $rangeStart = max($rangeStart, 50); // Minimum range starts at 50
        $rangeEnd = $rangeStart + $rangeSize;
        
        return $rangeStart . '-' . $rangeEnd;
    }

    /**
     * Catalog-wide bounds: MIN/MAX of each guiding's resolveLowestPrice(),
     * rounded to €50 steps (same rounding as the former JSON_TABLE query).
     *
     * @return array{min: int, max: int}
     */
    private function getPriceBounds($guidings): array
    {
        $minPrice = null;
        $maxPrice = 0;

        foreach ($guidings as $guiding) {
            $lowestPrice = $this->resolveLowestPrice($guiding);

            if ($lowestPrice === null || $lowestPrice <= 0) {
                continue;
            }

            if ($minPrice === null || $lowestPrice < $minPrice) {
                $minPrice = $lowestPrice;
            }

            if ($lowestPrice > $maxPrice) {
                $maxPrice = $lowestPrice;
            }
        }

        $minPrice = $minPrice ?? 50;
        $maxPrice = $maxPrice ?: 5000;

        return [
            'min' => (int) max(50, floor($minPrice / 50) * 50),
            'max' => (int) ceil($maxPrice / 50) * 50,
        ];
    }

    private function addFilterCounts(&$filterMapping)
    {
        // Add counts to metadata for quick access
        $filterMapping['metadata']['counts'] = [
            'targets' => [],
            'methods' => [],
            'water_types' => [],
            'duration_types' => [],
            'person_ranges' => [],
            'price_ranges' => []
        ];

        foreach ($filterMapping['targets'] as $id => $guidingIds) {
            $filterMapping['metadata']['counts']['targets'][$id] = count($guidingIds);
        }

        foreach ($filterMapping['methods'] as $id => $guidingIds) {
            $filterMapping['metadata']['counts']['methods'][$id] = count($guidingIds);
        }

        foreach ($filterMapping['water_types'] as $id => $guidingIds) {
            $filterMapping['metadata']['counts']['water_types'][$id] = count($guidingIds);
        }

        foreach ($filterMapping['duration_types'] as $type => $guidingIds) {
            $filterMapping['metadata']['counts']['duration_types'][$type] = count($guidingIds);
        }

        foreach ($filterMapping['person_ranges'] as $range => $guidingIds) {
            $filterMapping['metadata']['counts']['person_ranges'][$range] = count($guidingIds);
        }

        foreach ($filterMapping['price_ranges'] as $range => $guidingIds) {
            $filterMapping['metadata']['counts']['price_ranges'][$range] = count($guidingIds);
        }

        // minPrice / maxPrice are set in initializeFilterArrays via getPriceBounds()
        $minPrice = $filterMapping['metadata']['minPrice'] ?? 50;
        $maxPrice = $filterMapping['metadata']['maxPrice'] ?? 1000;
        
        // Add some debug info
        $this->info('Filter counts summary:');
        $this->info('- Targets: ' . count(array_filter($filterMapping['metadata']['counts']['targets'])));
        $this->info('- Methods: ' . count(array_filter($filterMapping['metadata']['counts']['methods'])));
        $this->info('- Water Types: ' . count(array_filter($filterMapping['metadata']['counts']['water_types'])));
        $this->info('- Duration Types: ' . count(array_filter($filterMapping['metadata']['counts']['duration_types'])));
        $this->info('- Person Ranges: ' . count(array_filter($filterMapping['metadata']['counts']['person_ranges'])));
        $this->info('- Price Ranges: ' . count(array_filter($filterMapping['metadata']['counts']['price_ranges'])));
        
        // Cache price ranges in a separate cache key for quick access
        Cache::put('guiding_price_ranges', [
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'ranges' => array_keys($filterMapping['price_ranges']),
        ], 7200); // 2 hours cache
    }
} 