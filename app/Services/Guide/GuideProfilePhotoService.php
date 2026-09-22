<?php

namespace App\Services\Guide;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class GuideProfilePhotoService
{
    /**
     * Public URL for a guide's profile photo.
     * Uses the stored upload when the file exists; otherwise the first listing image.
     */
    public function url(?User $user, mixed $listing = null): string
    {
        $stored = $this->storedProfileUrl($user);
        if ($stored !== null) {
            return $stored;
        }

        $fromListing = $this->listingImageUrl($listing);
        if ($fromListing !== null) {
            return $fromListing;
        }

        if ($user !== null) {
            $fromOther = $this->firstUserListingImageUrl($user, $listing);
            if ($fromOther !== null) {
                return $fromOther;
            }
        }

        return media_url(null);
    }

    /**
     * Stored profile upload URL, or null when the file is missing/empty.
     */
    public function storedProfileUrl(?User $user): ?string
    {
        if ($user === null) {
            return null;
        }

        $stored = trim((string) ($user->profil_image ?? ''));
        if ($stored === '') {
            return null;
        }

        if (str_starts_with($stored, 'http://') || str_starts_with($stored, 'https://')) {
            return $stored;
        }

        foreach ($this->candidateLocalPaths($stored) as $relative) {
            if ($this->localPublicFileExists($relative)) {
                return app('url')->asset($relative);
            }
        }

        $normalized = ltrim(str_replace('\\', '/', $stored), '/');
        if ($this->isManagedListingPath($normalized) && media_path_usable($normalized)) {
            return media_url($normalized);
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private function candidateLocalPaths(string $stored): array
    {
        $normalized = ltrim(str_replace('\\', '/', $stored), '/');
        $basename = basename($normalized);

        $paths = [];
        if (str_contains($normalized, '/')) {
            $paths[] = $normalized;
        }

        if ($basename !== '') {
            $paths[] = 'images/'.$basename;
            $paths[] = 'uploads/profile_images/'.$basename;
        }

        return array_values(array_unique($paths));
    }

    private function localPublicFileExists(string $relative): bool
    {
        $relative = ltrim(str_replace('\\', '/', $relative), '/');
        if ($relative === '' || str_contains($relative, '..')) {
            return false;
        }

        return is_file(public_path($relative));
    }

    private function isManagedListingPath(string $path): bool
    {
        $folders = array_values((array) config('media_storage.listing_folders', []));
        foreach ((array) config('media_storage.legacy_listing_folders', []) as $legacy) {
            foreach ((array) $legacy as $folder) {
                $folders[] = $folder;
            }
        }

        foreach (array_unique($folders) as $folder) {
            $folder = trim((string) $folder, '/');
            if ($folder !== '' && ($path === $folder || str_starts_with($path, $folder.'/'))) {
                return true;
            }
        }

        return false;
    }

    private function listingImageUrl(mixed $listing): ?string
    {
        if (! is_object($listing)) {
            return null;
        }

        $path = $this->firstListingMediaPath($listing);
        if ($path === null) {
            return null;
        }

        $url = media_url($path);

        return $this->isPlaceholderUrl($url) ? null : $url;
    }

    private function firstUserListingImageUrl(User $user, mixed $exceptListing = null): ?string
    {
        $exceptId = $exceptListing instanceof Model ? $exceptListing->getKey() : null;

        $guidings = $user->relationLoaded('guidings')
            ? $user->guidings
            : $user->guidings()
                ->select(['id', 'user_id', 'thumbnail_path', 'gallery_images'])
                ->orderByDesc('id')
                ->limit(8)
                ->get();

        foreach ($guidings as $guiding) {
            if ($exceptId !== null && (int) $guiding->getKey() === (int) $exceptId) {
                continue;
            }

            $url = $this->listingImageUrl($guiding);
            if ($url !== null) {
                return $url;
            }
        }

        return null;
    }

    private function firstListingMediaPath(object $listing): ?string
    {
        $gallery = $listing->gallery_images ?? [];
        if (is_string($gallery)) {
            $decoded = json_decode($gallery, true);
            $gallery = is_array($decoded) ? $decoded : [];
        }
        if (! is_array($gallery)) {
            $gallery = [];
        }

        $thumbnail = $listing->thumbnail_path ?? null;
        $candidates = array_merge(
            filled($thumbnail) ? [$thumbnail] : [],
            $gallery
        );

        foreach ($candidates as $path) {
            if (! is_string($path)) {
                continue;
            }

            $path = trim($path);
            if ($path === '' || ! media_path_usable($path)) {
                continue;
            }

            if ($this->isPlaceholderUrl($path) || str_contains($path, 'placeholder_guide')) {
                continue;
            }

            return $path;
        }

        return null;
    }

    private function isPlaceholderUrl(string $value): bool
    {
        return str_contains($value, 'placeholder_guide');
    }
}
