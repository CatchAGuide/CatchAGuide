<?php

namespace App\Services\Media;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ListingGalleryImageProcessor
{
    public function __construct(
        private readonly ListingMediaPathBuilder $paths,
    ) {}

    /**
     * Process gallery uploads for any listing type.
     *
     * @return array{gallery_images: array<int, string>, thumbnail_path: string}|null
     */
    public function process(
        Request $request,
        string $listingKey,
        string $slug,
        ?int $entityId = null,
        string $fileField = 'title_image',
    ): ?array {
        $options = $this->options($listingKey);
        $galleryImages = [];
        $imageListRaw = $request->input('image_list');
        // Empty/missing means ImageManager never synced the hidden field.
        // A synced empty gallery is the JSON string "[]".
        $imageListSynced = is_string($imageListRaw) && trim($imageListRaw) !== '';
        $imageList = $imageListSynced
            ? (json_decode($imageListRaw, true) ?? [])
            : [];
        $processedFilenames = [];
        $directory = $this->paths->entityDirectory($listingKey, $entityId);

        if ($request->input('is_update') == '1' && $entityId) {
            $galleryImages = $this->retainExistingImages($request, $imageList, $imageListSynced);
        }

        if ($request->hasFile($fileField)) {
            $imageCount = count($galleryImages);
            $tempSlug = $slug ?: $options['temp_slug'];

            foreach ($request->file($fileField) as $index => $image) {
                try {
                    $originalFilename = $image->getClientOriginalName();

                    if (in_array($originalFilename, $processedFilenames, true)) {
                        continue;
                    }

                    if (! $this->shouldKeepUpload($originalFilename, $imageList, $listingKey, $imageListSynced)) {
                        Log::warning("ListingGalleryImageProcessor [{$listingKey}] skipped upload not present in image_list", [
                            'filename' => $originalFilename,
                            'image_list' => $imageList,
                        ]);
                        continue;
                    }

                    $index = $index + $imageCount;
                    $filename = $tempSlug . '-' . $index . '-' . time();
                    $galleryImages[] = media_upload($image, $directory, $filename, 75, $entityId);
                    $processedFilenames[] = $originalFilename;
                } catch (\Exception $e) {
                    Log::error("ListingGalleryImageProcessor [{$listingKey}] upload failed", [
                        'error' => $e->getMessage(),
                        'filename' => $image->getClientOriginalName(),
                    ]);
                }
            }
        }

        if ($options['cropped'] && $request->hasFile('cropped_image')) {
            foreach ($request->file('cropped_image') as $image) {
                try {
                    $filename = ($slug ?: $options['temp_slug']) . '-cropped-' . count($galleryImages) . '-' . time();
                    $galleryImages[] = media_upload($image, $directory, $filename, 75, $entityId);
                } catch (\Exception $e) {
                    Log::error("ListingGalleryImageProcessor [{$listingKey}] cropped upload failed", [
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        $thumbnailPath = $this->resolveThumbnail($request, $galleryImages, $options['thumbnail']);

        if ($options['empty_returns_null'] && empty($galleryImages) && empty($request->input('thumbnail_path'))) {
            return null;
        }

        if (empty($galleryImages) && $options['empty_returns_null']) {
            return $thumbnailPath !== '' ? [
                'gallery_images' => [],
                'thumbnail_path' => $thumbnailPath,
            ] : null;
        }

        return [
            'gallery_images' => $galleryImages,
            'thumbnail_path' => $thumbnailPath,
        ];
    }

    public function uploadExtraFile(
        Request $request,
        string $listingKey,
        string $fieldName,
        string $slug,
        ?int $entityId,
        string $filenamePrefix,
    ): ?string {
        if (! $request->hasFile($fieldName)) {
            return null;
        }

        try {
            $image = $request->file($fieldName);
            $options = $this->options($listingKey);
            $filename = ($slug ?: $options['temp_slug']) . '-' . $filenamePrefix . '-' . time();
            $directory = $this->paths->entityDirectory($listingKey, $entityId);

            return media_upload($image, $directory, $filename, 75, $entityId);
        } catch (\Exception $e) {
            Log::error("ListingGalleryImageProcessor [{$listingKey}] extra file upload failed", [
                'field' => $fieldName,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @param  array<int, mixed>  $imageList
     * @return array<int, string>
     */
    private function retainExistingImages(Request $request, array $imageList, bool $imageListSynced): array
    {
        $existingImages = json_decode($request->input('existing_images', '[]'), true) ?? [];
        $existingImages = array_values(array_filter($existingImages, static fn ($path) => is_string($path) && $path !== ''));

        // Client never synced image_list: keep existing images instead of wiping the gallery.
        if (! $imageListSynced) {
            return $existingImages;
        }

        $keepImages = array_values(array_filter(array_map(
            static fn ($path) => is_string($path) ? ltrim($path, '/') : null,
            $imageList
        )));
        $keepBasenames = array_flip(array_map('basename', $keepImages));
        $galleryImages = [];

        foreach ($existingImages as $existingImage) {
            $normalizedExisting = ltrim($existingImage, '/');

            if (
                in_array($normalizedExisting, $keepImages, true)
                || isset($keepBasenames[basename($normalizedExisting)])
            ) {
                $galleryImages[] = $existingImage;
            } else {
                media_delete($existingImage);
            }
        }

        return $galleryImages;
    }

    /**
     * Match uploaded filenames against ImageManager's image_list entries.
     *
     * image_list may contain bare filenames, legacy prefixes (e.g. rental-boats-images/),
     * current listing folders (e.g. accommodations/), or full entity paths
     * (e.g. accommodations/81/photo.jpg). Compare by exact path and by basename so
     * storagePrefix / entity-folder paths do not silently drop uploads.
     *
     * @param  array<int, mixed>  $imageList
     */
    private function shouldKeepUpload(
        string $originalFilename,
        array $imageList,
        string $listingKey,
        bool $imageListSynced = true,
    ): bool {
        // Client never synced image_list: accept uploads rather than silently discarding them.
        if (! $imageListSynced) {
            return true;
        }

        $candidates = $this->imageListUploadCandidates($originalFilename, $listingKey);

        foreach ($imageList as $entry) {
            if (! is_string($entry) || $entry === '') {
                continue;
            }

            $normalized = ltrim($entry, '/');

            if (in_array($normalized, $candidates, true) || in_array($entry, $candidates, true)) {
                return true;
            }

            if (basename($normalized) === $originalFilename) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, string>
     */
    private function imageListUploadCandidates(string $originalFilename, string $listingKey): array
    {
        $candidates = [
            $originalFilename,
            '/' . $originalFilename,
        ];

        foreach ($this->imageListPrefixes($listingKey) as $prefix) {
            $candidates[] = $prefix . $originalFilename;
            $candidates[] = '/' . $prefix . $originalFilename;
        }

        return array_values(array_unique($candidates));
    }

    /**
     * Current + legacy folder prefixes used by ImageManager storagePrefix / path sync.
     *
     * @return array<int, string>
     */
    private function imageListPrefixes(string $listingKey): array
    {
        $folders = [];

        $current = config("media_storage.listing_folders.{$listingKey}");
        if (is_string($current) && $current !== '') {
            $folders[] = $current;
        }

        $legacy = config("media_storage.legacy_listing_folders.{$listingKey}", []);
        foreach ((array) $legacy as $folder) {
            if (is_string($folder) && $folder !== '') {
                $folders[] = $folder;
            }
        }

        return array_values(array_unique(array_map(
            static fn (string $folder) => rtrim($folder, '/') . '/',
            $folders
        )));
    }

    /**
     * @param  array<int, string>  $galleryImages
     */
    private function resolveThumbnail(Request $request, array $galleryImages, string $strategy): string
    {
        if ($strategy === 'requested_path') {
            return $this->resolveRequestedThumbnail($request, $galleryImages);
        }

        $primaryIndex = (int) $request->input('primaryImage', 0);

        return $galleryImages[$primaryIndex] ?? '';
    }

    /**
     * @param  array<int, string>  $galleryImages
     */
    private function resolveRequestedThumbnail(Request $request, array $galleryImages): string
    {
        $requestedThumbnail = $request->input('thumbnail_path');

        if (! is_string($requestedThumbnail) || $requestedThumbnail === '') {
            return $galleryImages[0] ?? '';
        }

        if (filter_var($requestedThumbnail, FILTER_VALIDATE_URL)) {
            $parsedUrl = parse_url($requestedThumbnail);
            $requestedThumbnail = ltrim($parsedUrl['path'] ?? '', '/');
        }

        $requestedThumbnail = ltrim($requestedThumbnail, '/');

        if (! empty($galleryImages)) {
            foreach ($galleryImages as $galleryImage) {
                $normalizedGalleryImage = ltrim($galleryImage, '/');
                $normalizedRequested = ltrim($requestedThumbnail, '/');

                if (
                    $normalizedGalleryImage === $normalizedRequested
                    || basename($normalizedGalleryImage) === basename($normalizedRequested)
                ) {
                    return $galleryImage;
                }
            }

            return $galleryImages[0];
        }

        return $requestedThumbnail;
    }

    /**
     * @return array{temp_slug: string, thumbnail: string, cropped: bool, empty_returns_null: bool}
     */
    private function options(string $listingKey): array
    {
        $defaults = [
            'temp_slug' => 'temp-' . str_replace('_', '-', $listingKey),
            'thumbnail' => 'primary_index',
            'cropped' => false,
            'empty_returns_null' => false,
        ];

        $configured = config("media_storage.listing_upload.{$listingKey}", []);

        return array_merge($defaults, is_array($configured) ? $configured : []);
    }
}
