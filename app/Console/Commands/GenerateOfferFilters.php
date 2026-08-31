<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * @deprecated Use `catalog:generate-filters --only=offers` (or without --only for all).
 */
class GenerateOfferFilters extends Command
{
    protected $signature = 'offers:generate-filters';

    protected $description = 'Deprecated alias of catalog:generate-filters --only=offers';

    public function handle(): int
    {
        $this->warn('offers:generate-filters is deprecated. Use: php artisan catalog:generate-filters --only=offers');

        return $this->call('catalog:generate-filters', [
            '--only' => 'offers',
            '--dump' => true,
        ]);
    }
}
