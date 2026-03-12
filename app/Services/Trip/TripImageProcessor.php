<?php

namespace App\Services\Trip;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TripImageProcessor
{
    public function processImageUploads(Request $request, string $slug, ?int $tripId = null): ?array
    {
        $galleryImages = [];
        $thumbnailPath = '';
        $imageList = json_decode($request->input('image_list', '[]')) ?? [];
        $processedFilenames = [];

        // Handle existing images for updates (same pattern as camps/accommodations)
        if ($request->input('is_update') == '1' && $tripId) {
            $existingImagesJson = $request->input('existing_images');
            $existingImages = json_decode($existingImagesJson, true) ?? [];
            $keepImages = array_filter($imageList);

            foreach ($existingImages as $existingImage) {
                $imagePath = $existingImage;
                $imagePathWithSlash = '/' . $existingImage;
                if (in_array($imagePath, $keepImages, true) || in_array($imagePathWithSlash, $keepImages, true)) {
                    $galleryImages[] = $existingImage;
                } else {
                    $this->deleteImage($existingImage);
                }
            }
        }

        // New gallery uploads
        if ($request->hasFile('title_image')) {
            $imageCount = count($galleryImages);
            $tempSlug = $slug ?: 'temp-trip';

            foreach ($request->file('title_image') as $index => $image) {
                try {
                    $originalFilename = $image->getClientOriginalName();
                    $filename = 'trips-images/' . $originalFilename;

                    if (in_array($originalFilename, $processedFilenames, true)) {
                        continue;
                    }

                    if (
                        in_array($originalFilename, $imageList, true) ||
                        in_array($filename, $imageList, true) ||
                        in_array('/' . $filename, $imageList, true)
                    ) {
                        $index = $index + $imageCount;
                        $timestamp = time();
                        $filename = $tempSlug . '-' . $index . '-' . $timestamp;
                        $directory = $tripId ? "trips/{$tripId}/gallery" : 'trips/temp/gallery';

                        $webpPath = media_upload($image, $directory, $filename, 75, $tripId);
                        $galleryImages[] = $webpPath;
                        $processedFilenames[] = $originalFilename;
                    }
                } catch (\Exception $e) {
                    Log::error('TripImageProcessor::processImageUploads - Error storing trip image', [
                        'error' => $e->getMessage(),
                        'filename' => $image->getClientOriginalName(),
                    ]);
                }
            }
        }

        // Cropped versions
        if ($request->hasFile('cropped_image')) {
            foreach ($request->file('cropped_image') as $image) {
                try {
                    $timestamp = time();
                    $filename = ($slug ?: 'temp-trip') . '-cropped-' . count($galleryImages) . '-' . $timestamp;
                    $directory = $tripId ? "trips/{$tripId}/gallery" : 'trips/temp/gallery';

                    $webpPath = media_upload($image, $directory, $filename, 75, $tripId);
                    $galleryImages[] = $webpPath;
                } catch (\Exception $e) {
                    Log::error('TripImageProcessor::processImageUploads - Error storing cropped trip image', [
                        'error' => $e->getMessage(),
                        'filename' => $image->getClientOriginalName(),
                    ]);
                }
            }
        }

        // Thumbnail from primary index or explicit selection
        $primaryIndex = (int) $request->input('primaryImage', 0);
        if (isset($galleryImages[$primaryIndex])) {
            $thumbnailPath = $galleryImages[$primaryIndex];
        }

        if (empty($galleryImages)) {
            return null;
        }

        return [
            'gallery_images' => $galleryImages,
            'thumbnail_path' => $thumbnailPath,
        ];
    }

    public function moveImagesToFinalDirectory(int $tripId, string $slug, array $imageData): array
    {
        $tempDirectory = 'trips/temp/gallery';
        $finalDirectory = "trips/{$tripId}/gallery";

        Storage::disk('public')->makeDirectory($finalDirectory);
        if (!file_exists(public_path($finalDirectory))) {
            mkdir(public_path($finalDirectory), 0755, true);
        }

        $updatedGalleryImages = [];
        $updatedThumbnailPath = '';

        foreach ($imageData['gallery_images'] as $imagePath) {
            if (strpos($imagePath, $tempDirectory) === 0) {
                $filename = basename($imagePath);
                $newPath = $finalDirectory . '/' . $filename;

                if (Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->move($imagePath, $newPath);
                }

                $oldPublicPath = public_path($imagePath);
                $newPublicPath = public_path($newPath);
                if (file_exists($oldPublicPath)) {
                    @rename($oldPublicPath, $newPublicPath);
                }

                $updatedGalleryImages[] = $newPath;
            } else {
                $updatedGalleryImages[] = $imagePath;
            }
        }

        if (!empty($imageData['thumbnail_path']) && strpos($imageData['thumbnail_path'], $tempDirectory) === 0) {
            $filename = basename($imageData['thumbnail_path']);
            $newThumbnailPath = $finalDirectory . '/' . $filename;

            if (Storage::disk('public')->exists($imageData['thumbnail_path'])) {
                Storage::disk('public')->move($imageData['thumbnail_path'], $newThumbnailPath);
            }

            $oldPublicPath = public_path($imageData['thumbnail_path']);
            $newPublicPath = public_path($newThumbnailPath);
            if (file_exists($oldPublicPath)) {
                @rename($oldPublicPath, $newPublicPath);
            }

            $updatedThumbnailPath = $newThumbnailPath;
        } else {
            $updatedThumbnailPath = $imageData['thumbnail_path'] ?? '';
        }

        return [
            'gallery_images' => $updatedGalleryImages,
            'thumbnail_path' => $updatedThumbnailPath,
        ];
    }

    public function processProviderPhoto(Request $request, string $slug, ?int $tripId = null): ?string
    {
        if (!$request->hasFile('provider_photo')) {
            return null;
        }

        try {
            $image = $request->file('provider_photo');
            $timestamp = time();
            $filename = ($slug ?: 'trip-provider') . '-provider-' . $timestamp;
            $directory = $tripId ? "trips/{$tripId}/provider" : 'trips/temp/provider';

            return media_upload($image, $directory, $filename, 75, $tripId);
        } catch (\Exception $e) {
            Log::error('TripImageProcessor::processProviderPhoto - Error storing provider photo', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function deleteImage(string $imagePath): void
    {
        try {
            media_delete($imagePath);
        } catch (\Exception $e) {
            Log::error('TripImageProcessor::deleteImage - Error deleting trip image', [
                'error' => $e->getMessage(),
                'path' => $imagePath,
            ]);
        }
    }
}

