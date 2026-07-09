<?php

namespace App\Console\Commands;

use App\Services\Location\ListingLocationNormalizer;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

class NormalizeListingLocations extends Command
{
    protected $signature = 'listings:normalize-locations
                            {--type=* : Listing types: camp, trip, rental_boat, special_offer, accommodation (default: all)}
                            {--camp=* : Specific camp IDs}
                            {--trip=* : Specific trip IDs}
                            {--rental-boat=* : Specific rental boat IDs}
                            {--special-offer=* : Specific special offer IDs}
                            {--accommodation=* : Specific accommodation IDs}
                            {--dry-run : Report changes without writing}
                            {--limit=0 : Max rows per type (0 = all)}
                            {--only-country : Normalize country only (fast, no Nominatim)}
                            {--sleep=1 : Seconds between Nominatim geocode calls}
                            {--explain : Explain why each listing was or was not updated}';

    protected $description = 'Backfill listing coordinates and normalize city/country/region to English for camps, trips, rental boats, special offers, and accommodations';

    public function __construct(private readonly ListingLocationNormalizer $normalizer)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit = max(0, (int) $this->option('limit'));
        $onlyCountry = (bool) $this->option('only-country');
        $sleepSeconds = max(0, (int) $this->option('sleep'));
        $types = $this->getTypesToProcess();

        if ($types === []) {
            $this->error('No valid listing types selected.');

            return self::FAILURE;
        }

        if ($dryRun) {
            $this->info('[DRY RUN] No database writes will be performed.');
        }

        if (! $onlyCountry && $sleepSeconds > 0) {
            $this->comment("Nominatim: ~{$sleepSeconds}s pause per listing that needs geocoding (use --only-country to skip).");
        }

        $totalProcessed = 0;
        $totalUpdated = 0;

        foreach ($types as $type) {
            [$processed, $updated] = $this->processType(
                $type,
                $dryRun,
                $limit,
                $onlyCountry,
                $sleepSeconds
            );

            $totalProcessed += $processed;
            $totalUpdated += $updated;
        }

        $this->newLine();
        $this->info(($dryRun ? 'Would update' : 'Updated')." {$totalUpdated} of {$totalProcessed} processed listing(s).");

        return self::SUCCESS;
    }

    /**
     * @return array{0: int, 1: int} [processed, updated]
     */
    private function processType(
        string $type,
        bool $dryRun,
        int $limit,
        bool $onlyCountry,
        int $sleepSeconds
    ): array {
        $config = $this->normalizer->configFor($type);
        $ids = $this->getIdsForType($type);

        $query = $config['model']::query()->orderBy('id');

        if ($ids !== []) {
            $query->whereIn('id', $ids);
        } elseif ($limit > 0) {
            $query->limit($limit);
        }

        $count = (clone $query)->count();
        if ($count === 0) {
            $this->line("No {$type} listings to process.");

            return [0, 0];
        }

        $scope = $ids !== []
            ? count($ids).' id(s)'
            : ($limit > 0 ? "up to {$limit}" : 'all');

        $this->newLine();
        $this->info("Processing {$type} ({$scope}, {$count} row(s))...");

        $processed = 0;
        $updated = 0;

        $process = function (Model $listing) use (
            $type,
            $dryRun,
            $onlyCountry,
            $sleepSeconds,
            &$processed,
            &$updated
        ): void {
            $processed++;
            $analysis = $this->normalizer->analyze($listing, $type, $onlyCountry, $sleepSeconds);
            $changes = $analysis['changes'];

            if ($this->option('explain')) {
                $this->line(strtoupper($type)." #{$listing->id}:");
                foreach ($analysis['notes'] as $note) {
                    $this->line('  - '.$note);
                }
            }

            if ($changes === []) {
                if (! $this->option('explain')) {
                    $this->line(strtoupper($type)." #{$listing->id}: no changes (use --explain for details)");
                }

                return;
            }

            if (! $this->option('explain')) {
                $this->line(strtoupper($type)." #{$listing->id}: ".json_encode($changes));
            } elseif ($changes !== []) {
                $this->line('  => changes: '.json_encode($changes));
            }

            if (! $dryRun) {
                $listing->update($changes);
            }

            $updated++;
        };

        if ($ids !== [] || $limit > 0) {
            foreach ($query->get() as $listing) {
                $process($listing);
            }
        } else {
            $query->chunkById(200, function ($listings) use ($process) {
                foreach ($listings as $listing) {
                    $process($listing);
                }
            });
        }

        return [$processed, $updated];
    }

    /**
     * @return array<int, string>
     */
    private function getTypesToProcess(): array
    {
        $requested = $this->option('type');

        if ($requested === [] || $requested === null) {
            return array_keys(ListingLocationNormalizer::LISTING_TYPES);
        }

        $types = [];
        foreach ($requested as $value) {
            foreach (explode(',', (string) $value) as $type) {
                $type = trim($type);
                if ($type !== '' && isset(ListingLocationNormalizer::LISTING_TYPES[$type])) {
                    $types[] = $type;
                }
            }
        }

        return array_values(array_unique($types));
    }

    /**
     * @return array<int, int>
     */
    private function getIdsForType(string $type): array
    {
        $optionMap = [
            ListingLocationNormalizer::TYPE_CAMP => 'camp',
            ListingLocationNormalizer::TYPE_TRIP => 'trip',
            ListingLocationNormalizer::TYPE_RENTAL_BOAT => 'rental-boat',
            ListingLocationNormalizer::TYPE_SPECIAL_OFFER => 'special-offer',
            ListingLocationNormalizer::TYPE_ACCOMMODATION => 'accommodation',
        ];

        $option = $optionMap[$type] ?? null;
        if ($option === null) {
            return [];
        }

        $raw = $this->option($option);
        if ($raw === [] || $raw === null) {
            return [];
        }

        $ids = [];
        foreach ($raw as $value) {
            foreach (explode(',', (string) $value) as $id) {
                $id = (int) trim($id);
                if ($id > 0) {
                    $ids[] = $id;
                }
            }
        }

        return array_values(array_unique($ids));
    }
}
