<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Dynamic Terms & Conditions
    |--------------------------------------------------------------------------
    |
    | When true, the /agb page renders CMS-managed sections from the database
    | (with a left sidebar navigation). When false, or when no active sections
    | exist for the current locale, the legacy static AGB page is shown.
    |
    */
    'dynamic_enabled' => filter_var(env('DYNAMIC_TERMS_ENABLED', false), FILTER_VALIDATE_BOOLEAN),

];
