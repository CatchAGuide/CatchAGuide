<?php

namespace App\Services\Security;

enum CrawlerLane: string
{
    case User = 'user';
    case SearchEngine = 'search_engine';
    case SeoCrawler = 'seo_crawler';
    case SpoofedCrawler = 'spoofed_crawler';

    public function isTrusted(): bool
    {
        return $this === self::SearchEngine || $this === self::SeoCrawler;
    }
}
