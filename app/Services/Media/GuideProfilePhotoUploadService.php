<?php

namespace App\Services\Media;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Guide profile photos: converted to WebP and written to the configured upload storage under
 * {guide_area.photo.folder}/{user-id}/. Deliberately not a sitewide_folders entry: the orphan
 * image scan would treat these files as unreferenced.
 */
class GuideProfilePhotoUploadService
{
    public function __construct(
        private readonly MediaWriteStorageResolver $storageResolver,
        private readonly WebpMediaProcessor $processor,
    ) {}

    public static function folder(): string
    {
        return trim((string) config('guide_area.photo.folder', 'guide-profiles'), '/');
    }

    public static function owns(?string $path): bool
    {
        $path = ltrim(str_replace('\\', '/', (string) $path), '/');

        return $path !== '' && str_starts_with($path, self::folder().'/');
    }

    public function url(string $path): string
    {
        return $this->storageResolver->forUploads()->url(ltrim($path, '/'));
    }

    public function store(User $user, UploadedFile $file): string
    {
        $storage = $this->storageResolver->forUploads();
        // Random part: the processor hashes name + time(), so two uploads in the same second
        // would otherwise get the same path and the "previous" delete would remove the new file.
        $path = $this->processor->process(
            $file,
            self::folder().'/'.$user->id,
            'profile-'.$user->id.'-'.Str::random(12),
            (int) config('guide_area.photo.quality', 82),
            $user->id,
            $storage
        );

        $previous = $user->profil_image;
        $user->forceFill(['profil_image' => $path])->save();
        if ($previous !== $path) {
            $this->deleteIfOwned($previous);
        }

        return $path;
    }

    public function remove(User $user): void
    {
        $previous = $user->profil_image;
        $user->forceFill(['profil_image' => null])->save();
        $this->deleteIfOwned($previous);
    }

    /** Only files this service wrote are deleted; legacy public/images files are left alone. */
    private function deleteIfOwned(?string $path): void
    {
        if (! self::owns($path)) {
            return;
        }

        try {
            $this->storageResolver->forUploads()->delete(ltrim((string) $path, '/'));
        } catch (\Throwable $e) {
            Log::warning('Guide profile photo delete failed', ['path' => $path, 'error' => $e->getMessage()]);
        }
    }
}
