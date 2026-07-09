<?php

namespace App\Presenters\Vacation;

class TripInquiryPayloadFormatter
{
    /**
     * @param  array<string, mixed>  $fields
     */
    public function format(array $fields, string $userMessage): string
    {
        $lines = [];

        if (! empty($fields['date_flexible'])) {
            $lines[] = 'Date flexible: ' . ($fields['date_flexible'] === 'yes' ? 'Yes' : 'No');
        }
        if (! empty($fields['room_configuration'])) {
            $lines[] = 'Room configuration: ' . $fields['room_configuration'];
        }
        if (! empty($fields['dietary_requirements'])) {
            $lines[] = 'Dietary requirements: ' . $fields['dietary_requirements'];
        }
        if (! empty($fields['experience_level'])) {
            $lines[] = 'Experience level: ' . $fields['experience_level'];
        }
        if (! empty($fields['addons'])) {
            $lines[] = 'Add-ons: ' . $fields['addons'];
        }

        $structured = implode("\n", $lines);

        return trim($structured . "\n\n" . $userMessage);
    }
}
