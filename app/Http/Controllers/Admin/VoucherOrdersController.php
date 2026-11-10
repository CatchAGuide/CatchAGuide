<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DoneReason;
use App\Enums\VoucherOrderKind;
use App\Http\Controllers\Controller;
use App\Models\VoucherOrder;
use App\Services\Booking\BookingStatusHistory;
use App\Services\Voucher\GuideVoucherOrders;
use App\Services\Voucher\VoucherPdfGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

/**
 * Admin › Bookings › Vouchers: gift voucher orders (a tour bought as a voucher; the guide handles
 * payment and delivery) and the /guidings/voucher landing page requests (no tour yet; CaG follows
 * up and moves them through open → in process → done, logged like the other request lists). They
 * have no date, so they get their own list instead of a column in the tours table.
 * ?kind=order|request narrows the list, ?status=open|in_process|done the requests.
 */
class VoucherOrdersController extends Controller
{
    private const PER_PAGE = 50;

    public function index(Request $request, BookingStatusHistory $statusHistory)
    {
        $search = trim((string) $request->query('q', ''));
        $kind = VoucherOrderKind::tryFrom((string) $request->query('kind', ''));
        $status = array_key_exists((string) $request->query('status'), VoucherOrder::statusOptions()) ? (string) $request->query('status') : null;
        if ($status !== null) {
            $kind = VoucherOrderKind::Request;
        }
        $filtered = fn () => VoucherOrder::query()
            ->search($search)
            ->when($kind, fn ($query) => $query->where('kind', $kind->value))
            ->when($status, fn ($query) => $query->where('status', $status));

        $orders = $filtered()
            ->with(['guide:id,firstname,lastname,email', 'financeItem', 'latestStatusChange.employee'])
            ->latest()
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $totals = $filtered()
            ->selectRaw('COUNT(*) as orders, COALESCE(SUM(price_total), 0) as volume, COALESCE(SUM(commission_amount), 0) as commission')
            ->toBase()
            ->first();

        $statusOptions = VoucherOrder::statusOptions();

        return view('admin.pages.voucher-orders.index', compact('orders', 'search', 'kind', 'status', 'statusOptions', 'statusHistory', 'totals'));
    }

    /** Status of a landing page request (orders have none). */
    public function updateStatus(Request $request, VoucherOrder $voucherOrder, BookingStatusHistory $history)
    {
        abort_unless($voucherOrder->isRequest(), 404);

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(array_keys(VoucherOrder::statusOptions()))],
            'done_reason' => ['nullable', Rule::enum(DoneReason::class)],
        ]);

        $history->change(
            $voucherOrder,
            $validated['status'],
            $request->user('employees'),
            doneReason: DoneReason::tryFrom((string) ($validated['done_reason'] ?? '')),
        );

        return response()->json([
            'success' => true,
            'status' => $voucherOrder->status,
            'status_label' => $voucherOrder->statusLabel(),
            'done_reason' => $voucherOrder->doneReason()?->value,
            'status_summary' => $history->summary($voucherOrder),
        ]);
    }

    public function statusHistory(VoucherOrder $voucherOrder, BookingStatusHistory $history)
    {
        abort_unless($voucherOrder->isRequest(), 404);

        return response()->json([
            'success' => true,
            'items' => $history->timeline($voucherOrder),
        ]);
    }

    public function showComment(VoucherOrder $voucherOrder)
    {
        return response()->json([
            'id' => $voucherOrder->id,
            'admin_comment' => $voucherOrder->admin_comment,
        ]);
    }

    public function updateComment(Request $request, VoucherOrder $voucherOrder)
    {
        $validated = $request->validate([
            'admin_comment' => ['nullable', 'string'],
        ]);

        $voucherOrder->update([
            'admin_comment' => $validated['admin_comment'] ?? null,
        ]);

        return response()->json([
            'success' => true,
        ]);
    }

    public function pdf(VoucherOrder $voucherOrder, GuideVoucherOrders $orders, VoucherPdfGenerator $pdfs): Response|RedirectResponse
    {
        if (($pdf = $orders->pdf($voucherOrder)) === null) {
            return redirect()->route('admin.voucher-orders.index')->with('error', __('admin.voucher_orders.pdf_missing'));
        }

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$pdfs->fileName($voucherOrder).'"',
        ]);
    }
}
