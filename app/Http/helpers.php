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
        
        $webpImageName = pathinfo($filename, PATHINFO_FILENAME) . '.webp';
        $webpImage = $image->encode('webp', $quality);
        
        $webp_path = $directory . '/' . $webpImageName;
        Storage::disk('public')->put($webp_path, $webpImage->encoded);
        $webpImage->save(public_path($webp_path));
        
        return $webp_path;
    }
}

