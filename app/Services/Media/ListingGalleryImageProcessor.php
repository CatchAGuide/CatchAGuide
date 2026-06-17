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
        $imageList = json_decode($request->input('image_list', '[]'), true) ?? [];
        $processedFilenames = [];
        $directory = $this->paths->entityDirectory($listingKey, $entityId);

        if ($request->input('is_update') == '1' && $entityId) {
            $galleryImages = $this->retainExistingImages($request, $imageList);
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

                    if (! $this->shouldKeepUpload($originalFilename, $imageList, $listingKey)) {
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
    private function retainExistingImages(Request $request, array $imageList): array
    {
        $existingImages = json_decode($request->input('existing_images', '[]'), true) ?? [];
        $keepImages = array_filter($imageList);
        $galleryImages = [];

        foreach ($existingImages as $existingImage) {
            $imagePath = $existingImage;
            $imagePathWithSlash = '/' . $existingImage;

            if (in_array($imagePath, $keepImages, true) || in_array($imagePathWithSlash, $keepImages, true)) {
                $galleryImages[] = $existingImage;
            } else {
                media_delete($existingImage);
            }
        }

        return $galleryImages;
    }

    /**
     * @param  array<int, mixed>  $imageList
     */
    private function shouldKeepUpload(string $originalFilename, array $imageList, string $listingKey): bool
    {
        if (in_array($originalFilename, $imageList, true) || in_array('/' . $originalFilename, $imageList, true)) {
            return true;
        }

        foreach ($this->legacyImageListPrefixes($listingKey) as $prefix) {
            if (
                in_array($prefix . $originalFilename, $imageList, true)
                || in_array('/' . $prefix . $originalFilename, $imageList, true)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, string>
     */
    private function legacyImageListPrefixes(string $listingKey): array
    {
        $legacy = config("media_storage.legacy_listing_folders.{$listingKey}", []);

        return array_map(
            static fn (string $folder) => rtrim($folder, '/') . '/',
            array_filter((array) $legacy, 'is_string')
        );
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
