<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RejectionRequest;
use App\Models\Booking;
use App\Models\BlockedEvent;
use App\Models\CalendarSchedule;

use App\Events\BookingStatusChanged;
use App\Jobs\SendCheckoutEmail;
use App\Services\EventService;
use App\Services\HelperService;
use Carbon\Carbon;

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
        if ($blockedevent) {
            $blockedevent->type = 'booking';
            $blockedevent->save();
        }

        event(new BookingStatusChanged($booking, 'accepted'));

        if($source !== null && $source !== 'null' && $source !== '' && !is_null($source)){
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

        $booking->status = 'rejected';
        $booking->additional_information = $request->reason;
        
        $alternativeDates = json_decode($request->alternative_dates);
        if (is_array($alternativeDates)) {
            usort($alternativeDates, function($a, $b) {
                return strtotime($a) - strtotime($b);
            });
            $booking->alternative_dates = json_encode($alternativeDates);
        } else {
            $booking->alternative_dates = $request->alternative_dates;
        }
        
        $booking->save();
     
        event(new BookingStatusChanged($booking, 'rejected'));

        return redirect()->route('booking.rejectsuccess');
    }

    public function reschedule($token){
        $booking = Booking::where('token',$token)->where('status','rejected')->where('is_rescheduled',false)->first();
        $selectedDate = request()->get('date');
        
        if ($booking && $selectedDate && !empty($booking->alternative_dates)) {
            $alternativeDates = json_decode($booking->alternative_dates, true);
            if (!is_array($alternativeDates) || !in_array($selectedDate, $alternativeDates)) {
                return redirect()->route('ratings.notified')->with(['title' => 'Selected date is not in the list of alternative dates', 'message' => 'Please select a valid date from the list of alternative dates that was provided in the rejection email'])->withErrors(['booking' => 'Invalid booking or date selection']);
            }
        }
        
        if(!$booking || !$selectedDate){
            return redirect()->route('ratings.notified')->with(['title' => 'Booking not found or rejected or selected date is not in the list of alternative dates or is not available', 'message' => 'Please try again with a valid date or booking'])->withErrors(['booking' => 'Invalid booking or date selection']);
        }

        return view('pages.checkout.reschedule',[
            'booking' => $booking,
            'guiding' => $booking->guiding,
            'user' => $booking->user,
            'selectedDate' => $selectedDate
        ]);
    }

    public function rescheduleStore(Request $request)
    {
        // Validate the request data
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'selectedDate' => 'required|date',
            'count_of_users' => 'required|integer|min:1',
            'terms_accepted' => 'required|accepted',
            'total_price' => 'required|numeric',
        ]);

        // Get the original booking
        $originalBooking = Booking::findOrFail($request->booking_id);
        $guiding = $originalBooking->guiding;
        $user = $originalBooking->user;

        // Create a blocked event for the new date
        $eventService = new EventService();
        $blockedEvent = $eventService->createBlockedEvent('00:00', $request->selectedDate, $guiding, 'tour_request', $user);

        // Calculate the fee
        $helperService = new HelperService();
        $fee = $helperService->calculateRates($request->total_price);
        
        // Set expiration time based on date difference
        $expiresAt = Carbon::now()->addHours(24); // Default expiration time (24 hours)
        $dateDifference = Carbon::parse($request->selectedDate)->diffInDays(Carbon::now());
        if ($dateDifference > 3) {
            // If the selected date is more than 3 days from now, add 72 hours to the expiration time
            $expiresAt = Carbon::now()->addHours(72);
        }

        // Process extras
        $extraData = null;
        if ($request->has('extras')) {
            $extraData = $this->processExtras($request->extras, $guiding);
        }

        // Create a new booking
        $newBooking = Booking::create([
            'user_id' => $originalBooking->user_id,
            'is_guest' => $originalBooking->is_guest,
            'guiding_id' => $guiding->id,
            'blocked_event_id' => $blockedEvent->id,
            'is_paid' => false,
            'extras' => $extraData,
            'total_extra_price' => $this->calculateTotalExtraPrice($extraData),
            'count_of_users' => $request->count_of_users,
            'price' => $request->total_price,
            'cag_percent' => $fee,
            'status' => 'pending',
            'book_date' => $request->selectedDate,
            'expires_at' => $expiresAt,
            'phone' => $originalBooking->phone,
            'email' => $originalBooking->email,
            'token' => $this->generateBookingToken($blockedEvent->id),
            'parent_id' => $originalBooking->id, // Link to the original booking
        ]);

        $updateSchedule = CalendarSchedule::find($blockedEvent->id);
        $updateSchedule->booking_id = $newBooking->id;
        $updateSchedule->save();

        // Send notification emails
        if (!app()->environment('local')) {
            SendCheckoutEmail::dispatch($newBooking, $user, $guiding, $guiding->user);
        }

        if ($newBooking) {
            // Return success response for AJAX
            $originalBooking->is_rescheduled = true;
            $originalBooking->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Your booking has been successfully rescheduled.',
                'booking_id' => $newBooking->id
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to reschedule booking.'
        ]);
    }

    /**
     * Process extras from the request
     */
    private function processExtras($requestExtras, $guiding)
    {
        $guidingExtras = json_decode($guiding->pricing_extra, true) ?? [];
        $extraData = [];

        foreach ($requestExtras as $index => $extra) {
            if (isset($extra['selected']) && $extra['selected'] === 'on') {
                $quantity = isset($extra['quantity']) ? intval($extra['quantity']) : 1;
                $price = isset($guidingExtras[$index]['price']) ? floatval($guidingExtras[$index]['price']) : 0;
                $name = isset($guidingExtras[$index]['name']) ? $guidingExtras[$index]['name'] : '';
                
                $extraData[] = [
                    'extra_id' => $index,
                    'extra_name' => $name,
                    'extra_price' => $price,
                    'extra_quantity' => $quantity,
                    'extra_total_price' => $price * $quantity,
                ];
            }
        }

        return !empty($extraData) ? serialize($extraData) : null;
    }

    /**
     * Calculate total extra price from serialized extras data
     */
    private function calculateTotalExtraPrice($serializedExtras)
    {
        if (!$serializedExtras) {
            return 0;
        }

        $extras = unserialize($serializedExtras);
        $total = 0;

        foreach ($extras as $extra) {
            $total += $extra['extra_total_price'];
        }

        return $total;
    }

    private function generateBookingToken($eventID) 
    {
        $timestamp = time();
        $combinedString = $eventID . '-' . $timestamp;

        return hash('sha256', $combinedString);
    }
}
