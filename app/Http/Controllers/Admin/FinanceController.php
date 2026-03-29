<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\CampVacationBooking;
use App\Models\FinanceItem;
use App\Models\TripBooking;
use App\Services\Finance\FinanceAggregationService;
use App\Services\Finance\FinanceAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class FinanceController extends Controller
{
    public function analytics(FinanceAnalyticsService $financeAnalytics)
    {
        return view('admin.pages.finance.analytics.index', [
            'analytics' => $financeAnalytics->buildPayload(),
        ]);
    }

    public function invoices(Request $request, FinanceAggregationService $aggregation)
    {
        $rows = $aggregation->getInvoiceRows($request);

        return view('admin.pages.finance.invoices.index', [
            'rows' => $rows,
        ]);
    }

    public function updateInvoice(Request $request, string $source, int $id): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'invoice_sent' => ['required', 'boolean'],
        ]);

        $billable = $this->resolveBillable($source, $id);
        $financeItem = $this->ensureFinanceItem($billable);

        if ($validated['invoice_sent']) {
            $financeItem->invoice_status = 'sent';
            $financeItem->invoice_sent_at = $financeItem->invoice_sent_at ?? now();
        } else {
            $financeItem->invoice_status = 'not_sent';
            $financeItem->invoice_sent_at = null;
        }

        $financeItem->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'invoice_status' => $financeItem->invoice_status,
                'invoice_sent_at' => optional($financeItem->invoice_sent_at)->toISOString(),
            ]);
        }

        return back()->with('success', 'Invoice status updated.');
    }

    public function updatePaid(Request $request, string $source, int $id): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'paid_status' => ['required', 'string', 'in:unpaid,paid'],
        ]);

        $billable = $this->resolveBillable($source, $id);
        $financeItem = $this->ensureFinanceItem($billable);

        $financeItem->paid_status = $validated['paid_status'];
        $financeItem->paid_at = $validated['paid_status'] === 'paid'
            ? ($financeItem->paid_at ?? now())
            : null;

        $financeItem->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'paid_status' => $financeItem->paid_status,
                'paid_at' => optional($financeItem->paid_at)->toISOString(),
            ]);
        }

        return back()->with('success', 'Paid status updated.');
    }

    private function resolveBillable(string $source, int $id)
    {
        return match ($source) {
            'booking' => Booking::query()->findOrFail($id),
            'trip' => TripBooking::query()->findOrFail($id),
            'camp_vacation' => CampVacationBooking::query()->findOrFail($id),
            default => abort(404),
        };
    }

    private function ensureFinanceItem($billable): FinanceItem
    {
        if ($billable->relationLoaded('financeItem') && $billable->financeItem) {
            return $billable->financeItem;
        }

        return $billable->financeItem()->firstOrCreate([], [
            'invoice_status' => 'not_sent',
            'paid_status' => 'unpaid',
        ]);
    }
}

