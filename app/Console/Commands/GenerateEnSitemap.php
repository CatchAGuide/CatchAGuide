<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Legacy alias — prefer `php artisan generate:sitemap --lang=en`.
 */
class GenerateEnSitemap extends Command
{
    protected $signature = 'generate:ensitemap';

    protected $description = 'Generate EN sitemaps (alias of generate:sitemap --lang=en)';

    public function handle(): int
    {
        $this->warn('generate:ensitemap is deprecated; use generate:sitemap --lang=en');

        return $this->call('generate:sitemap', ['--lang' => 'en']);
    }
}
