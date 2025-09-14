<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Booking Expiration Settings
    |--------------------------------------------------------------------------
    |
    | These settings control how long booking requests remain valid before
    | they expire and are automatically cancelled.
    |
    */

    // Default expiration time in hours for bookings
    'default_expiration_hours' => env('BOOKING_DEFAULT_EXPIRATION_HOURS', 24),

    // Extended expiration time in hours for future bookings
    'extended_expiration_hours' => env('BOOKING_EXTENDED_EXPIRATION_HOURS', 48),

    // Days threshold for extended expiration (if booking date is more than this many days away)
    'extended_expiration_days_threshold' => env('BOOKING_EXTENDED_EXPIRATION_DAYS_THRESHOLD', 2),

    // Guide reminder time periods (in hours before expiration)
    'reminder_periods' => [
        24, 12, 6, 3, 1
    ],

    // Email log type prefix for guide reminders
    'reminder_email_type_prefix' => 'guide_booking_reminder_',
];
