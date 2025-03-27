<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRatingRequest;
use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\Guide\RatingConfirmation;

class RatingsController extends Controller
{
    public function show($token)
    {
        $booking = Booking::where('token', $token)->where('status', 'accepted')->firstOrFail();
        $user = User::find($booking->user_id);

        if($booking->is_reviewed){
            return redirect()->route('ratings.notified')->with('message', 'Thank you for your rating!');
        }

        return view('pages.rating.show', [
            'booking' => $booking,
            'user' => $user
        ]);
    }

    public function store(StoreRatingRequest $request, $token)
    {
        $booking = Booking::where('token', $token)->with('guiding', 'guiding.user')->firstOrFail();
        $data = $request->validated();
        
        $dataSave = [
            'overall_score' => $data['rating_overall'],
            'guide_score' => $data['rating_guide'],
            'region_water_score' => $data['rating_region'],
            'comment' => $data['comment'],
            'user_id' => $booking->user->id,
            'guide_id' => $booking->guiding->user->id,
            'booking_id' => $booking->id,
            'guiding_id' => $booking->guiding->id
        ];

        $rating = $booking->review()->create($dataSave);

        if ($rating) {
            $booking->is_reviewed = true;
            $booking->save();
            Mail::to($rating->guide->email)->send(new RatingConfirmation($rating));
        }

        return response()->json([
            'success' => true,
            'message' => __('guidings.rating_thank_you')
        ]);
    }

    public function notified()
    {
        return view('pages.rating.notified', [
            'message' => 'Thank you for your rating!'
        ]);
    }
}
