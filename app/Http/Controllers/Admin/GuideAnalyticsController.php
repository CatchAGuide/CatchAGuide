<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guiding;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GuideAnalyticsController extends Controller
{
    /**
     * Display guide analytics: guides without active/draft guidings,
     * and guidings per guide with deactivation date analysis.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        // Function 1: Active guides with NO guidings that are active (1) or draft (2)
        // These are guides who have zero active/draft tours - either no guidings at all,
        // or only deactivated (status 0) guidings
        $guidesWithoutActiveOrDraftGuidings = User::where('is_guide', true)
            ->whereDoesntHave('guidings', function ($query) {
                $query->whereIn('status', [1, 2]);
            })
            ->with(['information', 'guidings' => fn ($q) => $q->where('status', 0)->orderByDesc('updated_at')])
            ->withCount('guidings')
            ->orderBy('guidings_count', 'desc')
            ->get();

        $guidesWithoutActiveOrDraftCount = $guidesWithoutActiveOrDraftGuidings->count();

        // Function 2: Guidings per guide with deactivation analysis
        $guidesWithGuidingsStats = User::where('is_guide', true)
            ->whereHas('guidings')
            ->withCount([
                'guidings',
                'guidings as active_guidings_count' => function ($query) {
                    $query->where('status', 1);
                },
                'guidings as draft_guidings_count' => function ($query) {
                    $query->where('status', 2);
                },
                'guidings as deactivated_guidings_count' => function ($query) {
                    $query->where('status', 0);
                },
            ])
            ->orderByDesc('guidings_count')
            ->get()
            ->map(function ($guide) {
                $deactivatedGuidings = Guiding::where('user_id', $guide->id)
                    ->where('status', 0)
                    ->orderByDesc('updated_at')
                    ->get(['id', 'title', 'status', 'updated_at', 'created_at']);

                return [
                    'guide' => $guide,
                    'total_guidings' => $guide->guidings_count,
                    'active_count' => $guide->active_guidings_count ?? 0,
                    'draft_count' => $guide->draft_guidings_count ?? 0,
                    'deactivated_count' => $guide->deactivated_guidings_count ?? 0,
                    'deactivated_guidings' => $deactivatedGuidings,
                    'last_deactivation_date' => $deactivatedGuidings->first()?->updated_at,
                ];
            });

        // Summary stats for KPI cards
        $totalGuides = User::where('is_guide', true)->count();
        $guidesWithActiveTours = User::where('is_guide', true)
            ->whereHas('guidings', fn ($q) => $q->where('status', 1))
            ->count();
        $totalDeactivatedGuidings = Guiding::where('status', 0)->count();

        // Deactivation date analysis: guidings deactivated per month
        $deactivationByMonth = Guiding::where('status', 0)
            ->select(
                DB::raw('YEAR(updated_at) as year'),
                DB::raw('MONTH(updated_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->whereNotNull('updated_at')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                $date = \Carbon\Carbon::createFromDate($item->year, $item->month, 1);
                return [
                    'label' => $date->format('M Y'),
                    'count' => $item->count,
                    'date' => $date,
                ];
            });

        return view('admin.pages.guide-analytics.index', compact(
            'guidesWithoutActiveOrDraftGuidings',
            'guidesWithoutActiveOrDraftCount',
            'guidesWithGuidingsStats',
            'deactivationByMonth',
            'totalGuides',
            'guidesWithActiveTours',
            'totalDeactivatedGuidings'
        ));
    }
}
