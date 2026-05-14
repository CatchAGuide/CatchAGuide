<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\CampVacationBooking;
use App\Models\Employee;
use App\Models\FinanceItem;
use App\Models\FinanceItemEvent;
use App\Models\TripBooking;
use App\Services\Finance\FinanceAggregationService;
use App\Services\Finance\FinanceAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinanceController extends Controller
{
    public function analytics(Request $request, FinanceAnalyticsService $financeAnalytics)
    {
        return view('admin.pages.finance.analytics.index', [
            'analytics' => $financeAnalytics->buildPayload(
                dateBasis: (string) $request->query('date_basis', 'reservation')
            ),
        ]);
    }

    public function invoices(Request $request, FinanceAggregationService $aggregation)
    {
        $rows = $aggregation->getInvoiceRows($request);

        return view('admin.pages.finance.invoices.index', [
            'rows' => $rows,
        ]);
    }

    public function exportInvoices(Request $request, FinanceAggregationService $aggregation): StreamedResponse
    {
        $rows = $aggregation->getInvoiceRows($request);
        $filename = 'finance_invoices_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($rows) {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }

            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'Source',
                'ID',
                'Reservation date',
                'Booking date',
                'Guest',
                'Guide',
                'Gross amount',
                'Provision',
                'Tax',
                'Invoice status',
                'Invoice sent at',
                'Invoice due at',
                'Paid status',
                'Paid at',
                'Overdue days',
            ]);

            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['source'] ?? '',
                    $r['id'] ?? '',
                    $r['reservation_date'] ?? '',
                    $r['booking_date'] ?? '',
                    $r['guest_name'] ?? '',
                    $r['guide_name'] ?? '',
                    $r['price'] ?? '',
                    $r['provision'] ?? '',
                    $r['tax'] ?? '',
                    ($r['invoice_sent'] ?? false) ? 'sent' : 'not_sent',
                    $r['invoice_sent_at_iso'] ?? '',
                    $r['invoice_due_at_iso'] ?? '',
                    $r['paid_status'] ?? 'unpaid',
                    $r['paid_at_iso'] ?? '',
                    $r['overdue_days'] ?? 0,
                ]);
            }

            fclose($out);
        }, 200, $headers);
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
            $financeItem->invoice_due_at = $financeItem->invoice_due_at ?? now()->addDays((int) config('finance.invoice_due_days', 10));
        } else {
            $financeItem->invoice_status = 'not_sent';
            $financeItem->invoice_sent_at = null;
            $financeItem->invoice_due_at = null;
            $financeItem->reminder_step = 0;
            $financeItem->last_reminder_sent_at = null;
            $financeItem->next_reminder_at = null;
        }

        $financeItem->save();

        $this->syncBookingLegacyFlags($billable, $financeItem);
        $this->logEvent($financeItem, $validated['invoice_sent'] ? 'invoice_sent' : 'invoice_unsent', [
            'source' => $source,
            'billable_id' => $id,
        ]);

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

        $this->logEvent($financeItem, $validated['paid_status'] === 'paid' ? 'paid_marked' : 'unpaid_marked', [
            'source' => $source,
            'billable_id' => $id,
        ]);

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

    private function syncBookingLegacyFlags($billable, FinanceItem $financeItem): void
    {
        if (!$billable instanceof Booking) {
            return;
        }

        // Keep legacy booking flags in sync so existing admin pages continue to work.
        if ($financeItem->invoice_status === 'sent') {
            $billable->is_guide_billed = true;
            $billable->guide_billed_at = $billable->guide_billed_at ?? now();
            $billable->guide_invoice_sent_at = $billable->guide_invoice_sent_at ?? $financeItem->invoice_sent_at ?? now();
        } else {
            $billable->is_guide_billed = false;
            $billable->guide_billed_at = null;
        }

        $billable->save();
    }

    private function logEvent(FinanceItem $financeItem, string $eventType, ?array $payload = null): void
    {
        $actorId = auth('employees')->id();
        $actorType = $actorId ? Employee::class : null;

        FinanceItemEvent::create([
            'finance_item_id' => $financeItem->id,
            'event_type' => $eventType,
            'payload' => $payload,
            'actor_type' => $actorType,
            'actor_id' => $actorId,
        ]);
    }
}

