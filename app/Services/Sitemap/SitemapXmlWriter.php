<?php

namespace App\Services\Sitemap;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

final class SitemapXmlWriter
{
    /**
     * @param  Collection<int, SitemapEntry>|iterable<SitemapEntry>  $entries
     */
    public function writeUrlset(string $filePath, iterable $entries): int
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" '
            . 'xmlns:xhtml="http://www.w3.org/1999/xhtml" '
            . 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" '
            . 'xmlns:video="http://www.google.com/schemas/sitemap-video/1.1" '
            . 'xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">' . "\n";

        $count = 0;
        foreach ($entries as $entry) {
            $lastmod = $entry->lastmod ?? Carbon::now()->toISOString();
            $xml .= "\t" . '<url>' . "\n";
            $xml .= "\t\t" . '<loc>' . htmlspecialchars($entry->loc, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</loc>' . "\n";
            $xml .= "\t\t" . '<changefreq>' . htmlspecialchars($entry->changefreq, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</changefreq>' . "\n";
            $xml .= "\t\t" . '<priority>' . htmlspecialchars((string) $entry->priority, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</priority>' . "\n";
            $xml .= "\t\t" . '<lastmod>' . htmlspecialchars($lastmod, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</lastmod>' . "\n";
            $xml .= "\t" . '</url>' . "\n";
            $count++;
        }

        $xml .= '</urlset>' . "\n";
        Storage::disk('sitemaps')->put($filePath, $xml);

        return $count;
    }

    /**
     * @param  list<string>  $sitemapUrls  Absolute child sitemap locs
     */
    public function writeIndex(string $filePath, array $sitemapUrls): void
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($sitemapUrls as $sitemapUrl) {
            $xml .= "\t" . '<sitemap>' . "\n";
            $xml .= "\t\t" . '<loc>' . htmlspecialchars($sitemapUrl, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</loc>' . "\n";
            $xml .= "\t\t" . '<lastmod>' . Carbon::now()->toISOString() . '</lastmod>' . "\n";
            $xml .= "\t" . '</sitemap>' . "\n";
        }

        $xml .= '</sitemapindex>';
        Storage::disk('sitemaps')->put($filePath, $xml);
    }
}
