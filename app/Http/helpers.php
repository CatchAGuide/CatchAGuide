<?php

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

