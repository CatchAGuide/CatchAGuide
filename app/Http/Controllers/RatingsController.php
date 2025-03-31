<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRatingRequest;
use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;

class RatingsController extends Controller
{
    public function show(Booking $booking)
    {
        #if (! request()->hasValidSignature() || $booking->rating_id !== null) {
        #    abort(401);
        #}
        $user = User::find($booking->user_id);

        return view('pages.rating.show', [
            'booking' => $booking,
            'user' => $user
        ]);
    }

    public function store(StoreRatingRequest $request, $bookingid)
    {
        $booking = Booking::find($bookingid);
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['booking_id'] = $booking->id;
        $data['guide_id'] = $booking->guiding->user->id;
        $data['created_at'] = Carbon::now();
        $data['updated_at'] = Carbon::now();

        $booking->rating()->create($data);

        return redirect()->route('welcome')->with('message', 'Die Bewertung wurde erfolgreich abgegeben!');
    }
}
