<?php

namespace App\Services;

use Carbon\Carbon;
use Exception;

class HelperService {

    public function calculateRates($price)
    {
        if($price > env('PRICE_CAT_0') && $price <= env('PRICE_CAT_1')) {
            $fee = $price * env('PRICE_CAT_0_FEE');
        } elseif ($price > env('PRICE_CAT_1') && $price <= env('PRICE_CAT_2')) {
            $fee = $price * env('PRICE_CAT_1_FEE');
        } elseif($price > env('PRICE_CAT_2')) {
            $fee = $price * env('PRICE_CAT_2_FEE');
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
