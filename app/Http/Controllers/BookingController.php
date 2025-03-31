<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RejectionRequest;
use App\Models\Booking;
use App\Models\BlockedEvent;

use App\Events\BookingStatusChanged;
use App\Models\UserInformation;

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
        $booking = Booking::where('token',$token)->first();
        $selectedDate = request()->get('date');
        
        if(!$booking && !$selectedDate){
            abort(404);
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
        $eventService = new \App\Services\EventService();
        $blockedEvent = $eventService->createBlockedEvent('00:00', $request->selectedDate, $guiding);

        // Calculate the fee
        $helperService = new \App\Services\HelperService();
        $fee = $helperService->calculateRates($request->total_price);
        
        // Set expiration time based on date difference
        $expiresAt = \Carbon\Carbon::now()->addHours(24); // Default expiration time (24 hours)
        $dateDifference = \Carbon\Carbon::parse($request->selectedDate)->diffInDays(\Carbon\Carbon::now());
        if ($dateDifference > 3) {
            // If the selected date is more than 3 days from now, add 72 hours to the expiration time
            $expiresAt = \Carbon\Carbon::now()->addHours(72);
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
            'token' => $this->generateBookingToken($blockedEvent->id),
            'has_parent' => $originalBooking->id, // Link to the original booking
        ]);

        // Send notification emails
        if (!app()->environment('local')) {
            $this->sendRescheduleEmails($newBooking, $originalBooking);
        }

        // Return success response for AJAX
        return response()->json([
            'success' => true,
            'message' => 'Your booking has been successfully rescheduled.',
            'booking_id' => $newBooking->id
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

    /**
     * Generate a unique booking token
     */
    private function generateBookingToken($eventID) 
    {
        $timestamp = time();
        $combinedString = $eventID . '-' . $timestamp;

        // Generate a hash of the combined string
        return hash('sha256', $combinedString);
    }

    /**
     * Send reschedule notification emails
     */
    private function sendRescheduleEmails($newBooking, $originalBooking)
    {
        // Create a new job to send emails
        \App\Jobs\SendRescheduleEmail::dispatch($newBooking, $originalBooking);
    }
}
