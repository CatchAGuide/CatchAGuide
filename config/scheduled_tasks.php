<?php

/**
 * All scheduler tasks (must match what register() applies — single source of truth for admin UI).
 *
 * default: enabled, frequency, schedule_time (H:i), day_of_week (0=Sun..6=Sat), cron_expression (5-field cron)
 */
return [

    'frequencies' => [
        'every_minute' => 'Every minute',
        'every_five_minutes' => 'Every 5 minutes',
        'every_ten_minutes' => 'Every 10 minutes',
        'every_fifteen_minutes' => 'Every 15 minutes',
        'every_thirty_minutes' => 'Every 30 minutes',
        'hourly' => 'Every hour',
        'every_two_hours' => 'Every 2 hours',
        'daily' => 'Daily (midnight)',
        'daily_at' => 'Daily at specific time',
        'weekly' => 'Weekly on a weekday',
        'cron' => 'Custom (cron expression)',
    ],

    'tasks' => [

        'update_booking_status' => [
            'label' => 'Update booking status',
            'description' => 'Updates booking statuses (update:booking-status).',
            'command' => 'update:booking-status',
            'default' => [
                'enabled' => true,
                'frequency' => 'hourly',
                'schedule_time' => null,
                'day_of_week' => null,
                'cron_expression' => null,
            ],
        ],

        'bookings_send_guest_reviews' => [
            'label' => 'Send guest review requests',
            'description' => 'Guest review emails (bookings:send-guest-reviews).',
            'command' => 'bookings:send-guest-reviews',
            'default' => [
                'enabled' => true,
                'frequency' => 'hourly',
                'schedule_time' => null,
                'day_of_week' => null,
                'cron_expression' => null,
            ],
        ],

        'bookings_create_automatic_reviews' => [
            'label' => 'Create automatic reviews',
            'description' => 'Automatic reviews (bookings:create-automatic-reviews).',
            'command' => 'bookings:create-automatic-reviews',
            'default' => [
                'enabled' => true,
                'frequency' => 'daily_at',
                'schedule_time' => '02:15',
                'day_of_week' => null,
                'cron_expression' => null,
            ],
        ],

        'bookings_send_guest_tour_reminders' => [
            'label' => 'Guest tour reminders',
            'description' => 'Reminders to guests (bookings:send-guest-tour-reminders).',
            'command' => 'bookings:send-guest-tour-reminders',
            'default' => [
                'enabled' => true,
                'frequency' => 'hourly',
                'schedule_time' => null,
                'day_of_week' => null,
                'cron_expression' => null,
            ],
        ],

        'bookings_send_guide_reminders' => [
            'label' => 'Guide reminders (24h)',
            'description' => 'Guide reminders (bookings:send-guide-reminders).',
            'command' => 'bookings:send-guide-reminders',
            'default' => [
                'enabled' => true,
                'frequency' => 'hourly',
                'schedule_time' => null,
                'day_of_week' => null,
                'cron_expression' => null,
            ],
        ],

        'bookings_send_guide_reminders_12hrs' => [
            'label' => 'Guide reminders (12h)',
            'description' => 'Optional 12-hour guide reminders (bookings:send-guide-reminders-12hrs).',
            'command' => 'bookings:send-guide-reminders-12hrs',
            'default' => [
                'enabled' => false,
                'frequency' => 'hourly',
                'schedule_time' => null,
                'day_of_week' => null,
                'cron_expression' => null,
            ],
        ],

        'guidings_generate_filters' => [
            'label' => 'Generate guiding filter mappings',
            'description' => 'Rebuilds filter mappings (guidings:generate-filters).',
            'command' => 'guidings:generate-filters',
            'default' => [
                'enabled' => true,
                'frequency' => 'hourly',
                'schedule_time' => null,
                'day_of_week' => null,
                'cron_expression' => null,
            ],
            'without_overlapping' => true,
            'run_in_background' => true,
        ],

        'cache_warm_files' => [
            'label' => 'Warm file existence cache',
            'description' => 'File cache warmup (cache:warm-files).',
            'command' => 'cache:warm-files',
            'default' => [
                'enabled' => true,
                'frequency' => 'every_two_hours',
                'schedule_time' => null,
                'day_of_week' => null,
                'cron_expression' => null,
            ],
            'without_overlapping' => true,
            'run_in_background' => true,
        ],

        'images_cleanup_report_only' => [
            'label' => 'Image cleanup (report only)',
            'description' => 'Reports missing image files (images:cleanup --report-only).',
            'command' => 'images:cleanup --report-only',
            'default' => [
                'enabled' => false,
                'frequency' => 'weekly',
                'schedule_time' => '03:00',
                'day_of_week' => 0,
                'cron_expression' => null,
            ],
            'append_output_to' => 'logs/image-cleanup.log',
        ],

        'generate_sitemap' => [
            'label' => 'Generate sitemap',
            'description' => 'Sitemap generation (generate:sitemap).',
            'command' => 'generate:sitemap',
            'default' => [
                'enabled' => true,
                'frequency' => 'daily',
                'schedule_time' => null,
                'day_of_week' => null,
                'cron_expression' => null,
            ],
            'run_in_background' => true,
        ],

        'threat_intelligence_cleanup' => [
            'label' => 'Threat intelligence cleanup',
            'description' => 'Deletes threat_intelligence rows older than retention (threat-intelligence:cleanup).',
            'command' => 'threat-intelligence:cleanup',
            'default' => [
                'enabled' => true,
                'frequency' => 'daily_at',
                'schedule_time' => '03:30',
                'day_of_week' => null,
                'cron_expression' => null,
            ],
            'without_overlapping' => true,
            'run_in_background' => true,
            'append_output_to' => 'logs/threat-intelligence-cleanup.log',
        ],


        'vacation_translate_admin_changes' => [
            'label' => 'Vacation translations (admin changes)',
            'description' => 'Translates vacation content after admin edits (vacation:translate --admin-changes --relations).',
            'command' => 'vacation:translate --admin-changes --relations',
            'default' => [
                'enabled' => false,
                'frequency' => 'daily',
                'schedule_time' => null,
                'day_of_week' => null,
                'cron_expression' => null,
            ],
            'without_overlapping' => true,
            'run_in_background' => true,
        ],

        'guiding_translate_detect_language' => [
            'label' => 'Guiding source language fix',
            'description' => 'Audit/fix guidings.language from content (guiding:translate --detect-language --mismatches-only). Does not translate.',
            'command' => 'guiding:translate --detect-language --mismatches-only',
            'default' => [
                'enabled' => false,
                'frequency' => 'daily',
                'schedule_time' => null,
                'day_of_week' => null,
                'cron_expression' => null,
            ],
            'without_overlapping' => true,
            'run_in_background' => true,
        ],

        'ical_sync_feeds' => [
            'label' => 'iCal feed sync',
            'description' => 'Syncs all iCal feeds (ical:sync-feeds).',
            'command' => 'ical:sync-feeds',
            'default' => [
                'enabled' => true,
                'frequency' => 'every_two_hours',
                'schedule_time' => null,
                'day_of_week' => null,
                'cron_expression' => null,
            ],
            'without_overlapping' => true,
            'run_in_background' => true,
            'append_output_to' => 'logs/ical-sync.log',
        ],

        'finance_auto_send_guide_invoices' => [
            'label' => 'Auto-send guide invoices',
            'description' => 'Sends guide commission invoices after tour date (finance:auto-send-guide-invoices).',
            'command' => 'finance:auto-send-guide-invoices',
            'default' => [
                'enabled' => false,
                'frequency' => 'daily_at',
                'schedule_time' => '03:10',
                'day_of_week' => null,
                'cron_expression' => null,
            ],
            'without_overlapping' => true,
            'run_in_background' => true,
            'append_output_to' => 'logs/finance-invoices.log',
        ],
    ],
];
