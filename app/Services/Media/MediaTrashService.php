<?php

namespace App\Services\Media;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Recycle-bin / backup for listing media.
 *
 * Live removals always copy into `_trash/{folder}/{id}/{Y-m-d}/` before the
 * live object is deleted. Gallery updates can snapshot the previous gallery
 * into the same tree without deleting live files.
 *
 * Purge keeps at least the last N backup dates per entity.
 */
class MediaTrashService
{
    public function __construct(
        private readonly MediaPathResolver $pathResolver,
        private readonly MediaWriteStorageResolver $writeStorageResolver,
        private readonly MediaEnvironmentResolver $environmentResolver,
        private readonly ListingGalleryRetention $retention,
    ) {}

    public function trashRoot(): string
    {
        $root = trim((string) config('media_storage.trash.root', '_trash'), '/');

        return $root !== '' ? $root : '_trash';
    }

    public function retentionDays(): int
    {
        return max(1, (int) config('media_storage.trash.retention_days', 14));
    }

    /**
     * Always keep at least this many newest Y-m-d backup folders per entity.
     * Floor is 2 so a single bad day cannot wipe the only remaining backup.
     */
    public function keepDates(): int
    {
        return max(2, (int) config('media_storage.trash.keep_dates', 2));
    }

    public function backupBeforeGalleryUpdateEnabled(): bool
    {
        return (bool) config('media_storage.trash.backup_before_gallery_update', true);
    }

    public function deleteLiveAfterBackupEnabled(): bool
    {
        return (bool) config('media_storage.trash.delete_live_after_backup', true);
    }

    /**
     * Relative trash/backup destination for a live media path.
     */
    public function trashRelativePath(string $originalPath, ?\DateTimeInterface $when = null): string
    {
        $normalized = $this->pathResolver->normalizePath($originalPath);
        $when ??= now();
        $segments = array_values(array_filter(explode('/', $normalized), static fn ($part) => $part !== ''));
        $folder = $segments[0] ?? 'unknown';
        $entityId = isset($segments[1]) && ctype_digit((string) $segments[1]) ? $segments[1] : '0';
        $basename = basename($normalized) ?: 'file';
        $date = $when->format('Y-m-d');

        return $this->trashRoot() . '/' . $folder . '/' . $entityId . '/' . $date . '/' . $basename;
    }

    public function isTrashPath(string $path): bool
    {
        $normalized = $this->pathResolver->normalizePath($path);

        return str_starts_with($normalized, $this->trashRoot() . '/');
    }

    /**
     * Copy a live file into `_trash` without deleting the live object.
     */
    public function backup(string $path, ?\DateTimeInterface $when = null): ?string
    {
        $from = $this->pathResolver->normalizePath($path);
        if ($from === '' || $this->isTrashPath($from)) {
            return null;
        }

        if (! $this->pathResolver->exists($from)) {
            return null;
        }

        $to = $this->uniqueTrashPath($this->trashRelativePath($from, $when));
        $contents = $this->pathResolver->read($from);
        if ($contents === '') {
            Log::warning('MediaTrashService backup skipped empty source', ['path' => $from]);

            return null;
        }

        $written = $this->writeStorageResolver->forUploads()->write($to, $contents, [
            'visibility' => config('media_storage.object_visibility', 'public'),
        ]);

        if (! $written) {
            Log::error('MediaTrashService failed to write backup copy; live file untouched', [
                'from' => $from,
                'to' => $to,
            ]);

            return null;
        }

        $this->pathResolver->forgetExistsCache($to);

        return $to;
    }

    /**
     * Snapshot live gallery/thumbnail paths into `_trash` (no live deletes).
     *
     * @param  array<int, mixed>  $paths
     * @return array<int, string> Backup destinations written
     */
    public function backupMany(array $paths, ?\DateTimeInterface $when = null): array
    {
        $backedUp = [];

        foreach (array_unique($this->retention->stringifyPaths($paths)) as $path) {
            $destination = $this->backup($path, $when);
            if ($destination !== null) {
                $backedUp[] = $destination;
            }
        }

        return $backedUp;
    }

    /**
     * Move a live file to trash: backup copy first, then delete live only if
     * the backup succeeded and delete_live_after_backup is enabled.
     *
     * @param  array<int, mixed>  $keepPaths  Still-referenced live gallery paths
     */
    public function trash(string $path, array $keepPaths = []): ?string
    {
        $safe = $this->retention->filterDeletesAgainstCommitted([$path], $keepPaths);
        if ($safe === []) {
            return null;
        }

        $from = $this->pathResolver->normalizePath($path);
        if ($from === '' || $this->isTrashPath($from)) {
            return null;
        }

        $to = $this->backup($from);
        if ($to === null) {
            // Never delete live media without a successful backup copy.
            return null;
        }

        if ($this->deleteLiveAfterBackupEnabled()) {
            media_delete($from);
        }

        return $to;
    }

    /**
     * @param  array<int, mixed>  $paths
     * @param  array<int, mixed>  $keepPaths
     * @return array<int, string> Trash destinations written
     */
    public function trashMany(array $paths, array $keepPaths = []): array
    {
        $trashed = [];

        foreach (array_unique($this->retention->stringifyPaths($paths)) as $path) {
            $destination = $this->trash($path, $keepPaths);
            if ($destination !== null) {
                $trashed[] = $destination;
            }
        }

        return $trashed;
    }

    /**
     * @return array<int, array{path: string, basename: string, date: string|null}>
     */
    public function listForEntity(string $listingFolder, int $entityId): array
    {
        $directory = $this->trashRoot() . '/' . trim($listingFolder, '/') . '/' . $entityId;
        $items = [];

        foreach ($this->listRelativeFiles($directory) as $path) {
            $items[] = [
                'path' => $path,
                'basename' => basename($path),
                'date' => $this->dateFromTrashPath($path),
            ];
        }

        usort($items, static function (array $a, array $b): int {
            return strcmp((string) $b['date'], (string) $a['date']);
        });

        return $items;
    }

    /**
     * Copy a trash file back to a live directory (trash copy is kept until purge).
     */
    public function restoreToDirectory(string $trashPath, string $targetDirectory): ?string
    {
        $from = $this->pathResolver->normalizePath($trashPath);
        if (! $this->isTrashPath($from) || ! $this->pathResolver->exists($from)) {
            return null;
        }

        $targetDirectory = trim($targetDirectory, '/');
        $to = $targetDirectory . '/' . basename($from);
        $contents = $this->pathResolver->read($from);
        if ($contents === '') {
            return null;
        }

        $written = $this->writeStorageResolver->forUploads()->write($to, $contents, [
            'visibility' => config('media_storage.object_visibility', 'public'),
        ]);

        if (! $written) {
            return null;
        }

        $this->pathResolver->forgetExistsCache($to);

        return $to;
    }

    /**
     * Restore missing gallery files from trash (matched by basename).
     *
     * @param  array<int, mixed>  $galleryPaths
     * @return array{restored: array<int, string>, gallery: array<int, string>}
     */
    public function restoreMissingGallery(
        array $galleryPaths,
        string $listingFolder,
        int $entityId,
        string $liveDirectory,
    ): array {
        $gallery = $this->retention->stringifyPaths($galleryPaths);
        $trashByBasename = [];
        foreach ($this->listForEntity($listingFolder, $entityId) as $item) {
            if (! isset($trashByBasename[$item['basename']])) {
                $trashByBasename[$item['basename']] = $item['path'];
            }
        }

        $restored = [];
        $updatedGallery = [];

        foreach ($gallery as $path) {
            if (media_exists($path)) {
                $updatedGallery[] = $path;
                continue;
            }

            $basename = basename($path);
            if (! isset($trashByBasename[$basename])) {
                $updatedGallery[] = $path;
                continue;
            }

            $livePath = $this->restoreToDirectory($trashByBasename[$basename], $liveDirectory);
            if ($livePath !== null) {
                $updatedGallery[] = $livePath;
                $restored[] = $livePath;
            } else {
                $updatedGallery[] = $path;
            }
        }

        if ($gallery === [] && $trashByBasename !== []) {
            foreach ($trashByBasename as $trashPath) {
                $livePath = $this->restoreToDirectory($trashPath, $liveDirectory);
                if ($livePath !== null) {
                    $updatedGallery[] = $livePath;
                    $restored[] = $livePath;
                }
            }
        }

        return [
            'restored' => $restored,
            'gallery' => array_values(array_unique($updatedGallery)),
        ];
    }

    public function isExpiredTrashPath(string $relativePath, \DateTimeInterface $cutoff): bool
    {
        $date = $this->dateFromTrashPath($relativePath);
        if ($date === null) {
            return false;
        }

        try {
            $fileDate = new \DateTimeImmutable($date);
        } catch (\Exception) {
            return false;
        }

        return $fileDate < \DateTimeImmutable::createFromInterface($cutoff)->setTime(0, 0);
    }

    /**
     * Whether a trash file may be purged.
     * Always protects the newest `keepDates` backup days for that entity.
     */
    public function shouldPurgeTrashPath(
        string $relativePath,
        \DateTimeInterface $cutoff,
        array $protectedDatesByEntity,
    ): bool {
        if (! $this->isTrashPath($relativePath)) {
            return false;
        }

        $entityKey = $this->entityKeyFromTrashPath($relativePath);
        $date = $this->dateFromTrashPath($relativePath);
        if ($entityKey === null || $date === null) {
            return false;
        }

        $protected = $protectedDatesByEntity[$entityKey] ?? [];
        if (in_array($date, $protected, true)) {
            return false;
        }

        return $this->isExpiredTrashPath($relativePath, $cutoff);
    }

    /**
     * Build map of entityKey => newest keepDates date strings.
     *
     * @param  array<int, string>  $trashPaths
     * @return array<string, array<int, string>>
     */
    public function protectedDatesByEntity(array $trashPaths, ?int $keepDates = null): array
    {
        $keepDates ??= $this->keepDates();
        $datesByEntity = [];

        foreach ($trashPaths as $path) {
            $entityKey = $this->entityKeyFromTrashPath($path);
            $date = $this->dateFromTrashPath($path);
            if ($entityKey === null || $date === null) {
                continue;
            }
            $datesByEntity[$entityKey][$date] = true;
        }

        $protected = [];
        foreach ($datesByEntity as $entityKey => $dateSet) {
            $dates = array_keys($dateSet);
            rsort($dates, SORT_STRING);
            $protected[$entityKey] = array_values(array_slice($dates, 0, $keepDates));
        }

        return $protected;
    }

    /**
     * Permanently delete expired trash backups while always keeping the last
     * `keep_dates` backup days per entity.
     *
     * @return array{purged: int, skipped_protected: int, skipped_retention: int}
     */
    public function purgeExpired(?int $days = null, ?int $keepDates = null, bool $dryRun = false): array
    {
        $days ??= $this->retentionDays();
        $keepDates ??= $this->keepDates();
        $cutoff = now()->subDays($days);
        $allTrashPaths = $this->listRelativeFiles($this->trashRoot());
        $protected = $this->protectedDatesByEntity($allTrashPaths, $keepDates);

        $purged = 0;
        $skippedProtected = 0;
        $skippedRetention = 0;

        foreach ($allTrashPaths as $path) {
            $entityKey = $this->entityKeyFromTrashPath($path);
            $date = $this->dateFromTrashPath($path);
            if ($entityKey === null || $date === null) {
                continue;
            }

            if (in_array($date, $protected[$entityKey] ?? [], true)) {
                $skippedProtected++;
                continue;
            }

            if (! $this->isExpiredTrashPath($path, $cutoff)) {
                $skippedRetention++;
                continue;
            }

            if ($dryRun) {
                $purged++;
                continue;
            }

            if (media_delete($path)) {
                $purged++;
            }
        }

        return [
            'purged' => $purged,
            'skipped_protected' => $skippedProtected,
            'skipped_retention' => $skippedRetention,
        ];
    }

    private function uniqueTrashPath(string $desired): string
    {
        if (! $this->pathResolver->exists($desired)) {
            return $desired;
        }

        $directory = trim(dirname($desired), '.');
        $filename = pathinfo($desired, PATHINFO_FILENAME);
        $extension = pathinfo($desired, PATHINFO_EXTENSION);
        $suffix = now()->format('His');
        $candidate = $directory . '/' . $filename . '_' . $suffix . ($extension !== '' ? '.' . $extension : '');

        return $candidate;
    }

    public function dateFromTrashPath(string $path): ?string
    {
        $normalized = $this->pathResolver->normalizePath($path);
        $root = preg_quote($this->trashRoot(), '/');
        if (preg_match('/^' . $root . '\/[^\/]+\/[^\/]+\/(\d{4}-\d{2}-\d{2})\//', $normalized, $matches) !== 1) {
            return null;
        }

        return $matches[1];
    }

    /**
     * Entity key like "camps/28" from `_trash/camps/28/2026-09-10/file.webp`.
     */
    public function entityKeyFromTrashPath(string $path): ?string
    {
        $normalized = $this->pathResolver->normalizePath($path);
        $root = preg_quote($this->trashRoot(), '/');
        if (preg_match('/^' . $root . '\/([^\/]+\/[^\/]+)\//', $normalized, $matches) !== 1) {
            return null;
        }

        return $matches[1];
    }

    /**
     * @return array<int, string>
     */
    private function listRelativeFiles(string $relativeDirectory): array
    {
        $relativeDirectory = trim($relativeDirectory, '/');
        $diskName = $this->writeStorageResolver->usesObjectStorage()
            ? (string) config('media_storage.disk', 'do_spaces')
            : (string) config('media_storage.local_disk', 'public');

        $directory = $this->writeStorageResolver->usesObjectStorage()
            ? $this->environmentResolver->applyBucketPrefix($relativeDirectory)
            : $relativeDirectory;

        try {
            $files = Storage::disk($diskName)->allFiles($directory);
        } catch (\Throwable $e) {
            Log::debug('MediaTrashService list failed', [
                'directory' => $directory,
                'error' => $e->getMessage(),
            ]);

            return [];
        }

        return array_values(array_filter(array_map(
            fn ($path) => $this->environmentResolver->stripBucketPrefix((string) $path),
            $files
        )));
    }
}
