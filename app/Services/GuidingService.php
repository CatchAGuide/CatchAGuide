<?php
namespace App\Services;

class GuidingService
{
    public function getImagesUrl($guiding)
    {
        $images = [];
        for ($i = 0; $i <= 4; $i++) {
            $key = 'image_' . $i;
            $imgUrl = $guiding->getImageUrl($key, 'view');
            
            if ($imgUrl !== null && $imgUrl !== '') {
                $images[$key] = $imgUrl;
                
                if (app()->getLocale() == 'en') {
                    $url = str_replace('https://catchaguide.com//', config('cag.en_app_url'), $imgUrl);
                    $images[$key] = $url;
                }

                if (app()->getLocale() == 'de') {
                    $url = str_replace('https://catchaguide.com//', config('cag.de_app_url'), $imgUrl);
                    $images[$key] = $url;
                }
            }
        }
        return $images;
    }
}