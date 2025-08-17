<?php
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Stichoza\GoogleTranslate\GoogleTranslate;

use App\Models\Faq;
use App\Models\EmailLog;

if (! function_exists('translate')) {
    function translate($string, $language = '')
    {
        if ($string === null) {
            return '';
        }
        
        $currentLocale = ($language != '' || $language != null) ? $language : app()->getLocale();
        $cacheKey = 'translation_'.$currentLocale.'_'.$string;

        $translation = Cache::rememberForever($cacheKey, function () use ($string, $currentLocale) {
            try {
                $translate = GoogleTranslate::trans($string, $currentLocale);

                if (strpos($translate, 'Führungen')) {
                    $translate = str_replace('Führungen', 'Angelguidings', $translate);
                }

                if (strpos($translate, 'Führung')) {
                    $translate = str_replace('Führung', 'guiding', $translate);
                }

                return ucfirst($translate);
            } catch (\Exception $e) {
                Log::error('Translation failed: ' . $e->getMessage(), [
                    'string' => $string,
                    'locale' => $currentLocale
                ]);
                return $string;
            }
        });

        return $translation;
    }
}

if (! function_exists('translateVacationField')) {
    /**
     * Get translated field from vacation data with fallback to original
     * @param object $translatedVacation - The translated vacation object from controller
     * @param object $originalVacation - The original vacation model
     * @param string $field - The field name to get
     * @return string|array
     */
    function translateVacationField($translatedVacation, $originalVacation, string $field)
    {
        // Try to get from translated data first
        if (isset($translatedVacation->$field) && !empty($translatedVacation->$field)) {
            $value = $translatedVacation->$field;
        } else {
            // Fallback to original
            $value = $originalVacation->$field;
        }
        
        // Handle JSON arrays
        if (is_string($value) && (strpos($value, '[') === 0 || strpos($value, '{') === 0)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }
        
        return $value ?? '';
    }
}

if (! function_exists('formatVacationArray')) {
    /**
     * Format array values for display (e.g., target fish, travel times)
     * @param array|string $value
     * @return string
     */
    function formatVacationArray($value): string
    {
        if (is_array($value)) {
            return implode(', ', $value);
        }
        
        if (is_string($value) && (strpos($value, '[') === 0 || strpos($value, '{') === 0)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return implode(', ', $decoded);
            }
        }
        
        return (string) $value;
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
        // Use cached file check for data integrity
        if($model->thumbnail_path && file_exists_cached($model->thumbnail_path)){
            return asset($model->thumbnail_path);
        }
        
        return asset('images/placeholder_guide.jpg');
    }
}

if (! function_exists('get_galleries_image_link')) {
    function get_galleries_image_link($model, $type = 0)
    {   
        $links = [];
        $uniqueUrls = []; // Track unique URLs to prevent duplicates

        // Add thumbnail if it exists (with cached file check)
        if($model->thumbnail_path){
            if(file_exists_cached($model->thumbnail_path)){
                $thumbnailUrl = asset($model->thumbnail_path);
                $links[] = $thumbnailUrl;
                $uniqueUrls[] = $thumbnailUrl;
            }
        }

        // Get gallery images based on type
        if($type == 0){
            $galleries = $model->gallery_images;
            if (is_string($galleries)) {
                $galleries = json_decode($galleries, true);
            }
            if (!is_array($galleries)) {
                $galleries = [];
            }
        }else{
            $galleries = $model->gallery;
            if (is_string($galleries)) {
                $galleries = json_decode($galleries, true);
            }
            if (!is_array($galleries)) {
                $galleries = [];
            }
        }

        // Add gallery images, avoiding duplicates (with cached file checks)
        if(is_array($galleries) && count($galleries)){
            foreach($galleries as $url){
                if(!empty($url) && file_exists_cached($url)){
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
            $links[] = asset('images/placeholder_guide.jpg');
        }

        return $links;
    }
}

if (! function_exists('file_exists_cached')) {
    function file_exists_cached($path)
    {
        // Skip file checks in staging/production for performance, but keep in development
        if (app()->environment(['staging', 'production'])) {
            // In production, assume files exist (faster) but cache negative results
            $cacheKey = 'file_missing_' . md5($path);
            
            // If we've previously confirmed this file is missing, return false
            if (Cache::has($cacheKey)) {
                return false;
            }
            
            // Otherwise assume it exists (optimistic approach)
            return true;
        }
        
        // In development, do the actual file check with caching
        $cacheKey = 'file_exists_' . md5($path);
        
        return Cache::remember($cacheKey, 3600, function() use ($path) {
            return file_exists(public_path($path));
        });
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

if (!function_exists('CheckEmailLog')) {
    function CheckEmailLog($type, $target, $email)
    {
        $existingEmail = EmailLog::where('email', $email)
            ->where('type', $type)
            ->where('target', $target)
            ->where('created_at', '>=', now()->subHours(24)) // Adjust time window as needed
            ->first();
            
        if ($existingEmail) {
            Log::info("Duplicate email prevented: {$type} to {$email} for target {$target}");
            return true; // Exit without sending the email
        }

        return false;
    }
}

if (!function_exists('decode_if_json')) {
    function decode_if_json($value)
    {
        if (is_string($value) && (strpos($value, '[') === 0 || strpos($value, '{') === 0)) {
            return json_decode($value, true) ?? $value;
        }
        return $value ?? [];
    }
}

if (!function_exists('getUserField')) {
    function getUserField($user, $booking, $guestField, $infoField) {
        if ($booking->is_guest) {
            return $user->$guestField ?? '';
        }
        return $user->information && $user->information->$infoField ? $user->information->$infoField : '';
    }
}
