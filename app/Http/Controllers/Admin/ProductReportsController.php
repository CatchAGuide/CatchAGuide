<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReport;
use Illuminate\Http\Request;

class ProductReportsController extends Controller
{
    public function index()
    {
        $productReports = ProductReport::query()
            ->latest()
            ->get();

        return view('admin.pages.product-reports.index', compact('productReports'));
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

    public function updateStatus(Request $request, ProductReport $productReport)
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:open,in_process,done'],
        ]);

        $productReport->update(['status' => $validated['status']]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => $productReport->status,
                'status_label' => ProductReport::statusOptions()[$productReport->status] ?? $productReport->status,
            ]);
        }

        return redirect()
            ->route('admin.product-reports.index')
            ->with('success', 'Status updated.');
    }
}
