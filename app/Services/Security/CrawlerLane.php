<?php

namespace App\Services\Security;

enum CrawlerLane: string
{
    case User = 'user';
    case SearchEngine = 'search_engine';
    case Social = 'social';
    case SeoCrawler = 'seo_crawler';
    case SpoofedCrawler = 'spoofed_crawler';

    public function isTrusted(): bool
    {
        return $this === self::SearchEngine || $this === self::Social || $this === self::SeoCrawler;
    }

    /**
     * DNS-verified search engines are never rate limited by the app. A 429 makes
     * Google cut its crawl rate for the whole site, and the engines already pace
     * themselves by our response times.
     */
    public function isRateLimited(): bool
    {
        return $this !== self::SearchEngine;
    }
}
