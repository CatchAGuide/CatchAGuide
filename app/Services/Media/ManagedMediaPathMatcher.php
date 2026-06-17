<?php

namespace App\Services\Media;

class ManagedMediaPathMatcher
{
    /**
     * Whether a path passed to asset() should use media storage resolution
     * (local first, then DigitalOcean Spaces).
     */
    public function matches(?string $path): bool
    {
        if (! is_string($path) || $path === '') {
            return false;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return false;
        }

        $normalized = ltrim(str_replace('//', '/', $path), '/');

        foreach (['images/', 'storage/'] as $legacyPrefix) {
            if (str_starts_with($normalized, $legacyPrefix)) {
                $stripped = substr($normalized, strlen($legacyPrefix));
                if ($this->matchesRelativePath($stripped)) {
                    return true;
                }
            }
        }

        return $this->matchesRelativePath($normalized);
    }

    private function matchesRelativePath(string $path): bool
    {
        foreach (config('media_storage.sitewide_folders', []) as $group) {
            foreach (array_keys($group) as $folder) {
                if ($path === $folder || str_starts_with($path, $folder . '/')) {
                    return true;
                }
            }
        }

        return false;
    }
}
