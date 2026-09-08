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
use App\Services\BookingService;
use Carbon\Carbon;
use InvalidArgumentException;

class BookingController extends Controller
{
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

    public function rejectProcess(string $token, RejectionRequest $request)
    {
        $booking = Booking::where('token', $token)->first();

        if (!$booking) {
            abort(404);
        }

        if ($booking->status !== 'pending') {
            return redirect()->route('booking.rejectsuccess');
        }

        // Authenticated guide (or employee) may reject their own booking;
        // unauthenticated email flow is allowed only with the secret token above.
        if (auth('web')->check()) {
            $guideId = $booking->guiding?->user_id;
            if ((int) auth('web')->id() !== (int) $guideId && !auth('employees')->check()) {
                abort(403);
            }
        }

        $booking->status = 'rejected';
        $booking->additional_information = $request->reason;

        $alternativeDates = json_decode($request->alternative_dates);
        if (is_array($alternativeDates)) {
            usort($alternativeDates, function ($a, $b) {
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
        // Validate the request data. Note: no `booking_id`/`total_price` here — both used
        // to be trusted straight from the client, which let anyone reschedule any booking
        // (by guessing a sequential ID) at any price they named. The booking is now looked
        // up by the same secret token the GET reschedule page requires, and the price is
        // recomputed server-side below instead of trusted from the form.
        $request->validate([
            'token' => 'required|string',
            'selectedDate' => 'required|date',
            'count_of_users' => 'required|integer|min:1',
            'terms_accepted' => 'required|accepted',
        ]);

        $originalBooking = Booking::where('token', $request->token)
            ->where('status', 'rejected')
            ->where('is_rescheduled', false)
            ->first();

        if (!$originalBooking) {
            return response()->json([
                'success' => false,
                'message' => 'This reschedule link is invalid or has already been used.',
            ], 404);
        }

        if (!empty($originalBooking->alternative_dates)) {
            $alternativeDates = json_decode($originalBooking->alternative_dates, true);
            if (!is_array($alternativeDates) || !in_array($request->selectedDate, $alternativeDates)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select one of the alternative dates provided in the rejection email.',
                ], 422);
            }
        }

        $guiding = $originalBooking->guiding;

        $maxGuests = $guiding->max_guests ?? 10;
        $persons = min((int) $request->count_of_users, (int) $maxGuests);

        // Process extras — prices come from the guiding record, not the client; only the
        // selection/quantity is client-supplied (same as the original checkout flow).
        $extraData = [];
        if ($request->has('extras')) {
            $extraData = $this->processExtras($request->extras, $guiding);
        }
        $totalExtraPrice = array_reduce($extraData, fn ($carry, $extra) => $carry + ($extra['extra_total_price'] ?? 0), 0.0);

        $guidingPrice = $this->calculateGuidingBasePrice($guiding, $persons);
        $totalPrice = $guidingPrice + $totalExtraPrice;

        $bookingService = app(BookingService::class);

        $bookingData = [
            'selected_date' => $request->selectedDate,
            'total_price' => $totalPrice,
            'total_extra_price' => $totalExtraPrice,
            'count_of_users' => $persons,
            'extras_serialized' => !empty($extraData) ? serialize($extraData) : null,
        ];

        try {
            $newBooking = $bookingService->rescheduleGuidingBooking(
                $originalBooking,
                $bookingData,
                sendEmails: !app()->environment('local'),
            );
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
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
     * Server-side guiding base price, mirroring the pricing rules used at initial
     * checkout (Api\ModernCheckoutApiController::calculatePrice) — kept separate here
     * since the reschedule flow doesn't go through the checkout API.
     */
    private function calculateGuidingBasePrice($guiding, int $persons): float
    {
        if ($guiding->price_type == 'per_person') {
            $prices = json_decode($guiding->prices, true);
            if ($prices) {
                foreach ($prices as $price) {
                    if ($price['person'] == $persons) {
                        return (float) $price['amount'];
                    }
                }
                $lastPrice = end($prices);
                return (float) $lastPrice['amount'] * $persons;
            }

            return (float) $guiding->price_per_person * $persons;
        }

        return (float) $guiding->price;
    }

    /**
     * Process extras from the request. Returns the raw array (prices resolved from the
     * guiding record, not the client) — callers serialize it themselves once they've
     * also had a chance to total it up for the price recomputation.
     */
    private function processExtras($requestExtras, $guiding): array
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

        return $extraData;
    }

}
