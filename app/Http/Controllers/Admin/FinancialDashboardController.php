<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Finance\FinancialDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinancialDashboardController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.pages.finance.financial_dashboard.index', [
            'apiUrl' => route('admin.api.financial-dashboard'),
            'exportUrl' => route('admin.financial.dashboard.export'),
            'initialYear' => (int) $request->query('year', now()->year),
            'initialMonth' => (int) $request->query('month', now()->month),
            'initialPeriod' => strtolower((string) $request->query('period', 'month')) === 'all' ? 'all' : 'month',
        ]);
    }

    public function data(Request $request, FinancialDashboardService $financialDashboard): JsonResponse
    {
        return response()->json($financialDashboard->buildPayload($request));
    }

    public function export(Request $request, FinancialDashboardService $financialDashboard): StreamedResponse
    {
        $rows = $financialDashboard->buildExportRows($request);
        $filename = 'financial_dashboard_' . now()->format('Y-m-d_His') . '.csv';

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
                'ID',
                'Booking date',
                'Tour date',
                'Guide',
                'Country',
                'Target fish',
                'Product type',
                'Price (EUR)',
                'Commission (EUR)',
                'Price tier',
                'Lead time (days)',
                'Status',
            ]);

            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['id'] ?? '',
                    isset($r['booking_date']) ? substr((string) $r['booking_date'], 0, 19) : '',
                    $r['tour_date'] ?? '',
                    $r['guide_name'] ?? '',
                    $r['country'] ?? '',
                    $r['target_fish'] ?? '',
                    $r['product_type'] ?? '',
                    $r['price'] ?? '',
                    $r['commission'] ?? '',
                    $r['price_tier'] ?? '',
                    $r['lead_time_days'] ?? '',
                    $r['status'] ?? '',
                ]);
            }

            fclose($out);
        }, 200, $headers);
    }
}
