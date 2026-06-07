<?php

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;

/**
 * Upload/delete for listing media directories (guidings, trips, camps, etc.).
 */
class ListingImageUploadService
{
    private const GUIDING_LEGACY_DIRECTORY = 'assets/guides';

    public function __construct(
        private readonly ListingMediaPathBuilder $paths,
    ) {}

    public function upload(
        UploadedFile|string $file,
        string $directory,
        ?string $filename = null,
        ?int $entityId = null,
        int $quality = 75
    ): string {
        return media_upload($file, $directory, $filename, $quality, $entityId);
    }

    public function uploadForListing(
        string $listingKey,
        UploadedFile $file,
        ?int $entityId = null,
        ?string $filename = null,
        int $quality = 75
    ): string {
        return $this->upload(
            $file,
            $this->paths->entityDirectory($listingKey, $entityId),
            $filename,
            $entityId,
            $quality
        );
    }

    public function delete(string $path, ?string $legacyDirectory = null): bool
    {
        if ($path === '') {
            return false;
        }

        if (str_contains($path, '/')) {
            return media_delete($path);
        }

        if ($legacyDirectory !== null) {
            return media_delete(rtrim($legacyDirectory, '/') . '/' . ltrim($path, '/'));
        }

        return media_delete($path);
    }

    public function deleteGuiding(string $path): bool
    {
        return $this->delete($path, self::GUIDING_LEGACY_DIRECTORY);
    }
}
