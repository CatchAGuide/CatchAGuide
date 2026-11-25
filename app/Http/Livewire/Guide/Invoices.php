<?php

namespace App\Http\Livewire\Guide;

use App\Models\FinanceItem;
use App\Presenters\Booking\GuideBookingPresenter;
use App\Services\Guide\Area\GuideEarningsService;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Guide Area › Invoices & commission (/guide/invoices): year tabs, earnings and request tiles,
 * monthly chart and the commission invoices (spec 11). Read-only from today's data.
 */
#[Layout('layouts.guide-area')]
class Invoices extends Component
{
    #[Url(except: 0)]
    public int $year = 0;

    public function render(GuideEarningsService $earnings, GuideBookingPresenter $money)
    {
        $guide = auth()->user();
        $years = $earnings->years($guide);
        $year = in_array($this->year, $years, true) ? $this->year : $years[0];
        $data = $earnings->year($guide, $year);
        $max = max(1, (float) max(array_column($data['months'], 'value')));

        $invoices = $earnings->invoices($guide, $year)->map(fn (FinanceItem $item) => [
            'id' => (int) $item->id,
            'number' => $item->invoice_number ?: '#'.$item->billable_id,
            'booking_id' => (int) $item->billable_id,
            'tour' => (string) ($item->billable?->guiding?->title ?? ''),
            'tour_date' => $item->billable?->getBookingDate()?->translatedFormat('j M Y'),
            'sent' => ($item->invoice_sent_at ?? $item->created_at)?->translatedFormat('j M Y'),
            'value' => $money->money($earnings->amounts($item)['gross']),
            'commission' => $money->money($earnings->amounts($item)['commission']),
            'status' => $earnings->invoiceStatus($item),
            'due' => $item->invoice_due_at?->translatedFormat('j M'),
        ]);

        return view('livewire.guide.invoices', [
            'years' => $years,
            'selectedYear' => $year,
            'data' => $data,
            'max' => $max,
            'money' => fn (float $v) => $money->money($v),
            'invoices' => $invoices,
            'monthNames' => collect(range(1, 12))->map(fn (int $m) => Carbon::create(2026, $m)->translatedFormat('M'))->all(),
        ])->title(__('guide_area.invoices.title'));
    }
}
