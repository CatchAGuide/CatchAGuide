<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Gift vouchers
    |--------------------------------------------------------------------------
    | Generated Catch a Guide standard voucher PDFs hold buyer and recipient names, so they go
    | to a private disk (not the public media CDN). Stored per order so the same file can be
    | re-sent later instead of being regenerated from changed tour data.
    */

    'pdf_disk' => env('VOUCHER_PDF_DISK', 'local'),

    'pdf_folder' => 'vouchers',

];
