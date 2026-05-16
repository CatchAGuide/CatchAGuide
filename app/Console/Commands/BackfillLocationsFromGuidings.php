<?php

namespace App\Console\Commands;

use App\Models\Guiding;
use App\Models\Location;
use Illuminate\Console\Command;

class BackfillLocationsFromGuidings extends Command
{
    protected $signature = 'locations:backfill-from-guidings';

    protected $description = 'Seed the locations table from distinct English city/country/region on published guidings (no Google API).';

    public function handle(): int
    {
        $rows = Guiding::query()
            ->where('status', 1)
            ->whereNotNull('country')
            ->select('city', 'country', 'region')
            ->distinct()
            ->get();

        $created = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            if (!$row->country) {
                $skipped++;
                continue;
            }

            $exists = Location::query()
                ->where('city', $row->city)
                ->where('country', $row->country)
                ->where('region', $row->region)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            Location::create([
                'city' => $row->city,
                'country' => $row->country,
                'region' => $row->region,
                'translation' => ['city' => [], 'country' => [], 'region' => []],
            ]);
            $created++;
        }

        $this->info("Backfill complete: {$created} created, {$skipped} skipped.");

        return self::SUCCESS;
    }
}
