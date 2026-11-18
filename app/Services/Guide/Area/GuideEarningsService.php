<?php

namespace App\Services\Guide\Area;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\FinanceItem;
use App\Models\Guiding;
use App\Models\User;
use App\Models\VoucherOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Guide Area › Invoices & commission (spec 11), read-only from today's data until Finance
 * decides on monthly invoices (plan D-1). Basis as recommended in Q-08: tours by tour date
 * (completed bookings only), vouchers when marked paid. Invoices are the existing per-booking
 * finance_items (one emailed invoice per booking).
 */
class GuideEarningsService
{
    /** @return list<int> Years with activity, current year first (spec IN-01). */
    public function years(User $guide): array
    {
        $tours = $this->tourIds($guide);

        $years = Booking::query()->whereIn('guiding_id', $tours)
            ->selectRaw('DISTINCT YEAR(COALESCE(book_date, created_at)) AS y')
            ->pluck('y')
            ->merge(VoucherOrder::query()->where('guide_id', $guide->id)->whereNotNull('paid_at')->selectRaw('DISTINCT YEAR(paid_at) AS y')->pluck('y'))
            ->push((int) Carbon::now()->year)
            ->map(fn ($y) => (int) $y)
            ->filter(fn (int $y) => $y > 2000 && $y <= Carbon::now()->year)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        return $years;
    }

    /**
     * @return array{value: float, commission: float, earnings: float, bookings: int, vouchers: int,
     *     open_commission: float, open_due: ?Carbon, requests: int, accepted: int, declined: int,
     *     expired: int, acceptance_rate: ?int, months: list<array{month: int, value: float, commission: float, bookings: int}>}
     */
    public function year(User $guide, int $year): array
    {
        $tours = $this->tourIds($guide);
        $today = Carbon::today()->toDateString();
        $start = Carbon::create($year)->startOfYear();
        $end = $start->copy()->endOfYear();

        $completed = Booking::query()
            ->whereIn('guiding_id', $tours)
            ->where('status', 'accepted')
            ->whereBetween('book_date', [$start, $end])
            ->where('book_date', '<', $today)
            ->get(['id', 'book_date', 'price', 'total_extra_price', 'cag_percent']);

        $vouchers = VoucherOrder::query()
            ->orders()
            ->where('guide_id', $guide->id)
            ->where('is_paid', true)
            ->whereBetween('paid_at', [$start, $end])
            ->get(['id', 'paid_at', 'price_total', 'commission_amount']);

        $months = collect(range(1, 12))->mapWithKeys(fn (int $m) => [$m => ['month' => $m, 'value' => 0.0, 'commission' => 0.0, 'bookings' => 0]])->all();
        foreach ($completed as $booking) {
            $m = (int) Carbon::parse($booking->book_date)->month;
            $months[$m]['value'] += $booking->getGrossAmount();
            $months[$m]['commission'] += (float) $booking->cag_percent;
            $months[$m]['bookings']++;
        }
        foreach ($vouchers as $order) {
            $m = (int) $order->paid_at->month;
            $months[$m]['value'] += (float) $order->price_total;
            $months[$m]['commission'] += (float) $order->commission_amount;
            $months[$m]['bookings']++;
        }

        $value = round(array_sum(array_column($months, 'value')), 2);
        $commission = round(array_sum(array_column($months, 'commission')), 2);

        $requests = Booking::query()->whereIn('guiding_id', $tours)->whereBetween('created_at', [$start, $end])->whereNotNull('status')->where('status', '!=', '');
        $requestCount = (clone $requests)->count();
        $accepted = (clone $requests)->where('status', 'accepted')->count();
        $declined = (clone $requests)->where('status', 'rejected')->count();
        $expired = (clone $requests)
            ->whereIn('status', BookingStatus::CANCELLED_STORED)
            ->whereHas('statusChanges', fn (Builder $q) => $q->where('reason', BookingStatus::expiryReason()))
            ->count();
        $answered = $accepted + $declined + $expired;

        $open = $this->invoiceQuery($guide)->where('paid_status', '!=', 'paid')->where('invoice_status', 'sent')->with('billable')->get();
        $due = $open->whereNotNull('invoice_due_at')->min('invoice_due_at');

        return [
            'value' => $value,
            'commission' => $commission,
            'earnings' => round($value - $commission, 2),
            'bookings' => $completed->count(),
            'vouchers' => $vouchers->count(),
            'open_commission' => round($open->sum(fn (FinanceItem $item) => $this->amounts($item)['commission']), 2),
            'open_due' => $due ? Carbon::parse($due) : null,
            'requests' => $requestCount,
            'accepted' => $accepted,
            'declined' => $declined,
            'expired' => $expired,
            'acceptance_rate' => $answered > 0 ? (int) round($accepted / $answered * 100) : null,
            'months' => array_values($months),
        ];
    }

    /** Commission invoices sent in the year, newest first. */
    public function invoices(User $guide, int $year): Collection
    {
        return $this->invoiceQuery($guide)
            ->where('invoice_status', 'sent')
            ->whereRaw('YEAR(COALESCE(invoice_sent_at, created_at)) = ?', [$year])
            ->with(['billable' => fn ($q) => $q->with('guiding:id,title')])
            ->orderByDesc('invoice_sent_at')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Amounts of an invoice. Older finance rows were stored without them; the booking's own
     * price and commission are what was invoiced then.
     *
     * @return array{gross: float, commission: float}
     */
    public function amounts(FinanceItem $item): array
    {
        $booking = $item->billable instanceof Booking ? $item->billable : null;

        return [
            'gross' => $item->gross_amount !== null ? (float) $item->gross_amount : (float) ($booking?->getGrossAmount() ?? 0),
            'commission' => $item->commission_amount !== null ? (float) $item->commission_amount : (float) ($booking?->cag_percent ?? 0),
        ];
    }

    /** Paid / Open / Overdue (spec IN-05). */
    public function invoiceStatus(FinanceItem $item): string
    {
        if ($item->paid_status === 'paid') {
            return 'paid';
        }

        return $item->invoice_due_at && $item->invoice_due_at->isPast() ? 'overdue' : 'open';
    }

    public function hasOverdue(User $guide): bool
    {
        return $this->invoiceQuery($guide)
            ->where('invoice_status', 'sent')
            ->where('paid_status', '!=', 'paid')
            ->where('invoice_due_at', '<', now())
            ->exists();
    }

    public function findInvoice(User $guide, int $id): ?FinanceItem
    {
        return $this->invoiceQuery($guide)->with('billable')->find($id);
    }

    private function invoiceQuery(User $guide): Builder
    {
        return FinanceItem::query()
            ->where('billable_type', (new Booking)->getMorphClass())
            ->whereIn('billable_id', Booking::query()->select('id')->whereIn('guiding_id', $this->tourIds($guide)));
    }

    private function tourIds(User $guide): Builder
    {
        return Guiding::query()->select('id')->where('user_id', $guide->id);
    }
}
