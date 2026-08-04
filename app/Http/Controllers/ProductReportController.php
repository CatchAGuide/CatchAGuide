<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductReportRequest;
use App\Mail\ProductReportAdminMail;
use App\Mail\ProductReportCustomerMail;
use App\Models\ProductReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ProductReportController extends Controller
{
    public function show(): View
    {
        return view('pages.law.notice-and-takedown');
    }

    public function store(StoreProductReportRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();

        $report = ProductReport::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'reason' => $validated['reason'],
            'description' => $validated['description'],
            'reported_url' => $validated['reported_url'],
            'source_type' => $validated['source_type'] ?? null,
            'source_id' => $validated['source_id'] ?? null,
            'status' => ProductReport::STATUS_OPEN,
            'locale' => app()->getLocale(),
            'ip' => $request->ip(),
        ]);

        Mail::send(new ProductReportAdminMail($report));
        Mail::send(new ProductReportCustomerMail($report));

        $successMessage = __('notice-takedown.success');

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $successMessage,
            ]);
        }

        return redirect()
            ->route('law.notice-and-takedown')
            ->with('message', $successMessage);
    }
}
