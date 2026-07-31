<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Legacy alias — prefer `php artisan generate:sitemap --lang=de`.
 */
class GenerateDeSitemap extends Command
{
    protected $signature = 'generate:desitemap';

    protected $description = 'Generate DE sitemaps (alias of generate:sitemap --lang=de)';

    public function handle(): int
    {
        $this->warn('generate:desitemap is deprecated; use generate:sitemap --lang=de');

        return $this->call('generate:sitemap', ['--lang' => 'de']);
    }
}
