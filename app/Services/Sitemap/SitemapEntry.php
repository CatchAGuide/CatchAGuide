<?php

namespace App\Services\Sitemap;

/**
 * One <url> in a sitemap. No changefreq/priority: Google ignores both.
 */
final class SitemapEntry
{
    /**
     * @param  ?string  $lastmod  When the page's own content or listing set last changed — never
     *                            the generation time. Null omits the tag rather than inventing one.
     * @param  array<string, string>  $alternates  hreflang code => absolute URL. Left empty, the
     *                                              generator derives en/de/x-default from the
     *                                              path (see SitemapGenerator).
     * @param  bool  $localized  False for pages with no counterpart on the other locale's domain
     *                           (e.g. magazine articles, which are written per language).
     */
    public function __construct(
        public readonly string $loc,
        public readonly ?string $lastmod = null,
        public readonly array $alternates = [],
        public readonly bool $localized = true,
    ) {}

    public static function make(
        string $loc,
        ?string $lastmod = null,
        array $alternates = [],
        bool $localized = true,
    ): self {
        return new self($loc, $lastmod, $alternates, $localized);
    }

    public function withAlternates(array $alternates): self
    {
        return new self($this->loc, $this->lastmod, $alternates, $this->localized);
    }
}
