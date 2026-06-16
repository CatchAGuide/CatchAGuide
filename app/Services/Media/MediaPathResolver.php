<?php

namespace App\Services\Media;

use App\Contracts\Storage\ObjectStorageInterface;
use Illuminate\Support\Facades\Cache;

/**
 * Resolves media paths: object storage (DigitalOcean) first, then local fallback.
 * Existence checks against DO are cached to avoid repeated S3 HEAD requests.
 */
class MediaPathResolver
{
    public function __construct(
        private readonly ObjectStorageInterface $localStorage,
        private readonly ObjectStorageInterface $objectStorage,
        private readonly MediaEnvironmentResolver $environmentResolver,
    ) {}

    public function normalizePath(string $path): string
    {
        $path = trim(str_replace('//', '/', $path));
        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8);
        }

        if (str_starts_with($path, 'public/')) {
            $path = substr($path, 7);
        }

        return $this->environmentResolver->stripBucketPrefix($path);
    }

    public function existsLocally(string $path): bool
    {
        $normalized = $this->normalizePath($path);

        return $this->rememberExists('local', $normalized, fn () => $this->localStorage->exists($normalized));
    }

    public function existsOnObjectStorage(string $path): bool
    {
        $normalized = $this->normalizePath($path);

        return $this->rememberExists('object', $normalized, fn () => $this->objectStorage->exists($normalized));
    }

    public function exists(string $path): bool
    {
        return $this->existsOnObjectStorage($path) || $this->existsLocally($path);
    }

    public function read(string $path): string
    {
        $normalized = $this->normalizePath($path);

        if ($this->existsOnObjectStorage($path)) {
            return $this->objectStorage->read($normalized);
        }

        if ($this->existsLocally($path)) {
            return $this->localStorage->read($normalized);
        }

        return '';
    }

    public function forgetExistsCache(string $path): void
    {
        $normalized = $this->normalizePath($path);

        Cache::forget($this->existsCacheKey('object', $normalized));
        Cache::forget($this->existsCacheKey('local', $normalized));
    }

    public function isRemoteUrl(string $path): bool
    {
        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://');
    }

    public function isServedFromObjectStorage(string $path): bool
    {
        if ($this->isRemoteUrl($path)) {
            return true;
        }

        if (! $this->usesObjectStorage()) {
            return false;
        }

        if ($this->urlSkipsExistsCheck()) {
            return app(ManagedMediaPathMatcher::class)->matches($this->normalizePath($path));
        }

        return $this->existsOnObjectStorage($path);
    }

    private function usesObjectStorage(): bool
    {
        return (string) config('media_storage.disk', 'do_spaces')
            !== (string) config('media_storage.local_disk', 'public');
    }

    private function rememberExists(string $disk, string $normalized, callable $callback): bool
    {
        $ttl = (int) config('media_storage.exists_cache_ttl', 86400);

        if ($ttl <= 0) {
            return (bool) $callback();
        }

        return (bool) Cache::remember(
            $this->existsCacheKey($disk, $normalized),
            $ttl,
            $callback
        );
    }

    private function existsCacheKey(string $disk, string $normalized): string
    {
        return 'media:exists:' . $disk . ':' . md5($normalized);
    }

    private function urlSkipsExistsCheck(): bool
    {
        return (bool) config('media_storage.url_skip_exists', true);
    }
}
