<?php

namespace App\Presenters\Vacation;

use Carbon\CarbonImmutable;

/**
 * Human-readable trip checkout values: departure ranges, party size, the estimate and the
 * plain-text summary that goes into the request's message (admin list + notification mails).
 * Money and single dates are formatted like the camp checkout (CampQuotePresenter).
 */
class TripQuotePresenter
{
    public function __construct(
        private readonly CampQuotePresenter $camp,
    ) {}

    /**
     * "03.–09. Okt. 2026" (de) / "Oct 3–9, 2026" (en); the year is left out for short labels.
     */
    public function range(string $start, ?string $end, bool $withYear = true): string
    {
        $locale = app()->getLocale();
        $from = CarbonImmutable::parse($start)->locale($locale);
        $de = $locale === 'de';

        if ($end === null || $end === '' || $end === $start) {
            return $from->isoFormat($de ? ($withYear ? 'DD. MMM YYYY' : 'DD. MMM') : ($withYear ? 'MMM D, YYYY' : 'MMM D'));
        }

        $to = CarbonImmutable::parse($end)->locale($locale);
        $year = $withYear ? ($de ? ' YYYY' : ', YYYY') : '';

        if ($from->year !== $to->year) {
            return $from->isoFormat($de ? 'DD. MMM'.$year : 'MMM D'.$year).' – '.$to->isoFormat($de ? 'DD. MMM'.$year : 'MMM D'.$year);
        }

        if ($from->month === $to->month) {
            return $de
                ? $from->isoFormat('DD.').'–'.$to->isoFormat('DD. MMM'.$year)
                : $from->isoFormat('MMM D').'–'.$to->isoFormat('D'.$year);
        }

        return $de
            ? $from->isoFormat('DD. MMM').' – '.$to->isoFormat('DD. MMM'.$year)
            : $from->isoFormat('MMM D').' – '.$to->isoFormat('MMM D'.$year);
    }

    /**
     * Numeric range for the guest mail: "Sa, 03.10. – Fr, 09.10.2026" / "Sat, Oct 3 – Fri, Oct 9, 2026"
     * with weekdays, "01.05. – 31.05.2027" / "May 1 – May 31, 2027" without.
     */
    public function dayRange(string $start, ?string $end, bool $withWeekday): string
    {
        $locale = app()->getLocale();
        $de = $locale === 'de';
        $day = ($withWeekday ? ($de ? 'dd, ' : 'ddd, ') : '').($de ? 'DD.MM.' : 'MMM D');
        $year = $de ? 'YYYY' : ', YYYY';
        $from = CarbonImmutable::parse($start)->locale($locale);

        if ($end === null || $end === '' || $end === $start) {
            return $from->isoFormat($day.$year);
        }

        $to = CarbonImmutable::parse($end)->locale($locale);

        return $from->isoFormat($from->year !== $to->year ? $day.$year : $day).' – '.$to->isoFormat($day.$year);
    }

    /**
     * "Only 2 spots left" for nearly full departures, otherwise empty.
     */
    public function spots(?int $spots, int $fewSpots): string
    {
        return $spots !== null && $spots > 0 && $spots <= $fewSpots
            ? trans_choice('checkout.trip.spots_left', $spots, ['count' => $spots])
            : '';
    }

    /**
     * "7 Tage · 6 Nächte"; empty when the trip has no duration.
     */
    public function duration(int $days, int $nights): string
    {
        return implode(' · ', array_filter([
            $days > 0 ? trans_choice('checkout.trip.days_count', $days, ['count' => $days]) : null,
            $nights > 0 ? trans_choice('checkout.trip.nights_count', $nights, ['count' => $nights]) : null,
        ]));
    }

    public function persons(int $persons): string
    {
        return trans_choice('checkout.trip.persons_count', $persons, ['count' => $persons]);
    }

    /**
     * "ca. 2.580 €" for a fixed departure, "ab 2.580 €" for a preferred window (the organiser
     * prices it with the offer), "Auf Anfrage" without a published price.
     */
    public function total(?float $total, bool $fixedDate): string
    {
        if ($total === null || $total <= 0) {
            return __('checkout.trip.on_request');
        }

        return __($fixedDate ? 'checkout.trip.approx' : 'checkout.trip.from', ['amount' => $this->money($total)]);
    }

    public function money(float $amount): string
    {
        return $this->camp->money($amount);
    }

    public function date(?string $date): string
    {
        return $this->camp->date($date);
    }

    /**
     * Plain-text request summary stored as the request's message.
     */
    public function summary(
        ?string $departure,
        ?string $departureEnd,
        ?string $wishStart,
        ?string $wishEnd,
        int $persons,
        ?float $pricePerPerson,
        ?float $total,
        string $guestMessage,
    ): string {
        $rows = [__('checkout.trip.summary.heading')];

        if ($departure !== null) {
            $rows[] = __('checkout.trip.summary.departure').': '.$this->range($departure, $departureEnd);
        } elseif ($wishStart !== null) {
            $rows[] = __('checkout.trip.summary.wish').': '.$this->date($wishStart).' – '.$this->date($wishEnd);
        }

        $rows[] = __('checkout.trip.summary.persons').': '.$this->persons($persons);

        if ($pricePerPerson !== null) {
            $rows[] = __('checkout.trip.summary.price_per_person').': '.$this->money($pricePerPerson);
        }

        $rows[] = __('checkout.trip.summary.total').': '.$this->total($total, $departure !== null);

        if (trim($guestMessage) !== '') {
            $rows[] = '';
            $rows[] = __('checkout.trip.summary.guest_message').':';
            $rows[] = trim($guestMessage);
        }

        return implode("\n", $rows);
    }
}
