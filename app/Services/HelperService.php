<?php

namespace App\Services;

use Carbon\Carbon;
use Exception;

class HelperService {

    public function calculateRates($price)
    {
        if($price <= config('cag.pricing.cat_0')) {
            $fee = $price * config('cag.pricing.cat_0_fee');
        } elseif($price > config('cag.pricing.cat_0') && $price <= config('cag.pricing.cat_1')) {
            $fee = $price * config('cag.pricing.cat_0_fee');
        } elseif ($price > config('cag.pricing.cat_1') && $price <= config('cag.pricing.cat_2')) {
            $fee = $price * config('cag.pricing.cat_1_fee');
        } elseif($price > config('cag.pricing.cat_2')) {
            $fee = $price * config('cag.pricing.cat_2_fee');
        } else {
            $fee = 0; // Fallback
        }
        return $fee;
    }

    public function convertAmountToString($amount)
    {
        $string = twoString($amount);
        $return = str_replace('.', '', $string);
        return $return;
    }
}
