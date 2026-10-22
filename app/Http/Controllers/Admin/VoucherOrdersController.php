<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VoucherOrder;
use App\Services\Voucher\GuideVoucherOrders;
use App\Services\Voucher\VoucherPdfGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Admin › Bookings › Vouchers: gift voucher orders. They have no date and no status (the guide
 * handles payment and delivery), so they get their own list instead of a column in the tours table.
 */
class VoucherOrdersController extends Controller
{
    private const PER_PAGE = 50;

    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $orders = VoucherOrder::query()
            ->search($search)
            ->with(['guide:id,firstname,lastname,email', 'financeItem'])
            ->latest()
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $totals = VoucherOrder::query()
            ->search($search)
            ->selectRaw('COUNT(*) as orders, COALESCE(SUM(price_total), 0) as volume, COALESCE(SUM(commission_amount), 0) as commission')
            ->toBase()
            ->first();

        return view('admin.pages.voucher-orders.index', compact('orders', 'search', 'totals'));
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
