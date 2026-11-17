<?php

namespace App\Http\Controllers\Api\Guide;

use App\Enums\TourStatus;
use App\Http\Controllers\Controller;
use App\Models\Guiding;
use App\Presenters\Guiding\GuideTourPresenter;
use App\Services\Guide\Area\GuideTourService;
use App\Services\Guide\Area\TourActionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * /api/guide/tours — My fishing tours for API clients (same services as the Guide Area page).
 */
class ToursController extends Controller
{
    public function __construct(
        private readonly GuideTourService $tours,
        private readonly GuideTourPresenter $presenter,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'status' => ['nullable', 'in:'.implode(',', array_map(fn (TourStatus $s) => $s->key(), TourStatus::cases()))],
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);
        $guide = $request->user();
        $search = (string) ($data['search'] ?? '');

        $page = $this->tours->paginate(
            $guide,
            isset($data['status']) ? TourStatus::fromKey($data['status']) : null,
            $search,
            (int) ($data['per_page'] ?? GuideTourService::PER_PAGE),
        );

        return response()->json([
            'data' => collect($page->items())->map(fn (Guiding $g) => $this->presenter->present($g))->values(),
            'meta' => [
                'counts' => $this->tours->counts($guide, $search),
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    public function goLive(Request $request, Guiding $guiding): JsonResponse
    {
        $this->authorize('update', $guiding);

        try {
            $this->tours->goLive($request->user(), $guiding);
        } catch (TourActionException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'reason' => $e->reason,
                'gaps' => $e->gaps,
                'gap_labels' => array_map(fn (string $gap) => __('guide_area.tours.gaps.'.$gap), $e->gaps),
            ], 422);
        }

        return response()->json(['data' => $this->presenter->present($guiding->refresh())]);
    }

    public function deactivate(Guiding $guiding): JsonResponse
    {
        $this->authorize('update', $guiding);

        return response()->json(['data' => $this->presenter->present($this->tours->deactivate($guiding)->refresh())]);
    }

    public function duplicate(Guiding $guiding): JsonResponse
    {
        $this->authorize('duplicate', $guiding);

        return response()->json(['data' => $this->presenter->present($this->tours->duplicate($guiding)->refresh())], 201);
    }
}
