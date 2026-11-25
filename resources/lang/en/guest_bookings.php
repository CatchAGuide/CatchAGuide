<?php

// Guest booking link (/b/{token} → /your-booking/{id}). Wording rule: "your booking", never
// "your account" or "your profile" (plan "Guest Booking Access").

return [
    'title' => 'Your booking',
    'eyebrow' => 'Booking details',
    'eyebrow_voucher' => 'Gift voucher order',
    'subtitle' => 'Everything about your booking in one place. Keep the email with this link: it always opens this page.',
    'redacted_note' => 'This trip was more than 90 days ago, so we only show the main details. Contact details and messages are hidden.',
    'contact_hidden' => 'Hidden after the tour',
    'add_to_calendar' => 'Add to calendar',
    'download_voucher' => 'Download voucher (PDF)',
    'calendar_description' => 'Your Catch A Guide booking. Guide: :guide',
    'help_title' => 'Need to change something?',
    'help_tour' => 'For a different date, more people or a cancellation, write to your guide or to us. We\'ll take care of it with your guide.',
    'help_other' => 'For questions or changes, write to us. We\'ll get back to you as soon as we can.',
    'contact_support' => 'Contact support',
    'privacy' => 'We show you the details you gave us when you booked, so you and your guide have everything for your trip. We don\'t create an account for you, and we don\'t use this page for advertising.',
    'privacy_link' => 'Privacy policy',

    'types' => [
        'tour' => 'tour booking',
        'camp' => 'fishing camp request',
        'trip' => 'fishing trip request',
        'voucher' => 'gift voucher order',
    ],

    'unavailable' => [
        'title' => 'This link doesn\'t work here',
        'text' => 'Open your booking with the link from your booking email. If it still doesn\'t work, the link may have been replaced: write to us and we\'ll send you the current one.',
        'signed_in' => 'My bookings',
        'home' => 'Back to the homepage',
    ],

    'mail' => [
        'button' => 'View your booking',
        'button_voucher' => 'View your order',
        'hint' => 'Your booking at any time, no password needed. Keep this email.',
        'link_subject' => 'The link to your booking',
        'link_title' => 'The link to your booking',
        'link_greeting' => 'Hello,',
        'link_body' => 'here is the link to your :type. It shows the current status and all the details, without a password.',
        'link_not_you' => 'Didn\'t ask for this? You can ignore this email. The link only shows this one booking.',
    ],

    'admin' => [
        'send' => 'Send booking link',
        'revoke' => 'Turn link off and send a new one',
        'confirm_send' => 'Send the link to this booking to :email?',
        'confirm_revoke' => 'Turn the current link off (it stops working at once) and send a new one to :email?',
        'sent' => 'Booking link sent to :email.',
        'revoked' => 'Old link turned off. New link sent to :email.',
        'no_email' => 'This booking has no email address, so no link can be sent.',
    ],
];
