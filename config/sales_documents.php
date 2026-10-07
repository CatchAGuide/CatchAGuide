<?php

/*
 * Offer & booking confirmation builder (Admin › Sales › Offers).
 */
return [

    // Default "valid until" for a new offer, in days from today.
    'validity_days' => (int) env('SALES_OFFER_VALIDITY_DAYS', 14),

    // Receives the internal "offer accepted" notification next to the document's creator.
    'sales_inbox' => env('SALES_INBOX', env('TO_CEO', 'info@catchaguide.com')),

    // Customer and listing links use the document language's domain (catchaguide.de for DE,
    // catchaguide.com for EN). Off outside production so local links stay on the dev host.
    'language_domains' => (bool) env('SALES_LANGUAGE_DOMAINS', env('APP_ENV') === 'production'),

];
