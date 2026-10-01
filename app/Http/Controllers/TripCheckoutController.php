<?php

namespace App\Http\Controllers;

use App\Domain\Checkout\ViewModels\TripCheckoutViewModel;
use App\Http\Requests\TripCheckoutRequest;
use App\Models\TripBooking;
use App\Presenters\Vacation\TripQuotePresenter;
use App\Services\Checkout\Trip\BookableTripFinder;
use App\Services\Checkout\Trip\TripBookingConfirmationAccess;
use App\Services\Checkout\Trip\TripBookingSubmissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Trip checkout: the request form for one trip, its submission and the confirmation page.
 * The organiser answers with a binding offer, so nothing is paid here.
 */
class TripCheckoutController extends Controller
{
    public function show(Request $request, string $slug, BookableTripFinder $trips)
    {
        $trip = $trips->findBySlug($slug);

        // Drafts and unknown slugs go back to the trip catalog instead of a dead end.
        if ($trip === null) {
            return redirect()->route('vacations.trips.index');
        }

        return view('pages.trip-checkout.index', [
            'checkout' => new TripCheckoutViewModel($trip, $request->user(), $request->only(['date', 'persons'])),
        ]);
    }

    public function store(
        TripCheckoutRequest $request,
        TripBookingSubmissionService $submissions,
        TripBookingConfirmationAccess $confirmations,
    ): JsonResponse {
        $trip = $request->trip();

        try {
            $booking = $submissions->submit(
                $request->offer(),
                $request->departureDate(),
                $request->wishStart(),
                $request->wishEnd(),
                $request->persons(),
                $request->contact(),
                $request->guestMessage(),
                $request->user(),
                app()->getLocale(),
            );
        } catch (Throwable $e) {
            Log::error('Trip checkout submission failed', ['trip_id' => $trip->id, 'error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => __('checkout.booking_failed')], 500);
        }

        $confirmations->grant($booking);

        return response()->json([
            'success' => true,
            'redirect_url' => route('checkout.trip.thank-you', [$trip->slug, $booking->id]),
        ]);
    }

    /**
     * Only the session that sent the request (or its signed-in sender) may see it; everyone
     * else gets the same 404 as for a missing id, so ids can't be probed.
     */
    public function thankYou(
        string $slug,
        int $requestId,
        TripBookingConfirmationAccess $confirmations,
        TripQuotePresenter $presenter,
    ) {
        $booking = TripBooking::with('trip')->find($requestId);

        abort_unless(
            $booking
            && $booking->source_type === TripBooking::SOURCE_TRIP
            && $booking->trip?->slug === $slug
            && $confirmations->allows($booking, auth()->user()),
            404,
        );

        $fixedDate = $booking->preferred_date_to === null;

        return view('pages.trip-checkout.thank-you', [
            'booking' => $booking,
            'tripUrl' => route('vacations.trips.show', $slug),
            'tripTitle' => (string) $booking->trip->title,
            'fixedDate' => $fixedDate,
            'date' => $presenter->date($booking->preferred_date?->toDateString()),
            'total' => $presenter->total($booking->estimated_total !== null ? (float) $booking->estimated_total : null, $fixedDate),
        ]);
    }
}
