<?php

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

if (!function_exists('two')) {
    function two($number) {
        return number_format($number, 2, ',', '.');
    }
}

if (!function_exists('twoString')) {
    function twoString($number) {
        return number_format($number, 2, '.');
    }
}

if (!function_exists('one')) {
    function one($number) {
        return number_format($number, 1, ',', '.');
    }
}

if(!function_exists('slugify')) {
    function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }
        return $text;
    }
}

if (!function_exists('getLocalizedValue')) {
    function getLocalizedValue($model) {
        $locale = app()->getLocale();

        if($locale == 'de'){
            return $model->name;
        }
        if($locale == 'en'){
            if(isset($model->name_en) && !empty($model->name_en)){
                return $model->name_en;
            }else{
                return $model->name;
            }
        }

    }
}

if (!function_exists('media_upload')) {
    function media_upload($file, $directory = 'uploads', $filename = null, $quality = 75)
    {
        if (filter_var($file, FILTER_VALIDATE_URL)) {
            
            $image = Image::make($file);
            if (!$filename) {
                $filename = basename(parse_url($file, PHP_URL_PATH));
            }

        } elseif ($file instanceof \Illuminate\Http\UploadedFile) {

            $thumbnail_path = $file->store('public/' . $directory);
            $imagePath = Storage::disk()->path($thumbnail_path);
            $image = Image::make($imagePath);
            if (!$filename) {
                $filename = $file->getClientOriginalName();
            }

        } else {
            throw new \InvalidArgumentException('Invalid input: must be a URL or an uploaded file');
        }
        
        // Check file size and resize if needed
        $fileSize = strlen($image->encode('webp', $quality)->encoded);
        if ($fileSize > 2048 * 1024) { // If larger than 2048KB
            $width = $image->width();
            $height = $image->height();
            
            // Calculate new dimensions while maintaining aspect ratio
            $ratio = sqrt(2048 * 1024 / $fileSize);
            $newWidth = round($width * $ratio);
            $newHeight = round($height * $ratio);
            
            $image->resize($newWidth, $newHeight);
        }
        
        $webpImageName = pathinfo($filename, PATHINFO_FILENAME) . '.webp';
        $webpImage = $image->encode('webp', $quality);
        
        $webp_path = $directory . '/' . $webpImageName;

        // Check if file exists and delete it
        if (Storage::disk('public')->exists($webp_path)) {
            Storage::disk('public')->delete($webp_path);
        }
        if (file_exists(public_path($webp_path))) {
            unlink(public_path($webp_path));
        }

        // Save new file
        Storage::disk('public')->put($webp_path, $webpImage->encoded);
        $webpImage->save(public_path($webp_path));
        
        return $webp_path;
    }
}

if (!function_exists('media_delete')) {
    function media_delete($path)
    {
        // Remove any double slashes
        $path = str_replace('//', '/', $path);
        
        // Delete from storage
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
        
        // Delete from public path
        $publicPath = public_path($path);
        if (file_exists($publicPath)) {
            unlink($publicPath);
        }
        
        return true;
    }
}

