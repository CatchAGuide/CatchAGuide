<?php

namespace App\Services\Media;

use App\Contracts\Storage\ObjectStorageInterface;

class MediaUrlResolver
{
    public function __construct(
        private readonly ObjectStorageInterface $objectStorage,
        private readonly ObjectStorageInterface $localStorage,
        private readonly MediaPathResolver $pathResolver,
        private readonly ManagedMediaPathMatcher $pathMatcher,
        private readonly MediaEnvironmentResolver $environmentResolver,
    ) {}

    public function resolve(?string $path, ?string $placeholder = 'images/placeholder_guide.jpg'): string
    {
        if ($path === null || $path === '') {
            return $this->staticAsset($placeholder ?? 'images/placeholder_guide.jpg');
        }

        if ($this->pathResolver->isRemoteUrl($path)) {
            return $this->rewriteAppHostedUrl($path) ?? $path;
        }

        $normalized = $this->pathResolver->normalizePath($path);

        if ($this->shouldUseObjectStorageUrl($normalized)) {
            $publicUrl = $this->objectStoragePublicUrl($normalized);

            if ($this->urlSkipsExistsCheck()) {
                return $this->toAbsoluteUrl($publicUrl);
            }

            if ($this->pathResolver->existsOnObjectStorage($normalized)) {
                return $this->toAbsoluteUrl($publicUrl);
            }

            if ($this->pathResolver->existsLocally($normalized)) {
                return $this->toAbsoluteUrl($this->localStorage->url($normalized));
            }

            return $this->staticAsset($placeholder ?? 'images/placeholder_guide.jpg');
        }

        if ($this->localStorage->exists($normalized)) {
            return $this->toAbsoluteUrl($this->localStorage->url($normalized));
        }

        if ($this->pathMatcher->matches($normalized) && $this->cdnBaseUrl() !== '') {
            return $this->objectStoragePublicUrl($normalized);
        }

        return $this->staticAsset($normalized);
    }

    public function normalizePath(string $path): string
    {
        return $this->pathResolver->normalizePath($path);
    }

    public function isRemoteUrl(string $path): bool
    {
        return $this->pathResolver->isRemoteUrl($path);
    }

    public function isCloudPath(string $path): bool
    {
        return $this->pathResolver->isServedFromObjectStorage($path);
    }

    private function shouldUseObjectStorageUrl(string $normalized): bool
    {
        if (! $this->pathMatcher->matches($normalized)) {
            return false;
        }

        if ($this->cdnBaseUrl() !== '') {
            return true;
        }

        return (string) config('media_storage.disk', 'do_spaces')
            !== (string) config('media_storage.local_disk', 'public');
    }

    /**
     * Build a public CDN URL for a managed media path (staging|production prefix applied).
     */
    private function objectStoragePublicUrl(string $normalized): string
    {
        $cdnBase = $this->cdnBaseUrl();

        if ($cdnBase !== '') {
            $storagePath = $this->environmentResolver->applyBucketPrefix($normalized);

            return $cdnBase . '/' . ltrim($storagePath, '/');
        }

        return $this->objectStorage->url($normalized);
    }

    /**
     * Rewrite legacy full URLs that point at the app host (e.g. staging.catchaguide.de/guidings-images/…).
     */
    private function rewriteAppHostedUrl(string $url): ?string
    {
        $parsed = parse_url($url);
        $path = isset($parsed['path']) ? ltrim($parsed['path'], '/') : '';

        if ($path === '') {
            return null;
        }

        $normalized = $this->pathResolver->normalizePath($path);

        if (! $this->pathMatcher->matches($normalized)) {
            return null;
        }

        return $this->objectStoragePublicUrl($normalized);
    }

    private function cdnBaseUrl(): string
    {
        $disk = (string) config('media_storage.disk', 'do_spaces');

        return rtrim((string) config("filesystems.disks.{$disk}.url", ''), '/');
    }

    private function urlSkipsExistsCheck(): bool
    {
        return (bool) config('media_storage.url_skip_exists', true);
    }

    private function toAbsoluteUrl(string $url): string
    {
        if ($this->pathResolver->isRemoteUrl($url)) {
            return $url;
        }

        return url($url);
    }

    private function staticAsset(string $path): string
    {
        return app('url')->asset($path);
    }
}
