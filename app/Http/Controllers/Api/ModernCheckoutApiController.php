<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guiding;
use App\Models\Booking;
use App\Models\User;
use App\Models\UserGuest;
use App\Models\UserInformation;
use App\Models\CalendarSchedule;
use App\Services\EventService;
use App\Services\HelperService;
use App\Jobs\SendCheckoutEmail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ModernCheckoutApiController extends Controller
{
    /**
     * Get guiding details for checkout
     */
    public function getGuiding($id)
    {
        try {
            $guiding = Guiding::with(['user'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $guiding->id,
                    'title' => $guiding->title,
                    'location' => $guiding->location,
                    'duration' => $guiding->duration,
                    'duration_type' => $guiding->duration_type,
                    'price' => $guiding->price,
                    'price_per_person' => $guiding->price_per_person,
                    'price_type' => $guiding->price_type,
                    'prices' => $guiding->prices,
                    'requirements' => $guiding->getRequirementsAttribute(),
                    'target_fish' => collect($guiding->getTargetFishNames())->pluck('name')->toArray(),
                    'extras' => json_decode($guiding->pricing_extra, true) ?? [],
                    'blocked_events' => $guiding->getBlockedEvents(),
                    'guide' => [
                        'name' => $guiding->user->firstname . ' ' . $guiding->user->lastname,
                        'profile_image' => $guiding->user->profil_image ? asset('images/' . $guiding->user->profil_image) : asset('images/placeholder_guide.jpg'),
                        'payment_methods' => $this->getPaymentMethods($guiding->user)
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching guiding: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Guiding not found'], 404);
        }
    }

    /**
     * Calculate total price
     */
    public function calculatePrice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'guiding_id' => 'required|exists:guidings,id',
            'persons' => 'required|integer|min:1|max:10',
            'selected_extras' => 'array',
            'selected_extras.*' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $guiding = Guiding::findOrFail($request->guiding_id);
            $persons = $request->persons;
            $selectedExtras = $request->selected_extras ?? [];
            $extras = json_decode($guiding->pricing_extra, true) ?? [];

            // Calculate guiding price - this should be per tour, not per person
            Log::info('Price calculation - price_type: ' . $guiding->price_type . ', price: ' . $guiding->price . ', persons: ' . $persons);
            
            $guidingPrice = 0;
            if ($guiding->price_type == 'per_person') {
                // If price_type is per_person, use the pricing structure
                $prices = json_decode($guiding->prices, true);
                if ($prices) {
                    foreach ($prices as $price) {
                        if ($price['person'] == $persons) {
                            $guidingPrice = $price['amount'];
                            break;
                        }
                    }
                    // If no exact match found, use the last price * persons
                    if ($guidingPrice == 0) {
                        $lastPrice = end($prices);
                        $guidingPrice = $lastPrice['amount'] * $persons;
                    }
                } else {
                    // Fallback to price_per_person * persons
                    $guidingPrice = $guiding->price_per_person * $persons;
                }
            } else {
                // If price_type is not per_person, it's per tour (total price for the tour)
                $guidingPrice = $guiding->price;
            }

            // Calculate extras price
            $totalExtraPrice = 0;
            $extrasBreakdown = [];
            
            foreach ($selectedExtras as $index => $isSelected) {
                if ($isSelected && isset($extras[$index])) {
                    $extraTotal = $extras[$index]['price'] * $persons; // Quantity will be handled in booking summary
                    $totalExtraPrice += $extraTotal;
                    
                    $extrasBreakdown[] = [
                        'name' => $extras[$index]['name'],
                        'price' => $extras[$index]['price'],
                        'quantity' => $persons,
                        'total' => $extraTotal
                    ];
                }
            }

            $totalPrice = $guidingPrice + $totalExtraPrice;
            
            Log::info('Final calculation - guidingPrice: ' . $guidingPrice . ', totalExtraPrice: ' . $totalExtraPrice . ', totalPrice: ' . $totalPrice);

            return response()->json([
                'success' => true,
                'data' => [
                    'guidingPrice' => $guidingPrice,
                    'totalExtraPrice' => $totalExtraPrice,
                    'totalPrice' => $totalPrice,
                    'breakdown' => [
                        'extras' => $extrasBreakdown
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error calculating price: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error calculating price'], 500);
        }
    }

    /**
     * Get available dates for a guiding
     */
    public function getAvailableDates($guidingId)
    {
        try {
            $guiding = Guiding::findOrFail($guidingId);
            $blockedDates = $this->getBlockedDates($guiding);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'blocked_dates' => $blockedDates,
                    'min_date' => Carbon::tomorrow()->format('Y-m-d')
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching available dates: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error fetching dates'], 500);
        }
    }

    /**
     * Submit booking
     */
    public function submitBooking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'guiding_id' => 'required|exists:guidings,id',
            'persons' => 'required|integer|min:1|max:10',
            'selected_date' => 'required|date|after:today',
            'form_data' => 'required|array',
            'form_data.firstName' => 'required|string|max:255',
            'form_data.lastName' => 'required|string|max:255',
            'form_data.email' => 'required|email|max:255',
            'form_data.phone' => 'required|string|min:3',
            'form_data.countryCode' => 'required|string',
            'form_data.policyAccepted' => 'required|accepted',
            'selected_extras' => 'array'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            // Get guiding and user data
            $guiding = Guiding::with(['user'])->findOrFail($request->guiding_id);
            $currentUser = auth()->user();
            $locale = app()->getLocale();

            // Calculate total price
            $priceResponse = $this->calculatePrice($request);
            $priceData = $priceResponse->getData();
            
            if (!$priceData->success) {
                return $priceResponse;
            }

            // Handle user creation/retrieval (similar to original checkout)
            $user = $this->handleUserCreation($currentUser, $request->form_data, $locale);
            $isGuest = $user instanceof UserGuest;
            $userId = $user->id;

            // Create blocked event
            $blockedEvent = $this->createBlockedEvent($request->selected_date, $guiding);

            // Calculate fees
            $helperService = app(HelperService::class);
            $fee = $helperService->calculateRates($priceData->data->guidingPrice);
            $partnerFee = $helperService->convertAmountToString($fee);
            
            // Debug logging
            Log::info('Fee calculation - guidingPrice: ' . $priceData->data->guidingPrice . ', fee: ' . $fee . ', partnerFee: ' . $partnerFee);

            // Set expiration time
            $expiresAt = $this->calculateExpirationTime($request->selected_date);

            // Prepare extra data
            $extraData = $this->prepareExtraData($request->selected_extras, $guiding, $request->persons);

            // Create booking
            $booking = Booking::create([
                'user_id' => $userId,
                'is_guest' => $isGuest,
                'guiding_id' => $guiding->id,
                'blocked_event_id' => $blockedEvent->id,
                'is_paid' => false,
                'extras' => $extraData,
                'total_extra_price' => $priceData->data->totalExtraPrice,
                'count_of_users' => $request->persons,
                'price' => $priceData->data->totalPrice,
                'cag_percent' => $fee,
                'status' => 'pending',
                'book_date' => $request->selected_date,
                'expires_at' => $expiresAt,
                'phone' => $request->form_data['countryCode'] . ' ' . $request->form_data['phone'],
                'phone_country_code' => $request->form_data['countryCode'],
                'language' => $locale,
                'email' => $request->form_data['email'],
                'token' => $this->generateBookingToken($blockedEvent->id),
            ]);
            
            // Debug logging for booking creation
            Log::info('Booking created - ID: ' . $booking->id . ', cag_percent: ' . $booking->cag_percent . ', price: ' . $booking->price);

            // Update calendar schedule
            $this->updateCalendarSchedule($blockedEvent->id, $booking->id);

            // Send email (if not in local environment)
            if (!app()->environment('local')) {
                $this->sendBookingEmail($booking, $user, $guiding);
            }

            return response()->json([
                'success' => true,
                'message' => 'Booking submitted successfully',
                'data' => [
                    'booking_id' => $booking->id,
                    'total_price' => $priceData->data->totalPrice,
                    'redirect_url' => route('modern-checkout.thank-you', [$booking])
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error submitting booking: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error submitting booking'], 500);
        }
    }

    /**
     * Get payment methods for a guide
     */
    private function getPaymentMethods($guide)
    {
        $methods = [];
        
        if ($guide->bar_allowed) {
            $methods[] = 'Cash';
        }
        if ($guide->banktransfer_allowed) {
            $methods[] = 'Bank transfer';
        }
        if ($guide->paypal_allowed) {
            $methods[] = 'PayPal';
        }
        
        return $methods;
    }

    /**
     * Get blocked dates for a guiding
     */
    private function getBlockedDates($guiding)
    {
        try {
            $blockedEvents = $guiding->getBlockedEvents();
            $blockedDates = [];
            
            if ($blockedEvents && is_array($blockedEvents)) {
                foreach ($blockedEvents as $event) {
                    if (isset($event['from']) && isset($event['due'])) {
                        $fromDate = Carbon::parse($event['from']);
                        $dueDate = Carbon::parse($event['due']);
                        
                        while ($fromDate->lte($dueDate)) {
                            $blockedDates[] = $fromDate->format('Y-m-d');
                            $fromDate->addDay();
                        }
                    }
                }
            }
            
            return $blockedDates;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Handle user creation/retrieval based on authentication status
     */
    private function handleUserCreation($currentUser, $formData, $locale)
    {
        if ($currentUser) {
            // Update phone for registered user
            if (!$currentUser->is_guide) {
                $currentUser->phone = $formData['phone'];
                $currentUser->phone_country_code = $formData['countryCode'];
                $currentUser->save();
            }

            if ($currentUser->information) {
                $currentUser->information->phone = $formData['phone'];
                $currentUser->information->phone_country_code = $formData['countryCode'];
                $currentUser->information->save();
            }

            return $currentUser;
        } else {
            // Check if guest user already exists with this email
            $user = UserGuest::where('email', $formData['email'])->first();

            if (!$user) {
                // Create new guest user
                $user = UserGuest::create([
                    'salutation' => 'male',
                    'title' => '',
                    'firstname' => $formData['firstName'],
                    'lastname' => $formData['lastName'],
                    'address' => '',
                    'postal' => '',
                    'city' => '',
                    'country' => 'Deutschland',
                    'phone' => $formData['phone'],
                    'phone_country_code' => $formData['countryCode'],
                    'email' => $formData['email'],
                    'language' => $locale,
                ]);
            } else {
                // Update existing guest user information
                $user->firstname = $formData['firstName'];
                $user->lastname = $formData['lastName'];
                $user->phone = $formData['phone'];
                $user->phone_country_code = $formData['countryCode'];
                $user->language = $locale;
                $user->save();
            }

            return $user;
        }
    }

    /**
     * Create blocked event for the booking
     */
    private function createBlockedEvent($selectedDate, $guiding)
    {
        $eventService = app(EventService::class);
        return $eventService->createBlockedEvent('00:00', $selectedDate, $guiding, 'tour_request');
    }

    /**
     * Calculate rates using HelperService
     */
    private function calculateRates($guidingPrice)
    {
        $helperService = app(HelperService::class);
        return $helperService->calculateRates($guidingPrice);
    }

    /**
     * Convert amount to string using HelperService
     */
    private function convertAmountToString($amount)
    {
        $helperService = app(HelperService::class);
        return $helperService->convertAmountToString($amount);
    }

    /**
     * Calculate expiration time based on selected date
     */
    private function calculateExpirationTime($selectedDate)
    {
        $expiresAt = Carbon::now()->addHours(24); // Default expiration time (24 hours)

        // Calculate the difference between the selected date and the current date
        $dateDifference = Carbon::parse($selectedDate)->diffInDays(Carbon::now());

        if ($dateDifference > 3) {
            // If the selected date is more than 3 days from now, add 72 hours to the expiration time
            $expiresAt = Carbon::now()->addHours(48);
        }

        return $expiresAt;
    }

    /**
     * Prepare extra data for booking
     */
    private function prepareExtraData($selectedExtras, $guiding, $persons)
    {
        $extras = json_decode($guiding->pricing_extra, true) ?? [];
        $extraData = [];

        foreach ($selectedExtras as $index => $isSelected) {
            if ($isSelected && isset($extras[$index])) {
                $extra = $extras[$index];
                $quantity = $persons; // Use persons as quantity for extras
                $price = floatval($extra['price']);
                $subtotal = $price * intval($quantity);
                
                $extraData[] = [
                    'extra_id' => $index,
                    'extra_name' => $extra['name'],
                    'extra_price' => $price,
                    'extra_quantity' => $quantity,
                    'extra_total_price' => $subtotal,
                ];
            }
        }

        return !empty($extraData) ? serialize($extraData) : null;
    }

    /**
     * Update calendar schedule with booking ID
     */
    private function updateCalendarSchedule($eventId, $bookingId)
    {
        $updateSchedule = CalendarSchedule::find($eventId);
        if ($updateSchedule) {
            $updateSchedule->booking_id = $bookingId;
            $updateSchedule->save();
        }
    }

    /**
     * Send booking email
     */
    private function sendBookingEmail($booking, $user, $guiding)
    {
        try {
            SendCheckoutEmail::dispatch($booking, $user, $guiding, $guiding->user);
        } catch (\Exception $e) {
            Log::error('Error sending booking email: ' . $e->getMessage());
        }
    }

    /**
     * Generate booking token
     */
    private function generateBookingToken($eventId)
    {
        $timestamp = time();
        $combinedString = $eventId . '-' . $timestamp;
        return hash('sha256', $combinedString);
    }
}
