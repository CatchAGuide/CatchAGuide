<?php

namespace App\Services\Sitemap;

final class SitemapEntry
{
    /**
     * @param  array<string, string>  $alternates  hreflang code => absolute URL of that language's
     *                                              equivalent page, e.g. ['en' => '...', 'de' => '...'].
     *                                              Include the entry's own language/URL too — Google
     *                                              expects each url block to list every locale.
     */
    public function __construct(
        public readonly string $loc,
        public readonly string $changefreq = 'weekly',
        public readonly float $priority = 0.5,
        public readonly ?string $lastmod = null,
        public readonly array $alternates = [],
    ) {}

    public static function make(
        string $loc,
        string $changefreq = 'weekly',
        float $priority = 0.5,
        ?string $lastmod = null,
        array $alternates = [],
    ): self {
        return new self($loc, $changefreq, $priority, $lastmod, $alternates);
    }
}
