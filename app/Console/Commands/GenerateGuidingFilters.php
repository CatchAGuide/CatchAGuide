<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * @deprecated Use `catalog:generate-filters --only=guidings` (or without --only for all).
 */
class GenerateGuidingFilters extends Command
{
    protected $signature = 'guidings:generate-filters';

    protected $description = 'Deprecated alias of catalog:generate-filters --only=guidings';

    public function handle(): int
    {
        $this->warn('guidings:generate-filters is deprecated. Use: php artisan catalog:generate-filters --only=guidings');

        return $this->call('catalog:generate-filters', [
            '--only' => 'guidings',
            '--dump' => true,
        ]);
    }
}
