<?php

namespace App\Http\Controllers;

use App\Domain\Checkout\ViewModels\TourCheckoutViewModel;
use App\Http\Requests\TourCheckoutRequest;
use App\Models\Booking;
use App\Models\Guiding;
use App\Presenters\Offers\TourCardPresenter;
use App\Services\Checkout\BookingConfirmationAccess;
use App\Services\Checkout\TourBookingSubmissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Throwable;

class ModernCheckoutController extends Controller
{
    /**
     * Display the tour checkout for the guiding stored in session by CheckoutController::checkout.
     */
    public function index(TourCardPresenter $presenter)
    {
        $guidingId = Session::get('guiding_id');

        if (!$guidingId) {
            return redirect()->route('guidings.index')->with('error', 'No guiding selected for checkout.');
        }

        // Unpublished tours (drafts, deactivated, unverified guide) can't be booked.
        $guiding = Guiding::publiclyVisible()->with(['user'])->find($guidingId);

        if (!$guiding) {
            Session::forget('guiding_id');

            return redirect()->route('guidings.index')->with('error', __('checkout.tour.errors.tour_unavailable'));
        }

        return view('pages.modern-checkout.index', [
            'checkout' => new TourCheckoutViewModel(
                $guiding,
                $presenter,
                auth()->user(),
                (int) Session::get('person', 1),
                Session::get('selected_date'),
            ),
        ]);
    }

    /**
     * Create the booking request. Runs on the web stack (session, CSRF, locale) so a
     * signed-in visitor books as themselves instead of as a guest.
     */
    public function store(
        TourCheckoutRequest $request,
        TourBookingSubmissionService $submissions,
        BookingConfirmationAccess $confirmations,
    ): JsonResponse {
        $guiding = $request->guiding();
        $quote = $request->pricing()->quote((int) $request->validated('persons'), $request->extraIndexes());

        try {
            $booking = $submissions->submit(
                $guiding,
                $quote,
                (string) $request->validated('selected_date'),
                $request->contact(),
                $request->user(),
                app()->getLocale(),
            );
        } catch (Throwable $e) {
            Log::error('Tour checkout submission failed', ['guiding_id' => $guiding->id, 'error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => __('checkout.booking_failed')], 500);
        }

        $confirmations->grant($booking);

        return response()->json([
            'success' => true,
            'redirect_url' => route('checkout.thank-you', [$booking]),
        ]);
    }

    /**
     * Booking confirmation. Only the session that created the booking or its signed-in owner
     * may see it; everyone else gets the same 404 as for a missing id, so ids can't be probed.
     */
    public function thankYou(int $bookingId, BookingConfirmationAccess $confirmations)
    {
        $booking = Booking::with(['guiding.user'])->find($bookingId);

        abort_unless($booking && $confirmations->allows($booking, auth()->user()), 404);

        return view('pages.modern-checkout.thank-you', compact('booking'));
    }
}
