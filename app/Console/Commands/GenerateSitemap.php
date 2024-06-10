<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Filesystem\Filesystem;

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

    public function generateXmlContent(){

        $sxml = '<?xml version="1.0" encoding="UTF-8"?>';
        $sxml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';

        foreach($this->pages as $pageInfo){
            $sxml .= '<url>'
                .'<loc>'.$pageInfo['loc'].'</loc>'
                .'<lastmod>'.$pageInfo['lastmod'].'</lastmod>'
                .'<changefreq>'.$pageInfo['changefreq'].'</changefreq>'
                .'<priority>'.$pageInfo['priority'].'</priority>'
            .'</url>';
        }

        $sxml .= '</urlset>';
        return $sxml;
    }


    public function addItem($loc, $lastmod = null, $changefreq = 'daily', $priority = 0.8){

        if(!$lastmod){
            $lastmod = Carbon::now()->subDays(1)->format('Y-m-d\TH:i:s+00:00');
        }

        if(empty($changefreq)){
            $changefreq = 'daily';
        }

        if(!($priority>0 && $priority<=1)){
            $priority = 0.8;
        }

        $this->pages[$loc] = [
            'loc' => $loc,
            'lastmod' => $lastmod,
            'changefreq' => $changefreq,
            'priority' => $priority
        ];
    }
}