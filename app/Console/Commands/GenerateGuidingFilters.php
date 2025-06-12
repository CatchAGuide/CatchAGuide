<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Guiding;
use App\Models\Target;
use App\Models\Method;
use App\Models\Water;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

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
        $this->initializeFilterArrays($filterMapping);

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

    private function initializeFilterArrays(&$filterMapping)
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

        // Initialize price ranges (we'll calculate max price dynamically)
        $maxPrice = $this->getMaxPrice();
        for ($i = 50; $i <= $maxPrice; $i += 50) {
            $rangeKey = $i . '-' . min($i + 50, $maxPrice);
            $filterMapping['price_ranges'][$rangeKey] = [];
        }
    }

    private function calculateLowestPrice($guiding)
    {
        if ($guiding->prices) {
            $prices = json_decode($guiding->prices, true);
            if (is_array($prices) && count($prices) > 0) {
                $lowestPrice = null;
                foreach ($prices as $priceData) {
                    if (isset($priceData['person']) && isset($priceData['amount'])) {
                        $pricePerPerson = $priceData['person'] > 1 
                            ? $priceData['amount'] / $priceData['person']
                            : $priceData['amount'];
                        
                        if ($lowestPrice === null || $pricePerPerson < $lowestPrice) {
                            $lowestPrice = $pricePerPerson;
                        }
                    }
                }
                return $lowestPrice ?? $guiding->price;
            }
        }
        
        return $guiding->price ?? 0;
    }

    private function getPriceRange($price)
    {
        $rangeSize = 50;
        $rangeStart = floor($price / $rangeSize) * $rangeSize;
        $rangeStart = max($rangeStart, 50); // Minimum range starts at 50
        $rangeEnd = $rangeStart + $rangeSize;
        
        return $rangeStart . '-' . $rangeEnd;
    }

    private function getMaxPrice()
    {
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

        return ceil(($maxPriceResult->max_price ?? 5000) / 50) * 50;
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
    }
} 