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

        // Handle existing images for updates
        if ($request->input('is_update') == '1' && $campId) {
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
            $tempSlug = $slug ?: 'temp-camp';
            $processedFilenames = [];

            foreach($request->file('title_image') as $index => $image) {
                try {
                    $originalFilename = $image->getClientOriginalName();
                    $filename = 'camps-images/' . $originalFilename;
                    
                    // Check if this image was already processed (existing image)
                    if (in_array($originalFilename, $processedFilenames)) continue;
                    
                    // Check if this image is in the image_list (new image that should be kept)
                    if (in_array($originalFilename, $imageList) || in_array($filename, $imageList) || in_array('/' . $filename, $imageList)) {
                        $index = $index + $imageCount;
                        $timestamp = time();
                        $filename = $tempSlug . "-" . $index . "-" . $timestamp;
                        
                        // Use isolated directory structure: camps/{id}/gallery/
                        $directory = $campId ? "camps/{$campId}/gallery" : "camps/temp/gallery";
                        
                        $webp_path = media_upload($image, $directory, $filename, 75, $campId);
                        $galleryImages[] = $webp_path;
                        $processedFilenames[] = $originalFilename;
                    }
                } catch (\Exception $e) {
                    Log::error('Error storing camp image:', [
                        'error' => $e->getMessage(),
                        'filename' => $image->getClientOriginalName()
                    ]);
                }
            }
        } else {
            Log::info('CampImageProcessor::processImageUploads - No title_image files found');
        }

        // Handle cropped images
        if ($request->hasFile('cropped_image')) {
            foreach($request->file('cropped_image') as $image) {
                try {
                    $timestamp = time();
                    $filename = ($slug ?: 'temp-camp') . "-cropped-" . count($galleryImages) . "-" . $timestamp;
                    
                    // Use isolated directory structure: camps/{id}/gallery/
                    $directory = $campId ? "camps/{$campId}/gallery" : "camps/temp/gallery";
                    
                    $webp_path = media_upload($image, $directory, $filename, 75, $campId);
                    $galleryImages[] = $webp_path;
                } catch (\Exception $e) {
                    Log::error('Error storing cropped camp image:', [
                        'error' => $e->getMessage(),
                        'filename' => $image->getClientOriginalName()
                    ]);
                }
            }
        }

        // Set thumbnail path - check if user selected a specific thumbnail from request
        $requestedThumbnail = $request->input('thumbnail_path');
        $originalRequestedThumbnail = $requestedThumbnail;
        
        // Extract relative path from full URL if needed
        if ($requestedThumbnail && !empty($requestedThumbnail)) {
            // If it's a full URL, extract the path
            if (filter_var($requestedThumbnail, FILTER_VALIDATE_URL)) {
                $parsedUrl = parse_url($requestedThumbnail);
            }
            // Remove leading slash if present
            $requestedThumbnail = ltrim($requestedThumbnail, '/');
        }
        
        if (!empty($galleryImages)) {
            // If user selected a specific thumbnail, use it if it exists in gallery
            if ($requestedThumbnail && !empty($requestedThumbnail)) {
                // Check if the requested thumbnail exists in the gallery images
                // Compare by basename or full path
                $foundThumbnail = null;
                foreach ($galleryImages as $galleryImage) {
                    $normalizedGalleryImage = ltrim($galleryImage, '/');
                    $normalizedRequested = ltrim($requestedThumbnail, '/');
                    
                    // Match by full path or basename
                    if ($normalizedGalleryImage === $normalizedRequested || 
                        basename($normalizedGalleryImage) === basename($normalizedRequested)) {
                        $foundThumbnail = $galleryImage;
                        break;
                    }
                }
                
                if ($foundThumbnail) {
                    // Use the exact path from gallery to maintain consistency
                    $thumbnailPath = $foundThumbnail;
                } else {
                    // Requested thumbnail not in gallery, use first image
                    $thumbnailPath = $galleryImages[0];
                }
            } else {
                // No specific thumbnail requested, use first image
                $thumbnailPath = $galleryImages[0];
            }
        } elseif ($requestedThumbnail && !empty($requestedThumbnail)) {
            // No new images uploaded, but user selected a thumbnail
            // This will be handled separately in the controller for updates
            $thumbnailPath = $requestedThumbnail;
        }

        // Return null if no images to avoid overwriting existing data
        // BUT if thumbnail_path was explicitly set, we should still return it
        if (empty($galleryImages) && empty($requestedThumbnail)) {
            return null;
        }

        return [
            'gallery_images' => $galleryImages,
            'thumbnail_path' => $thumbnailPath,
        ];
    }

    /**
     * Move images from temp directory to final directory after camp creation
     */
    public function moveImagesToFinalDirectory(int $campId, string $slug, array $imageData): array
    {
        $tempDirectory = "camps/temp/gallery";
        $finalDirectory = "camps/{$campId}/gallery";

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
     * Delete an image file
     */
    private function deleteImage(string $imagePath): void
    {
        try {
            media_delete($imagePath);
        } catch (\Exception $e) {
            Log::error('Error deleting camp image:', [
                'error' => $e->getMessage(),
                'path' => $imagePath
            ]);
        }
    }

}
