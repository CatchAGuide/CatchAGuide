<?php

namespace App\Http\Controllers;

use App\Domain\Checkout\ViewModels\BookingRejectViewModel;
use App\Http\Requests\RejectionRequest;
use App\Models\Booking;
use App\Models\BlockedEvent;
use App\Events\BookingStatusChanged;
use App\Presenters\Offers\TourCardPresenter;
use App\Services\Booking\BookingRejectionService;

class BookingController extends Controller
{
    /** The reject form is reached through the guide's emailed token link. */
    private const PRIVATE_HEADERS = [
        'Referrer-Policy' => 'no-referrer',
        'Cache-Control' => 'no-store, private',
        'X-Robots-Tag' => 'noindex, nofollow',
    ];

    public function accept($token){
        // The token is looked up as an opaque value with no `|suffix` parsing — that
        // suffix used to be read as an unauthenticated "acting employee id" and let
        // anyone bypass the already-processed guard below by appending anything
        // after a `|`. No current caller (admin panel included) sends a suffixed
        // token, so the parsing was pure attack surface with no real feature behind it.
        $booking = Booking::where('token',$token)->first();

        if(!$booking){
            abort(404);
        }

        if($booking->status != 'pending'){
            if($booking->guiding->user->language == 'en'){
                \App::setLocale('en');
            }
           return view('pages.additional.mail_redirection.status',[
            'booking' => $booking,
            'action' => 'accept',
           ]);
        }

        $booking->status = 'accepted';
        $booking->save();

        $blockedevent = BlockedEvent::find($booking->blocked_event_id);
        if ($blockedevent) {
            $blockedevent->type = 'booking';
            $blockedevent->save();
        }

        event(new BookingStatusChanged($booking, 'accepted'));

        return view('pages.additional.accepted');
    }

    public function reject($token, TourCardPresenter $presenter){
        $booking = Booking::with('guiding.user')->where('token',$token)->first();

        if(!$booking){
            abort(404);
        }

        if ($booking->status !== 'pending') {
            // The form URL stays in history until the success navigation replaces it.
            // A back click (or a second open of the emailed link) must land on the
            // same confirmation as a fresh rejection, not the legacy status notice.
            if ($booking->status === 'rejected') {
                return redirect()->route('booking.rejectsuccess');
            }

            if ($booking->guiding->user->language == 'en') {
                \App::setLocale('en');
            }

            return view('pages.additional.mail_redirection.status', [
                'booking' => $booking,
                'action' => 'reject',
            ]);
        }

        return response()
            ->view('pages.modern-checkout.reject', [
                'reject' => new BookingRejectViewModel($booking, $presenter),
            ])
            ->withHeaders(self::PRIVATE_HEADERS);
    }

    public function rejectProcess(string $token, RejectionRequest $request, BookingRejectionService $rejections)
    {
        $booking = $request->booking();

        if (!$booking) {
            abort(404);
        }

        $done = $request->expectsJson()
            ? response()->json(['success' => true, 'redirect_url' => route('booking.rejectsuccess')])
            : redirect()->route('booking.rejectsuccess');

        if ($booking->status !== 'pending') {
            return $done;
        }

        // Authenticated guide (or employee) may reject their own booking;
        // unauthenticated email flow is allowed only with the secret token above.
        if (auth('web')->check()) {
            $guideId = $booking->guiding?->user_id;
            if ((int) auth('web')->id() !== (int) $guideId && !auth('employees')->check()) {
                abort(403);
            }
        }

        $rejections->reject($booking, (string) $request->validated('reason'), $request->alternativeDates());

        return $done;
    }
}
