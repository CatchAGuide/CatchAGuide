<?php

namespace App\Services\Media;

class ListingMediaPathBuilder
{
    public function folder(string $listingKey): string
    {
        $folder = config("media_storage.listing_folders.{$listingKey}");

        if (! is_string($folder) || $folder === '') {
            throw new \InvalidArgumentException("Unknown listing folder for [{$listingKey}]");
        }

        return $folder;
    }

    /**
     * Entity upload directory: {folder}/{id} or {folder}/temp when id is missing.
     */
    public function entityDirectory(string $listingKey, ?int $entityId = null): string
    {
        $folder = $this->folder($listingKey);

        if ($entityId !== null && $entityId > 0) {
            return "{$folder}/{$entityId}";
        }

        return "{$folder}/temp";
    }

    public function tempDirectory(string $listingKey): string
    {
        return $this->entityDirectory($listingKey, null);
    }

    /**
     * @return array<int, string>
     */
    public function legacyTempDirectories(string $listingKey): array
    {
        $folder = $this->folder($listingKey);
        $legacy = config("media_storage.legacy_listing_folders.{$listingKey}", []);

        $directories = [
            "{$folder}/temp/gallery",
        ];

        foreach ((array) $legacy as $entry) {
            if (is_string($entry) && $entry !== '') {
                $directories[] = trim($entry, '/');
            }
        }

        return array_values(array_unique($directories));
    }
}
