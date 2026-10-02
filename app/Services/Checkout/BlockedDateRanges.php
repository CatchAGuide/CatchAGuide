<?php

namespace App\Services\Checkout;

use Carbon\CarbonImmutable;

/**
 * Normalizes a guiding's blocked events (see Guiding::getBlockedEvents) into a sorted list of
 * non-overlapping [from, due] Y-m-d ranges. Merging keeps the checkout payload small — the raw
 * list holds one row per blocked day — and lets the client check a day with plain string
 * comparisons instead of expanding every range into single days.
 */
final class BlockedDateRanges
{
    /**
     * @param  iterable<array{from?: mixed, due?: mixed}>  $events
     * @return list<array{from: string, due: string}>
     */
    public static function merge(iterable $events): array
    {
        $ranges = [];
        foreach ($events as $event) {
            $from = self::date($event['from'] ?? null);
            $due = self::date($event['due'] ?? null);
            if ($from === null || $due === null) {
                continue;
            }

            $ranges[] = $from <= $due ? ['from' => $from, 'due' => $due] : ['from' => $due, 'due' => $from];
        }

        usort($ranges, fn (array $a, array $b) => [$a['from'], $a['due']] <=> [$b['from'], $b['due']]);

        $merged = [];
        foreach ($ranges as $range) {
            $last = array_key_last($merged);
            if ($last !== null && $range['from'] <= self::nextDay($merged[$last]['due'])) {
                if ($range['due'] > $merged[$last]['due']) {
                    $merged[$last]['due'] = $range['due'];
                }

                continue;
            }

            $merged[] = $range;
        }

        return $merged;
    }

    private static function date(mixed $value): ?string
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
            return null;
        }

        return substr($value, 0, 10);
    }

    private static function nextDay(string $date): string
    {
        return CarbonImmutable::createFromFormat('!Y-m-d', $date)->addDay()->format('Y-m-d');
    }
}
