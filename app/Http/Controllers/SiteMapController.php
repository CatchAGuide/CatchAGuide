<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Guiding;
use App\Models\Thread;
use App\Models\CategoryPage;

class SiteMapController extends Controller
{
    public function index()
    {
        $content = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $content .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        // Add main pages
        $content .= $this->addUrl(url('/'), '1.0', 'daily');
        $content .= $this->addUrl(route('guidings.index'), '0.9', 'daily');
        $content .= $this->addUrl(route('vacations.index'), '0.8', 'weekly');
        
        // Add guidings (only active ones)
        $guidings = Guiding::where('status', 1)->get();
        foreach ($guidings as $guiding) {
            $content .= $this->addUrl(
                route('guidings.show', [$guiding->id, $guiding->slug]),
                '0.7',
                'weekly',
                $guiding->updated_at
            );
        }
        
        // Add blog posts
        $threads = Thread::where('status', 1)->get();
        foreach ($threads as $thread) {
            $content .= $this->addUrl(
                route('blog.thread.show', $thread->slug),
                '0.6',
                'monthly',
                $thread->updated_at
            );
        }
        
        $content .= '</urlset>';
        
        return response($content, 200)
            ->header('Content-Type', 'text/xml');
    }
    
    private function addUrl($url, $priority = '0.5', $changefreq = 'weekly', $lastmod = null)
    {
        $lastmod = $lastmod ? $lastmod->toISOString() : now()->toISOString();
        
        return "  <url>\n" .
               "    <loc>" . $url . "</loc>\n" .
               "    <lastmod>" . $lastmod . "</lastmod>\n" .
               "    <changefreq>" . $changefreq . "</changefreq>\n" .
               "    <priority>" . $priority . "</priority>\n" .
               "  </url>\n";
    }
}
