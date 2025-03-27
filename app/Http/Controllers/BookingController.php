<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RejectionRequest;
use App\Models\Booking;
use App\Models\BlockedEvent;

use App\Events\BookingStatusChanged;

class BookingController extends Controller
{
    public function accept($token){
        $tokenParts = explode('|', $token);
        $token = $tokenParts[0];
        $source = $tokenParts[1] ?? null; 
        
        $booking = Booking::where('token',$token)->first();

        if(!$booking){
            abort(404);
        }

        if($booking && $booking->status != 'pending' && $source == null){
            if($booking->guiding->user->language == 'en'){
                \App::setLocale('en');
            }    
           return view('pages.additional.mail_redirection.status',[
            'booking' => $booking,
            'action' => 'accept',
           ]);
        }

        $booking->status = 'accepted';
        if($source !== null || $source !== 'null' || $source !== ''){
            $booking->last_employee_id = $source;
        }
        
        $booking->save();

        $blockedevent = BlockedEvent::find($booking->blocked_event_id);
        $blockedevent->type = 'booking';
        $blockedevent->save();

        event(new BookingStatusChanged($booking, 'accepted'));

        if($source !== null || $source !== 'null' || $source !== ''){
            return back();
        }
        return view('pages.additional.accepted');
    }

    public function reject($token){
        $booking = Booking::where('token',$token)->first();

        if(!$booking){
            abort(404);
        }

        if($booking && $booking->status != 'pending'){
            if($booking->guiding->user->language == 'en'){
                \App::setLocale('en');
            }    

            return view('pages.additional.mail_redirection.status',[
             'booking' => $booking,
             'action' => 'reject',
            ]);
        }

        $blockedevent = $booking->guiding->getBlockedEvents();
        return view('pages.additional.rejected',[
            'booking' => $booking,
            'blocked_events' => $blockedevent
        ]);
        

    }

    public function rejectProcess(Booking $booking,RejectionRequest $request){


        $booking->update([
            'status' => 'rejected',
            'additional_information' => $request->reason,
        ]);

     
        event(new BookingStatusChanged($booking, 'rejected'));


        return redirect()->route('booking.rejectsuccess');

    }
}
