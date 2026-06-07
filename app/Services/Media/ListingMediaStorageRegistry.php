<?php

namespace App\Services\Media;

use App\Contracts\Media\ListingMediaStorageInterface;
use InvalidArgumentException;

class ListingMediaStorageRegistry
{
    /** @var array<string, ListingMediaStorageInterface> */
    private array $byListingKey = [];

    /** @var array<string, ListingMediaStorageInterface> */
    private array $byDirectory = [];

    public function register(ListingMediaStorageInterface $storage): void
    {
        $this->byListingKey[$storage->listingKey()] = $storage;
        $this->byDirectory[$storage->baseDirectory()] = $storage;
    }

    public function forListing(string $listingKey): ListingMediaStorageInterface
    {
        if (! isset($this->byListingKey[$listingKey])) {
            throw new InvalidArgumentException("No media storage registered for listing [{$listingKey}]");
        }

        return $this->byListingKey[$listingKey];
    }

    public function forDirectory(string $directory): ?ListingMediaStorageInterface
    {
        $directory = trim($directory, '/');

        if (isset($this->byDirectory[$directory])) {
            return $this->byDirectory[$directory];
        }

        $listingKey = config("media_storage.directories.{$directory}");

        if ($listingKey) {
            return $this->byListingKey[$listingKey] ?? null;
        }

        $rootDirectory = explode('/', $directory, 2)[0];
        $listingKey = config("media_storage.directories.{$rootDirectory}");

        return $listingKey ? ($this->byListingKey[$listingKey] ?? null) : null;
    }

    /**
     * @return array<string, ListingMediaStorageInterface>
     */
    public function all(): array
    {
        return $this->byListingKey;
    }
}
