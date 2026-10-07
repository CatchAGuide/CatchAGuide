<?php

namespace App\Console\Commands\Sales;

use App\Models\CustomCampOffer;
use App\Services\Sales\CustomCampOfferImporter;
use Illuminate\Console\Command;
use Throwable;

/**
 * Moves the former "Custom camp offers" into Admin › Sales › Offers. Safe to re-run: offers
 * that were already imported are skipped.
 */
class ImportCustomCampOffersCommand extends Command
{
    protected $signature = 'sales:import-custom-camp-offers {--dry-run : List what would be imported without writing}';

    protected $description = 'Import custom_camp_offers rows into sales documents (offer builder)';

    public function handle(CustomCampOfferImporter $importer): int
    {
        $imported = 0;
        $skipped = 0;
        $failed = 0;

        foreach (CustomCampOffer::query()->orderBy('id')->cursor() as $offer) {
            if ($importer->alreadyImported($offer)) {
                $skipped++;

                continue;
            }

            if ($this->option('dry-run')) {
                $this->line("Would import #{$offer->id} {$offer->recipient_email}");
                $imported++;

                continue;
            }

            try {
                $document = $importer->import($offer);
                $this->line("Imported #{$offer->id} → {$document?->number}");
                $imported++;
            } catch (Throwable $exception) {
                $failed++;
                $this->error("Failed #{$offer->id}: {$exception->getMessage()}");
            }
        }

        $this->info(($this->option('dry-run') ? 'Would import' : 'Imported')." {$imported}, skipped {$skipped}, failed {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
