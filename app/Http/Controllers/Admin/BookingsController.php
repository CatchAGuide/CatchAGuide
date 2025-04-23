<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
        
        if ($booking->status === 'rejected') {
            try {
                $rejection = $booking;
                $text = __('emails.guest_booking_request_cancelled_text_1');
                $text = str_replace('[Guide Name]', $guideName, $text);

                $formattedDate = date('F j, Y', strtotime($booking->book_date));
                $text = str_replace('[Date]', $formattedDate, $text);

                $text = str_replace('[Location]', $guiding->location, $text);

                $rejection->guideName = $guideName;
                $rejection->textNote = $text;
                
                $textProvide = __('emails.guest_booking_request_cancelled_text_4');
                $textProvide = str_replace('[Guide Name]', $guideName, $textProvide);

                $rejection->alternativeText = $textProvide;
                $rejection->alternativeDates = json_decode($rejection->alternative_dates);

                $rejectedBookingEmail = view('mails.guest.rejected_mail', [
                    'user' => $user,
                    'guide' => $guide,
                    'guiding' => $guiding,
                    'booking' => $rejection
                ])->render();

            } catch (\Exception $e) {
                \Log::error('Error rendering rejected booking email template: ' . $e->getMessage());
            }
        }
        
        if ($booking->status === 'accepted') {
            try {
                $acceptedBookingEmail = view('mails.guest.accepted_mail', compact(
                    'user', 'guide', 'guiding', 'booking'
                ))->render();
            } catch (\Exception $e) {
                \Log::error('Error rendering accepted booking email template: ' . $e->getMessage());
            }

            try {
                $guestName = $booking->is_guest 
                    ? ($booking->user->firstname ?? 'Guest') 
                    : $booking->user->firstname;
                
                $guideName = $booking->guiding->user->name;
                $location = $booking->guiding->location;
                
                $eventDate = Carbon::parse($booking->blocked_event->from)->format('d.m.Y');
                $eventTime = Carbon::parse($booking->blocked_event->from)->format('H:i');
        
                $tourReminderEmail = view('mails.guest.guest_tour_reminder')
                    ->with([
                        'guestName' => $guestName,
                        'guideName' => $guideName,
                        'location' => $location,
                        'date' => $eventDate,
                        'time' => $eventTime,
                    ])->render();
            } catch (\Exception $e) {
                \Log::error('Error rendering tour reminder email template: ' . $e->getMessage());
            }
            
            try {
                $reviewUrl = route('ratings.show', ['token' => $booking->token]);
                $userName = $user->firstname;
                $guideName = $guide->firstname;
                $location = $guiding->location;
                
                $guestReviewEmail = view('mails.guest.guest_review', compact(
                    'userName', 'guideName', 'location', 'reviewUrl'
                ))->render();
            } catch (\Exception $e) {
                dd($e->getMessage());
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
        $guideReviewConfirmationEmail = null;
        
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
        
        // Render guide reminder email if applicable
        if ($booking->status === 'pending') {
            try {
                $guideReminderEmail = view('mails.guide.guide_reminder', compact(
                    'user', 'guide', 'guiding', 'booking'
                ))->render();
            } catch (\Exception $e) {
                \Log::error('Error rendering guide reminder email template: ' . $e->getMessage());
            }

            try {
                $guideReminder12hrsEmail = view('mails.guide.guide_reminder_12hrs', compact(
                    'user', 'guide', 'guiding', 'booking'
                ))->render();
            } catch (\Exception $e) {
                \Log::error('Error rendering guide 12hrs reminder email template: ' . $e->getMessage());
            }
        }
        
        // Render guide upcoming tour email if applicable
        if ($booking->status === 'accepted') {
            try {
                $guideAcceptedBookingEmail = view('mails.guide.guide_accepted_mail', compact(
                    'user', 'guide', 'guiding', 'booking'
                ))->render();
            } catch (\Exception $e) {
                dd($e->getMessage());
                \Log::error('Error rendering guide accepted booking email template: ' . $e->getMessage());
            }

            try {
                $guideUpcomingTourEmail = view('mails.guide.guide_upcoming_tour')
                ->with([
                    'guide' => $guiding,
                    'booking' => $booking,
                ])->render();
            } catch (\Exception $e) {
                dd($e->getMessage());
                \Log::error('Error rendering guide upcoming tour email template: ' . $e->getMessage());
            }
        }
        
        // Render guide review confirmation email
        try {            
            // Check if review exists, if not create a mock object for preview purposes
            $review = $booking->review;
            if (!$review) {
                $review = new \stdClass();
                $review->grandtotal_score = 8.5;
                $review->guide_score = 9.0;
                $review->region_water_score = 8.0;
                $review->overall_score = 8.5;
                $review->comment = "This is a sample review comment for preview purposes.";
            }
            $guideReviewConfirmationEmail = view('mails.guide.review_confirmation_email')
                ->with([
                    'name' => $guide->firstname,
                    'guiding_name' => $guiding->title,
                    'score' => round($review->grandtotal_score, 1),
                    'comment' => $review->comment,
                    'guide_score' => round($review->guide_score, 1),
                    'region_water_score' => round($review->region_water_score, 1),
                    'overall_score' => round($review->overall_score, 1),
                ])->render();
        } catch (\Exception $e) {
            \Log::error('Error rendering guide review confirmation email template: ' . $e->getMessage());
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
            'guideUpcomingTourEmail' => $guideUpcomingTourEmail,
            'guideReviewConfirmationEmail' => $guideReviewConfirmationEmail
        ]);
    }
}
