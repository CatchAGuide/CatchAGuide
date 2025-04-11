<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingsController extends Controller
{
    public function index()
    {
        $booking = Booking::with('employee', 'guiding.user')->orderBy('created_at', 'DESC')->get();
        
        return view('admin.pages.bookings.index', [
            'bookings' => $booking
        ]);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(Booking $booking)
    {
        //
    }

    public function edit(Booking $booking)
    {

    }

    public function update(Request $request, Booking $booking)
    {
        //
    }

    public function destroy(Booking $booking)
    {
        $booking->delete();
        return redirect()->back();
    }

    public function emailPreview(Booking $booking)
    {
        // Get the necessary data for the email templates
        $user = $booking->user ?? (object)['firstname' => $booking->firstname, 'lastname' => $booking->lastname];
        $guide = $booking->guiding->user;
        $guiding = $booking->guiding;
        
        // Prepare data for the booking request email
        $guideName = $guide->firstname;
        $textNote = __('emails.guest_booking_request_text_1');
        $textNote = str_replace('[Guide Name]', $guideName, $textNote);
        
        $formattedDate = date('F j, Y', strtotime($booking->book_date));
        $textNote = str_replace('[Date]', $formattedDate, $textNote);
        
        $textNote = str_replace('[Location]', $guiding->location, $textNote);
        
        $alternativeText = __('emails.guest_booking_request_text_5');
        $alternativeText = str_replace('[Guide Name]', $guideName, $alternativeText);
        
        // Render the guest email templates
        $bookingRequestEmail = view('mails.guest.guest_booking_request', compact(
            'user', 'guide', 'guiding', 'booking', 'textNote', 'alternativeText'
        ))->render();
        
        $expiredBookingEmail = view('mails.guest.guest_expired_booking', compact(
            'user', 'guide', 'guiding', 'booking'
        ))->render();
        
        // Initialize optional email templates as null
        $acceptedBookingEmail = null;
        $rejectedBookingEmail = null;
        $tourReminderEmail = null;
        $guestReviewEmail = null;
        
        // Render accepted booking email if applicable
        if ($booking->status === 'accepted' || $booking->status === 'completed') {
            try {
                $acceptedBookingEmail = view('mails.guest.guest_accepted_mail', compact(
                    'user', 'guide', 'guiding', 'booking'
                ))->render();
            } catch (\Exception $e) {
                // If template doesn't exist or there's an error, leave it as null
                \Log::error('Error rendering accepted booking email template: ' . $e->getMessage());
            }
        }
        
        // Render rejected booking email if applicable
        if ($booking->status === 'rejected') {
            try {
                $rejectedBookingEmail = view('mails.guest.guest_rejected_mail', compact(
                    'user', 'guide', 'guiding', 'booking'
                ))->render();
            } catch (\Exception $e) {
                // If template doesn't exist or there's an error, leave it as null
                \Log::error('Error rendering rejected booking email template: ' . $e->getMessage());
            }
        }
        
        // Render tour reminder email if applicable
        if ($booking->status === 'accepted' || $booking->status === 'completed') {
            try {
                $tourReminderEmail = view('mails.guest.guest_tour_reminder', compact(
                    'user', 'guide', 'guiding', 'booking'
                ))->render();
            } catch (\Exception $e) {
                // If template doesn't exist or there's an error, leave it as null
                \Log::error('Error rendering tour reminder email template: ' . $e->getMessage());
            }
        }
        
        // Render guest review email if applicable
        if ($booking->status === 'completed') {
            try {
                $reviewUrl = route('ratings.create', ['token' => $booking->token]);
                $userName = $user->firstname;
                $guideName = $guide->firstname;
                $location = $guiding->location;
                
                $guestReviewEmail = view('mails.guest.guest_review', compact(
                    'userName', 'guideName', 'location', 'reviewUrl'
                ))->render();
            } catch (\Exception $e) {
                // If template doesn't exist or there's an error, leave it as null
                \Log::error('Error rendering guest review email template: ' . $e->getMessage());
            }
        }
        
        // Initialize guide email templates as null
        $guideBookingRequestEmail = null;
        $guideExpiredBookingEmail = null;
        $guideAcceptedBookingEmail = null;
        $guideReminderEmail = null;
        $guideReminder12hrsEmail = null;
        $guideUpcomingTourEmail = null;
        
        // Render guide booking request email
        try {
            $guideBookingRequestEmail = view('mails.guide.guide_booking_request', compact(
                'user', 'guide', 'guiding', 'booking'
            ))->render();
        } catch (\Exception $e) {
            \Log::error('Error rendering guide booking request email template: ' . $e->getMessage());
        }
        
        // Render guide expired booking email
        try {
            $guideExpiredBookingEmail = view('mails.guide.guide_expired_booking', compact(
                'user', 'guide', 'guiding', 'booking'
            ))->render();
        } catch (\Exception $e) {
            \Log::error('Error rendering guide expired booking email template: ' . $e->getMessage());
        }
        
        // Render guide accepted booking email if applicable
        if ($booking->status === 'accepted' || $booking->status === 'completed') {
            try {
                $guideAcceptedBookingEmail = view('mails.guide.guide_accepted_mail', compact(
                    'user', 'guide', 'guiding', 'booking'
                ))->render();
            } catch (\Exception $e) {
                \Log::error('Error rendering guide accepted booking email template: ' . $e->getMessage());
            }
        }
        
        // Render guide reminder email if applicable
        if ($booking->status === 'pending') {
            try {
                $guideReminderEmail = view('mails.guide.guide_reminder', compact(
                    'user', 'guide', 'guiding', 'booking'
                ))->render();
            } catch (\Exception $e) {
                \Log::error('Error rendering guide reminder email template: ' . $e->getMessage());
            }
        }
        
        // Render guide 12hrs reminder email if applicable
        if ($booking->status === 'pending') {
            try {
                $guideReminder12hrsEmail = view('mails.guide.guide_reminder_12hrs', compact(
                    'user', 'guide', 'guiding', 'booking'
                ))->render();
            } catch (\Exception $e) {
                \Log::error('Error rendering guide 12hrs reminder email template: ' . $e->getMessage());
            }
        }
        
        // Render guide upcoming tour email if applicable
        if ($booking->status === 'accepted' || $booking->status === 'completed') {
            try {
                $guideUpcomingTourEmail = view('mails.guide.guide_upcoming_tour', compact(
                    'user', 'guide', 'guiding', 'booking'
                ))->render();
            } catch (\Exception $e) {
                \Log::error('Error rendering guide upcoming tour email template: ' . $e->getMessage());
            }
        }
        
        return response()->json([
            // Guest emails
            'bookingRequestEmail' => $bookingRequestEmail,
            'expiredBookingEmail' => $expiredBookingEmail,
            'acceptedBookingEmail' => $acceptedBookingEmail,
            'rejectedBookingEmail' => $rejectedBookingEmail,
            'tourReminderEmail' => $tourReminderEmail,
            'guestReviewEmail' => $guestReviewEmail,
            
            // Guide emails
            'guideBookingRequestEmail' => $guideBookingRequestEmail,
            'guideExpiredBookingEmail' => $guideExpiredBookingEmail,
            'guideAcceptedBookingEmail' => $guideAcceptedBookingEmail,
            'guideReminderEmail' => $guideReminderEmail,
            'guideReminder12hrsEmail' => $guideReminder12hrsEmail,
            'guideUpcomingTourEmail' => $guideUpcomingTourEmail
        ]);
    }
}
