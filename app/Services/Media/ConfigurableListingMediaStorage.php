<?php

namespace App\Services\Media;

use App\Contracts\Media\MediaProcessorInterface;
use App\Contracts\Storage\ObjectStorageInterface;

class ConfigurableListingMediaStorage extends AbstractListingMediaStorage
{
    public function __construct(
        private readonly string $listingKey,
        ObjectStorageInterface $objectStorage,
        ObjectStorageInterface $localStorage,
        MediaProcessorInterface $processor,
        MediaUrlResolver $urlResolver,
        MediaPathResolver $pathResolver,
        MediaWriteStorageResolver $writeStorageResolver,
        private readonly ListingMediaPathBuilder $pathBuilder,
    ) {
        parent::__construct(
            $objectStorage,
            $localStorage,
            $processor,
            $urlResolver,
            $pathResolver,
            $writeStorageResolver,
        );
    }

    public function listingKey(): string
    {
        return $this->listingKey;
    }

    public function baseDirectory(): string
    {
        return $this->pathBuilder->tempDirectory($this->listingKey);
    }
}
