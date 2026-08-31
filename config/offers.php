<?php

return [
    'per_page' => 9,

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
