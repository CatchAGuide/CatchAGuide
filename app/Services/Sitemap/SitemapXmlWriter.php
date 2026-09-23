<?php

namespace App\Services\Sitemap;

use Illuminate\Support\Facades\Storage;

final class SitemapXmlWriter
{
    /**
     * @param  iterable<SitemapEntry>  $entries
     * @return array{count: int, lastmod: ?string} lastmod is the newest entry lastmod, for the index
     */
    public function writeUrlset(string $filePath, iterable $entries): array
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" '
            . 'xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";

        $count = 0;
        $newest = null;
        foreach ($entries as $entry) {
            $xml .= "\t" . '<url>' . "\n";
            $xml .= "\t\t" . '<loc>' . $this->escape($entry->loc) . '</loc>' . "\n";
            if ($entry->lastmod !== null) {
                $xml .= "\t\t" . '<lastmod>' . $this->escape($entry->lastmod) . '</lastmod>' . "\n";
                $newest = $this->newer($newest, $entry->lastmod);
            }
            foreach ($entry->alternates as $hreflang => $href) {
                $xml .= "\t\t" . '<xhtml:link rel="alternate" hreflang="'
                    . $this->escape((string) $hreflang) . '" href="'
                    . $this->escape($href) . '" />' . "\n";
            }
            $xml .= "\t" . '</url>' . "\n";
            $count++;
        }

        $xml .= '</urlset>' . "\n";
        Storage::disk('sitemaps')->put($filePath, $xml);

        return ['count' => $count, 'lastmod' => $newest];
    }

    /**
     * @param  array<string, ?string>  $sitemaps  Absolute child sitemap loc => newest lastmod inside it
     */
    public function writeIndex(string $filePath, array $sitemaps): void
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($sitemaps as $sitemapUrl => $lastmod) {
            $xml .= "\t" . '<sitemap>' . "\n";
            $xml .= "\t\t" . '<loc>' . $this->escape($sitemapUrl) . '</loc>' . "\n";
            if ($lastmod !== null) {
                $xml .= "\t\t" . '<lastmod>' . $this->escape($lastmod) . '</lastmod>' . "\n";
            }
            $xml .= "\t" . '</sitemap>' . "\n";
        }

        $xml .= '</sitemapindex>' . "\n";
        Storage::disk('sitemaps')->put($filePath, $xml);
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    private function newer(?string $a, string $b): string
    {
        return $a === null || strtotime($b) > strtotime($a) ? $b : $a;
    }
}
