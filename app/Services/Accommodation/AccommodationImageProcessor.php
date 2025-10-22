<?php

namespace App\Services\Accommodation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AccommodationImageProcessor
{
    /**
     * Process image uploads with isolated storage strategy
     */
    public function processImageUploads(Request $request, string $slug, ?int $accommodationId = null): ?array
    {
        $galleryImages = [];
        $thumbnailPath = '';
        $imageList = json_decode($request->input('image_list', '[]')) ?? [];
        $processedFilenames = [];

        // Handle existing images for updates
        if ($request->input('is_update') == '1' && $accommodationId) {
            $existingImagesJson = $request->input('existing_images');
            $existingImages = json_decode($existingImagesJson, true) ?? [];
            $keepImages = array_filter($imageList);

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
            $tempSlug = $slug ?: 'temp-accommodation';

            foreach($request->file('title_image') as $index => $image) {
                $originalFilename = $image->getClientOriginalName();
                $filename = 'accommodations-images/' . $originalFilename;
                
                // Check if this image was already processed
                if (in_array($originalFilename, $processedFilenames)) continue;
                
                // Check if this image is in the image_list
                if (in_array($originalFilename, $imageList) || in_array($filename, $imageList) || in_array('/' . $filename, $imageList)) {
                    $index = $index + $imageCount;
                    $timestamp = time();
                    $filename = $tempSlug . "-" . $index . "-" . $timestamp;
                    
                    // Use isolated directory structure: accommodations/{id}/gallery/
                    $directory = $accommodationId ? "accommodations/{$accommodationId}/gallery" : "accommodations/temp/gallery";
                    
                    // Store the image
                    $path = $this->storeImage($image, $directory, $filename);
                    $galleryImages[] = $path;
                    $processedFilenames[] = $originalFilename;
                }
            }
        }

        // Set the primary image if available
        $primaryImageIndex = $request->input('primaryImage', 0);
        if (isset($galleryImages[$primaryImageIndex])) {
            $thumbnailPath = $galleryImages[$primaryImageIndex];
        }

        return [
            'gallery_images' => $galleryImages,
            'thumbnail_path' => $thumbnailPath
        ];
    }

    /**
     * Move images from temp directory to final directory after accommodation creation
     */
    public function moveImagesToFinalDirectory(int $accommodationId, string $slug, array $imageData): array
    {
        $tempDirectory = "accommodations/temp/gallery";
        $finalDirectory = "accommodations/{$accommodationId}/gallery";

        // Ensure final directory exists
        Storage::disk('public')->makeDirectory($finalDirectory);
        if (!file_exists(public_path($finalDirectory))) {
            mkdir(public_path($finalDirectory), 0755, true);
        }

        $updatedGalleryImages = [];
        $updatedThumbnailPath = '';

        // Move gallery images
        foreach ($imageData['gallery_images'] as $imagePath) {
            if (strpos($imagePath, $tempDirectory) === 0) {
                $filename = basename($imagePath);
                $newPath = $finalDirectory . '/' . $filename;
                
                // Move file from temp to final directory
                if (Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->move($imagePath, $newPath);
                }
                
                // Move in public directory as well
                $oldPublicPath = public_path($imagePath);
                $newPublicPath = public_path($newPath);
                if (file_exists($oldPublicPath)) {
                    rename($oldPublicPath, $newPublicPath);
                }
                
                $updatedGalleryImages[] = $newPath;
            } else {
                $updatedGalleryImages[] = $imagePath;
            }
        }

        // Move thumbnail
        if ($imageData['thumbnail_path'] && strpos($imageData['thumbnail_path'], $tempDirectory) === 0) {
            $filename = basename($imageData['thumbnail_path']);
            $newThumbnailPath = $finalDirectory . '/' . $filename;
            
            // Move file from temp to final directory
            if (Storage::disk('public')->exists($imageData['thumbnail_path'])) {
                Storage::disk('public')->move($imageData['thumbnail_path'], $newThumbnailPath);
            }
            
            // Move in public directory as well
            $oldPublicPath = public_path($imageData['thumbnail_path']);
            $newPublicPath = public_path($newThumbnailPath);
            if (file_exists($oldPublicPath)) {
                rename($oldPublicPath, $newPublicPath);
            }
            
            $updatedThumbnailPath = $newThumbnailPath;
        } else {
            $updatedThumbnailPath = $imageData['thumbnail_path'];
        }

        return [
            'gallery_images' => $updatedGalleryImages,
            'thumbnail_path' => $updatedThumbnailPath
        ];
    }

    /**
     * Store image file
     */
    private function storeImage($image, string $directory, string $filename): string
    {
        // Check if media_upload helper exists (from RentalBoats pattern)
        if (function_exists('media_upload')) {
            return media_upload($image, $directory, $filename, 75);
        }
        
        // Fallback to standard Laravel storage
        return $image->storeAs($directory, $filename . '.webp', 'public');
    }

    /**
     * Delete image file
     */
    private function deleteImage(string $imagePath): void
    {
        // Check if media_delete helper exists
        if (function_exists('media_delete')) {
            media_delete($imagePath);
            return;
        }
        
        // Fallback to standard deletion
        if (file_exists(public_path($imagePath))) {
            unlink(public_path($imagePath));
        }
        
        if (Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
    }
}

