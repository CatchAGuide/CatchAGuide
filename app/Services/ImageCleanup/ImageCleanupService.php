<?php

namespace App\Services\ImageCleanup;

use App\Models\Accommodation;
use App\Models\Camp;
use App\Models\CategoryPage;
use App\Models\City;
use App\Models\Country;
use App\Models\Destination;
use App\Models\Guiding;
use App\Models\GuideThread;
use App\Models\Media;
use App\Models\Region;
use App\Models\RentalBoat;
use App\Models\SpecialOffer;
use App\Models\Thread;
use App\Models\Vacation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * Service to collect image paths from models, check existence on disk,
 * fix DB references to missing files, and optionally delete orphan files.
 *
 * Paths are stored in various formats: relative to public/ or storage (e.g. "guidings-images/x.webp").
 * Files may exist in Storage::disk('public') and/or public_path(). We check both.
 */
class ImageCleanupService
{
    /** @var array<string, array{model: string, thumbnail_path: string|null, gallery_field: string|null}> */
    protected static array $modelConfig = [
        'vacation' => [
            'model' => Vacation::class,
            'thumbnail_path' => null,
            'gallery_field' => 'gallery',
        ],
        'guiding' => [
            'model' => Guiding::class,
            'thumbnail_path' => 'thumbnail_path',
            'gallery_field' => 'gallery_images',
        ],
        'accommodation' => [
            'model' => Accommodation::class,
            'thumbnail_path' => 'thumbnail_path',
            'gallery_field' => 'gallery_images',
        ],
        'camp' => [
            'model' => Camp::class,
            'thumbnail_path' => 'thumbnail_path',
            'gallery_field' => 'gallery_images',
        ],
        'rental_boat' => [
            'model' => RentalBoat::class,
            'thumbnail_path' => 'thumbnail_path',
            'gallery_field' => 'gallery_images',
        ],
        'special_offer' => [
            'model' => SpecialOffer::class,
            'thumbnail_path' => 'thumbnail_path',
            'gallery_field' => 'gallery_images',
        ],
        'region' => [
            'model' => Region::class,
            'thumbnail_path' => 'thumbnail_path',
            'gallery_field' => null,
        ],
        'country' => [
            'model' => Country::class,
            'thumbnail_path' => 'thumbnail_path',
            'gallery_field' => null,
        ],
        'city' => [
            'model' => City::class,
            'thumbnail_path' => 'thumbnail_path',
            'gallery_field' => null,
        ],
        'destination' => [
            'model' => Destination::class,
            'thumbnail_path' => 'thumbnail_path',
            'gallery_field' => null,
        ],
        'category_page' => [
            'model' => CategoryPage::class,
            'thumbnail_path' => 'thumbnail_path',
            'gallery_field' => null,
        ],
        'thread' => [
            'model' => Thread::class,
            'thumbnail_path' => 'thumbnail_path',
            'gallery_field' => null,
        ],
        'guide_thread' => [
            'model' => GuideThread::class,
            'thumbnail_path' => 'thumbnail_path',
            'gallery_field' => null,
        ],
    ];

    /** Directories we consider for orphan file scan. Subdirs included. */
    protected static array $knownImageDirectories = [
        'vacations-images',
        'guidings-images',
        'accommodations',
        'camps',
        'rental-boats',
        'special-offers',
        'blog',
        'newblog',
        'category',
    ];

    public function normalizePath(string $path): string
    {
        $path = trim($path);
        $path = str_replace('//', '/', $path);
        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8);
        }
        if (str_starts_with($path, 'public/')) {
            $path = substr($path, 7);
        }

        return $path;
    }

    /**
     * Check if file exists. Tries both Storage::disk('public') and public_path()
     * since uploads may be in either (or both).
     */
    public function pathExists(string $path): bool
    {
        $n = $this->normalizePath($path);
        if ($n === '') {
            return false;
        }

        if (Storage::disk('public')->exists($n)) {
            return true;
        }
        $pub = public_path($n);
        return is_file($pub) && file_exists($pub);
    }

    /**
     * Collect all referenced image paths from enabled models.
     *
     * @param array<string>|null $models  Model keys (e.g. ['guiding','vacation']). Null = all.
     * @return array<int, array{model: string, id: int, field: string, path: string, normalized: string}>
     */
    public function collectReferencedPaths(?array $models = null): array
    {
        $allowed = $models !== null ? array_map('strtolower', $models) : null;
        $refs = [];

        foreach (self::$modelConfig as $key => $config) {
            if ($allowed !== null && !in_array(strtolower($key), $allowed, true)) {
                continue;
            }

            $modelClass = $config['model'];
            $thumbField = $config['thumbnail_path'];
            $galleryField = $config['gallery_field'];

            $query = $modelClass::query()->where(function ($q) use ($thumbField, $galleryField) {
                if ($thumbField) {
                    $q->orWhereNotNull($thumbField);
                }
                if ($galleryField) {
                    $q->orWhereNotNull($galleryField);
                }
            });

            $query->select(['id', ...array_filter([$thumbField, $galleryField])]);

            foreach ($query->get() as $row) {
                $id = (int) $row->id;

                if ($thumbField && !empty($row->{$thumbField})) {
                    $path = is_string($row->{$thumbField}) ? $row->{$thumbField} : (string) $row->{$thumbField};
                    $refs[] = [
                        'model' => $key,
                        'id' => $id,
                        'field' => 'thumbnail_path',
                        'path' => $path,
                        'normalized' => $this->normalizePath($path),
                    ];
                }

                if ($galleryField && $row->{$galleryField} !== null) {
                    $arr = $row->{$galleryField};
                    if (is_string($arr)) {
                        $decoded = json_decode($arr, true);
                        $arr = is_array($decoded) ? $decoded : [];
                    }
                    if (!is_array($arr)) {
                        continue;
                    }
                    foreach ($arr as $item) {
                        if ($item === null || $item === '') {
                            continue;
                        }
                        $path = is_string($item) ? $item : (string) $item;
                        $refs[] = [
                            'model' => $key,
                            'id' => $id,
                            'field' => $galleryField,
                            'path' => $path,
                            'normalized' => $this->normalizePath($path),
                        ];
                    }
                }
            }
        }

        $this->addMediaPaths($refs, $allowed);
        return $refs;
    }

    /**
     * Add paths from media table (Guiding thumbnail_id + guiding_gallery_media).
     */
    protected function addMediaPaths(array &$refs, ?array $allowed): void
    {
        if ($allowed !== null && !in_array('guiding', $allowed, true)) {
            return;
        }

        $mediaIds = [];

        $thumbIds = Guiding::whereNotNull('thumbnail_id')->pluck('thumbnail_id', 'id');
        foreach ($thumbIds as $guidingId => $mediaId) {
            $mediaIds[(int) $mediaId] = (int) $guidingId;
        }

        $galleryMedia = DB::table('guiding_gallery_media')->get();
        foreach ($galleryMedia as $row) {
            $mediaIds[(int) $row->media_id] = (int) $row->guiding_id;
        }

        if (empty($mediaIds)) {
            return;
        }

        $media = Media::whereIn('id', array_keys($mediaIds))->get(['id', 'file_path']);
        foreach ($media as $m) {
            if (empty($m->file_path)) {
                continue;
            }
            $path = $m->file_path;
            $n = $this->normalizePath($path);
            $refs[] = [
                'model' => 'guiding',
                'id' => $mediaIds[(int) $m->id],
                'field' => 'media',
                'path' => $path,
                'normalized' => $n,
            ];
        }
    }

    /**
     * Run report: referenced paths, which exist, which are missing.
     *
     * @param array<string>|null $models
     * @return array{referenced: array, referenced_unique: array<string>, missing: array, existing_count: int, missing_count: int}
     */
    public function runReport(?array $models = null): array
    {
        $refs = $this->collectReferencedPaths($models);
        $referencedUnique = [];
        $missing = [];
        $existingCount = 0;

        foreach ($refs as $r) {
            $n = $r['normalized'];
            if ($n === '') {
                continue;
            }
            $referencedUnique[$n] = true;

            if ($this->pathExists($r['path'])) {
                $existingCount++;
            } else {
                $missing[] = $r;
            }
        }

        return [
            'referenced' => $refs,
            'referenced_unique' => array_keys($referencedUnique),
            'missing' => $missing,
            'existing_count' => $existingCount,
            'missing_count' => count($missing),
        ];
    }

    /**
     * Fix DB references: remove refs to missing files (set thumbnail null, filter gallery arrays).
     *
     * @param array{models?: array<string>, dry_run?: bool} $options
     * @return array{fixed: array, errors: array}
     */
    public function fixDbRefs(array $options = []): array
    {
        $models = $options['models'] ?? null;
        $dryRun = (bool) ($options['dry_run'] ?? true);

        $report = $this->runReport($models);
        $missing = $report['missing'];
        $fixed = [];
        $errors = [];

        $byRecord = [];
        foreach ($missing as $m) {
            $k = $m['model'] . '|' . $m['id'] . '|' . $m['field'];
            if (!isset($byRecord[$k])) {
                $byRecord[$k] = ['model' => $m['model'], 'id' => $m['id'], 'field' => $m['field'], 'paths' => []];
            }
            $byRecord[$k]['paths'][] = $m['path'];
        }

        $order = ['thumbnail_path' => 0, 'media' => 1, 'gallery_images' => 2, 'gallery' => 2];
        uasort($byRecord, function ($a, $b) use ($order) {
            $oa = $order[$a['field']] ?? 3;
            $ob = $order[$b['field']] ?? 3;
            if ($oa !== $ob) {
                return $oa <=> $ob;
            }
            return ($a['model'] . '|' . $a['id']) <=> ($b['model'] . '|' . $b['id']);
        });

        $config = self::$modelConfig;
        foreach ($byRecord as $k => $rec) {
            $modelKey = $rec['model'];
            $id = $rec['id'];
            $field = $rec['field'];
            $pathsToRemove = array_unique($rec['paths']);

            if (!isset($config[$modelKey])) {
                continue;
            }

            $modelClass = $config[$modelKey]['model'];
            $instance = $modelClass::find($id);
            if (!$instance) {
                $errors[] = "Record not found: {$modelKey}#{$id}";
                continue;
            }

            if ($field === 'media') {
                $thumbId = Guiding::where('id', $id)->value('thumbnail_id');
                $galleryMediaIds = DB::table('guiding_gallery_media')->where('guiding_id', $id)->pluck('media_id')->all();
                $mediaIds = array_filter(array_unique(array_merge(
                    $thumbId ? [(int) $thumbId] : [],
                    array_map('intval', $galleryMediaIds)
                )));
                $candidates = Media::whereIn('id', $mediaIds)->get(['id', 'file_path']);
                $normalizedRemove = array_map([$this, 'normalizePath'], $pathsToRemove);
                $toUnlink = [];
                foreach ($candidates as $media) {
                    if ($media->file_path === '' || $media->file_path === null) {
                        continue;
                    }
                    $n = $this->normalizePath($media->file_path);
                    if (in_array($media->file_path, $pathsToRemove, true) || in_array($n, $normalizedRemove, true)) {
                        $toUnlink[] = $media;
                    }
                }
                if (empty($toUnlink)) {
                    continue;
                }
                if (!$dryRun) {
                    try {
                        DB::transaction(function () use ($instance, $toUnlink) {
                            foreach ($toUnlink as $media) {
                                $instance->gallery_media()->where('media_id', $media->id)->delete();
                                if ((int) $instance->thumbnail_id === (int) $media->id) {
                                    $instance->update(['thumbnail_id' => null]);
                                }
                                $media->delete();
                            }
                        });
                    } catch (\Throwable $e) {
                        $errors[] = "Failed to remove media for guiding#{$id}: " . $e->getMessage();
                        Log::warning('ImageCleanup: fix media', ['error' => $e->getMessage(), 'guiding_id' => $id]);
                        continue;
                    }
                }
                foreach ($toUnlink as $media) {
                    $fixed[] = ['model' => $modelKey, 'id' => $id, 'field' => 'media', 'path' => $media->file_path];
                }
                continue;
            }

            if ($field === 'thumbnail_path') {
                if (!$dryRun) {
                    try {
                        DB::transaction(function () use ($instance, $modelKey) {
                            $instance->update(['thumbnail_path' => null]);
                        });
                    } catch (\Throwable $e) {
                        $errors[] = "Failed to clear thumbnail {$modelKey}#{$id}: " . $e->getMessage();
                        Log::warning('ImageCleanup: fix thumbnail', ['error' => $e->getMessage(), 'model' => $modelKey, 'id' => $id]);
                        continue;
                    }
                }
                $fixed[] = ['model' => $modelKey, 'id' => $id, 'field' => 'thumbnail_path', 'path' => $pathsToRemove[0] ?? ''];
                continue;
            }

            $galleryField = $config[$modelKey]['gallery_field'] ?? null;
            if ($galleryField === null) {
                continue;
            }

            $current = $instance->{$galleryField};
            if (is_string($current)) {
                $current = json_decode($current, true) ?? [];
            }
            if (!is_array($current)) {
                continue;
            }

            $normalizedRemove = array_map([$this, 'normalizePath'], $pathsToRemove);
            $keep = [];
            foreach ($current as $p) {
                $s = is_string($p) ? $p : (string) $p;
                if ($s === '') {
                    continue;
                }
                if (!in_array($this->normalizePath($s), $normalizedRemove, true)) {
                    $keep[] = $p;
                }
            }

            $thumbField = $config[$modelKey]['thumbnail_path'] ?? null;
            $changeThumb = false;
            $newThumbVal = null;
            if ($thumbField && !empty($instance->{$thumbField})) {
                $t = $instance->{$thumbField};
                $tn = $this->normalizePath(is_string($t) ? $t : (string) $t);
                if (in_array($tn, $normalizedRemove, true)) {
                    $changeThumb = true;
                    $newThumbVal = !empty($keep) ? $keep[0] : null;
                }
            }

            if (!$dryRun) {
                try {
                    DB::transaction(function () use ($instance, $galleryField, $keep, $thumbField, $changeThumb, $newThumbVal) {
                        $up = [$galleryField => $keep];
                        if ($thumbField !== null && $changeThumb) {
                            $up[$thumbField] = $newThumbVal;
                        }
                        $instance->update($up);
                    });
                } catch (\Throwable $e) {
                    $errors[] = "Failed to update gallery {$modelKey}#{$id}: " . $e->getMessage();
                    Log::warning('ImageCleanup: fix gallery', ['error' => $e->getMessage(), 'model' => $modelKey, 'id' => $id]);
                    continue;
                }
            }

            $fixed[] = ['model' => $modelKey, 'id' => $id, 'field' => $galleryField, 'paths_removed' => $pathsToRemove];
        }

        return ['fixed' => $fixed, 'errors' => $errors];
    }

    /**
     * Scan known image directories for files not in referenced set. Optionally delete (with backup).
     *
     * @param array{models?: array<string>, dry_run?: bool, backup?: bool} $options
     * @return array{orphans: array<string>, deleted: array<string>, backed_up: array<string>, errors: array<string>}
     */
    public function deleteOrphanFiles(array $options = []): array
    {
        $models = $options['models'] ?? null;
        $dryRun = (bool) ($options['dry_run'] ?? true);
        $backup = (bool) ($options['backup'] ?? false);

        $report = $this->runReport($models);
        $referencedSet = array_flip($report['referenced_unique']);
        $orphans = [];
        $deleted = [];
        $backedUp = [];
        $errors = [];

        $storageRoot = Storage::disk('public')->path('');
        $publicRoot = public_path('');

        foreach (self::$knownImageDirectories as $dir) {
            $this->scanDirectoryForOrphans($dir, $storageRoot, $referencedSet, $orphans);
            $this->scanDirectoryForOrphans($dir, $publicRoot, $referencedSet, $orphans);
        }

        $orphans = array_unique($orphans);
        sort($orphans);

        $backupDir = $backup ? storage_path('app/image-cleanup-backups/' . date('Y-m-d_His')) : null;
        if ($backup && !empty($orphans) && !is_dir($backupDir)) {
            @mkdir($backupDir, 0755, true);
        }

        foreach ($orphans as $relPath) {
            $normalized = $this->normalizePath($relPath);
            if (isset($referencedSet[$normalized])) {
                continue;
            }

            $deletedPath = null;
            if (Storage::disk('public')->exists($normalized)) {
                $deletedPath = Storage::disk('public')->path($normalized);
            } elseif (is_file(public_path($normalized))) {
                $deletedPath = public_path($normalized);
            }

            if ($deletedPath === null || !is_file($deletedPath)) {
                continue;
            }

            if ($backup && $backupDir) {
                $dest = $backupDir . '/' . str_replace(['/', '\\'], '_', $normalized);
                if (@copy($deletedPath, $dest)) {
                    $backedUp[] = $normalized;
                }
            }

            if (!$dryRun) {
                try {
                    if (function_exists('media_delete')) {
                        media_delete($normalized);
                    } else {
                        if (Storage::disk('public')->exists($normalized)) {
                            Storage::disk('public')->delete($normalized);
                        }
                        $pub = public_path($normalized);
                        if (is_file($pub)) {
                            @unlink($pub);
                        }
                    }
                    $deleted[] = $normalized;
                } catch (\Throwable $e) {
                    $errors[] = "Failed to delete {$normalized}: " . $e->getMessage();
                    Log::warning('ImageCleanup: delete orphan', ['path' => $normalized, 'error' => $e->getMessage()]);
                }
            } else {
                $deleted[] = $normalized;
            }
        }

        return [
            'orphans' => $orphans,
            'deleted' => $deleted,
            'backed_up' => $backedUp,
            'errors' => $errors,
        ];
    }

    protected function scanDirectoryForOrphans(string $dir, string $root, array $referencedSet, array &$orphans): void
    {
        $base = rtrim($root, '/\\') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $dir);
        if (!is_dir($base)) {
            return;
        }

        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($base, \RecursiveDirectoryIterator::SKIP_DOTS | \RecursiveDirectoryIterator::FOLLOW_SYMLINKS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $rootLen = strlen(rtrim($root, '/\\') . DIRECTORY_SEPARATOR);
        foreach ($it as $fi) {
            if (!$fi->isFile()) {
                continue;
            }
            $path = $fi->getPathname();
            $ext = strtolower($fi->getExtension());
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                continue;
            }

            $rel = str_replace('\\', '/', substr($path, $rootLen));
            $norm = $this->normalizePath($rel);
            if ($norm !== '' && !isset($referencedSet[$norm])) {
                $orphans[] = $norm;
            }
        }
    }

    public static function getModelKeys(): array
    {
        return array_keys(self::$modelConfig);
    }
}
