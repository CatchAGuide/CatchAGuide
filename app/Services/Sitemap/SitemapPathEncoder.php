<?php

namespace App\Services\Sitemap;

/**
 * Builds absolute sitemap locs with UTF-8-safe path segment encoding.
 */
final class SitemapPathEncoder
{
    /**
     * @param  list<string>  $segments  Path segments without leading/trailing slashes
     */
    public function join(string $baseUrl, array $segments = []): string
    {
        $baseUrl = rtrim($baseUrl, '/');
        if ($segments === []) {
            return $baseUrl;
        }

        $encoded = array_map(static function (string $segment): string {
            $segment = trim($segment, '/');
            // Preserve UTF-8 letters (ö, ä, ü) while encoding spaces/unsafe chars.
            return rawurlencode(rawurldecode($segment));
        }, $segments);

        return $baseUrl . '/' . implode('/', $encoded);
    }

    public function fromPath(string $baseUrl, string $path): string
    {
        $path = trim($path, '/');
        if ($path === '') {
            return rtrim($baseUrl, '/');
        }

        return $this->join($baseUrl, explode('/', $path));
    }
}
