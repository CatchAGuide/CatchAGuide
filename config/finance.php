<?php

return [
    // VAT / sales tax applied to the platform commission invoice sent to guides.
    // Default matches current behaviour (19%).
    'tax_rate' => (float) env('FINANCE_TAX_RATE', 0.19),

    // Default invoice due date (days after invoice is marked as sent).
    'invoice_due_days' => (int) env('FINANCE_INVOICE_DUE_DAYS', 10),

    // Bookings store platform commission in EUR on `cag_percent`. Cap vs gross to limit bad rows.
    'commission_max_ratio_of_gross' => (float) env('FINANCE_COMMISSION_MAX_RATIO_OF_GROSS', 0.5),
];

