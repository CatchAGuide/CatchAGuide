<?php

namespace App\Console\Commands;

use App\Services\Sitemap\SitemapGenerator;
use Illuminate\Console\Command;

class GenerateSitemap extends Command
{
    protected $signature = 'generate:sitemap {--lang=all : Generate sitemap for specific language (en/de) or all}';

    protected $description = 'Generate sitemap.xml for the website';

    protected array $languages = ['en', 'de'];

    public function __construct(
        private readonly SitemapGenerator $generator,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Starting sitemap generation...');

        $targetLang = $this->option('lang');
        $languages = $targetLang === 'all' ? $this->languages : [$targetLang];

        $urls = [
            'en' => config('cag.en_app_url'),
            'de' => config('cag.de_app_url'),
        ];

        foreach ($languages as $lang) {
            if (! isset($urls[$lang]) || blank($urls[$lang])) {
                $this->error("URL not configured for language: {$lang}");
                continue;
            }

            $this->info("Generating sitemaps for {$lang}...");
            $result = $this->generator->generateForLanguage($lang, $urls[$lang]);

            foreach ($result['counts'] as $key => $count) {
                $this->info("✓ {$key}: {$count}");
            }
        }

        $this->info('Sitemap generation completed successfully!');

        return self::SUCCESS;
    }
}
