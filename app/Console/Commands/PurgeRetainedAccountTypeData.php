<?php

namespace App\Console\Commands;

use App\Services\Guide\Area\GuideAccountService;
use Illuminate\Console\Command;

/**
 * Clears the previous account type's legal data 30 days after a guide switched between
 * individual and company in Account & security (spec 13.6).
 */
class PurgeRetainedAccountTypeData extends Command
{
    protected $signature = 'guide-area:purge-account-type-data';

    protected $description = 'Clear the old account type\'s legal data once the retention period after a type switch is over';

    public function handle(GuideAccountService $account): int
    {
        $count = $account->purgeRetainedTypeData();
        $this->info("Cleared retained account type data for {$count} guide(s).");

        return self::SUCCESS;
    }
}
