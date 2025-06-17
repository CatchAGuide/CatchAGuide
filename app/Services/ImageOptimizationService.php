<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ImageOptimizationService
{
    private const CACHE_TTL = 86400; // 24 hours
    private const THUMBNAIL_WIDTH = 400;
    private const THUMBNAIL_HEIGHT = 300;
    private const QUALITY = 80;

    public function getOptimizedImage($imagePath, $width = null, $height = null)
    {
        $cacheKey = $this->generateCacheKey($imagePath, $width, $height);
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($imagePath, $width, $height) {
            return $this->optimizeImage($imagePath, $width, $height);
        });
    }

    public function getOptimizedThumbnail($imagePath)
    {
        return $this->getOptimizedImage($imagePath, self::THUMBNAIL_WIDTH, self::THUMBNAIL_HEIGHT);
    }

    private function optimizeImage($imagePath, $width = null, $height = null)
    {
        $cacheDir = public_path('cache/guidings');
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        $cacheFile = $cacheDir . '/' . md5($imagePath . $width . $height) . '.jpg';

        // If cache file exists, just return its URL
        if (file_exists($cacheFile)) {
            return asset('cache/guidings/' . basename($cacheFile));
        }

        // Otherwise, generate and save it
        $fullPath = public_path($imagePath);
        if (!file_exists($fullPath)) {
            $fullPath = public_path(ltrim($imagePath, '/'));
            if (!file_exists($fullPath)) {
                return asset('images/placeholder_guide.jpg');
            }
        }

        $image = Image::make($fullPath);

        if ($width && $height) {
            $image->fit($width, $height, function ($constraint) {
                $constraint->upsize();
            });
        }

        $image->encode('jpg', self::QUALITY);
        $image->save($cacheFile);

        return asset('cache/guidings/' . basename($cacheFile));
    }

    private function generateCacheKey($imagePath, $width = null, $height = null)
    {
        return 'optimized_image_' . md5($imagePath . $width . $height);
    }

    public function clearImageCache($imagePath)
    {
        $cacheKey = $this->generateCacheKey($imagePath);
        Cache::forget($cacheKey);
        
        // Clear thumbnail cache
        $thumbnailCacheKey = $this->generateCacheKey($imagePath, self::THUMBNAIL_WIDTH, self::THUMBNAIL_HEIGHT);
        Cache::forget($thumbnailCacheKey);
    }
} 