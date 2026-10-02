<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Booking;
use App\Models\Guiding;
use App\Services\Checkout\BookingConfirmationAccess;
use Illuminate\Support\Facades\Session;

class CheckoutController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|string
     */
    public function checkoutView()
    {
        if(Session::has('guiding_id')) {
            return view('pages.checkout.index', [
                'guiding' => Guiding::find(Session::get('guiding_id')),
                'persons' => Session::get('person'),
                'selectedDate' => Session::get('selected_date', null)
            ]);
        }
        return route('welcome');
    }

    /**
     * @param CheckoutRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function checkout(CheckoutRequest $request)
    {
        Session::put([
            'guiding_id' => $request->guiding_id, 
            'person' => $request->person,
            'selected_date' => $request->selected_date
        ]);

        return redirect()->route('checkout');
    }


    /**
     * Legacy booking confirmation; guarded like ModernCheckoutController::thankYou.
     */
    public function thankYou(Booking $booking, BookingConfirmationAccess $confirmations)
    {
        abort_unless($confirmations->allows($booking, auth()->user()), 404);

        return view('pages.additional.thank_you', [
            'booking' => $booking
        ]);
    }
}
