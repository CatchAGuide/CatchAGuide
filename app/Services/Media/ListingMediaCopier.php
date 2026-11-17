<?php

namespace App\Services\Media;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Copies a listing's images into another entity's folder ({folder}/{id}/…), e.g. when a guide
 * duplicates a tour. Each copy is its own file, so deleting an image from one listing never
 * removes it from the other (the gallery retention / trash logic deletes by path).
 */
class ListingMediaCopier
{
    public function __construct(
        private readonly MediaPathResolver $paths,
        private readonly MediaWriteStorageResolver $storage,
        private readonly ListingMediaPathBuilder $folders,
    ) {}

    /**
     * @param  array<int, string>  $gallery
     * @return array{gallery_images: array<int, string>, thumbnail_path: ?string}
     */
    public function copyGallery(string $listingKey, int $entityId, array $gallery, ?string $thumbnail): array
    {
        $directory = $this->folders->entityDirectory($listingKey, $entityId);
        $copied = [];
        $map = [];

        foreach ($gallery as $path) {
            if (! is_string($path) || $path === '') {
                continue;
            }
            $new = $this->copy($path, $directory);
            if ($new !== null) {
                $copied[] = $new;
                $map[$path] = $new;
            }
        }

        $newThumbnail = null;
        if (is_string($thumbnail) && $thumbnail !== '') {
            $newThumbnail = $map[$thumbnail] ?? $this->copy($thumbnail, $directory);
        }

        return ['gallery_images' => $copied, 'thumbnail_path' => $newThumbnail ?? ($copied[0] ?? null)];
    }

    /** New path of the copy, or null when the source can't be read. */
    public function copy(string $path, string $directory): ?string
    {
        try {
            $contents = $this->paths->read($this->paths->normalizePath($path));
            if ($contents === '') {
                return null;
            }

            $extension = pathinfo($path, PATHINFO_EXTENSION) ?: 'webp';
            $target = trim($directory, '/').'/'.Str::beforeLast(basename($path), '.').'-'.Str::lower(Str::random(6)).'.'.$extension;

            $this->storage->forUploads()->write($target, $contents, [
                'visibility' => config('media_storage.object_visibility', 'public'),
            ]);
            $this->paths->forgetExistsCache($target);

            return $target;
        } catch (\Throwable $e) {
            Log::warning('Listing media copy failed', ['path' => $path, 'error' => $e->getMessage()]);

            return null;
        }
    }
}
