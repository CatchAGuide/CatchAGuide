<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VacationReview;
use App\Services\Vacation\VacationReviewService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Camp and trip reviews submitted from the review request email (admin only for now).
 */
class VacationReviewsController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'type' => ['nullable', 'in:'.VacationReviewService::TYPE_CAMP.','.VacationReviewService::TYPE_TRIP],
            'review' => ['nullable', 'integer'],
        ]);
        $type = $validated['type'] ?? null;

        $reviews = VacationReview::query()
            ->with('reviewable')
            ->when($type === VacationReviewService::TYPE_TRIP, fn ($q) => $q->where('listing_type', 'trip'))
            ->when($type === VacationReviewService::TYPE_CAMP, fn ($q) => $q->where('listing_type', '!=', 'trip'))
            ->newestFirst()
            ->paginate(50)
            ->withQueryString();

        return view('admin.pages.vacation-reviews.index', [
            'reviews' => $reviews,
            'type' => $type,
            'highlight' => isset($validated['review']) ? (int) $validated['review'] : null,
            'stats' => [
                'total' => VacationReview::query()->count(),
                'avg_score' => round((float) VacationReview::query()->avg('grandtotal_score'), 1),
            ],
        ]);
    }
}
