<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReport;
use App\Services\Booking\BookingStatusHistory;
use Illuminate\Http\Request;

class ProductReportsController extends Controller
{
    public function index(BookingStatusHistory $history)
    {
        $productReports = ProductReport::query()
            ->with('latestStatusChange.employee')
            ->latest()
            ->get();

        $statusSummaries = $productReports->mapWithKeys(fn (ProductReport $item) => [$item->id => $history->summary($item)]);

        return view('admin.pages.product-reports.index', compact('productReports', 'statusSummaries'));
    }

    public function showComment(ProductReport $productReport)
    {
        return response()->json([
            'id' => $productReport->id,
            'admin_comment' => $productReport->admin_comment,
        ]);
    }

    public function updateComment(Request $request, ProductReport $productReport)
    {
        $validated = $request->validate([
            'admin_comment' => ['nullable', 'string'],
        ]);

        $productReport->update([
            'admin_comment' => $validated['admin_comment'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comment saved.',
        ]);
    }

    public function updateStatus(Request $request, ProductReport $productReport, BookingStatusHistory $history)
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:open,in_process,done'],
        ]);

        $history->change($productReport, $validated['status'], $request->user('employees'));

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => $productReport->status,
                'status_label' => $productReport->statusLabel(),
                'status_summary' => $history->summary($productReport),
            ]);
        }

        return redirect()
            ->route('admin.product-reports.index')
            ->with('success', 'Status updated.');
    }

    public function statusHistory(ProductReport $productReport, BookingStatusHistory $history)
    {
        return response()->json([
            'success' => true,
            'items' => $history->timeline($productReport),
        ]);
    }
}
