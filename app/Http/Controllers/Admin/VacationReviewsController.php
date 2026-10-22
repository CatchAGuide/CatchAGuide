<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreManualVacationReviewRequest;
use App\Models\VacationReview;
use App\Services\Vacation\VacationReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Camp and trip reviews: submitted from the review request email or entered by an admin, plus
 * the done requests that are still open for review.
 */
class VacationReviewsController extends Controller
{
    public const TAB_REVIEWS = 'reviews';
    public const TAB_OPEN = 'open';

    public function __construct(private readonly VacationReviewService $reviews) {}

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'type' => ['nullable', 'in:'.VacationReviewService::TYPE_CAMP.','.VacationReviewService::TYPE_TRIP],
            'review' => ['nullable', 'integer'],
            'tab' => ['nullable', 'in:'.self::TAB_REVIEWS.','.self::TAB_OPEN],
            'add' => ['nullable', 'boolean'],
            'booking_type' => ['nullable', 'in:'.VacationReviewService::TYPE_CAMP.','.VacationReviewService::TYPE_TRIP],
            'booking_id' => ['nullable', 'integer'],
        ]);
        $type = $validated['type'] ?? null;
        $tab = $validated['tab'] ?? self::TAB_REVIEWS;
        $awaiting = $this->reviews->awaitingReview($type);

        $reviews = $tab === self::TAB_REVIEWS
            ? VacationReview::query()
                ->with('reviewable')
                ->when($type === VacationReviewService::TYPE_TRIP, fn ($q) => $q->where('listing_type', 'trip'))
                ->when($type === VacationReviewService::TYPE_CAMP, fn ($q) => $q->where('listing_type', '!=', 'trip'))
                ->newestFirst()
                ->paginate(50)
                ->withQueryString()
            : null;

        return view('admin.pages.vacation-reviews.index', [
            'tab' => $tab,
            'reviews' => $reviews,
            'listings' => $reviews
                ? $this->reviews->listingLinks(collect($reviews->items())->map(fn (VacationReview $review) => [
                    'type' => $review->listing_type,
                    'id' => (int) $review->listing_id,
                ]))
                : [],
            'awaiting' => $awaiting,
            'type' => $type,
            'highlight' => isset($validated['review']) ? (int) $validated['review'] : null,
            'stats' => [
                'total' => VacationReview::query()->count(),
                'avg_score' => round((float) VacationReview::query()->avg('grandtotal_score'), 1),
                'open' => $awaiting->count(),
            ],
            // Add-review modal: reopened with the booking it was for after a failed submit.
            'listingOptions' => $this->reviews->manualListingOptions(),
            'modalBooking' => $this->reviews->bookingOpenForReview(
                $request->old('booking_type', $validated['booking_type'] ?? null),
                (int) $request->old('booking_id', $validated['booking_id'] ?? 0) ?: null,
            ),
            'openModal' => (bool) ($validated['add'] ?? false),
        ]);
    }

    /** Old form URL: opens the add-review modal on the list instead. */
    public function create(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'booking_type' => ['nullable', 'in:'.VacationReviewService::TYPE_CAMP.','.VacationReviewService::TYPE_TRIP],
            'booking_id' => ['nullable', 'integer'],
        ]);

        $booking = isset($validated['booking_type'], $validated['booking_id'])
            ? $this->reviews->findBooking($validated['booking_type'], (int) $validated['booking_id'])
            : null;

        if ($booking?->review) {
            return redirect()
                ->route('admin.vacation-reviews.index', ['review' => $booking->review->id])
                ->with('error', __('admin.vacation_reviews.error_booking_reviewed'));
        }

        return redirect()->route('admin.vacation-reviews.index', array_filter([
            'tab' => $booking ? self::TAB_OPEN : null,
            'add' => 1,
            'booking_type' => $booking ? $this->reviews->typeOf($booking) : null,
            'booking_id' => $booking?->id,
        ]));
    }

    public function store(StoreManualVacationReviewRequest $request): RedirectResponse
    {
        $data = $request->validated();
        [$listingType, $listingId] = $request->listing();

        $review = $this->reviews->createManual([
            ...$data,
            'listing_type' => $listingType,
            'listing_id' => $listingId,
        ], $request->booking());

        return redirect()
            ->route('admin.vacation-reviews.index', ['review' => $review->id])
            ->with('success', __('admin.vacation_reviews.manual_saved'));
    }
}
