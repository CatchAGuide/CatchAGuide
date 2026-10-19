<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVacationRatingRequest;
use App\Services\Vacation\VacationReviewService;

/**
 * Guest review form for a camp or trip stay, opened from the review request email.
 */
class VacationRatingsController extends Controller
{
    public function __construct(private readonly VacationReviewService $reviews) {}

    public function show(string $type, string $token)
    {
        $booking = $this->reviews->findByToken($type, $token) ?? abort(404);

        if ($booking->review !== null) {
            return redirect()->route('ratings.notified');
        }

        return view('pages.rating.vacation', [
            'type' => $type,
            'token' => $token,
            'summary' => $this->reviews->summary($booking),
        ]);
    }

    public function store(StoreVacationRatingRequest $request, string $type, string $token)
    {
        $booking = $this->reviews->findByToken($type, $token) ?? abort(404);

        if ($booking->review !== null) {
            return response()->json([
                'success' => false,
                'errors' => ['comment' => [__('vacations.rating_already_submitted')]],
            ], 422);
        }

        $data = $request->validated();

        $this->reviews->submit($booking, [
            'overall_score' => $data['rating_overall'],
            'accommodation_score' => $data['rating_accommodation'],
            'fishing_score' => $data['rating_fishing'],
            'value_score' => $data['rating_value'],
            'comment' => $data['comment'],
        ]);

        return response()->json([
            'success' => true,
            'message' => __('guidings.rating_thank_you'),
        ]);
    }
}
