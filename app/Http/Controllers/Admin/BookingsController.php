<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\EmailLog;
use App\Models\BlockedEvent;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\Guest\GuestBookingRequestMail;
use App\Mail\Guide\GuideBookingRequestMail;
use App\Mail\Guide\GuideInvoiceMail;
use App\Events\BookingStatusChanged;

class BookingsController extends Controller
{
    public function index()
    {
        $booking = Booking::with('employee', 'guiding.user', 'blocked_event', 'calendar_schedule')->orderBy('created_at', 'DESC')->get();
        
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
        // Only return the necessary fields for editing
        return response()->json([
            'id' => $booking->id,
            'email' => $booking->email,
            'phone' => $booking->phone,
            'status' => $booking->status,
            'allowed_status_edit' => in_array($booking->status, ['cancelled', 'rejected']),
        ]);
    }

    public function update(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:pending,accepted,rejected,cancelled',
        ]);

        $updated = false;
        $statusChanged = false;
        
        if (isset($data['email'])) {
            $booking->email = $data['email'];
            $updated = true;
        }
        if (isset($data['phone'])) {
            $booking->phone = $data['phone'];
            $updated = true;
        }
        // Only allow status change if initial status is cancelled or rejected
        if (isset($data['status']) && in_array($booking->status, ['cancelled', 'rejected'])) {
            $booking->status = $data['status'];
            $statusChanged = true;
            $updated = true;
        }
        
        if ($updated) {
            $booking->save();
            
            // If status changed to 'accepted', trigger the full acceptance flow
            if ($statusChanged && $booking->status === 'accepted') {
                // Update blocked_event type to 'booking' (same as email acceptance flow)
                $blockedEvent = BlockedEvent::find($booking->blocked_event_id);
                if ($blockedEvent) {
                    $blockedEvent->type = 'booking';
                    $blockedEvent->save();
                }
                
                // Fire the BookingStatusChanged event to trigger email notifications
                event(new BookingStatusChanged($booking, 'accepted'));
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Booking updated successfully.',
            'booking' => [
                'id' => $booking->id,
                'email' => $booking->email,
                'phone' => $booking->phone,
                'status' => $booking->status,
            ]
        ]);
    }

    public function destroy(Booking $booking)
    {
        $booking->delete();
        return redirect()->back();
    }

    public function sendBookingRequestEmails(Booking $booking)
    {
        try {
            // Validate booking has required relationships
            if (!$booking->guiding) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking does not have associated guiding information.'
                ], 400);
            }
            
            if (!$booking->guiding->user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking guiding does not have associated guide user.'
                ], 400);
            }
            
            $user = $booking->user ?? (object)['firstname' => $booking->firstname, 'lastname' => $booking->lastname];
            $guide = $booking->guiding->user;
            $guiding = $booking->guiding;
            
            $guestEmail = $booking->email ?: ($user->email ?? null);
            $guideEmail = $guide->email;
            
            // Validate we have email addresses
            if (!$guestEmail) {
                return response()->json([
                    'success' => false,
                    'message' => 'No guest email address found for this booking.'
                ], 400);
            }
            
            if (!$guideEmail) {
                return response()->json([
                    'success' => false,
                    'message' => 'No guide email address found for this booking.'
                ], 400);
            }
            
            $emailsSent = [];
            $emailsSkipped = [];
            
            // Check and send guest booking request email
            $guestEmailLog = CheckEmailLog('guest_booking_request', 'booking_' . $booking->id, $guestEmail);
            if (!$guestEmailLog) {
                Mail::to($guestEmail)->send(new GuestBookingRequestMail($booking, $user, $guiding, $guide));
                $emailsSent[] = 'Guest email sent to ' . $guestEmail;
            } else {
                $emailsSkipped[] = 'Guest email already sent to ' . $guestEmail;
            }
            
            // Check and send guide booking request email
            $guideEmailLog = CheckEmailLog('guide_booking_request', 'guide_' . $guide->id . '_booking_' . $booking->id, $guideEmail);
            if (!$guideEmailLog) {
                Mail::to($guideEmail)->locale($guide->language ?? app()->getLocale())->send(new GuideBookingRequestMail($booking, $user, $guiding, $guide));
                $emailsSent[] = 'Guide email sent to ' . $guideEmail;
            } else {
                $emailsSkipped[] = 'Guide email already sent to ' . $guideEmail;
            }
            
            $message = '';
            if (!empty($emailsSent)) {
                $message .= 'Emails sent successfully: ' . implode(', ', $emailsSent) . '. ';
            }
            if (!empty($emailsSkipped)) {
                $message .= 'Emails skipped (already sent): ' . implode(', ', $emailsSkipped) . '.';
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'emails_sent' => count($emailsSent),
                'emails_skipped' => count($emailsSkipped)
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error sending booking request emails: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error sending emails: ' . $e->getMessage()
            ], 500);
        }
    }

    public function sendGuideInvoice(Booking $booking)
    {
        try {
            if ($booking->status !== 'accepted') {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice can only be sent for accepted bookings.'
                ], 422);
            }

            if (!$booking->isBookingOver()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice can only be sent after the booked guiding date has passed.'
                ], 422);
            }

            if (!$booking->guiding || !$booking->guiding->user) {
                return response()->json([
                    'success' => false,
                    'message' => 'No guide is associated with this booking.'
                ], 400);
            }

            $guide = $booking->guiding->user;
            $guideEmail = $guide->email;

            if (!$guideEmail) {
                return response()->json([
                    'success' => false,
                    'message' => 'No guide email address found for this booking.'
                ], 400);
            }

            $emailLogExists = CheckEmailLog(
                'guide_booking_invoice',
                'booking_' . $booking->id,
                $guideEmail
            );

            if ($emailLogExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice email was already sent in the last 24 hours for this booking.'
                ], 409);
            }

            Mail::to($guideEmail)
                ->locale($guide->language ?? app()->getLocale())
                ->send(new GuideInvoiceMail($booking));

            $booking->guide_invoice_sent_at = now();
            $booking->save();

            return response()->json([
                'success' => true,
                'message' => 'Invoice email sent successfully to ' . $guideEmail . '.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error sending guide invoice email: ' . $e->getMessage(), [
                'booking_id' => $booking->id ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error sending invoice email: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateGuideBillingStatus(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'is_guide_billed' => 'required|boolean',
        ]);

        if ($booking->status !== 'accepted' || !$booking->isBookingOver()) {
            return response()->json([
                'success' => false,
                'message' => 'Billing status can only be updated for completed accepted bookings.'
            ], 422);
        }

        $booking->is_guide_billed = (bool) $data['is_guide_billed'];
        $booking->guide_billed_at = $booking->is_guide_billed ? now() : null;
        $booking->save();

        return response()->json([
            'success' => true,
            'message' => $booking->is_guide_billed
                ? 'Booking marked as billed.'
                : 'Booking marked as not billed.',
            'is_guide_billed' => (bool) $booking->is_guide_billed,
        ]);
    }

    public function guideInvoicePreview(Booking $booking)
    {
        try {
            if (!$booking->guiding || !$booking->guiding->user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Guide information is not available for this booking.'
                ], 422);
            }

            $user = $booking->user ?? (object) [
                'firstname' => $booking->firstname,
                'lastname' => $booking->lastname
            ];
            $guide = $booking->guiding->user;
            $guiding = $booking->guiding;

            $guideInvoiceEmail = view('mails.guide.guide_invoice', compact(
                'booking',
                'user',
                'guide',
                'guiding'
            ))->render();

            return response()->json([
                'success' => true,
                'html' => $guideInvoiceEmail
            ]);
        } catch (\Exception $e) {
            \Log::error('Error rendering guide invoice preview: ' . $e->getMessage(), [
                'booking_id' => $booking->id ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to render invoice preview.'
            ], 500);
        }
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
                
                $guideName = $booking->guiding->user->firstname;
                $location = $booking->guiding->location;
                
                $eventDate = Carbon::parse($booking->blocked_event->from)->format('F j, Y');
        
                $tourReminderEmail = view('mails.guest.guest_tour_reminder')
                    ->with([
                        'guestName' => $guestName,
                        'guideName' => $guideName,
                        'location' => $location,
                        'date' => $eventDate
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
        
        // Render guide 24h reminder email (always available for preview)
        try {
            $guideReminderEmail = view('mails.guide.guide_reminder', compact(
                'user', 'guide', 'guiding', 'booking'
            ))->render();
        } catch (\Exception $e) {
            \Log::error('Error rendering guide reminder email template: ' . $e->getMessage());
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
                \Log::error('Error rendering guide upcoming tour email template: ' . $e->getMessage());
            }
        }

        // Render guide 12hrs reminder email (always available for preview)
        try {
            $guideReminder12hrsEmail = view('mails.guide.guide_reminder_12hrs', compact(
                'user', 'guide', 'guiding', 'booking'
            ))->render();
        } catch (\Exception $e) {
            \Log::error('Error rendering guide 12hrs reminder email template: ' . $e->getMessage());
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
