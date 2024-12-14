<?php
use Stichoza\GoogleTranslate\GoogleTranslate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use App\Models\Faq;

if (! function_exists('translate')) {
    function translate($string)
    {
        $currentLocale = app()->getLocale();
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
            $link = '/assets/guides/'.$model->thumbnail_path;
        }else{
            $link = 'images/placeholder_guide.jpg';
        }

        return $link;
    }
}

if (! function_exists('get_galleries_image_link')) {
    function get_galleries_image_link($model)
    {   

        $imagepath = '/assets/guides/';
        $links = [];

        if($model->thumbnail_path){
            $links[] = '/assets/guides/'.$model->thumbnail_path;
        }
        $galleries = json_decode($model->galleries,true);

        if(is_array($galleries) && count($galleries)){
            foreach($galleries as $url){
                if(!empty($url)){
                    $links[] = '/assets/guides/'.$url;
                }
            }
        
        }

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