<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Platform email templates — admin catalogue
    |--------------------------------------------------------------------------
    | Metadata for Admin → Email maintenance. Source: Platform Email Status Report.
    */

    'templates' => [

        // ── Guest — fishing tours ──────────────────────────────────────────

        'guest_booking_request' => [
            'name' => 'Booking Request Submitted',
            'category' => 'guest',
            'description' => 'Confirms the guest that their booking request was received and is awaiting guide response.',
            'view' => 'mails.guest.guest_booking_request',
            'log_type' => 'guest_booking_request',
            'status' => 'active',
            'trigger_type' => 'immediate',
            'schedule' => 'Sent immediately after checkout',
            'scheduler_command' => null,
            'recipient' => 'Guest',
            'trigger_conditions' => [
                'Guest completes booking checkout',
                'Booking status is pending',
                'Queued mail worker processes the job',
            ],
            'pdf_ref' => '#7',
        ],

        'accepted_mail' => [
            'name' => 'Booking Confirmed',
            'category' => 'guest',
            'description' => 'Sent when a guide accepts the pending booking request.',
            'view' => 'mails.guest.accepted_mail',
            'log_type' => 'booking_accept_mail',
            'status' => 'active',
            'trigger_type' => 'immediate',
            'schedule' => 'Sent immediately when guide accepts',
            'scheduler_command' => null,
            'recipient' => 'Guest',
            'trigger_conditions' => [
                'Guide accepts booking in dashboard',
                'BookingStatusChanged event → status accepted',
                'Skipped in local environment for some flows',
            ],
            'pdf_ref' => '#8',
        ],

        'rejected_mail' => [
            'name' => 'Rejection & Alternative Dates',
            'category' => 'guest',
            'description' => 'Guide declined the request with a reason and at least one alternative date for rebooking.',
            'view' => 'mails.guest.rejected_mail',
            'log_type' => 'booking_reject_mail',
            'status' => 'active',
            'trigger_type' => 'immediate',
            'schedule' => 'Sent immediately when guide rejects',
            'scheduler_command' => null,
            'recipient' => 'Guest',
            'trigger_conditions' => [
                'Guide submits reject form with reason + alternative dates',
                'Booking status becomes rejected',
                'CEO also receives reject notification',
            ],
            'pdf_ref' => '#9',
        ],

        'guest_tour_reminder' => [
            'name' => 'Upcoming Tour Reminder',
            'category' => 'guest',
            'description' => 'Reminds the guest that their accepted fishing tour is in 48 hours.',
            'view' => 'mails.guest.guest_tour_reminder',
            'log_type' => 'guest_tour_reminder',
            'status' => 'active',
            'trigger_type' => 'scheduled',
            'schedule' => 'Hourly cron check',
            'scheduler_command' => 'bookings:send-guest-tour-reminders',
            'recipient' => 'Guest',
            'trigger_conditions' => [
                'Booking status is accepted',
                'Tour date is 2–3 days from now (48h window)',
                'Duplicate blocked within 24h via email log',
            ],
            'pdf_ref' => '#11',
        ],

        'guest_review' => [
            'name' => 'Review Request',
            'category' => 'guest',
            'description' => 'Asks the guest to leave a review after their tour has taken place.',
            'view' => 'mails.guest.guest_review',
            'log_type' => 'guest_review',
            'status' => 'active',
            'trigger_type' => 'scheduled',
            'schedule' => 'Hourly cron check',
            'scheduler_command' => 'bookings:send-guest-reviews',
            'recipient' => 'Guest',
            'trigger_conditions' => [
                'Booking status is accepted',
                'Tour date was 24–48 hours ago',
                'Booking not yet reviewed (is_reviewed = 0)',
                'Uses booking email for guest-checkout users',
            ],
            'pdf_ref' => '#12',
        ],

        'guest_expired_booking' => [
            'name' => 'Booking Expired',
            'category' => 'guest',
            'description' => 'Notifies the guest that their request expired because the guide did not respond in time.',
            'view' => 'mails.guest.guest_expired_booking',
            'log_type' => 'guest_booking_expired',
            'status' => 'active',
            'trigger_type' => 'scheduled',
            'schedule' => 'Hourly cron check',
            'scheduler_command' => 'update:booking-status',
            'recipient' => 'Guest',
            'trigger_conditions' => [
                'Booking status is pending',
                'expires_at has passed (24h or 48h window)',
                'Booking auto-cancelled by scheduler',
            ],
            'pdf_ref' => '#13',
        ],

        // ── Guide — fishing tours ──────────────────────────────────────────

        'guide_booking_request' => [
            'name' => 'New Booking Request',
            'category' => 'guide',
            'description' => 'Alerts the guide that a guest submitted a new booking request for their tour.',
            'view' => 'mails.guide.guide_booking_request',
            'log_type' => 'guide_booking_request',
            'status' => 'active',
            'trigger_type' => 'immediate',
            'schedule' => 'Sent immediately after guest books',
            'scheduler_command' => null,
            'recipient' => 'Guide',
            'trigger_conditions' => [
                'Guest completes booking checkout',
                'Booking status is pending',
                'Queued mail worker processes the job',
            ],
            'pdf_ref' => '#1',
        ],

        'guide_reminder' => [
            'name' => 'Respond Reminder (24h)',
            'category' => 'guide',
            'description' => 'Reminds the guide to respond before the booking request deadline — 24 hours before expiry.',
            'view' => 'mails.guide.guide_reminder',
            'log_type' => 'guide_reminder',
            'status' => 'active',
            'trigger_type' => 'scheduled',
            'schedule' => 'Hourly cron check',
            'scheduler_command' => 'bookings:send-guide-reminders',
            'recipient' => 'Guide',
            'trigger_conditions' => [
                'Booking status is pending',
                'expires_at is within the next 24 hours',
                'Duplicate blocked via email log',
            ],
            'pdf_ref' => '#2',
        ],

        'guide_reminder_12hrs' => [
            'name' => 'Respond Reminder (12h)',
            'category' => 'guide',
            'description' => 'Final reminder for the guide to accept or reject before the request expires.',
            'view' => 'mails.guide.guide_reminder_12hrs',
            'log_type' => 'guide_reminder_12hrs',
            'status' => 'active',
            'trigger_type' => 'scheduled',
            'schedule' => 'Hourly cron check',
            'scheduler_command' => 'bookings:send-guide-reminders-12hrs',
            'recipient' => 'Guide',
            'trigger_conditions' => [
                'Booking status is pending',
                'expires_at is within the next 12 hours',
                'Duplicate blocked via email log',
            ],
            'pdf_ref' => '#3',
        ],

        'guide_accepted_mail' => [
            'name' => 'Booking Accepted (Guest Details)',
            'category' => 'guide',
            'description' => 'Confirms to the guide that they accepted the booking and includes guest contact details.',
            'view' => 'mails.guide.guide_accepted_mail',
            'log_type' => 'guide_booking_accepted',
            'status' => 'active',
            'trigger_type' => 'immediate',
            'schedule' => 'Sent immediately when guide accepts',
            'scheduler_command' => null,
            'recipient' => 'Guide',
            'trigger_conditions' => [
                'Guide accepts booking in dashboard',
                'BookingStatusChanged event → status accepted',
            ],
            'pdf_ref' => '#4',
        ],

        'guide_expired_booking' => [
            'name' => 'Booking Expired',
            'category' => 'guide',
            'description' => 'Notifies the guide that a pending request expired without their response.',
            'view' => 'mails.guide.guide_expired_booking',
            'log_type' => 'guide_booking_expired',
            'status' => 'active',
            'trigger_type' => 'scheduled',
            'schedule' => 'Hourly cron check',
            'scheduler_command' => 'update:booking-status',
            'recipient' => 'Guide',
            'trigger_conditions' => [
                'Booking status is pending',
                'expires_at has passed',
                'Booking auto-cancelled by scheduler',
            ],
            'pdf_ref' => '#5',
        ],

        'guide_upcoming_tour' => [
            'name' => 'Upcoming Tour Reminder',
            'category' => 'guide',
            'description' => 'Reminds the guide that an accepted tour is scheduled in 48 hours.',
            'view' => 'mails.guide.guide_upcoming_tour',
            'log_type' => 'guide_reminder_upcoming_tour',
            'status' => 'active',
            'trigger_type' => 'scheduled',
            'schedule' => 'Hourly cron check',
            'scheduler_command' => 'bookings:send-guide-upcoming-tour-reminders',
            'recipient' => 'Guide',
            'trigger_conditions' => [
                'Booking status is accepted',
                'Tour date is exactly 48 hours from now',
                'Duplicate blocked via email log',
            ],
            'pdf_ref' => '#6',
        ],

        'guide_review_confirmation' => [
            'name' => 'New Review Received',
            'category' => 'guide',
            'description' => 'Notifies the guide when a guest submits a review for their tour.',
            'view' => 'mails.guide.review_confirmation_email',
            'log_type' => 'guide_review_confirmation',
            'status' => 'active',
            'trigger_type' => 'immediate',
            'schedule' => 'Sent when guest submits review',
            'scheduler_command' => null,
            'recipient' => 'Guide',
            'trigger_conditions' => [
                'Guest submits review form',
                'Review linked to accepted booking',
            ],
            'pdf_ref' => '#17',
        ],

        'guide_application_approved' => [
            'name' => 'Account Approved',
            'category' => 'guide',
            'description' => 'Welcomes a new guide after admin approves their application.',
            'view' => 'mails.guide.application_approved',
            'log_type' => 'guide_application_approved',
            'status' => 'active',
            'trigger_type' => 'immediate',
            'schedule' => 'Sent when admin approves guide',
            'scheduler_command' => null,
            'recipient' => 'Guide',
            'trigger_conditions' => [
                'Admin approves guide application in dashboard',
                'Guide account status set to active',
            ],
            'pdf_ref' => '#18',
        ],

        'guide_invoice' => [
            'name' => 'Invoice',
            'category' => 'guide',
            'description' => 'Guide invoice email — automatic sending is disabled; admin sends manually.',
            'view' => 'mails.guide.guide_invoice',
            'log_type' => 'guide_invoice',
            'status' => 'inactive',
            'trigger_type' => 'manual',
            'schedule' => 'Manual only (auto disabled)',
            'scheduler_command' => 'finance:auto-send-guide-invoices',
            'recipient' => 'Guide',
            'trigger_conditions' => [
                'Admin sends invoice from finance dashboard',
                'Automatic command exists but is commented out in scheduler',
            ],
            'pdf_ref' => '#19',
        ],

        // ── Trips, camps & vacations ─────────────────────────────────────

        'customer_contact_mail' => [
            'name' => 'Trip / Camp Contact Confirmation',
            'category' => 'other',
            'description' => 'Confirmation sent to the customer after submitting a trip or camp contact/booking form.',
            'view' => 'mails.customercontactmail',
            'log_type' => 'customer_contact_mail',
            'status' => 'active',
            'trigger_type' => 'immediate',
            'schedule' => 'Sent on form submit',
            'scheduler_command' => null,
            'recipient' => 'Customer',
            'trigger_conditions' => [
                'Customer submits trip or camp contact form',
                'Form validation passes',
            ],
            'pdf_ref' => null,
        ],

        'contact_mail' => [
            'name' => 'Trip / Camp Contact (Admin Notify)',
            'category' => 'other',
            'description' => 'Internal notification to admin/CEO when a trip or camp contact form is submitted.',
            'view' => 'mails.contactmail',
            'log_type' => 'contact_mail',
            'status' => 'active',
            'trigger_type' => 'immediate',
            'schedule' => 'Sent on form submit',
            'scheduler_command' => null,
            'recipient' => 'Admin',
            'trigger_conditions' => [
                'Customer submits trip or camp contact form',
                'Sent alongside customer confirmation',
            ],
            'pdf_ref' => null,
        ],

        'guest_vacation_booking_notification' => [
            'name' => 'Vacation Package Booking',
            'category' => 'other',
            'description' => 'Confirmation for vacation package booking requests.',
            'view' => 'mails.guest.guest_vacation_booking_notification',
            'log_type' => 'guest_vacation_booking_notification',
            'status' => 'active',
            'trigger_type' => 'immediate',
            'schedule' => 'Sent on form submit',
            'scheduler_command' => null,
            'recipient' => 'Guest',
            'trigger_conditions' => [
                'Guest submits vacation booking form',
                'Booking record created',
            ],
            'pdf_ref' => null,
        ],

        'guide_application_received' => [
            'name' => 'Application Received',
            'category' => 'guide',
            'description' => 'Confirms to a new guide that their application was received and is under review.',
            'view' => 'mails.guide.application_received',
            'log_type' => 'guide_application_received',
            'status' => 'active',
            'trigger_type' => 'immediate',
            'schedule' => 'Sent when guide submits application',
            'scheduler_command' => null,
            'recipient' => 'Guide',
            'trigger_conditions' => [
                'Guide completes onboarding / application form',
            ],
            'pdf_ref' => null,
        ],

        'guide_application_rejected' => [
            'name' => 'Application Rejected',
            'category' => 'guide',
            'description' => 'Notifies a guide applicant that their application was not approved.',
            'view' => 'mails.guide.application_rejected',
            'log_type' => 'guide_application_rejected',
            'status' => 'active',
            'trigger_type' => 'immediate',
            'schedule' => 'Sent when admin rejects guide application',
            'scheduler_command' => null,
            'recipient' => 'Guide',
            'trigger_conditions' => [
                'Admin rejects guide application in dashboard',
                'Optional rejection reason included',
            ],
            'pdf_ref' => null,
        ],

        // ── Not in active use ────────────────────────────────────────────

        'guide_cancelled_mail' => [
            'name' => 'Guest Cancellation (Guide Notify)',
            'category' => 'guide',
            'description' => 'Legacy path for guest self-cancel — UI is disabled; only guide decline is used in production.',
            'view' => 'mails.guide.guide_cancelled_mail',
            'log_type' => 'guide_cancelled_mail',
            'status' => 'inactive',
            'trigger_type' => 'immediate',
            'schedule' => 'Not exposed in UI',
            'scheduler_command' => null,
            'recipient' => 'Guide',
            'trigger_conditions' => [
                'Guest cancel route exists but button is disabled in profile',
                'Normal flow uses reject email instead',
            ],
            'pdf_ref' => '#10',
        ],
    ],

    'categories' => [
        'guest' => ['label' => 'Guest', 'icon' => 'fa-user', 'color' => 'success'],
        'guide' => ['label' => 'Guide', 'icon' => 'fa-compass', 'color' => 'primary'],
        'other' => ['label' => 'Trips & Camps', 'icon' => 'fa-map-marker-alt', 'color' => 'info'],
    ],

    'trigger_types' => [
        'immediate' => ['label' => 'Immediate', 'icon' => 'fa-bolt', 'color' => 'warning'],
        'scheduled' => ['label' => 'Scheduled', 'icon' => 'fa-clock', 'color' => 'info'],
        'manual' => ['label' => 'Manual', 'icon' => 'fa-hand-paper', 'color' => 'secondary'],
    ],

    'statuses' => [
        'active' => ['label' => 'Active', 'color' => 'success'],
        'inactive' => ['label' => 'Inactive', 'color' => 'secondary'],
    ],
];
