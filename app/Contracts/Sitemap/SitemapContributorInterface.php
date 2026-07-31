<?php

namespace App\Contracts\Sitemap;

use App\Services\Sitemap\SitemapContext;
use App\Services\Sitemap\SitemapEntry;
use Illuminate\Support\Collection;

interface SitemapContributorInterface
{
    /**
     * Stable key used in logs and ordering (e.g. listing, vacations).
     */
    public function key(): string;

    /**
     * Filename relative to the sitemaps disk root, including leading slash.
     * Example: /sitemap_vacations_en.xml
     */
    public function fileName(string $lang): string;

    /**
     * @return Collection<int, SitemapEntry>
     */
    public function entries(SitemapContext $context): Collection;
}
