<?php

namespace App\Console\Commands;

use App\Models\Accommodation;
use App\Models\Camp;
use App\Models\Guiding;
use App\Models\RentalBoat;
use App\Models\SpecialOffer;
use App\Models\Trip;
use App\Models\Vacation;
use App\Services\Media\MediaEnvironmentResolver;
use App\Services\Media\MediaPathResolver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\UnableToCheckFileExistence;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class SyncListingMediaByIdCommand extends Command
{
  protected $signature = 'media:sync-listing-id
                            {listing : listing key or local root (guiding, accommodation, guidings, accommodations, ...)}
                            {id : Entity ID (positive integer)}
                            {--prefix= : Bucket folder prefix (production|staging). Default: APP_ENV prefix}
                            {--base= : Local root to resolve directories (default: public/)}
                            {--roots=* : Specific local root folders under --base (e.g. guidings, assets/images/guidings). Default: all matching roots from config}
                            {--skip-existing : Do not overwrite files already in the bucket}
                            {--dry-run : Show what would be uploaded}
                            {--limit=0 : Max files to upload (0 = all)}';

  protected $description = 'Upload all local listing images for one entity id to object storage';

  /** @var array<string, array{model: class-string, paths: array<int, string>}> */
  private const LISTING_ENTITIES = [
    'guiding' => [
      'model' => Guiding::class,
      'paths' => ['thumbnail_path', 'gallery_images'],
    ],
    'vacation' => [
      'model' => Vacation::class,
      'paths' => ['gallery'],
    ],
    'accommodation' => [
      'model' => Accommodation::class,
      'paths' => ['thumbnail_path', 'gallery_images'],
    ],
    'camp' => [
      'model' => Camp::class,
      'paths' => ['thumbnail_path', 'gallery_images'],
    ],
    'rental_boat' => [
      'model' => RentalBoat::class,
      'paths' => ['thumbnail_path', 'gallery_images'],
    ],
    'special_offer' => [
      'model' => SpecialOffer::class,
      'paths' => ['thumbnail_path', 'gallery_images'],
    ],
    'trip' => [
      'model' => Trip::class,
      'paths' => ['thumbnail_path', 'gallery_images'],
    ],
  ];

  public function handle(MediaEnvironmentResolver $envResolver, MediaPathResolver $pathResolver): int
  {
    if ((string) config('media_storage.disk') === (string) config('media_storage.local_disk')) {
      $this->error('MEDIA_STORAGE_DISK is set to local. Configure DO_SPACES_* in .env first.');

      return self::FAILURE;
    }

    $listingArg = (string) $this->argument('listing');
    $id = (int) $this->argument('id');

    if ($id <= 0) {
      $this->error('The id must be a positive integer.');

      return self::FAILURE;
    }

    $listingKey = $this->resolveListingKey($listingArg);
    if ($listingKey === null) {
      $this->error("Unknown listing type: [{$listingArg}]. Expected listing keys like guiding, accommodation, etc.");

      return self::FAILURE;
    }

    $bucketPrefix = (string) ($this->option('prefix') ?: $envResolver->bucketPrefix());
    $basePath = rtrim((string) ($this->option('base') ?: public_path()), '/\\');
    $roots = $this->resolveRoots($listingKey);

    if ($roots === []) {
      $this->error("No local roots found for listing [{$listingKey}].");

      return self::FAILURE;
    }

    $disk = Storage::disk((string) config('media_storage.disk'));
    $localStorage = app('media.local_storage');
    $objectStorage = app('media.object_storage');
    $dryRun = (bool) $this->option('dry-run');
    $skipExisting = (bool) $this->option('skip-existing');
    $limit = (int) $this->option('limit');
    $visibility = (string) config('media_storage.object_visibility', 'public');

    $this->info("Listing: {$listingKey} (#{$id})");
    $this->info("Local base: {$basePath}");
    $this->info("Bucket prefix: {$bucketPrefix}/");
    $this->newLine();
    $this->line('Roots to scan: ' . implode(', ', $roots));
    $this->newLine();

    $entity = $this->loadEntity($listingKey, $id);
    if ($entity === null) {
      $this->warn("No {$listingKey} record found with id {$id}. Will only scan local directories.");
      $this->newLine();
    }

    $relativePaths = $this->collectRelativePaths($roots, $basePath, $id, $entity, $listingKey, $pathResolver);
    if ($relativePaths === []) {
      $this->warn('No image paths found from local directories or database fields.');

      return self::SUCCESS;
    }

    $this->info('Found ' . count($relativePaths) . ' unique path(s) to sync.');
    $this->newLine();

    $uploaded = 0;
    $skipped = 0;
    $missingLocal = 0;
    $failed = 0;
    $existenceCheckWarnings = 0;

    $bar = $this->output->createProgressBar(count($relativePaths));
    $bar->start();

    foreach ($relativePaths as $relativePath) {
      if ($limit > 0 && $uploaded + $failed >= $limit) {
        break;
      }

      $objectKey = $envResolver->applyBucketPrefix($relativePath);

      if ($skipExisting && $this->objectExistsOnRemote($objectStorage, $relativePath, $disk, $objectKey, $existenceCheckWarnings)) {
        $skipped++;
        $bar->advance();
        continue;
      }

      if (! $localStorage->exists($relativePath)) {
        $missingLocal++;
        $bar->advance();
        continue;
      }

      if ($dryRun) {
        $uploaded++;
        $bar->advance();
        continue;
      }

      try {
        $contents = $localStorage->read($relativePath);
        if ($contents === '') {
          $missingLocal++;
          $bar->advance();
          continue;
        }

        $objectStorage->write($relativePath, $contents, ['visibility' => $visibility]);
        $pathResolver->forgetExistsCache($relativePath);
        $uploaded++;
      } catch (\Throwable $e) {
        $failed++;
        $this->newLine();
        $this->warn("  Failed: {$objectKey} — {$e->getMessage()}");
      }

      $bar->advance();
    }

    $bar->finish();
    $this->newLine(2);

    $this->info(sprintf(
      'Done. Uploaded: %d, skipped (exists): %d, missing locally: %d, failed: %d%s',
      $uploaded,
      $skipped,
      $missingLocal,
      $failed,
      $dryRun ? ' (dry run)' : ''
    ));

    if ($existenceCheckWarnings > 0) {
      $this->warn("Existence checks failed {$existenceCheckWarnings} time(s) (network/DNS). Those files were uploaded instead of skipped.");
    }

    if (! $dryRun && $uploaded > 0) {
      $this->line('Run media:make-objects-public --prefix=' . $bucketPrefix . ' if any files need public ACL.');
    }

    return $failed > 0 ? self::FAILURE : self::SUCCESS;
  }

  private function resolveListingKey(string $listingArg): ?string
  {
    $listingArg = trim($listingArg);
    if ($listingArg === '') {
      return null;
    }

    $listingFolders = (array) config('media_storage.listing_folders', []);
    if (array_key_exists($listingArg, $listingFolders)) {
      return $listingArg;
    }

    $directories = (array) config('media_storage.directories', []);
    if (isset($directories[$listingArg]) && is_string($directories[$listingArg]) && $directories[$listingArg] !== '') {
      return (string) $directories[$listingArg];
    }

    return null;
  }

  /**
   * @return array<int, string>
   */
  private function resolveRoots(string $listingKey): array
  {
    $rootsOption = (array) $this->option('roots');
    $roots = [];

    if ($rootsOption !== []) {
      foreach ($rootsOption as $root) {
        $root = trim((string) $root);
        if ($root !== '') {
          $roots[] = $root;
        }
      }

      return array_values(array_unique($roots));
    }

    foreach ((array) config('media_storage.directories', []) as $root => $mappedListingKey) {
      if ($mappedListingKey === $listingKey) {
        $roots[] = (string) $root;
      }
    }

    usort($roots, fn (string $a, string $b): int => strlen($b) <=> strlen($a));

    return array_values(array_unique($roots));
  }

  private function loadEntity(string $listingKey, int $id): ?object
  {
    $config = self::LISTING_ENTITIES[$listingKey] ?? null;
    if ($config === null) {
      return null;
    }

    /** @var class-string $modelClass */
    $modelClass = $config['model'];

    return $modelClass::query()->find($id);
  }

  /**
   * @param  array<int, string>  $roots
   * @return array<int, string>
   */
  private function collectRelativePaths(
    array $roots,
    string $basePath,
    int $id,
    ?object $entity,
    string $listingKey,
    MediaPathResolver $pathResolver,
  ): array {
    $paths = [];

    foreach ($roots as $root) {
      $localIdDir = $this->resolveLocalIdDirectory($basePath, $root, $id);

      if (! is_dir($localIdDir)) {
        $this->line("No id folder: {$localIdDir}");
        continue;
      }

      $localIdDirReal = realpath($localIdDir) ?: $localIdDir;
      $bucketFolder = trim($root, '/\\');

      $this->info("Scanning id folder: {$localIdDirReal}");

      foreach ($this->collectFiles($localIdDirReal) as $absolutePath) {
        $filename = ltrim(str_replace('\\', '/', substr($absolutePath, strlen($localIdDirReal))), '/');
        $paths[] = $bucketFolder . '/' . $id . '/' . $filename;
      }
    }

    if ($entity !== null) {
      $dbPaths = $this->collectDatabasePaths($entity, $listingKey, $pathResolver);
      if ($dbPaths !== []) {
        $this->info('Database paths: ' . count($dbPaths));
        $paths = array_merge($paths, $dbPaths);
      }
    }

    $normalized = [];
    foreach ($paths as $path) {
      $clean = $pathResolver->normalizePath($path);
      if ($clean !== '') {
        $normalized[$clean] = $clean;
      }
    }

    ksort($normalized);

    return array_values($normalized);
  }

  /**
   * @return array<int, string>
   */
  private function collectDatabasePaths(object $entity, string $listingKey, MediaPathResolver $pathResolver): array
  {
    $config = self::LISTING_ENTITIES[$listingKey] ?? null;
    if ($config === null) {
      return [];
    }

    $paths = [];

    foreach ($config['paths'] as $field) {
      $value = $entity->{$field} ?? null;
      foreach ($this->extractPathValues($value) as $path) {
        $normalized = $pathResolver->normalizePath($path);
        if ($normalized !== '' && ! $pathResolver->isRemoteUrl($path)) {
          $paths[] = $normalized;
        }
      }
    }

    return array_values(array_unique($paths));
  }

  /**
   * @return array<int, string>
   */
  private function extractPathValues(mixed $value): array
  {
    if ($value === null || $value === '') {
      return [];
    }

    if (is_string($value)) {
      $decoded = json_decode($value, true);
      if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        return $this->extractPathValues($decoded);
      }

      if (filter_var($value, FILTER_VALIDATE_URL)) {
        $parsed = parse_url($value);
        $path = isset($parsed['path']) ? ltrim((string) $parsed['path'], '/') : '';

        return $path !== '' ? [$path] : [];
      }

      return [$value];
    }

    if (! is_array($value)) {
      return [];
    }

    $paths = [];
    foreach ($value as $item) {
      if (is_string($item) && $item !== '') {
        $paths[] = $item;
        continue;
      }

      if (is_array($item)) {
        foreach (['path', 'url', 'image', 'src', 'thumbnail_path'] as $key) {
          if (! empty($item[$key]) && is_string($item[$key])) {
            $paths[] = $item[$key];
          }
        }
      }
    }

    return $paths;
  }

  private function resolveLocalIdDirectory(string $basePath, string $root, int $id): string
  {
    $root = trim($root, '/\\');

    return rtrim($basePath, '/\\') . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $root)
      . DIRECTORY_SEPARATOR . $id;
  }

  /**
   * @return array<int, string>
   */
  private function collectFiles(string $directory): array
  {
    $files = [];
    $iterator = new RecursiveIteratorIterator(
      new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    /** @var SplFileInfo $file */
    foreach ($iterator as $file) {
      if (! $file->isFile()) {
        continue;
      }

      $files[] = $file->getPathname();
    }

    sort($files);

    return $files;
  }

  private function objectExistsOnRemote(
    $objectStorage,
    string $relativePath,
    $disk,
    string $objectKey,
    int &$existenceCheckWarnings,
  ): bool {
    try {
      if ($objectStorage->exists($relativePath)) {
        return true;
      }
    } catch (\Throwable) {
      // Fall through to direct disk check.
    }

    return $this->objectExists($disk, $objectKey, $existenceCheckWarnings);
  }

  private function objectExists($disk, string $objectKey, int &$existenceCheckWarnings): bool
  {
    try {
      return $this->retryStorageCall(fn () => $disk->exists($objectKey));
    } catch (UnableToCheckFileExistence|\Throwable $e) {
      $existenceCheckWarnings++;

      if ($existenceCheckWarnings <= 3) {
        $this->newLine();
        $this->warn("  Could not check if object exists (will upload): {$objectKey} — {$e->getMessage()}");
      }

      return false;
    }
  }

  private function retryStorageCall(callable $callback, int $attempts = 3): mixed
  {
    $lastException = null;

    for ($attempt = 1; $attempt <= $attempts; $attempt++) {
      try {
        return $callback();
      } catch (\Throwable $e) {
        $lastException = $e;

        if ($attempt === $attempts || ! $this->isRetryableStorageError($e)) {
          throw $e;
        }

        usleep(250_000 * $attempt);
      }
    }

    throw $lastException;
  }

  private function isRetryableStorageError(\Throwable $e): bool
  {
    $message = strtolower($e->getMessage());

    return str_contains($message, 'could not resolve host')
      || str_contains($message, 'connection timed out')
      || str_contains($message, 'connection reset')
      || str_contains($message, 'ssl')
      || str_contains($message, 'temporarily unavailable')
      || str_contains($message, '503')
      || str_contains($message, '502')
      || str_contains($message, '504');
  }
}
