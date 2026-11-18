<?php

namespace App\Http\Livewire\Guide;

use App\Models\Review;
use App\Services\Guide\Area\GuideReviewService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Guide Area › Reviews (/guide/reviews): score summary and all reviews, 8 per page, NEW until
 * seen (spec 10). The reviews on screen are marked as seen after they are shown once.
 */
#[Layout('layouts.guide-area')]
class Reviews extends Component
{
    use WithPagination;

    public function render(GuideReviewService $reviews)
    {
        $guide = auth()->user();
        $page = $reviews->paginate($guide);

        $items = $page->getCollection()->map(fn (Review $r) => [
            'id' => (int) $r->id,
            'guest' => $reviews->guestName($r),
            'initial' => mb_strtoupper(mb_substr($reviews->guestName($r), 0, 1)),
            'tour' => (string) ($r->guiding?->title ?? ''),
            'date' => $r->created_at?->translatedFormat('M j, Y'),
            'overall' => (float) $r->overall_score,
            'guide' => (float) $r->guide_score,
            'region' => (float) $r->region_water_score,
            'comment' => trim((string) $r->comment),
            'is_automatic' => (bool) $r->is_automatic,
            'is_new' => $r->seen_by_guide_at === null,
        ]);

        // Marked after reading, so this render still shows NEW (spec RV-04).
        $reviews->markSeen($guide, $items->where('is_new', true)->pluck('id')->all());

        return view('livewire.guide.reviews', [
            'summary' => $reviews->summary($guide),
            'items' => $items,
            'page' => $page,
        ])->title(__('guide_area.reviews.title'));
    }
}
