<?php

namespace App\Services\Media;

class ListingMediaRelocator
{
    public function __construct(
        private readonly ListingMediaPathBuilder $paths,
    ) {}

    public function promoteForListing(string $listingKey, int $entityId, array $imageData): array
    {
        $data = $imageData;

        foreach (array_merge([$this->paths->tempDirectory($listingKey)], $this->paths->legacyTempDirectories($listingKey)) as $tempDirectory) {
            $data = $this->promoteTempPaths(
                $data,
                $tempDirectory,
                $this->paths->entityDirectory($listingKey, $entityId)
            );
        }

        return $data;
    }

    /**
     * Move gallery/thumbnail paths from a temp folder to the final entity folder (local or DO).
     *
     * @param  array{gallery_images?: array<int, string>, thumbnail_path?: string|null}  $imageData
     * @return array{gallery_images: array<int, string>, thumbnail_path: string}
     */
    public function promoteTempPaths(array $imageData, string $tempDirectory, string $finalDirectory): array
    {
        $tempDirectory = trim($tempDirectory, '/');
        $finalDirectory = trim($finalDirectory, '/');

        $updatedGalleryImages = [];

        foreach ($imageData['gallery_images'] ?? [] as $imagePath) {
            if (! is_string($imagePath) || $imagePath === '') {
                continue;
            }

            if (str_starts_with($imagePath, $tempDirectory)) {
                $updatedGalleryImages[] = media_move(
                    $imagePath,
                    $finalDirectory . '/' . basename($imagePath)
                );
            } else {
                $updatedGalleryImages[] = $imagePath;
            }
        }

        $updatedThumbnailPath = (string) ($imageData['thumbnail_path'] ?? '');

        if ($updatedThumbnailPath !== '' && str_starts_with($updatedThumbnailPath, $tempDirectory)) {
            $updatedThumbnailPath = media_move(
                $updatedThumbnailPath,
                $finalDirectory . '/' . basename($updatedThumbnailPath)
            );
        }

        return [
            'gallery_images' => $updatedGalleryImages,
            'thumbnail_path' => $updatedThumbnailPath,
        ];
    }
}
