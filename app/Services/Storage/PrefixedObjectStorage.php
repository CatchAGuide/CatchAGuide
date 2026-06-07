<?php

namespace App\Services\Storage;

use App\Contracts\Storage\ObjectStorageInterface;
use App\Services\Media\MediaEnvironmentResolver;

/**
 * Adds an environment prefix (production/staging) to object-storage paths.
 * Database paths remain unprefixed; the prefix is applied only on the remote disk.
 */
class PrefixedObjectStorage implements ObjectStorageInterface
{
    public function __construct(
        private readonly ObjectStorageInterface $inner,
        private readonly MediaEnvironmentResolver $environmentResolver,
    ) {}

    public function exists(string $path): bool
    {
        return $this->inner->exists($this->toStoragePath($path));
    }

    public function read(string $path): string
    {
        return $this->inner->read($this->toStoragePath($path));
    }

    public function url(string $path): string
    {
        return $this->inner->url($this->toStoragePath($path));
    }

    public function write(string $path, string $contents, array $options = []): bool
    {
        return $this->inner->write($this->toStoragePath($path), $contents, $options);
    }

    public function setPublicVisibility(string $path): bool
    {
        if (! method_exists($this->inner, 'setPublicVisibility')) {
            return false;
        }

        return $this->inner->setPublicVisibility($this->toStoragePath($path));
    }

    public function delete(string $path): bool
    {
        return $this->inner->delete($this->toStoragePath($path));
    }

    public function deleteMany(array $paths): void
    {
        $prefixed = array_map(fn (string $path): string => $this->toStoragePath($path), $paths);
        $this->inner->deleteMany($prefixed);
    }

    public function makeDirectory(string $path): bool
    {
        return $this->inner->makeDirectory($this->toStoragePath($path));
    }

    public function toStoragePath(string $relativePath): string
    {
        return $this->environmentResolver->applyBucketPrefix($relativePath);
    }

    public function toRelativePath(string $storagePath): string
    {
        return $this->environmentResolver->stripBucketPrefix($storagePath);
    }
}
