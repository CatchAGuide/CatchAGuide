<?php

namespace App\Services\Sitemap;

final class SitemapEntry
{
    public function __construct(
        public readonly string $loc,
        public readonly string $changefreq = 'weekly',
        public readonly float $priority = 0.5,
        public readonly ?string $lastmod = null,
    ) {}

    public static function make(
        string $loc,
        string $changefreq = 'weekly',
        float $priority = 0.5,
        ?string $lastmod = null,
    ): self {
        return new self($loc, $changefreq, $priority, $lastmod);
    }
}
