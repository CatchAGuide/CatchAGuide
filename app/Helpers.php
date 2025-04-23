<?php
use Stichoza\GoogleTranslate\GoogleTranslate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use App\Models\Faq;
use Illuminate\Support\Facades\Log;

if (! function_exists('translate')) {
    function translate($string, $language = '')
    {
        $currentLocale = ($language != '' || $language != null) ? $language : app()->getLocale();
        $cacheKey = 'translation_'.$currentLocale.'_'.$string;

        $translation = Cache::rememberForever($cacheKey, function () use ($string, $currentLocale) {
            $translate = GoogleTranslate::trans($string, $currentLocale);

            if (strpos($translate, 'Führungen')) {
                $translate = str_replace('Führungen', 'Angelguidings', $translate);
            }

            if (strpos($translate, 'Führung')) {
                $translate = str_replace('Führung', 'guiding', $translate);
            }

            return ucfirst($translate);
        });

        return $translation;
    }
}

if (! function_exists('get_link')) {
    function get_link($model)
    {
        $links = app('guiding')->getImagesUrl($model);
        $offset = 0;
        $link = null;
        foreach($links as $key => $image_link){
            if($offset == 0){
                $link = $image_link;
            }
        $offset++;
        }

        return $link;
    }
}

if (! function_exists('get_featured_image_link')) {
    function get_featured_image_link($model)
    {
        $link = null;
        if($model->thumbnail_path){
            if(file_exists(public_path($model->thumbnail_path))){
                $link = asset($model->thumbnail_path);
            }else{
                $link = asset('images/placeholder_guide.jpg');
            }
        }else{
            $link = asset('images/placeholder_guide.jpg');
        }

        return $link;
    }
}

if (! function_exists('get_galleries_image_link')) {
    function get_galleries_image_link($model, $type = 0)
    {   
        $links = [];
        $uniqueUrls = []; // Track unique URLs to prevent duplicates

        // Add thumbnail if it exists
        if($model->thumbnail_path && file_exists(public_path($model->thumbnail_path))){
            $thumbnailUrl = asset($model->thumbnail_path);
            $links[] = $thumbnailUrl;
            $uniqueUrls[] = $thumbnailUrl;
        }

        // Get gallery images based on type
        if($type == 0){
            $galleries = json_decode($model->gallery_images, true);
        }else{
            $galleries = json_decode($model->gallery, true);
        }

        // Add gallery images, avoiding duplicates
        if(is_array($galleries) && count($galleries)){
            foreach($galleries as $url){
                if(!empty($url) && file_exists(public_path($url))){
                    $galleryUrl = asset($url);
                    // Only add if not already in the links array
                    if(!in_array($galleryUrl, $uniqueUrls)){
                        $links[] = $galleryUrl;
                        $uniqueUrls[] = $galleryUrl;
                    }
                }
            }
        }

        // Fallback to placeholder if no images
        if(count($links) == 0){
            $links[] = 'images/placeholder_guide.jpg';
        }

        return $links;
    }
}

if (! function_exists('get_thread_excerpt')) {
    function get_thread_excerpt($model)
    {   

        $desc = $model->excerpt;

        $excerpt = strip_tags(substr($desc, 0, 100));

        $excerpt = rtrim($excerpt, ", \t\n\r\0\x0B");

        return $excerpt;
    }
}


if (! function_exists('get_faqs_by_page')) {
    function get_faqs_by_page($page)
    {

        $frequentlyAskedQuestions = Faq::where('page','=',$page)->where('language','=',app()->getLocale())->get();

        return $frequentlyAskedQuestions;
    }
}

if (!function_exists('targets')) {
    function targets()
    {
        return new \App\Helpers\TargetHelper();
    }
}

if (!function_exists('getRatingLabel')) {
    function getRatingLabel($score)
    {
        if ($score >= 9) return __('guidings.Excellent');
        if ($score >= 8) return __('guidings.Very_Good');
        if ($score >= 7) return __('guidings.Good');
        if ($score >= 6) return __('guidings.Satisfactory');
        if ($score >= 5) return __('guidings.Sufficient');
        if ($score >= 4) return __('guidings.Poor');
        if ($score >= 3) return __('guidings.Insufficient');
        if ($score >= 2) return __('guidings.Very_Poor');
        if ($score >= 1) return __('guidings.Bad');
        return __('guidings.Not_Rated');
    }
}
