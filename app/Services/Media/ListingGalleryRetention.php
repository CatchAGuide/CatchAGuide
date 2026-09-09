<?php

namespace App\Services\Media;

/**
 * Decide which existing gallery files to keep vs remove.
 *
 * Object storage cannot join a DB transaction, so callers must only trash
 * $toDelete after a successful commit, and must skip anything still referenced
 * by the saved gallery.
 */
class ListingGalleryRetention
{
    /**
     * @param  array<int, mixed>  $existingImages
     * @return array{
     *     kept: array<int, string>,
     *     to_delete: array<int, string>,
     *     image_list_synced: bool,
     *     image_list: array<int, string>
     * }
     */
    public function retain(mixed $imageListRaw, array $existingImages): array
    {
        $existing = $this->stringifyPaths($existingImages);

        if (! $this->isImageListSynced($imageListRaw)) {
            return [
                'kept' => $existing,
                'to_delete' => [],
                'image_list_synced' => false,
                'image_list' => [],
            ];
        }

        $decoded = json_decode((string) $imageListRaw, true);
        if (! is_array($decoded)) {
            return [
                'kept' => $existing,
                'to_delete' => [],
                'image_list_synced' => false,
                'image_list' => [],
            ];
        }

        $imageList = array_values(array_filter(array_map(
            fn ($path) => $this->normalizePath($path),
            $decoded
        )));
        $keepExact = array_flip($imageList);
        $keepBasenames = [];
        foreach ($imageList as $path) {
            $keepBasenames[basename($path)] = $path;
        }

        $kept = [];
        $toDelete = [];

        foreach ($existing as $existingImage) {
            $normalized = $this->normalizePath($existingImage);
            if ($normalized === null) {
                continue;
            }

            if (isset($keepExact[$normalized]) || isset($keepBasenames[basename($normalized)])) {
                $kept[] = $existingImage;
            } else {
                $toDelete[] = $existingImage;
            }
        }

        return [
            'kept' => $kept,
            'to_delete' => $toDelete,
            'image_list_synced' => true,
            'image_list' => $imageList,
        ];
    }

    /**
     * Never remove a file that is still referenced by the committed gallery.
     *
     * @param  array<int, mixed>  $pathsToDelete
     * @param  array<int, mixed>  $committedGallery
     * @return array<int, string>
     */
    public function filterDeletesAgainstCommitted(array $pathsToDelete, array $committedGallery): array
    {
        $committedNormalized = [];
        $committedBasenames = [];

        foreach ($this->stringifyPaths($committedGallery) as $path) {
            $normalized = $this->normalizePath($path);
            if ($normalized === null) {
                continue;
            }

            $committedNormalized[$normalized] = true;
            $committedBasenames[basename($normalized)] = true;
        }

        $safe = [];
        foreach ($this->stringifyPaths($pathsToDelete) as $path) {
            $normalized = $this->normalizePath($path);
            if ($normalized === null) {
                continue;
            }

            if (isset($committedNormalized[$normalized]) || isset($committedBasenames[basename($normalized)])) {
                continue;
            }

            $safe[] = $path;
        }

        return array_values(array_unique($safe));
    }

    public function isImageListSynced(mixed $imageListRaw): bool
    {
        return is_string($imageListRaw) && trim($imageListRaw) !== '';
    }

    public function normalizePath(mixed $path): ?string
    {
        if (is_array($path)) {
            $path = $path['path'] ?? $path['value'] ?? $path['url'] ?? reset($path);
        }

        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '//')) {
            $parsedPath = parse_url($path, PHP_URL_PATH);
            if (is_string($parsedPath) && $parsedPath !== '') {
                $path = $parsedPath;
            }
        }

        return ltrim(str_replace('\\', '/', $path), '/');
    }

    /**
     * @param  array<int, mixed>  $paths
     * @return array<int, string>
     */
    public function stringifyPaths(array $paths): array
    {
        $result = [];

        foreach ($paths as $path) {
            $normalized = $this->normalizePath($path);
            if ($normalized !== null) {
                $result[] = $normalized;
            }
        }

        return array_values($result);
    }
}
