<?php

namespace App\Services\Media;

class MediaEnvironmentResolver
{
    public function bucketPrefix(): string
    {
        return match (app()->environment()) {
            'production' => 'production',
            'staging' => 'staging',
            default => 'staging',
        };
    }

    public function isProduction(): bool
    {
        return $this->bucketPrefix() === 'production';
    }

    public function isStaging(): bool
    {
        return $this->bucketPrefix() === 'staging';
    }

    /**
     * Strip a known bucket environment prefix from a stored path.
     */
    public function stripBucketPrefix(string $path): string
    {
        $path = trim(str_replace('//', '/', $path), '/');

        foreach ($this->knownPrefixes() as $prefix) {
            if ($path === $prefix) {
                return '';
            }

            if (str_starts_with($path, $prefix . '/')) {
                return substr($path, strlen($prefix) + 1);
            }
        }

        return $path;
    }

    public function applyBucketPrefix(string $relativePath): string
    {
        $relativePath = $this->stripBucketPrefix($relativePath);
        $relativePath = ltrim($relativePath, '/');
        $prefix = $this->bucketPrefix();

        if ($relativePath === '') {
            return $prefix;
        }

        return $prefix !== '' ? "{$prefix}/{$relativePath}" : $relativePath;
    }

    /**
     * @return array<int, string>
     */
    public function knownPrefixes(): array
    {
        return ['production', 'staging'];
    }
}
