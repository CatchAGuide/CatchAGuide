<?php

namespace App\Console\Commands;

use App\Services\GuidingFilterService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateGuidingFilters extends Command
{
    protected $signature = 'guidings:generate-filters';
    protected $description = 'Rebuild the pre-computed filter mappings for guidings';

    public function handle(GuidingFilterService $filterService)
    {
        $this->info('Generating guiding filter mappings...');

        $filterMapping = $filterService->refresh();

        $this->info("Processed {$filterMapping['metadata']['total_guidings']} guidings.");

        // Written for inspection/diagnostics; the application reads the cached map.
        Storage::disk('local')->put('cache/guiding-filters.json', json_encode($filterMapping, JSON_PRETTY_PRINT));

        foreach (['targets', 'methods', 'water_types', 'duration_types', 'person_ranges', 'price_ranges'] as $facet) {
            $used = count(array_filter($filterMapping[$facet]));
            $this->info(ucfirst(str_replace('_', ' ', $facet)) . ": {$used} option(s) with results");
        }

        $this->info('Filter mappings generated successfully!');

        return 0;
    }
}
