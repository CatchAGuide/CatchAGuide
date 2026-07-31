<?php

namespace App\Services\Sitemap;

final class SitemapContext
{
    public function __construct(
        public readonly string $baseUrl,
        public readonly string $lang,
    ) {}

    public function absoluteUrl(string $path): string
    {
        $path = '/' . ltrim($path, '/');
        if ($path === '/') {
            return $this->baseUrl;
        }

        return $this->baseUrl . $path;
    }
}
