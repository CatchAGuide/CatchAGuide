<?php

namespace App\Http\Controllers;

use App\Models\Guiding;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ModernCheckoutController extends Controller
{
    /**
     * Display the modern checkout page
     */
    public function index()
    {
        $guidingId = Session::get('guiding_id');
        $persons = Session::get('person', 1);
        $selectedDate = Session::get('selected_date');

        if (!$guidingId) {
            return redirect()->route('guidings.index')->with('error', 'No guiding selected for checkout.');
        }

        $guiding = Guiding::with(['user'])->find($guidingId);
        
        if (!$guiding) {
            return redirect()->route('guidings.index')->with('error', 'Guiding not found.');
        }

        return view('pages.modern-checkout.index', [
            'guiding' => $guiding,
            'persons' => $persons,
            'selectedDate' => $selectedDate
        ]);
    }

    /**
     * Handle checkout form submission
     */
    public function store(Request $request)
    {
        // This method can be used for non-AJAX form submissions if needed
        // For now, we're using the API endpoints for AJAX submissions
        return redirect()->back()->with('error', 'Please use the booking form to submit your request.');
    }

    /**
     * Display thank you page
     */
    public function thankYou($bookingId)
    {
        try {
            $booking = Booking::with(['guiding.user'])->findOrFail($bookingId);
            return view('pages.modern-checkout.thank-you', compact('booking'));
        } catch (\Exception $e) {
            return redirect()->route('home')->with('error', 'Booking not found.');
        }
    }
}