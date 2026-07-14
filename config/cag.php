<?php

return [

  'contact_num' => env('CONTACT_NUM', ''),

  'en_app_url' => rtrim(env('EN_APP_URL', 'https://www.catchaguide.com'), '/'),
  'de_app_url' => rtrim(env('DE_APP_URL', 'https://www.catchaguide.de'), '/'),

  'pricing' => [
    'cat_0' => (float) env('PRICE_CAT_0', 0),
    'cat_1' => (float) env('PRICE_CAT_1', 350),
    'cat_2' => (float) env('PRICE_CAT_2', 1500),
    'cat_0_fee' => (float) env('PRICE_CAT_0_FEE', 0.10),
    'cat_1_fee' => (float) env('PRICE_CAT_1_FEE', 0.075),
    'cat_2_fee' => (float) env('PRICE_CAT_2_FEE', 0.003),
  ],

  'site_storage' => env('SITE_STORAGE', 'local'),
  'site_key' => env('SITE_KEY', ''),

];
