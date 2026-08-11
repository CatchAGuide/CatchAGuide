<?php

namespace App\Console\Commands;

use App\Services\GuidingFilterService;
use App\Services\Offers\OfferFilterService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GenerateCatalogFilters extends Command
{
    protected $signature = 'catalog:generate-filters
                            {--only=all : Scope to rebuild: all|guidings|offers}
                            {--dump : Write JSON snapshots under storage/app/cache}';

    protected $description = 'Rebuild pre-computed filter maps for guidings and/or the offers catalog (tours, trips, camps)';

    /**
     * @var list<string>
     */
    private const SCOPES = ['all', 'guidings', 'offers'];

    public function handle(GuidingFilterService $guidingFilters, OfferFilterService $offerFilters): int
    {
        $scope = strtolower((string) $this->option('only'));
        if (! in_array($scope, self::SCOPES, true)) {
            $this->error('Invalid --only value. Use: all, guidings, or offers.');

            return self::FAILURE;
        }

        $dump = (bool) $this->option('dump');
        $ok = true;

        if ($scope === 'all' || $scope === 'guidings') {
            $ok = $this->rebuildGuidings($guidingFilters, $dump) && $ok;
        }

        if ($scope === 'all' || $scope === 'offers') {
            $ok = $this->rebuildOffers($offerFilters, $dump) && $ok;
        }

        if ($ok) {
            $this->info('Catalog filter mappings generated successfully.');
        }

        return $ok ? self::SUCCESS : self::FAILURE;
    }

    private function rebuildGuidings(GuidingFilterService $guidingFilters, bool $dump): bool
    {
        $this->info('Generating guiding filter mappings...');

        try {
            $map = $guidingFilters->refresh();
        } catch (Throwable $e) {
            $this->error('Guidings rebuild failed: '.$e->getMessage());

            return false;
        }

        if ($dump) {
            Storage::disk('local')->put('cache/guiding-filters.json', json_encode($map, JSON_PRETTY_PRINT));
        }

        $this->info('Processed '.($map['metadata']['total_guidings'] ?? 0).' guidings.');

        foreach (['targets', 'methods', 'water_types', 'duration_types', 'person_ranges', 'price_ranges'] as $facet) {
            $used = count(array_filter($map[$facet] ?? []));
            $this->line('  '.ucfirst(str_replace('_', ' ', $facet)).": {$used} option(s) with results");
        }

        return true;
    }

    private function rebuildOffers(OfferFilterService $offerFilters, bool $dump): bool
    {
        $this->info('Generating offer filter mappings (tours + trips + camps)...');

        try {
            $map = $offerFilters->refresh();
        } catch (Throwable $e) {
            $this->error('Offers rebuild failed: '.$e->getMessage());

            return false;
        }

        if ($dump) {
            Storage::disk('local')->put('cache/offer-filters.json', json_encode($map, JSON_PRETTY_PRINT));
        }

        $this->info(sprintf(
            'Processed %d tours, %d trips, %d camps.',
            $map['metadata']['total_tours'] ?? 0,
            $map['metadata']['total_trips'] ?? 0,
            $map['metadata']['total_camps'] ?? 0,
        ));

        foreach (['tours', 'trips', 'camps'] as $pillar) {
            $used = count($map[$pillar]['targets'] ?? []);
            $this->line('  '.ucfirst($pillar).": {$used} target fish option(s) with results");
        }

        return true;
    }
}
