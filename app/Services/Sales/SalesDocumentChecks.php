<?php

namespace App\Services\Sales;

/**
 * Validation rules of spec §9. Errors block sending (and are shown on the card); warnings are
 * shown on the card and counted in the totals bar, sending still works with them.
 */
class SalesDocumentChecks
{
    /**
     * @param  array<string, mixed>  $header
     * @return array{errors: list<string>, warnings: list<string>}
     */
    public function check(array $header, SalesQuote $quote): array
    {
        $errors = [];

        if (filter_var(trim((string) ($header['email'] ?? '')), FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = __('sales.error.email');
        }

        if ($quote->lineCount() === 0) {
            $errors[] = __('sales.error.no_lines');
        }

        return [
            'errors' => array_values(array_unique([...$errors, ...$quote->errors()])),
            'warnings' => array_values($quote->warnings()),
        ];
    }
}
