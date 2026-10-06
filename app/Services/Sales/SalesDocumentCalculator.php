<?php

namespace App\Services\Sales;

use App\Enums\Sales\SalesItemType;
use App\Enums\Sales\SalesPriceUnit;
use App\Enums\TourExtraUnit;
use App\Services\Checkout\Camp\StayRate;
use Carbon\CarbonImmutable;

/**
 * Prices the product cards of a sales document (spec §5) from their listing snapshots alone,
 * so the builder preview, the saved items, the customer page and the emails all show the
 * same numbers. Customer-facing texts use the document language; formulas, warnings and
 * errors are for the employee and use the app locale.
 *
 * Card state (also what SalesDocumentStateMapper rebuilds from saved items):
 *  - tour:   listing_id, product, date, persons, override, extras[key => {qty, follow}]
 *  - camp:   listing_id, product, persons, subs[{key, kind, option_id, from, to, date, days, qty, override}]
 *  - trip:   listing_id, product, date, persons, override
 *  - custom: title, description, date, quantity, unit_label, unit_price
 */
class SalesDocumentCalculator
{
    /**
     * @param  list<array<string, mixed>>  $cards
     */
    public function calculate(array $cards, string $locale, ?CarbonImmutable $today = null): SalesQuote
    {
        $today ??= CarbonImmutable::today();
        $groups = [];

        foreach (array_values($cards) as $card) {
            $group = match ($card['type'] ?? null) {
                'tour' => $this->tour($card, $locale),
                'camp' => $this->camp($card, $locale),
                'trip' => $this->trip($card, $locale),
                'custom' => $this->custom($card, $locale),
                default => null,
            };

            if ($group === null) {
                continue;
            }

            foreach ($group['lines'] as $line) {
                foreach (array_filter([$line['from'], $line['to']]) as $date) {
                    if (SalesFormat::parse($date)?->lt($today)) {
                        $group['warnings'][] = __('sales.warn.date_in_past', ['title' => $line['title']]);
                        break;
                    }
                }
            }

            if (($card['product']['translation_missing'] ?? false) === true) {
                $group['warnings'][] = __('sales.warn.translation_missing', ['language' => strtoupper($locale)]);
            }

            $group['warnings'] = array_values(array_unique($group['warnings']));
            $group['subtotal'] = round(array_sum(array_column($group['lines'], 'total')), 2);
            $groups[] = $group;
        }

        $dates = [];
        foreach ($groups as $group) {
            foreach ($group['lines'] as $line) {
                array_push($dates, ...array_filter([$line['from'], $line['to']]));
            }
        }
        sort($dates);

        return new SalesQuote(
            $groups,
            round(array_sum(array_column($groups, 'subtotal')), 2),
            $dates[0] ?? null,
            $dates !== [] ? end($dates) : null,
        );
    }

    /**
     * @param  array<string, mixed>  $card
     * @return array<string, mixed>
     */
    private function tour(array $card, string $locale): array
    {
        $product = $card['product'] ?? null;
        $group = $this->group($card, 'tour', $product);
        if (! is_array($product)) {
            $group['errors'][] = __('sales.error.select_product.tour');

            return $group;
        }

        $persons = max(1, (int) ($card['persons'] ?? 1));
        $max = max(1, (int) ($product['max'] ?? 1));
        $priced = min($persons, $max);
        $calculated = (float) ($product['prices'][$priced] ?? $product['prices'][(string) $priced] ?? 0);
        $date = $this->date($card['date'] ?? null);

        if ($persons > $max) {
            $group['warnings'][] = __('sales.warn.tour_max', ['persons' => $persons, 'max' => $max]);
        }
        if ($date === null) {
            $group['errors'][] = __('sales.error.tour_date');
        }

        $line = $this->priced($calculated, $card['override'] ?? null);
        if ($line['total'] <= 0 && ! $line['adjusted']) {
            $group['errors'][] = __('sales.error.price_required', ['title' => $product['title']]);
        }

        $group['lines'][] = $this->line($card['key'] ?? 'tour', SalesItemType::Tour, $line, [
            'title' => trim($product['title'].($product['duration'] ? ' · '.$product['duration'] : '')),
            'detail' => implode(' · ', array_filter([$date !== null ? SalesFormat::date($date, $locale) : null, $this->persons($persons, $locale)])),
            'formula' => trans_choice('sales.formula.tour', $priced, ['count' => $priced]),
            'from' => $date,
            'to' => $date,
            'listing_type' => 'guiding',
            'listing_id' => $product['id'],
            'persons' => $persons,
            'price_unit' => SalesPriceUnit::GroupPrice,
            'unit_price' => $calculated,
        ]);

        $selected = (array) ($card['extras'] ?? []);
        foreach ((array) ($product['extras'] ?? []) as $extra) {
            $choice = $selected[$extra['key']] ?? null;
            if (! is_array($choice)) {
                continue;
            }

            $unit = TourExtraUnit::fromListing($extra['unit'] ?? null);
            $follow = $unit === TourExtraUnit::PerPerson && (bool) ($choice['follow'] ?? true);
            $quantity = match ($unit) {
                TourExtraUnit::PerPerson => $follow ? $persons : max(0, (int) ($choice['qty'] ?? $persons)),
                TourExtraUnit::PerBooking => 1,
                TourExtraUnit::PerItem => max(0, (int) ($choice['qty'] ?? 1)),
            };
            $price = (float) $extra['price'];

            if ($unit === TourExtraUnit::PerPerson && $quantity > $persons) {
                $group['warnings'][] = __('sales.warn.extra_persons', ['name' => $extra['name'], 'count' => $quantity, 'persons' => $persons]);
            }

            $total = round($price * $quantity, 2);
            $group['lines'][] = $this->line(($card['key'] ?? 'tour').'-x'.$extra['key'], SalesItemType::TourExtra, ['calculated' => $total, 'total' => $total, 'adjusted' => false], [
                'extra' => true,
                'title' => $extra['name'],
                'detail' => match ($unit) {
                    TourExtraUnit::PerPerson => $this->persons($quantity, $locale).' × '.SalesFormat::money($price, $locale),
                    TourExtraUnit::PerItem => $quantity.' × '.SalesFormat::money($price, $locale),
                    TourExtraUnit::PerBooking => '',
                },
                'formula' => $quantity.' × '.number_format($price, 2, '.', ''),
                'from' => $date,
                'to' => $date,
                'listing_type' => 'guiding_extra',
                'extra_key' => $extra['key'],
                'persons' => $unit === TourExtraUnit::PerPerson ? $quantity : null,
                'persons_follow_parent' => $follow,
                'quantity' => $unit === TourExtraUnit::PerPerson ? 1 : $quantity,
                'price_unit' => SalesPriceUnit::from($unit->value),
                'unit_price' => $price,
            ]);
        }

        return $group;
    }

    /**
     * @param  array<string, mixed>  $card
     * @return array<string, mixed>
     */
    private function camp(array $card, string $locale): array
    {
        $product = $card['product'] ?? null;
        $group = $this->group($card, 'camp', $product);
        if (! is_array($product)) {
            $group['errors'][] = __('sales.error.select_product.camp');

            return $group;
        }

        $persons = max(1, (int) ($card['persons'] ?? 1));
        $subs = array_values((array) ($card['subs'] ?? []));

        // Stay span from the accommodations, for boat-day and guiding-date warnings.
        $stayFrom = null;
        $stayTo = null;
        $stayDays = 0;
        foreach ($subs as $sub) {
            if (($sub['kind'] ?? null) !== 'accommodation') {
                continue;
            }
            $from = $this->date($sub['from'] ?? null);
            $to = $this->date($sub['to'] ?? null);
            if ($from !== null && $to !== null && $to > $from) {
                $stayFrom = $stayFrom === null || $from < $stayFrom ? $from : $stayFrom;
                $stayTo = $stayTo === null || $to > $stayTo ? $to : $stayTo;
                $stayDays = max($stayDays, $this->nights($from, $to) + 1);
            }
        }

        if ($subs === []) {
            $group['warnings'][] = __('sales.warn.camp_empty');
        }

        foreach ($subs as $index => $sub) {
            $key = (string) ($sub['key'] ?? ($card['key'] ?? 'camp').'-'.$index);
            $quantity = max(1, (int) ($sub['qty'] ?? 1));

            if (($sub['kind'] ?? null) === 'accommodation') {
                $option = $product['accommodations'][(string) ($sub['option_id'] ?? '')] ?? null;
                if ($option === null) {
                    continue;
                }

                $from = $this->date($sub['from'] ?? null);
                $to = $this->date($sub['to'] ?? null);
                $nights = $from !== null && $to !== null ? $this->nights($from, $to) : 0;
                if ($from === null || $to === null) {
                    $group['errors'][] = __('sales.error.stay_dates', ['name' => $option['name']]);
                } elseif ($nights <= 0) {
                    $group['errors'][] = __('sales.error.checkout_after_checkin', ['name' => $option['name']]);
                }
                if ($persons > $option['capacity'] * $quantity) {
                    $group['warnings'][] = __('sales.warn.capacity', ['name' => $option['name'], 'persons' => $persons, 'capacity' => $option['capacity'] * $quantity]);
                }

                $rate = $this->tierRate($option['tiers'] ?? [], $persons);
                $calculated = round($rate->total(max(0, $nights)) * $quantity, 2);
                $line = $this->priced($calculated, $sub['override'] ?? null);
                if ($nights > 0 && $line['total'] <= 0 && ! $line['adjusted']) {
                    $group['errors'][] = __('sales.error.price_required', ['title' => $option['name']]);
                }

                $group['lines'][] = $this->line($key, SalesItemType::CampAccommodation, $line, [
                    'title' => __('sales.line.accommodation', ['name' => $option['name']], $locale).' · '.$this->nightsLabel(max(0, $nights), $locale),
                    'detail' => SalesFormat::date($from, $locale).' – '.SalesFormat::date($to, $locale).($quantity > 1 ? ' · '.$quantity.'×' : ''),
                    'formula' => $this->stayFormula($rate, max(0, $nights), 'n', $quantity),
                    'from' => $from,
                    'to' => $to,
                    'listing_type' => 'accommodation',
                    'listing_id' => $option['id'],
                    'option_id' => $option['id'],
                    'persons' => $persons,
                    'quantity' => $quantity,
                    'days' => max(0, $nights),
                    'price_unit' => SalesPriceUnit::PerNight,
                    'unit_price' => (float) $rate->daily,
                ]);

                continue;
            }

            if (($sub['kind'] ?? null) === 'boat') {
                $option = $product['boats'][(string) ($sub['option_id'] ?? '')] ?? null;
                if ($option === null) {
                    continue;
                }

                $days = max(1, (int) ($sub['days'] ?? 1));
                if ($stayDays > 0 && $days > $stayDays) {
                    $group['warnings'][] = __('sales.warn.boat_days', ['name' => $option['name'], 'days' => $days, 'stay' => $stayDays]);
                }

                $rate = StayRate::from($option['daily'] ?? null, $option['weekly'] ?? null);
                $calculated = round($rate->total($days) * $quantity, 2);
                $line = $this->priced($calculated, $sub['override'] ?? null);
                if ($line['total'] <= 0 && ! $line['adjusted']) {
                    $group['errors'][] = __('sales.error.price_required', ['title' => $option['name']]);
                }

                $group['lines'][] = $this->line($key, SalesItemType::CampBoat, $line, [
                    'title' => __('sales.line.boat', ['name' => $option['name']], $locale),
                    'detail' => trans_choice('sales.days', $days, ['count' => $days], $locale)
                        .($quantity > 1 ? ' · '.trans_choice('sales.boats', $quantity, ['count' => $quantity], $locale) : ''),
                    'formula' => $this->stayFormula($rate, $days, 'd', $quantity),
                    'from' => null,
                    'to' => null,
                    'listing_type' => 'rental_boat',
                    'listing_id' => $option['id'],
                    'option_id' => $option['id'],
                    'days' => $days,
                    'quantity' => $quantity,
                    'price_unit' => SalesPriceUnit::PerDay,
                    'unit_price' => (float) $rate->daily,
                ]);

                continue;
            }

            if (($sub['kind'] ?? null) === 'guiding') {
                $option = $product['guidings'][(string) ($sub['option_id'] ?? '')] ?? null;
                if ($option === null) {
                    continue;
                }

                $date = $this->date($sub['date'] ?? null);
                $prices = (array) ($option['prices'] ?? []);
                $priced = $prices === [] ? 1 : min($persons, max(array_map('intval', array_keys($prices))));
                $price = (float) ($prices[$priced] ?? $prices[(string) $priced] ?? 0);

                if ($persons > $option['capacity'] * $quantity) {
                    $group['warnings'][] = __('sales.warn.guiding_capacity', ['name' => $option['name'], 'persons' => $persons, 'max' => $option['capacity']]);
                }
                if ($date !== null && $stayFrom !== null && ($date < $stayFrom || $date > $stayTo)) {
                    $group['warnings'][] = __('sales.warn.guiding_outside_stay', ['name' => $option['name']]);
                }
                if ($date === null) {
                    $group['warnings'][] = __('sales.warn.guiding_date', ['name' => $option['name']]);
                }

                $line = $this->priced(round($price * $quantity, 2), $sub['override'] ?? null);
                if ($line['total'] <= 0 && ! $line['adjusted']) {
                    $group['errors'][] = __('sales.error.price_required', ['title' => $option['name']]);
                }

                $group['lines'][] = $this->line($key, SalesItemType::CampGuiding, $line, [
                    'title' => $option['name'],
                    'detail' => implode(' · ', array_filter([
                        $date !== null ? SalesFormat::date($date, $locale) : null,
                        $quantity > 1 ? trans_choice('sales.guidings', $quantity, ['count' => $quantity], $locale) : null,
                    ])),
                    'formula' => number_format($price, 2, '.', '').' × '.$quantity,
                    'from' => $date,
                    'to' => $date,
                    'listing_type' => 'guiding',
                    'listing_id' => $option['id'],
                    'option_id' => $option['id'],
                    'persons' => $persons,
                    'quantity' => $quantity,
                    'price_unit' => SalesPriceUnit::GroupPrice,
                    'unit_price' => $price,
                ]);
            }
        }

        return $group;
    }

    /**
     * @param  array<string, mixed>  $card
     * @return array<string, mixed>
     */
    private function trip(array $card, string $locale): array
    {
        $product = $card['product'] ?? null;
        $group = $this->group($card, 'trip', $product);
        if (! is_array($product)) {
            $group['errors'][] = __('sales.error.select_product.trip');

            return $group;
        }

        $persons = max(1, (int) ($card['persons'] ?? 1));
        $nights = (int) ($product['nights'] ?? 0);
        $start = $this->date($card['date'] ?? null);
        $end = $start !== null ? SalesFormat::parse($start)->addDays($nights)->toDateString() : null;
        $price = (float) ($product['price'] ?? 0);

        if ($persons > (int) $product['max']) {
            $group['warnings'][] = __('sales.warn.trip_max', ['persons' => $persons, 'max' => $product['max']]);
        }
        if ($persons < (int) $product['min']) {
            $group['warnings'][] = __('sales.warn.trip_min', ['min' => $product['min']]);
        }
        if ($start === null) {
            $group['errors'][] = __('sales.error.trip_date');
        }

        $line = $this->priced(round($price * $persons, 2), $card['override'] ?? null);
        if ($line['total'] <= 0 && ! $line['adjusted']) {
            $group['errors'][] = __('sales.error.price_required', ['title' => $product['title']]);
        }

        $group['lines'][] = $this->line($card['key'] ?? 'trip', SalesItemType::Trip, $line, [
            'title' => $product['title'].($nights > 0 ? ' · '.$this->nightsLabel($nights, $locale) : ''),
            'detail' => SalesFormat::date($start, $locale).' – '.SalesFormat::date($end, $locale).' · '.$this->persons($persons, $locale),
            'formula' => number_format($price, 2, '.', '').' × '.$persons,
            'from' => $start,
            'to' => $end,
            'listing_type' => 'trip',
            'listing_id' => $product['id'],
            'persons' => $persons,
            'days' => $nights,
            'price_unit' => SalesPriceUnit::PerPerson,
            'unit_price' => $price,
        ]);
        $group['inclusions'] = (array) ($product['inclusions'] ?? []);

        return $group;
    }

    /**
     * @param  array<string, mixed>  $card
     * @return array<string, mixed>
     */
    private function custom(array $card, string $locale): array
    {
        $title = trim((string) ($card['title'] ?? ''));
        $group = $this->group($card, 'custom', null);
        $group['title'] = $title;

        if ($title === '') {
            $group['errors'][] = __('sales.error.custom_title');
        }

        $quantity = max(0, (int) ($card['quantity'] ?? 1));
        $price = SalesFormat::amount($card['unit_price'] ?? null) ?? 0.0;
        $date = $this->date($card['date'] ?? null);
        $total = round($quantity * $price, 2);
        $unitLabel = trim((string) ($card['unit_label'] ?? ''));

        $group['lines'][] = $this->line($card['key'] ?? 'custom', SalesItemType::Custom, ['calculated' => $total, 'total' => $total, 'adjusted' => false], [
            'title' => $title !== '' ? $title : __('sales.untitled'),
            'detail' => implode(' · ', array_filter([
                $date !== null ? SalesFormat::date($date, $locale) : null,
                $quantity !== 1 || $unitLabel !== '' ? trim($quantity.' × '.SalesFormat::money($price, $locale).' '.$unitLabel) : null,
                trim((string) ($card['description'] ?? '')) ?: null,
            ])),
            'formula' => number_format($price, 2, '.', '').' × '.$quantity.($unitLabel !== '' ? ' ('.$unitLabel.')' : ''),
            'from' => $date,
            'to' => $date,
            'quantity' => $quantity,
            'price_unit' => SalesPriceUnit::Custom,
            'unit_price' => $price,
        ]);

        return $group;
    }

    /**
     * @param  array<string, mixed>  $card
     * @param  array<string, mixed>|null  $product
     * @return array<string, mixed>
     */
    private function group(array $card, string $type, ?array $product): array
    {
        return [
            'key' => (string) ($card['key'] ?? $type),
            'type' => $type,
            'title' => (string) ($product['title'] ?? ''),
            'location' => (string) ($product['location'] ?? ''),
            'url' => $product['url'] ?? null,
            'partner' => $product['partner'] ?? null,
            'listing_id' => $product['id'] ?? null,
            'inclusions' => [],
            'lines' => [],
            'subtotal' => 0.0,
            'warnings' => [],
            'errors' => [],
        ];
    }

    /**
     * @param  array{calculated: float, total: float, adjusted: bool}  $price
     * @param  array<string, mixed>  $fields
     * @return array<string, mixed>
     */
    private function line(string $key, SalesItemType $type, array $price, array $fields): array
    {
        return [
            'key' => $key,
            'item_type' => $type,
            'extra' => false,
            'listing_type' => null,
            'listing_id' => null,
            'option_id' => null,
            'extra_key' => null,
            'persons' => null,
            'persons_follow_parent' => false,
            'days' => null,
            'quantity' => 1,
            ...$fields,
            ...$price,
        ];
    }

    /**
     * The listing price, or the employee's manual price for this line when one is set.
     *
     * @return array{calculated: float, total: float, adjusted: bool}
     */
    private function priced(float $calculated, mixed $override): array
    {
        $manual = SalesFormat::amount($override);

        return [
            'calculated' => round($calculated, 2),
            'total' => $manual ?? round($calculated, 2),
            'adjusted' => $manual !== null,
        ];
    }

    /**
     * Nightly rate for a party size: the tier for exactly that many guests, else the largest
     * tier below it, else the smallest tier (as CampCheckoutPricing::accommodationRate).
     *
     * @param  list<array{persons: int, daily: ?float, weekly: ?float}>  $tiers
     */
    private function tierRate(array $tiers, int $persons): StayRate
    {
        usort($tiers, fn (array $a, array $b) => $a['persons'] <=> $b['persons']);
        $match = $tiers[0] ?? ['daily' => null, 'weekly' => null];

        foreach ($tiers as $tier) {
            if ($tier['persons'] <= $persons) {
                $match = $tier;
            }
        }

        return StayRate::from($match['daily'] ?? null, $match['weekly'] ?? null);
    }

    /**
     * "235.00 × 7 n × 1", or with the weekly rate "1 × 1640.00/Woche + 1 n × 235.00 × 1" —
     * whichever StayRate::total() used, so the formula always explains the amount.
     */
    private function stayFormula(StayRate $rate, int $units, string $unit, int $quantity): string
    {
        $money = fn (?float $value) => number_format((float) $value, 2, '.', '');
        $weeks = intdiv($units, 7);
        $rest = $units % 7;
        $usesWeekly = $rate->weekly !== null
            && ($rate->daily === null || ($weeks > 0 && $rate->total($units) < round($units * $rate->daily, 2)));

        if (! $usesWeekly) {
            return $money($rate->daily).' × '.$units.' '.$unit.' × '.$quantity;
        }

        if ($rate->daily === null) {
            return (int) ceil($units / 7).' × '.$money($rate->weekly).__('sales.formula.per_week').' × '.$quantity;
        }

        return $weeks.' × '.$money($rate->weekly).__('sales.formula.per_week')
            .($rest > 0 ? ' + '.$rest.' '.$unit.' × '.$money($rate->daily) : '').' × '.$quantity;
    }

    private function date(mixed $value): ?string
    {
        return SalesFormat::parse(is_string($value) ? $value : null)?->toDateString();
    }

    private function nights(string $from, string $to): int
    {
        return (int) SalesFormat::parse($from)->diffInDays(SalesFormat::parse($to), false);
    }

    private function persons(int $count, string $locale): string
    {
        return trans_choice('sales.persons', $count, ['count' => $count], $locale);
    }

    private function nightsLabel(int $count, string $locale): string
    {
        return trans_choice('sales.nights', $count, ['count' => $count], $locale);
    }
}
