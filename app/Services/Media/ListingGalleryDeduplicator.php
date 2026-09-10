<?php

namespace App\Services\Media;

/**
 * Collapse duplicate listing gallery entries caused by re-saving existing previews.
 *
 * Typical pattern: older paths under `{folder}/{id}/gallery/` plus a newer re-upload
 * set under `{folder}/{id}/`. Exact path duplicates are removed first.
 *
 * When both families are present, prefer the family that still exists on storage.
 * Never drop a live family in favor of missing paths.
 */
class ListingGalleryDeduplicator
{
    public function __construct(
        private readonly ListingGalleryRetention $retention,
        private readonly ?\Closure $existsChecker = null,
    ) {}

    /**
     * @param  array<int, mixed>|string|null  $gallery
     * @return array{
     *     gallery: array<int, string>,
     *     removed: array<int, string>,
     *     thumbnail: string,
     *     changed: bool
     * }
     */
    public function dedupe(array|string|null $gallery, ?string $thumbnail = null): array
    {
        $paths = $this->retention->stringifyPaths(
            is_array($gallery)
                ? $gallery
                : (json_decode((string) ($gallery ?? '[]'), true) ?: [])
        );

        $exactUnique = [];
        $seen = [];
        foreach ($paths as $path) {
            if (isset($seen[$path])) {
                continue;
            }
            $seen[$path] = true;
            $exactUnique[] = $path;
        }

        $collapsed = $this->collapseMixedFamilies($exactUnique);
        $removed = $this->diffRemoved($paths, $collapsed);
        $originalThumb = is_string($thumbnail)
            ? (string) ($this->retention->normalizePath($thumbnail) ?? '')
            : '';
        $thumbnailPath = $this->resolveThumbnail($originalThumb !== '' ? $originalThumb : null, $collapsed, $removed);

        if ($originalThumb === '' && $removed === []) {
            $thumbnailPath = '';
        }

        return [
            'gallery' => $collapsed,
            'removed' => $removed,
            'thumbnail' => $thumbnailPath,
            'changed' => $removed !== [] || ($originalThumb !== '' && $thumbnailPath !== $originalThumb),
        ];
    }

    /**
     * @param  array<int, string>  $original
     * @param  array<int, string>  $kept
     * @return array<int, string>
     */
    private function diffRemoved(array $original, array $kept): array
    {
        $bag = array_count_values($kept);
        $removed = [];

        foreach ($original as $path) {
            if (isset($bag[$path]) && $bag[$path] > 0) {
                $bag[$path]--;
                continue;
            }

            $removed[] = $path;
        }

        return $removed;
    }

    /**
     * Prefer current entity-root files over legacy `gallery/` subdirectory copies.
     * When the preferred set is a multiple of the legacy set, keep the last N preferred
     * paths (most recent re-upload batch appended on save).
     *
     * @param  array<int, string>  $paths
     * @return array<int, string>
     */
    private function collapseMixedFamilies(array $paths): array
    {
        $legacy = [];
        $preferred = [];
        $other = [];

        foreach ($paths as $path) {
            if ($this->isLegacyGallerySubdirPath($path)) {
                $legacy[] = $path;
            } elseif ($this->isEntityRootPath($path)) {
                $preferred[] = $path;
            } else {
                $other[] = $path;
            }
        }

        if ($legacy === [] || $preferred === []) {
            return $paths;
        }

        $legacyLive = $this->countExisting($legacy);
        $preferredLive = $this->countExisting($preferred);

        // If only one family still exists on storage, keep that family.
        if ($preferredLive === 0 && $legacyLive > 0) {
            return array_values(array_merge($legacy, $other));
        }
        if ($legacyLive === 0 && $preferredLive > 0) {
            return array_values(array_merge($preferred, $other));
        }
        // If neither family resolves, do not collapse — leave paths for manual restore.
        if ($preferredLive === 0 && $legacyLive === 0) {
            return $paths;
        }

        $legacyCount = count($legacy);
        $preferredCount = count($preferred);

        if ($preferredCount === $legacyCount) {
            $keptPreferred = $preferred;
        } elseif (
            $legacyCount >= 2
            && $preferredCount > $legacyCount
            && $preferredCount % $legacyCount === 0
        ) {
            // Multiple full re-upload batches appended after the legacy set.
            $keptPreferred = array_slice($preferred, -$legacyCount);
        } else {
            // Prefer the current layout even when counts do not align cleanly.
            // Do not treat legacyCount=1 as a batch size (N % 1 === 0 always).
            $keptPreferred = $preferred;
        }

        // Final safety: never drop a live family for a mostly-missing preferred slice.
        if ($this->countExisting($keptPreferred) === 0 && $legacyLive > 0) {
            return array_values(array_merge($legacy, $other));
        }

        return array_values(array_merge($keptPreferred, $other));
    }

    /**
     * @param  array<int, string>  $paths
     */
    private function countExisting(array $paths): int
    {
        $checker = $this->existsChecker ?? static fn (string $path): bool => media_exists($path);
        $count = 0;

        foreach ($paths as $path) {
            if ($checker($path)) {
                $count++;
            }
        }

        return $count;
    }

    private function isLegacyGallerySubdirPath(string $path): bool
    {
        return (bool) preg_match('#^[^/]+/\d+/gallery/#', $path);
    }

    private function isEntityRootPath(string $path): bool
    {
        // camps/28/hash_28.webp — not camps/28/gallery/...
        return (bool) preg_match('#^[^/]+/\d+/[^/]+$#', $path)
            && ! str_contains($path, '/gallery/');
    }

    /**
     * @param  array<int, string>  $kept
     * @param  array<int, string>  $removed
     */
    private function resolveThumbnail(?string $thumbnail, array $kept, array $removed): string
    {
        $thumbnail = is_string($thumbnail) ? $this->retention->normalizePath($thumbnail) : null;

        if ($thumbnail === null || $thumbnail === '') {
            return $kept[0] ?? '';
        }

        foreach ($kept as $path) {
            if ($path === $thumbnail || basename($path) === basename($thumbnail)) {
                return $path;
            }
        }

        // Thumbnail pointed at a removed legacy path: map by index within removed set when possible.
        $removedIndex = array_search($thumbnail, $removed, true);
        if ($removedIndex !== false && isset($kept[$removedIndex])) {
            return $kept[$removedIndex];
        }

        return $kept[0] ?? $thumbnail;
    }
}
