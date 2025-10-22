<?php

namespace App\Console\Commands;

use App\Models\GuideThread;
use App\Models\Guiding;
use App\Models\Thread;
use App\Models\CategoryPage;
use App\Models\Country;
use App\Models\Region;
use App\Models\City;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Vacation;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:sitemap {--lang=all : Generate sitemap for specific language (en/de) or all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate sitemap.xml for the website';

    protected $pages = [];
    protected $languages = ['en', 'de'];
    protected $urls = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Starting sitemap generation...');
        
        $targetLang = $this->option('lang');
        $languages = $targetLang === 'all' ? $this->languages : [$targetLang];
        
        // Get URLs from environment
        $this->urls = [
            'en' => env('EN_APP_URL'),
            'de' => env('DE_APP_URL')
        ];

        foreach ($languages as $lang) {
            if (!isset($this->urls[$lang])) {
                $this->error("URL not configured for language: {$lang}");
                continue;
            }
            
            $this->info("Generating sitemaps for {$lang}...");
            $this->generateLanguageSitemaps($lang);
        }

        $this->info('Sitemap generation completed successfully!');
    }

    protected function generateLanguageSitemaps($lang)
    {
        $url = $this->urls[$lang];
        
        // Generate individual sitemaps
        $sitemap_listing = $this->generateListingSitemap($url, $lang);
        $sitemap_magazine = $this->generateMagazineSitemap($url, $lang);
        $sitemap_vacation = $this->generateVacationSitemap($url, $lang);
        $sitemap_category = $this->generateCategorySitemap($url, $lang);
        $sitemap_destination = $this->generateDestinationSitemap($url, $lang);
        $sitemap_main = $this->generateMainSitemap($url, $lang);

        // Create sitemap index
        $sitemapUrls = [
            $url . '/sitemaps' . $sitemap_listing,
            $url . '/sitemaps' . $sitemap_magazine,
            $url . '/sitemaps' . $sitemap_vacation,
            $url . '/sitemaps' . $sitemap_category,
            $url . '/sitemaps' . $sitemap_destination,
            $url . '/sitemaps' . $sitemap_main
        ];
        
        $this->generateSitemapIndex($url, $lang, $sitemapUrls);
    }

    protected function generateCategorySitemap($url, $lang)
    {
        $categoryPages = CategoryPage::whereNotNull('slug')
            ->where('slug', '!=', '')
            ->get();
        
        $xml = $this->generateSitemapHeader();
        
        foreach ($categoryPages as $categoryPage) {
            // Generate URL based on category page type and slug
            $sUrl = $url . '/category-page/' . strtolower($categoryPage->type) . '/' . $categoryPage->slug;
            $xml .= $this->generateUrlEntry($sUrl, 'monthly', 0.6);
        }
        
        $xml .= '</urlset>' . "\n";

        $filePath = '/sitemap_categories_' . $lang . '.xml';
        Storage::disk('sitemaps')->put($filePath, $xml);

        $this->info("✓ Generated category sitemap for {$lang} with " . count($categoryPages) . " entries");
        
        return $filePath;
    }

    protected function generateDestinationSitemap($url, $lang)
    {
        $xml = $this->generateSitemapHeader();
        $count = 0;
        
        // Get all countries
        $countries = Country::whereNotNull('slug')->where('slug', '!=', '')->get();
        foreach ($countries as $country) {
            $xml .= $this->generateUrlEntry($url . '/destination/' . $country->slug, 'monthly', 0.7);
            $count++;
        }
        
        // Get all regions with their country
        $regions = Region::with('country')->whereNotNull('slug')->where('slug', '!=', '')->get();
        foreach ($regions as $region) {
            if ($region->country) {
                $xml .= $this->generateUrlEntry($url . '/destination/' . $region->country->slug . '/' . $region->slug, 'monthly', 0.7);
                $count++;
            }
        }
        
        // Get all cities with their country and region
        $cities = City::with(['country', 'region'])->whereNotNull('slug')->where('slug', '!=', '')->get();
        foreach ($cities as $city) {
            if ($city->country && $city->region) {
                $xml .= $this->generateUrlEntry($url . '/destination/' . $city->country->slug . '/' . $city->region->slug . '/' . $city->slug, 'monthly', 0.7);
                $count++;
            }
        }
        
        $xml .= '</urlset>' . "\n";

        $filePath = '/sitemap_destinations_' . $lang . '.xml';
        Storage::disk('sitemaps')->put($filePath, $xml);

        $this->info("✓ Generated destination sitemap for {$lang} with {$count} entries");
        
        return $filePath;
    }

    protected function buildDestinationUrl($baseUrl, $destination)
    {
        // Build URL based on destination type and hierarchy
        $url = $baseUrl . '/destination';
        
        try {
            if ($destination->type === 'country') {
                return $url . '/' . $destination->slug;
            } elseif ($destination->type === 'region') {
                // Use the accessor method to get country slug
                $countrySlug = $destination->country_slug;
                if ($countrySlug && $countrySlug !== 'N/A') {
                    return $url . '/' . $countrySlug . '/' . $destination->slug;
                }
            } elseif ($destination->type === 'city') {
                // Use the accessor methods to get parent slugs
                $countrySlug = $destination->country_slug;
                $regionSlug = $destination->region_slug;
                if ($countrySlug && $countrySlug !== 'N/A' && $regionSlug && $regionSlug !== 'N/A') {
                    return $url . '/' . $countrySlug . '/' . $regionSlug . '/' . $destination->slug;
                }
            }
        } catch (\Exception $e) {
            // Log error but continue with fallback
            \Log::warning("Error building destination URL for {$destination->id}: " . $e->getMessage());
        }
        
        // Fallback for destinations that don't follow the hierarchy
        return $url . '/' . $destination->slug;
    }

    protected function generateSitemapIndex($url, $lang, $sitemapUrls)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($sitemapUrls as $sitemapUrl) {
            $xml .= "\t" . '<sitemap>' . "\n";
            $xml .= "\t\t" . '<loc>' . $sitemapUrl . '</loc>' . "\n";
            $xml .= "\t\t" . '<lastmod>' . Carbon::now()->toISOString() . '</lastmod>' . "\n";
            $xml .= "\t" . '</sitemap>' . "\n";
        }
        
        $xml .= '</sitemapindex>';

        $filePath = '/sitemap_index_' . $lang . '.xml';
        Storage::disk('sitemaps')->put($filePath, $xml);

        $this->info("✓ Generated sitemap index for {$lang}");
        
        return $filePath;
    }

    protected function generateListingSitemap($url, $lang)
    {
        $url .= '/guidings';
        
        // Get guidings with language-specific filtering if needed
        $guidings = $this->getGuidingsForLanguage($lang);
        
        $xml = $this->generateSitemapHeader();
        
        foreach ($guidings as $guiding) {
            $sUrl = $url . '/' . $guiding->id . '/' . $guiding->slug;
            $xml .= $this->generateUrlEntry($sUrl, 'monthly', 0.7);
        }
        
        $xml .= '</urlset>' . "\n";

        $filePath = '/sitemap_listing_' . $lang . '.xml';
        Storage::disk('sitemaps')->put($filePath, $xml);

        $this->info("✓ Generated listing sitemap for {$lang} with " . count($guidings) . " entries");
        
        return $filePath;
    }

    protected function generateMagazineSitemap($url, $lang)
    {
        $url .= '/fishing-magazine';
        
        $threads = Thread::where('language', $lang)->get();
        
        $xml = $this->generateSitemapHeader();
        
        foreach ($threads as $thread) {
            $sUrl = $url . '/' . $thread->slug;
            $xml .= $this->generateUrlEntry($sUrl, 'monthly', 0.6);
        }
        
        $xml .= '</urlset>' . "\n";

        $filePath = '/sitemap_fishing_magazine_' . $lang . '.xml';
        Storage::disk('sitemaps')->put($filePath, $xml);

        $this->info("✓ Generated magazine sitemap for {$lang} with " . count($threads) . " entries");
        
        return $filePath;
    }

    protected function generateMainSitemap($url, $lang)
    {
        $uris = [
            '/' => ['priority' => 1.0, 'changefreq' => 'weekly'],
            '/contact' => ['priority' => 0.5, 'changefreq' => 'monthly'],
            '/guidings' => ['priority' => 0.9, 'changefreq' => 'weekly'],
            '/login' => ['priority' => 0.3, 'changefreq' => 'monthly'],
            '/imprint' => ['priority' => 0.3, 'changefreq' => 'yearly'],
            '/data-protection' => ['priority' => 0.3, 'changefreq' => 'yearly'],
            '/agb' => ['priority' => 0.3, 'changefreq' => 'yearly'],
            '/faq' => ['priority' => 0.6, 'changefreq' => 'monthly'],
            '/about-us' => ['priority' => 0.7, 'changefreq' => 'monthly'],
            '/vacations' => ['priority' => 0.7, 'changefreq' => 'monthly']
        ];

        $xml = $this->generateSitemapHeader();
        
        foreach ($uris as $uri => $settings) {
            $sUrl = $url . $uri;
            $xml .= $this->generateUrlEntry($sUrl, $settings['changefreq'], $settings['priority']);
        }
        
        $xml .= '</urlset>';

        $filePath = '/sitemap_' . $lang . '.xml';
        Storage::disk('sitemaps')->put($filePath, $xml);

        $this->info("✓ Generated main sitemap for {$lang}");
        
        return $filePath;
    }

    protected function generateVacationSitemap($url, $lang)
    {
        $url .= '/vacations';
        $vacations = Vacation::where('status', 1)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->get();
        $xml = $this->generateSitemapHeader();
        foreach ($vacations as $vacation) {
            $sUrl = $url . '/' . $vacation->slug;
            $xml .= $this->generateUrlEntry($sUrl, 'monthly', 0.7);
        }
        $xml .= '</urlset>' . "\n";
        $filePath = '/sitemap_vacations_' . $lang . '.xml';
        \Storage::disk('sitemaps')->put($filePath, $xml);
        $this->info("✓ Generated vacation rentals sitemap for {$lang} with " . count($vacations) . " entries");
        return $filePath;
    }

    protected function getGuidingsForLanguage($lang)
    {
        // If guidings have language-specific content, filter by language
        // For now, we'll get all active guidings since they might be multilingual
        return Guiding::where('status', 1)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->get();
    }

    protected function generateSitemapHeader()
    {
        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
               '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ' .
               'xmlns:xhtml="http://www.w3.org/1999/xhtml" ' .
               'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" ' .
               'xmlns:video="http://www.google.com/schemas/sitemap-video/1.1" ' .
               'xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">' . "\n";
    }

    protected function generateUrlEntry($url, $changefreq, $priority)
    {
        return "\t" . '<url>' . "\n" .
               "\t\t" . '<loc>' . htmlspecialchars($url) . '</loc>' . "\n" .
               "\t\t" . '<changefreq>' . $changefreq . '</changefreq>' . "\n" .
               "\t\t" . '<priority>' . $priority . '</priority>' . "\n" .
               "\t\t" . '<lastmod>' . Carbon::now()->toISOString() . '</lastmod>' . "\n" .
               "\t" . '</url>' . "\n";
    }

    // Legacy methods for backward compatibility
    public function sitemap_index($url, $lang, $list)
    {
        return $this->generateSitemapIndex($url, $lang, $list);
    }

    public function listing($url, $lang)
    {
        return $this->generateListingSitemap($url, $lang);
    }

    public function magazine($url, $lang)
    {
        return $this->generateMagazineSitemap($url, $lang);
    }

    public function sitemap($url, $lang)
    {
        return $this->generateMainSitemap($url, $lang);
    }

    public function categories($url, $lang)
    {
        $xml = $this->generateSitemapHeader();
        $rows = GuideThread::get();

        foreach ($rows as $row) {
            $sUrl = $url . '/' . $row->slug;
            $xml .= $this->generateUrlEntry($sUrl, 'monthly', 0.5);
        }
        
        $xml .= '</urlset>' . "\n";

        $filePath = '/sitemap_category_' . $lang . '.xml';
        Storage::disk('sitemaps')->put($filePath, $xml);

        $this->info("✓ Generated category sitemap for {$lang}");

        return $filePath;
    }

    public function sitemap_old($url, $lang)
    {
        $sitemap = Sitemap::create()
            ->add(Url::create($url . '/')
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.5)
            )
            ->add(Url::create($url . '/contact')
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.5)
            )
            ->add(Url::create($url . '/guidings')
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.5)
            )
            ->add(Url::create($url . '/angelmagazin')
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.5)
            )
            ->add(Url::create($url . '/login')
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.5)
            )
            ->add(Url::create($url . '/imprint')
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.5)
            )
            ->add(Url::create($url . '/data-protection')
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.5)
            )
            ->add(Url::create($url . '/agb')
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.5)
            )
            ->add(Url::create($url . '/faq')
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.5)
            )
            ->add(Url::create($url . '/about-us')
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.5)
            );

        $filePath = public_path('/sitemaps/sitemap_' . $lang . '.xml');
        $sitemap->writeToFile($filePath);

        $this->info('Done Generating Sitemap ' . strtoupper($lang));

        return $filePath;
    }
}