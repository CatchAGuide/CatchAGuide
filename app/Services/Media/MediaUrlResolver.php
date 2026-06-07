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
    ) {}

    public function resolve(?string $path, ?string $placeholder = 'images/placeholder_guide.jpg'): string
    {
        if ($path === null || $path === '') {
            return $this->staticAsset($placeholder ?? 'images/placeholder_guide.jpg');
        }

        if ($this->pathResolver->isRemoteUrl($path)) {
            return $path;
        }

        $normalized = $this->pathResolver->normalizePath($path);

        if ($this->shouldUseObjectStorageUrl($normalized)) {
            if ($this->urlSkipsExistsCheck()) {
                return $this->toAbsoluteUrl($this->objectStorage->url($normalized));
            }

            if ($this->pathResolver->existsOnObjectStorage($normalized)) {
                return $this->toAbsoluteUrl($this->objectStorage->url($normalized));
            }

            if ($this->pathResolver->existsLocally($normalized)) {
                return $this->toAbsoluteUrl($this->localStorage->url($normalized));
            }

            return $this->staticAsset($placeholder ?? 'images/placeholder_guide.jpg');
        }

        if ($this->localStorage->exists($normalized)) {
            return $this->toAbsoluteUrl($this->localStorage->url($normalized));
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
        if ((string) config('media_storage.disk', 'do_spaces') === (string) config('media_storage.local_disk', 'public')) {
            return false;
        }

        return $this->pathMatcher->matches($normalized);
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
