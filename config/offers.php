<?php

return [
    // Mixed catalogs (/offers, destination pages) keep a shorter page.
    'per_page' => 9,

    // Tours-only catalogs: /guidings, geo tour pages, methods, tour target pages.
    'tour_per_page' => 24,

    // Vacation-only catalogs on the offers listing (type=vacation, vacation target pages).
    'vacation_per_page' => 18,

    /*
    |--------------------------------------------------------------------------
    | FAQ fallback (used when no admin FAQs exist for page key "offers")
    |--------------------------------------------------------------------------
    | Keys resolve via __('…') so both en and de lang files must define them.
    */
    'faq' => [
        [
            'question' => 'offers.faq_q1',
            'answer' => 'offers.faq_a1',
        ],
        [
            'question' => 'offers.faq_q2',
            'answer' => 'offers.faq_a2',
        ],
        [
            'question' => 'offers.faq_q3',
            'answer' => 'offers.faq_a3',
        ],
        [
            'question' => 'offers.faq_q4',
            'answer' => 'offers.faq_a4',
        ],
    ],
];
