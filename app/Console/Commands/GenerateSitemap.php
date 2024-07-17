<?php

namespace App\Console\Commands;

use App\Models\GuideThread;
use App\Models\Guiding;
use App\Models\Thread;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Filesystem\Filesystem;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use Spatie\Sitemap\SitemapIndex;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:sitemap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate sitemap.xml';

    protected $pages = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle(){

        $this->info('Start...');
        $url = url('/');
        $english = ENV('EN_APP_URL');
        $german = ENV('DE_APP_URL');

        
        $sitemap_listing_en = $this->listing($english, 'en');
        $sitemap_listing_de = $this->listing($german, 'de');

        $sitemap_magazine_en = $this->magazine($english, 'en');
        $sitemap_magazine_de = $this->magazine($german, 'de');

        $sitemap_category_en = $this->categories($english, 'en');
        $sitemap_category_de = $this->categories($german, 'de');

        $sitemap_en = $this->sitemap($english, 'en');
        $sitemap_de = $this->sitemap($german, 'de');

        $this->info('Sitemap Indexing...');

        $sitemap = SitemapIndex::create()
        ->add($english . '/sitemaps' . $sitemap_listing_en)
        ->add($german . '/sitemaps' . $sitemap_listing_de)

        ->add($english . '/sitemaps' . $sitemap_magazine_en)
        ->add($german . '/sitemaps' . $sitemap_magazine_de)

        ->add($english . '/sitemaps' . $sitemap_category_en)
        ->add($german . '/sitemaps' . $sitemap_category_de)

        ->add($english . '/sitemaps/sitemap_en.xml')
        ->add($german . '/sitemaps/sitemap_de.xml')
        ->writeToFile(public_path('/sitemaps/sitemap_index.xml'));

        $this->info('Done SitemapIndex');
    }

    public function listing($url, $lang)
    {
        $url .= '/guidings';
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' ."\n". '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">' ."\n";
        $guidings = Guiding::where('status', 1)->get();

        foreach ($guidings as $row) {
            $sUrl = $url . '/' . $row->id . '/' . $row->slug;
            $xml .= "\t".'<url>' ."\n" .
                    "\t\t".'<loc>' . $sUrl . '</loc>' ."\n" .
                    "\t\t".'<xhtml:link rel="alternate" hreflang="' . $lang . '" href="' . $sUrl . '" />' ."\n" .
                    "\t\t".'<changefreq>monthly</changefreq>' ."\n" .
                    "\t\t".'<priority>0.5</priority>' ."\n" .
                    "\t".'</url>' ."\n" ;
        }
        $xml .= '</urlset>' ."\n";

        $file_path = '/sitemap_listing_' . $lang . '.xml';
        Storage::disk('sitemaps')->put($file_path, $xml);

        $this->info('Done Generating Sitemap Listing ' . strtoupper($lang));

        return $file_path;
    }

    public function magazine($url, $lang)
    {
        $url .= '/fishing-magazine';
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' ."\n". '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">' ."\n";
        $guidings = Thread::where('language', $lang)->get();

        foreach ($guidings as $row) {
            $sUrl = $url . '/' . $row->slug;
            $xml .= "\t".'<url>' ."\n" .
                    "\t\t".'<loc>' . $sUrl . '</loc>' ."\n" .
                    //"\t\t".'<xhtml:link rel="alternate" hreflang="' . $lang . '" href="' . $sUrl . '" />' ."\n" .
                    "\t\t".'<changefreq>monthly</changefreq>' ."\n" .
                    "\t\t".'<priority>0.5</priority>' ."\n" .
                    "\t".'</url>' ."\n" ;
        }
        $xml .= '</urlset>' ."\n";

        $file_path = '/sitemap_fishing_magazine_' . $lang . '.xml';
        Storage::disk('sitemaps')->put($file_path, $xml);

        $this->info('Done Generating Sitemap Listing ' . strtoupper($lang));

        return $file_path;
    }

    public function categories($url, $lang)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' ."\n". '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">' ."\n";
        $rows = GuideThread::get();
        //$sitemap = Sitemap::create();

        foreach ($rows as $row) {
            $sUrl = $url . '/' . $row->slug;
            $xml .= "\t".'<url>' ."\n" .
                    "\t\t".'<loc>' . $sUrl . '</loc>' ."\n" .
                    //"\t\t".'<xhtml:link rel="alternate" hreflang="' . $lang . '" href="' . $sUrl . '" />' ."\n" .
                    "\t\t".'<changefreq>monthly</changefreq>' ."\n" .
                    "\t\t".'<priority>0.5</priority>' ."\n" .
                    "\t".'</url>' ."\n" ;
        }
        $xml .= '</urlset>' ."\n";

        $file_path = '/sitemap_category_' . $lang . '.xml';
        Storage::disk('sitemaps')->put($file_path, $xml);

        $this->info('Done Generating Sitemap Category ' . strtoupper($lang));

        return $file_path;
    }

    public function sitemap($url, $lang)
    {
        
        $sitemap = Sitemap::create()
            ->add(Url::create($url . '/')
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.5)
                ->addAlternate($url . '/', $lang)
            )
            ->add(Url::create($url . '/contact')
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.5)
                ->addAlternate($url . '/contact', $lang)
            )
            ->add(Url::create($url . '/guidings')
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.5)
                ->addAlternate($url . '/guidings', $lang)
            )
            ->add(Url::create($url . '/angelmagazin')
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.5)
                ->addAlternate($url . '/angelmagazin', $lang)
            )
            ->add(Url::create($url . '/login')
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.5)
                ->addAlternate($url . '/login', $lang)
            )
            ->add(Url::create($url . '/imprint')
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.5)
                ->addAlternate($url . '/imprint', $lang)
            )
            ->add(Url::create($url . '/data-protection')
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.5)
                ->addAlternate($url . '/data-protection', $lang)
            )
            ->add(Url::create($url . '/agb')
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.5)
                ->addAlternate($url . '/agb', $lang)
            )
            ->add(Url::create($url . '/faq')
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.5)
                ->addAlternate($url . '/faq', $lang)
            )
            ->add(Url::create($url . '/about-us')
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.5)
                ->addAlternate($url . '/about-us', $lang)
            );

        $file_path = public_path('/sitemaps/sitemap_' . $lang . '.xml');
        $sitemap->writeTofile($file_path);

        $this->info('Done Generating Sitemap '.strtoupper($lang));

        return $file_path;
    }
}