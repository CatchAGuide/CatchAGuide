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
                    $url = str_replace('https://catchaguide.com//', env('EN_APP_URL'), $imgUrl);
                    $images[$key] = $url;
                }

                if (app()->getLocale() == 'de') {
                    $url = str_replace('https://catchaguide.com//', env('DE_APP_URL'), $imgUrl);
                    $images[$key] = $url;
                }
            }
        }
        return $images;
    }
}