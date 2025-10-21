<?php

namespace App\Services\Camp;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CampImageProcessor
{
    /**
     * Process image uploads with isolated storage strategy
     */
    public function processImageUploads(Request $request, string $slug, ?int $campId = null): ?array
    {
        $galleryImages = [];
        $thumbnailPath = '';
        $imageList = json_decode($request->input('image_list', '[]')) ?? [];

        // Log image upload data
        Log::info('Camp Image Upload Debug:', [
            'has_title_image' => $request->hasFile('title_image'),
            'title_image_count' => $request->hasFile('title_image') ? count($request->file('title_image')) : 0,
            'has_cropped_image' => $request->hasFile('cropped_image'),
            'cropped_image_count' => $request->hasFile('cropped_image') ? count($request->file('cropped_image')) : 0,
            'image_list' => $imageList,
            'camp_id' => $campId,
            'slug' => $slug,
        ]);

        // Handle existing images for updates
        if ($request->input('is_update') == '1' && $campId) {
            $existingImagesJson = $request->input('existing_images');
            $existingImages = json_decode($existingImagesJson, true) ?? [];
            $keepImages = array_filter($imageList);

            Log::info('Camp Update - Existing Images:', [
                'existing_images' => $existingImages,
                'keep_images' => $keepImages
            ]);

            foreach ($existingImages as $existingImage) {
                $imagePath = $existingImage;
                $imagePathWithSlash = '/' . $existingImage;
                if (in_array($imagePath, $keepImages) || in_array($imagePathWithSlash, $keepImages)) {
                    $galleryImages[] = $existingImage;
                } else {
                    // Delete unused images
                    $this->deleteImage($existingImage);
                }
            }
        }

        // Process new file uploads
        if ($request->hasFile('title_image')) {
            $imageCount = count($galleryImages);
            $tempSlug = $slug ?: 'temp-camp';

            foreach($request->file('title_image') as $index => $image) {
                try {
                    // Generate unique filename
                    $filename = $this->generateUniqueFilename($image, $tempSlug, $imageCount + $index);
                    
                    // Store in temporary directory first
                    $path = $image->storeAs("camps/temp/{$tempSlug}", $filename, 'public');
                    $galleryImages[] = $path;
                    
                    Log::info('Camp Image Stored:', [
                        'path' => $path, 
                        'size' => $image->getSize(),
                        'original_name' => $image->getClientOriginalName()
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error storing camp image:', [
                        'error' => $e->getMessage(),
                        'filename' => $image->getClientOriginalName()
                    ]);
                }
            }
        }

        // Handle cropped images
        if ($request->hasFile('cropped_image')) {
            foreach($request->file('cropped_image') as $image) {
                try {
                    $filename = $this->generateUniqueFilename($image, $slug ?: 'temp-camp', count($galleryImages));
                    $path = $image->storeAs("camps/temp/{$slug}", $filename, 'public');
                    $galleryImages[] = $path;
                    
                    Log::info('Camp Cropped Image Stored:', [
                        'path' => $path, 
                        'size' => $image->getSize()
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error storing cropped camp image:', [
                        'error' => $e->getMessage(),
                        'filename' => $image->getClientOriginalName()
                    ]);
                }
            }
        }

        // Set thumbnail path (first image or existing)
        if (!empty($galleryImages)) {
            $thumbnailPath = $galleryImages[0];
        }

        Log::info('Final Camp Gallery Images Array:', $galleryImages);

        // Return null if no images to avoid overwriting existing data
        if (empty($galleryImages)) {
            return null;
        }

        return [
            'gallery_images' => $galleryImages,
            'thumbnail_path' => $thumbnailPath,
        ];
    }

    /**
     * Move images from temp directory to final directory
     */
    public function moveImagesToFinalDirectory(int $campId, string $slug, array $imageData): array
    {
        $finalGalleryImages = [];
        $finalThumbnailPath = '';

        foreach ($imageData['gallery_images'] as $tempPath) {
            try {
                // Create final path
                $filename = basename($tempPath);
                $finalPath = "camps/{$campId}/{$filename}";

                // Move file from temp to final location
                if (Storage::disk('public')->exists($tempPath)) {
                    Storage::disk('public')->move($tempPath, $finalPath);
                    $finalGalleryImages[] = $finalPath;
                    
                    Log::info('Camp Image Moved:', [
                        'from' => $tempPath,
                        'to' => $finalPath
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error moving camp image:', [
                    'error' => $e->getMessage(),
                    'temp_path' => $tempPath,
                    'camp_id' => $campId
                ]);
            }
        }

        // Set final thumbnail path
        if (!empty($finalGalleryImages)) {
            $finalThumbnailPath = $finalGalleryImages[0];
        }

        return [
            'gallery_images' => $finalGalleryImages,
            'thumbnail_path' => $finalThumbnailPath,
        ];
    }

    /**
     * Delete an image file
     */
    private function deleteImage(string $imagePath): void
    {
        try {
            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
                Log::info('Camp Image Deleted:', ['path' => $imagePath]);
            }
        } catch (\Exception $e) {
            Log::error('Error deleting camp image:', [
                'error' => $e->getMessage(),
                'path' => $imagePath
            ]);
        }
    }

    /**
     * Generate unique filename for uploaded image
     */
    private function generateUniqueFilename($image, string $slug, int $index): string
    {
        $extension = $image->getClientOriginalExtension();
        $timestamp = now()->format('YmdHis');
        return "{$slug}_{$timestamp}_{$index}.{$extension}";
    }
}
