<?php

namespace App\Services\Media;

use App\Contracts\Media\ListingMediaStorageInterface;
use App\Contracts\Media\MediaProcessorInterface;
use App\Contracts\Storage\ObjectStorageInterface;

abstract class AbstractListingMediaStorage implements ListingMediaStorageInterface
{
    public function __construct(
        protected ObjectStorageInterface $objectStorage,
        protected ObjectStorageInterface $localStorage,
        protected MediaProcessorInterface $processor,
        protected MediaUrlResolver $urlResolver,
        protected MediaPathResolver $pathResolver,
        protected MediaWriteStorageResolver $writeStorageResolver,
    ) {}

    abstract public function listingKey(): string;

    abstract public function baseDirectory(): string;

    public function upload(
        mixed $source,
        ?string $filename = null,
        int $quality = 75,
        ?int $id = null,
        ?string $directory = null
    ): string {
        $targetDirectory = $directory ?? $this->baseDirectory();

        $path = $this->processor->process(
            $source,
            $targetDirectory,
            $filename,
            $quality,
            $id,
            $this->writeStorageResolver->forUploads()
        );

        $this->pathResolver->forgetExistsCache($path);

        return $path;
    }

    public function delete(string $path): bool
    {
        $normalized = $this->pathResolver->normalizePath($path);
        $deleted = false;

        if ($this->pathResolver->existsLocally($normalized)) {
            $deleted = $this->localStorage->delete($normalized) || $deleted;
        }

        if ($this->pathResolver->existsOnObjectStorage($normalized)) {
            $deleted = $this->objectStorage->delete($normalized) || $deleted;
        }

        $this->pathResolver->forgetExistsCache($path);

        return $deleted;
    }

    public function exists(string $path): bool
    {
        return $this->pathResolver->exists($path);
    }

    public function read(string $path): string
    {
        return $this->pathResolver->read($path);
    }

    public function url(string $path): string
    {
        return $this->urlResolver->resolve($path);
    }
}
