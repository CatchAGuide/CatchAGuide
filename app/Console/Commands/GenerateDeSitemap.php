<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Filesystem\Filesystem;
use App\Console\Commands\GenerateSitemap;

use App\Models\Guiding;
use App\Models\Thread;

class GenerateDeSitemap extends GenerateSitemap
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:desitemap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate sitemap.xml';

    protected $en_url = 'https://catchaguide.com';
    protected $de_url = 'https://catchaguide.de';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Generating catchaguideDE.xml...');

        $this->addPages();

        $sitemapFs = '/catchaguideDE.xml';

        //  delete
        Storage::disk('public')->delete(
            $sitemapFs
        );

        // put
        if(!Storage::disk('public')->put($sitemapFs, $this->generateXmlContent())) {

            $this->info('Unable to create catchaguideDE.xml');
            echo 'Unable to create catchaguideDE.xml';
            return;
        }

        $this->info('catchaguideDE.xml has been updated with total '.count($this->pages).' links.');
    }

    protected function addPages(){

        // Home page
        $this->addItem(
            $this->de_url,
            null,
            'daily',
            1
        );

        // law
        $this->addItem(
            $this->de_url.'/imprint',
            null,
            'daily',
            1
        );

        $this->addItem(
            $this->de_url.'/data-protection',
            null,
            'daily',
            1
        );

        $this->addItem(
            $this->de_url.'/agb',
            null,
            'daily',
            1
        );

        $this->addItem(
            $this->de_url.'/faq',
            null,
            'daily',
            1
        );

        //Additional
        $this->addItem(
            $this->de_url.'/contact',
            null,
            'daily',
            1
        );

        $this->addItem(
            $this->de_url.'/about-us',
            null,
            'daily',
            1
        );

        // Guidings
        $this->addItem(
            $this->de_url.'/guidings',
            null,
            'daily',
            1
        );

        $guidings = Guiding::orderBy('created_at','desc')->get();

        foreach($guidings as $guiding){

            $priority = 1;

            $this->addItem(
                $this->de_url.'/guidings/'.$guiding->slug,
                $guiding->publishedDateFormatted,
                'daily',
                $priority
            );

        }
        unset($guidings);

        // Guidings
        $this->addItem(
            $this->de_url.'/fishing-magazine',
            null,
            'weekly',
            1
        );


        $threads = Thread::orderBy('created_at','desc')->get();

        foreach($threads as $thread){

            $priority = 1;

            $this->addItem(
                $this->de_url.'/fishing-magazine/'.$thread->slug,
                $thread->publishedDateFormatted,
                'weekly',
                $priority
            );

        }
    
        unset($threads);

    }
}