<?php

namespace Tests\Unit\Presenters;

use App\Presenters\Vacation\TripQuotePresenter;
use Tests\TestCase;

class TripQuotePresenterTest extends TestCase
{
    public function test_departure_ranges_in_german(): void
    {
        app()->setLocale('de');
        $presenter = app(TripQuotePresenter::class);

        $this->assertSame('03.–09. Okt 2026', $presenter->range('2026-10-03', '2026-10-09'));
        $this->assertSame('03.–09. Okt', $presenter->range('2026-10-03', '2026-10-09', withYear: false));
        $this->assertSame('28. Okt – 03. Nov 2026', $presenter->range('2026-10-28', '2026-11-03'));
        $this->assertSame('28. Dez 2026 – 03. Jan 2027', $presenter->range('2026-12-28', '2027-01-03'));
        $this->assertSame('03. Okt 2026', $presenter->range('2026-10-03', null));
    }

    public function test_departure_ranges_in_english(): void
    {
        app()->setLocale('en');
        $presenter = app(TripQuotePresenter::class);

        $this->assertSame('Oct 3–9, 2026', $presenter->range('2026-10-03', '2026-10-09'));
        $this->assertSame('Oct 28 – Nov 3, 2026', $presenter->range('2026-10-28', '2026-11-03'));
        $this->assertSame('Dec 28, 2026 – Jan 3, 2027', $presenter->range('2026-12-28', '2027-01-03'));
    }

    public function test_total_label_depends_on_a_fixed_date_and_a_price(): void
    {
        app()->setLocale('de');
        $presenter = app(TripQuotePresenter::class);

        $this->assertSame("ca. 2.580\u{00A0}€", $presenter->total(2580.0, true));
        $this->assertSame("ab 2.580\u{00A0}€", $presenter->total(2580.0, false));
        $this->assertSame(__('checkout.trip.on_request'), $presenter->total(null, true));
        $this->assertSame('Nur noch 2 Plätze', $presenter->spots(2, 2));
        $this->assertSame('', $presenter->spots(3, 2));
        $this->assertSame('7 Tage · 6 Nächte', $presenter->duration(7, 6));
    }
}
