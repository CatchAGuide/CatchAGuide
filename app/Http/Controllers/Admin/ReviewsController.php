<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ReviewsController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->validatedFilters($request);

        $query = Review::query()
            ->with([
                'booking.registeredUser',
                'booking.guestUser',
                'guiding',
                'reviewedGuide',
            ])
            ->latest('id');

        $this->applyFilters($query, $filters);

        $reviews = $query->get();

        $statsBase = Review::query();
        $stats = [
            'total' => (clone $statsBase)->count(),
            'automatic' => (clone $statsBase)->where('is_automatic', true)->count(),
            'guest' => (clone $statsBase)->where(function ($q) {
                $q->where('is_automatic', false)->orWhereNull('is_automatic');
            })->count(),
            'avg_score' => round((float) (clone $statsBase)->avg('grandtotal_score'), 1),
            'filtered' => $reviews->count(),
        ];

        $guides = $this->guideOptions();

        return view('admin.pages.reviews.index', [
            'reviews' => $reviews,
            'stats' => $stats,
            'filters' => $filters,
            'guides' => $guides,
        ]);
    }

    public function show(Review $review)
    {
        $review->load([
            'booking.registeredUser',
            'booking.guestUser',
            'guiding',
            'reviewedGuide',
        ]);

        $booking = $review->booking;
        $guest = $booking?->user;
        $guide = $review->reviewedGuide;
        $guiding = $review->guiding;

        return response()->json([
            'id' => $review->id,
            'is_automatic' => (bool) $review->is_automatic,
            'type_label' => $review->is_automatic
                ? __('admin.reviews.type_automatic')
                : __('admin.reviews.type_guest'),
            'comment' => $review->comment,
            'scores' => [
                'overall' => (float) $review->overall_score,
                'guide' => (float) $review->guide_score,
                'region_water' => (float) $review->region_water_score,
                'grandtotal' => round((float) $review->grandtotal_score, 2),
            ],
            'guest' => [
                'name' => $this->personName($guest),
                'email' => $booking?->customerEmail() ?? $guest?->email,
                'is_guest_booking' => (bool) ($booking?->is_guest),
            ],
            'guide' => [
                'id' => $guide?->id,
                'name' => $this->personName($guide),
                'email' => $guide?->email,
            ],
            'guiding' => [
                'id' => $guiding?->id,
                'title' => $guiding?->title,
                'location' => $guiding?->location,
                'admin_url' => $guiding ? route('admin.guidings.edit', $guiding) : null,
            ],
            'booking' => [
                'id' => $booking?->id,
                'admin_url' => $booking ? route('admin.bookings.show', $booking) : null,
                'book_date' => $booking?->book_date,
            ],
            'created_at' => optional($review->created_at)->format('Y-m-d H:i:s'),
            'created_at_human' => optional($review->created_at)->format('M j, Y g:i A'),
            'updated_at' => optional($review->updated_at)->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @return array{
     *     is_automatic: string|null,
     *     guide_id: int|null,
     *     score_min: float|null,
     *     score_max: float|null,
     *     date_from: string|null,
     *     date_to: string|null,
     *     has_comment: string|null
     * }
     */
    private function validatedFilters(Request $request): array
    {
        $validated = $request->validate([
            'is_automatic' => ['nullable', 'in:0,1'],
            'guide_id' => ['nullable', 'integer', 'min:1'],
            'score_min' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'score_max' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'has_comment' => ['nullable', 'in:0,1'],
        ]);

        return [
            'is_automatic' => $validated['is_automatic'] ?? null,
            'guide_id' => isset($validated['guide_id']) ? (int) $validated['guide_id'] : null,
            'score_min' => isset($validated['score_min']) ? (float) $validated['score_min'] : null,
            'score_max' => isset($validated['score_max']) ? (float) $validated['score_max'] : null,
            'date_from' => $validated['date_from'] ?? null,
            'date_to' => $validated['date_to'] ?? null,
            'has_comment' => $validated['has_comment'] ?? null,
        ];
    }

    private function applyFilters($query, array $filters): void
    {
        if ($filters['is_automatic'] === '1') {
            $query->where('is_automatic', true);
        } elseif ($filters['is_automatic'] === '0') {
            $query->where(function ($q) {
                $q->where('is_automatic', false)->orWhereNull('is_automatic');
            });
        }

        if ($filters['guide_id']) {
            $query->where('guide_id', $filters['guide_id']);
        }

        if ($filters['score_min'] !== null) {
            $query->where('grandtotal_score', '>=', $filters['score_min']);
        }

        if ($filters['score_max'] !== null) {
            $query->where('grandtotal_score', '<=', $filters['score_max']);
        }

        if ($filters['date_from']) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if ($filters['date_to']) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if ($filters['has_comment'] === '1') {
            $query->whereNotNull('comment')->where('comment', '!=', '');
        } elseif ($filters['has_comment'] === '0') {
            $query->where(function ($q) {
                $q->whereNull('comment')->orWhere('comment', '');
            });
        }
    }

    /**
     * @return Collection<int, string>
     */
    private function guideOptions(): Collection
    {
        $guideIds = Review::query()
            ->whereNotNull('guide_id')
            ->distinct()
            ->pluck('guide_id');

        if ($guideIds->isEmpty()) {
            return collect();
        }

        return User::query()
            ->whereIn('id', $guideIds)
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->get(['id', 'firstname', 'lastname'])
            ->mapWithKeys(fn (User $user) => [
                $user->id => trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? '')) ?: ('#' . $user->id),
            ]);
    }

    private function personName(?object $person): string
    {
        if (! $person) {
            return '—';
        }

        $name = trim(($person->firstname ?? '') . ' ' . ($person->lastname ?? ''));

        return $name !== '' ? $name : '—';
    }
}
