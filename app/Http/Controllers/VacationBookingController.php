<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use App\Models\Vacation;
use App\Models\VacationBooking;
use App\Mail\Ceo\VacationBookingNotification;
use App\Mail\Guest\GuestVacationBookingNotification;
class VacationBookingController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'vacation_id' => 'required|exists:vacations,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'duration' => 'required|integer|min:1',
                'person' => 'required|integer|min:1',
                'booking_type' => 'required|in:package,custom',
                'package_id' => 'nullable|exists:vacation_packages,id',
                'accommodation_id' => 'nullable|exists:vacation_accommodations,id',
                'boat_id' => 'nullable|exists:vacation_boats,id',
                'guiding_id' => 'nullable|exists:vacation_guidings,id',
                'title' => 'required|in:Mr,Mrs',
                'name' => 'required|string',
                'surname' => 'required|string',
                'street' => 'required|string',
                'post_code' => 'required|string',
                'city' => 'required|string',
                'country' => 'required|string',
                'phone_country_code' => 'required|string',
                'phone' => 'required|string',
                'email' => 'required|email',
                'comments' => 'nullable|string',
                'has_pets' => 'nullable|in:true,false,0,1',
                'extra_offers' => 'nullable|array',
                'extra_quantity' => 'nullable|array',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed:', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            throw $e;
        }

        $totalPrice = $this->calculateTotalPrice($request);

        $booking = VacationBooking::create([
            'vacation_id' => $validated['vacation_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'duration' => $validated['duration'],
            'number_of_persons' => $validated['person'],
            'booking_type' => $validated['booking_type'],
            'package_id' => $validated['package_id'] ?? null,
            'accommodation_id' => $validated['accommodation_id'] ?? null,
            'boat_id' => $validated['boat_id'] ?? null,
            'guiding_id' => $validated['guiding_id'] ?? null,
            'title' => $validated['title'],
            'name' => $validated['name'],
            'surname' => $validated['surname'],
            'street' => $validated['street'],
            'post_code' => $validated['post_code'],
            'city' => $validated['city'],
            'country' => $validated['country'],
            'phone_country_code' => $validated['phone_country_code'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'comments' => $validated['comments'],
            'has_pets' => $validated['has_pets'] == 'true' ? 1 : 0,
            'extra_offers' => $this->formatExtraOffers($validated['extra_offers'] ?? [], $validated['extra_quantity'] ?? []),
            'total_price' => $totalPrice,
            'status' => 'pending'
        ]);

        // Send email notification
        try {
            Mail::to(env('TO_CEO'))->send(new VacationBookingNotification($booking));
            Mail::to($booking->email)->send(new GuestVacationBookingNotification($booking));
        } catch (\Exception $e) {
            Log::error('Failed to send booking notification email:', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);
        }

        // Return a JSON response for AJAX handling
        return response()->json([
            'success' => true,
            'message' => translate('Your booking request has been submitted successfully!'),
            'booking' => $booking
        ]);
    }

    private function formatExtraOffers($extraOffers, $quantities)
    {
        $formatted = [];
        foreach ($extraOffers as $extraId) {
            $formatted[$extraId] = [
                'quantity' => $quantities[$extraId] ?? 1
            ];
        }
        return $formatted;
    }

    private function calculateTotalPrice(Request $request)
    {
        $totalPrice = 0;
        $persons = (int) $request->person;
        
        // Get the vacation with its relationships
        $vacation = Vacation::with(['packages', 'accommodations', 'boats', 'guidings', 'extras'])
            ->findOrFail($request->vacation_id);

        // Calculate price based on booking type
        if ($request->booking_type === 'package' && $request->package_id) {
            $package = $vacation->packages->find($request->package_id);
            if ($package) {
                $totalPrice += $this->calculateServicePrice($package, $persons);
            }
        } else {
            // Calculate accommodation price
            if ($request->accommodation_id) {
                $accommodation = $vacation->accommodations->find($request->accommodation_id);
                if ($accommodation) {
                    $totalPrice += $this->calculateServicePrice($accommodation, $persons);
                }
            }

            // Calculate boat price
            if ($request->boat_id) {
                $boat = $vacation->boats->find($request->boat_id);
                if ($boat) {
                    $totalPrice += $this->calculateServicePrice($boat, $persons);
                }
            }

            // Calculate guiding price
            if ($request->guiding_id) {
                $guiding = $vacation->guidings->find($request->guiding_id);
                if ($guiding) {
                    $totalPrice += $this->calculateServicePrice($guiding, $persons);
                }
            }
        }

        // Calculate extras price
        if ($request->extra_offers) {
            foreach ($request->extra_offers as $extraId) {
                $extra = $vacation->extras->find($extraId);
                if ($extra) {
                    if ($extra->type === 'per_person') {
                        $quantity = $request->extra_quantity[$extraId] ?? 1;
                        $totalPrice += $extra->price * $quantity;
                    } else {
                        $totalPrice += $extra->price;
                    }
                }
            }
        }

        return $totalPrice;
    }

    private function calculateServicePrice($service, $persons)
    {
        try {
            $dynamicFields = is_string($service->dynamic_fields) 
                ? json_decode($service->dynamic_fields, true) 
                : $service->dynamic_fields;

            if (!$dynamicFields || !isset($dynamicFields['prices']) || !is_array($dynamicFields['prices'])) {
                return 0;
            }

            $prices = array_map('floatval', $dynamicFields['prices']);
            $capacity = (int) ($service->capacity ?? 
                              ($dynamicFields['bed_count'] ?? 
                              count($prices)));
            $maxPrice = max($prices);

            if ($persons <= $capacity) {
                // If persons is within the direct price array range, use that price
                return $persons > 0 && $persons <= count($prices) ? $prices[$persons - 1] : 0;
            } else {
                // Calculate price for groups larger than capacity
                $fullGroups = floor($persons / $capacity);
                $remainder = $persons % $capacity;
                
                $totalPrice = $fullGroups * $maxPrice;
                
                if ($remainder > 0) {
                    $totalPrice += $remainder <= count($prices) 
                        ? $prices[$remainder - 1] 
                        : $maxPrice;
                }
                
                return $totalPrice;
            }
        } catch (\Exception $e) {
            \Log::error('Error calculating service price: ' . $e->getMessage());
            return 0;
        }
    }
} 