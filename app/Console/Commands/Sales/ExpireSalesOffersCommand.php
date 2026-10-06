<?php

namespace App\Console\Commands\Sales;

use App\Models\SalesDocument;
use App\Services\Sales\SalesDocumentCustomerActions;
use Illuminate\Console\Command;

/**
 * Nightly: sent or viewed offers whose "valid until" date has passed become expired (spec §7).
 */
class ExpireSalesOffersCommand extends Command
{
    protected $signature = 'sales:expire-offers';

    protected $description = 'Expire sent/viewed sales offers whose valid-until date has passed';

    public function handle(SalesDocumentCustomerActions $actions): int
    {
        $expired = 0;

        SalesDocument::query()
            ->awaitingCustomer()
            ->whereNotNull('valid_until')
            ->whereDate('valid_until', '<', today())
            ->chunkById(100, function ($documents) use ($actions, &$expired) {
                foreach ($documents as $document) {
                    $expired += (int) $actions->expire($document);
                }
            });

        $this->info("Expired {$expired} offer(s).");

        return self::SUCCESS;
    }
}
