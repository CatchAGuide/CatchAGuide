<?php

namespace App\Http\Controllers;

use App\Domain\Checkout\ViewModels\CampCheckoutViewModel;
use App\Http\Requests\CampCheckoutRequest;
use App\Models\CampVacationBooking;
use App\Presenters\Vacation\CampQuotePresenter;
use App\Services\Checkout\Camp\BookableCampFinder;
use App\Services\Checkout\Camp\CampBookingConfirmationAccess;
use App\Services\Checkout\Camp\CampBookingSubmissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Camp checkout: the availability request form for one camp, its submission and the
 * confirmation page. The camp answers with a binding offer, so nothing is paid here.
 */
class CampCheckoutController extends Controller
{
    public function show(Request $request, string $slug, BookableCampFinder $camps)
    {
        $camp = $camps->findBySlug($slug);

        // Drafts and unknown slugs go back to the camp page / catalog instead of a dead end.
        if ($camp === null) {
            return redirect()->route('vacations.camps.index');
        }

        return view('pages.camp-checkout.index', [
            'checkout' => new CampCheckoutViewModel(
                $camp,
                $request->user(),
                $request->only(['date', 'nights', 'persons', 'accommodation']),
            ),
        ]);
    }

    public function store(
        CampCheckoutRequest $request,
        CampBookingSubmissionService $submissions,
        CampBookingConfirmationAccess $confirmations,
    ): JsonResponse {
        $camp = $request->camp();
        $quote = $request->pricing()->quote($request->selection());

        try {
            $booking = $submissions->submit(
                $camp,
                $quote,
                (string) $request->validated('arrival_date'),
                $request->contact(),
                $request->guestMessage(),
                $request->user(),
                app()->getLocale(),
            );
        } catch (Throwable $e) {
            Log::error('Camp checkout submission failed', ['camp_id' => $camp->id, 'error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => __('checkout.booking_failed')], 500);
        }

        $confirmations->grant($booking);

        return response()->json([
            'success' => true,
            'redirect_url' => route('checkout.camp.thank-you', [$camp->slug, $booking->id]),
        ]);
    }

    /**
     * Only the session that sent the request (or its signed-in sender) may see it; everyone
     * else gets the same 404 as for a missing id, so ids can't be probed.
     */
    public function thankYou(
        string $slug,
        int $requestId,
        CampBookingConfirmationAccess $confirmations,
        CampQuotePresenter $presenter,
    ) {
        $booking = CampVacationBooking::with('camp')->find($requestId);

        abort_unless(
            $booking
            && $booking->source_type === CampVacationBooking::SOURCE_CAMP
            && $booking->camp?->slug === $slug
            && $confirmations->allows($booking, auth()->user()),
            404,
        );

        return view('pages.camp-checkout.thank-you', [
            'booking' => $booking,
            'campUrl' => route('vacations.camps.show', $slug),
            'campTitle' => (string) $booking->camp->title,
            'arrival' => $presenter->date($booking->preferred_date),
            'total' => $presenter->total($booking->estimated_total !== null ? (float) $booking->estimated_total : null),
        ]);
    }
}
