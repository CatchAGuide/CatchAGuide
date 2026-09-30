<?php

namespace App\Presenters\Vacation;

use Illuminate\Support\Carbon;

/**
 * Human-readable camp checkout estimate: price lines, totals and the plain-text summary that
 * goes into the request's message (admin list + notification mails). The checkout page renders
 * the same lines client-side from resources/js/checkout/camp-pricing.js.
 */
class CampQuotePresenter
{
    /**
     * @param  array{type: string, name: string, quantity: int, unit_price: ?float, amount: float}  $line
     */
    public function lineLabel(array $line): string
    {
        $quantity = (int) $line['quantity'];

        $label = match ($line['type']) {
            'accommodation' => __('checkout.camp.line_accommodation', [
                'name' => $line['name'],
                'nights' => trans_choice('checkout.camp.nights_count', $quantity, ['count' => $quantity]),
            ]),
            'boat' => __('checkout.camp.line_boat', [
                'days' => trans_choice('checkout.camp.days_count', $quantity, ['count' => $quantity]),
            ]),
            'tour' => __('checkout.camp.line_tour', ['name' => $line['name']]),
            default => $line['name'],
        };

        // "× 55 €" only when the amount really is quantity × rate (not a weekly rate).
        $unitPrice = $line['unit_price'] ?? null;
        if (in_array($line['type'], ['accommodation', 'boat'], true)
            && $unitPrice !== null
            && abs($unitPrice * $quantity - (float) $line['amount']) < 0.01) {
            return __('checkout.camp.line_rate', ['label' => $label, 'price' => $this->money($unitPrice)]);
        }

        return $label;
    }

    /**
     * @param  list<array{type: string, name: string, quantity: int, unit_price: ?float, amount: float}>  $lines
     * @return list<array{label: string, amount: string}>
     */
    public function lines(array $lines): array
    {
        return array_map(fn (array $line) => [
            'label' => $this->lineLabel($line),
            'amount' => (float) $line['amount'] > 0 ? $this->money((float) $line['amount']) : __('checkout.camp.on_request'),
        ], $lines);
    }

    public function total(?float $total): string
    {
        return $total !== null && $total > 0
            ? __('checkout.camp.approx', ['amount' => $this->money($total)])
            : __('checkout.camp.on_request');
    }

    public function stay(int $nights, int $persons): string
    {
        return trans_choice('checkout.camp.nights_count', $nights, ['count' => $nights])
            .' · '.trans_choice('checkout.camp.persons_count', $persons, ['count' => $persons]);
    }

    public function date(Carbon|string|null $date): string
    {
        if ($date === null || $date === '') {
            return '';
        }

        return Carbon::parse($date)->format(app()->getLocale() === 'de' ? 'd.m.Y' : 'M j, Y');
    }

    /**
     * Plain-text request summary stored as the request's message.
     *
     * @param  list<array{type: string, name: string, quantity: int, unit_price: ?float, amount: float}>  $lines
     */
    public function summary(string $arrival, int $nights, int $persons, array $lines, float $total, string $guestMessage): string
    {
        $rows = [
            __('checkout.camp.summary.heading'),
            __('checkout.camp.summary.arrival').': '.$this->date($arrival),
            __('checkout.camp.summary.stay').': '.$this->stay($nights, $persons),
        ];

        foreach ($this->lines($lines) as $line) {
            $rows[] = '- '.$line['label'].': '.$line['amount'];
        }

        $rows[] = __('checkout.camp.summary.total').': '.$this->total($total);

        if (trim($guestMessage) !== '') {
            $rows[] = '';
            $rows[] = __('checkout.camp.summary.guest_message').':';
            $rows[] = trim($guestMessage);
        }

        return implode("\n", $rows);
    }

    /**
     * Whole euros without decimals, cents only when present: "1.234 €" (de) / "€1,234" (en).
     */
    public function money(float $amount): string
    {
        $decimals = abs($amount - round($amount)) < 0.005 ? 0 : 2;

        return app()->getLocale() === 'de'
            ? number_format($amount, $decimals, ',', '.')."\u{00A0}€"
            : '€'.number_format($amount, $decimals, '.', ',');
    }
}
