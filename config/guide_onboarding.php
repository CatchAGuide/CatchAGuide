<?php

return [
    'new_onboarding_enabled' => env('NEW_ONBOARDING_ENABLED', true),
    'company_onboarding_enabled' => env('GUIDE_COMPANY_ONBOARDING_ENABLED', false),
    'email_verification_enabled' => env('EMAIL_VERIFICATION_ENABLED', false),

    /*
    | Email address(es) for new guide application alerts.
    | Defaults to TO_CEO. Optional GUIDE_ADMIN_NOTIFICATION_EMAIL overrides (comma-separated).
    */
    'admin_notification_email' => env('GUIDE_ADMIN_NOTIFICATION_EMAIL') ?: env('TO_CEO', 'info@catchaguide.com'),
];
