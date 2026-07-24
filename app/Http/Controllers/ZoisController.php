<?php

namespace App\Http\Controllers;
use Mail;
use Illuminate\Http\Request;

use App\Domain\Vacation\BookableListingPolicy;
use App\Mail\ContactMail;
use App\Models\Newsletter;
use App\Mail\NewsletterMail;
use App\Mail\CustomerContactMail;
use App\Mail\CustomerNewsletterMail;
use App\Mail\VacationBookingAdminMail;
use App\Mail\VacationBookingCustomerMail;
use App\Models\Camp;
use App\Models\ContactSubmission;
use App\Models\CampVacationBooking;
use App\Models\Trip;
use App\Models\TripBooking;
use App\Presenters\Vacation\TripInquiryPayloadFormatter;
use App\Rules\Recaptcha;

class ZoisController extends Controller
{
    public function sendcontact(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'countryCode' => 'required|string|max:10',
            'phone' => 'required|string|max:20',
            'preferred_date' => ['nullable', 'date'],
            'number_of_persons' => ['nullable', 'integer', 'min:1', 'max:99'],
            'g-recaptcha-response' => Recaptcha::production(),
        ]);

        // Get source information if available
        $sourceType = $request->input('source_type', null);
        $sourceId = $request->input('source_id', null);

        if ($rejection = $this->vacationListingNotBookableResponse($request, $sourceType, $sourceId)) {
            return $rejection;
        }

        $hasBookingDetails = $request->filled('preferred_date')
            && $request->filled('number_of_persons');
        
        // Add source information to the description if available (emails)
        $description = $request->description;
        if (strtolower((string) $sourceType) === TripBooking::SOURCE_TRIP) {
            $description = app(TripInquiryPayloadFormatter::class)->format(
                $request->only(['date_flexible', 'room_configuration', 'dietary_requirements', 'experience_level', 'addons']),
                (string) $request->description
            );
        }
        if ($sourceType && $sourceId) {
            $sourceInfo = "\n\nThis contact was submitted from: {$sourceType} ID: {$sourceId}";
            $description .= $sourceInfo;
        }

        // Save to database (structured booking rows only when date + party size are provided)
        if (in_array(strtolower((string) $sourceType), [CampVacationBooking::SOURCE_CAMP, CampVacationBooking::SOURCE_VACATION], true) && $sourceId && $hasBookingDetails) {
            CampVacationBooking::create([
                'source_type' => strtolower((string) $sourceType),
                'source_id' => (int) $sourceId,
                'preferred_date' => $request->input('preferred_date'),
                'number_of_persons' => (int) $request->input('number_of_persons'),
                'name' => $request->name,
                'email' => $request->email,
                'phone_country_code' => $request->countryCode,
                'phone' => $request->phone,
                'message' => $description,
                'status' => CampVacationBooking::STATUS_OPEN,
            ]);
        } elseif (strtolower((string) $sourceType) === TripBooking::SOURCE_TRIP && $sourceId && $hasBookingDetails) {
            TripBooking::create([
                'source_type' => TripBooking::SOURCE_TRIP,
                'source_id' => (int) $sourceId,
                'preferred_date' => $request->input('preferred_date'),
                'number_of_persons' => (int) $request->input('number_of_persons'),
                'name' => $request->name,
                'email' => $request->email,
                'phone_country_code' => $request->countryCode,
                'phone' => $request->phone,
                'message' => $description,
                'status' => TripBooking::STATUS_OPEN,
            ]);
        } else {
            ContactSubmission::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->countryCode . ' ' . $request->phone,
                'description' => $description,
                'source_type' => $sourceType,
                'source_id' => $sourceId
            ]);
        }

        $sourceTypeLower = strtolower((string) $sourceType);
        $isVacationBooking = $hasBookingDetails && in_array($sourceTypeLower, [
            TripBooking::SOURCE_TRIP,
            CampVacationBooking::SOURCE_CAMP,
            CampVacationBooking::SOURCE_VACATION,
        ], true) && $sourceId;

        $sourceTitle = ContactSubmission::resolveSourceTitle($sourceType, $sourceId);
        $contactMailExtra = array_filter([
            'contact_message' => $description,
            'preferred_date' => $request->filled('preferred_date') ? $request->input('preferred_date') : null,
            'number_of_persons' => $request->filled('number_of_persons') ? (int) $request->input('number_of_persons') : null,
            'source_type' => $sourceType,
            'source_id' => $sourceId ? (int) $sourceId : null,
            'camp_id' => ($sourceTypeLower === ContactSubmission::SOURCE_CAMP && $sourceId) ? (int) $sourceId : null,
            'source_title' => $sourceTitle,
            'view_requests_url' => $isVacationBooking
                ? ($sourceTypeLower === TripBooking::SOURCE_TRIP
                    ? route('admin.trip-bookings.index')
                    : route('admin.camp-vacation-bookings.index'))
                : null,
        ], fn ($value) => $value !== null && $value !== '');

        if ($isVacationBooking) {
            $adminEmail = config('mail.admin_email');
            $guestEmail = $request->email;

            try {
                Mail::to($guestEmail)->send(new VacationBookingCustomerMail(
                    $request->name,
                    $guestEmail,
                    $description,
                    $request->phone,
                    $request->countryCode,
                    $contactMailExtra
                ));
            } catch (\Throwable $e) {
                \Log::error('Vacation booking customer mail failed', [
                    'email' => $guestEmail,
                    'error' => $e->getMessage(),
                ]);
            }

            try {
                Mail::to($adminEmail)->send(new VacationBookingAdminMail(
                    $request->name,
                    $guestEmail,
                    $description,
                    $request->phone,
                    $request->countryCode,
                    $contactMailExtra
                ));
            } catch (\Throwable $e) {
                \Log::error('Vacation booking admin mail failed', [
                    'email' => $adminEmail,
                    'error' => $e->getMessage(),
                ]);
            }

            $successMessage = __('contact.bookingSuccessMessage');
        } else {
            Mail::send(new ContactMail(
                $request->name,
                $request->email,
                $description,
                $request->phone,
                $request->countryCode,
                $contactMailExtra
            ));
            Mail::send(new CustomerContactMail(
                $request->name,
                $request->email,
                $description,
                $request->phone,
                $request->countryCode,
                $contactMailExtra
            ));
            $successMessage = __('contact.successMessage');
        }

        // If it's an AJAX request or from a modal, return JSON
        if ($request->ajax() || $request->has('source_type')) {
            return response()->json([
                'success' => true,
                'message' => $successMessage,
            ]);
        }

        return back()->with('message', $successMessage);
    }

    /**
     * Block trip/camp contact & booking submissions when the listing is not active (e.g. draft).
     */
    private function vacationListingNotBookableResponse(Request $request, mixed $sourceType, mixed $sourceId): mixed
    {
        if (!$sourceId) {
            return null;
        }

        $type = strtolower((string) $sourceType);
        $listing = match ($type) {
            TripBooking::SOURCE_TRIP => Trip::query()->find((int) $sourceId),
            CampVacationBooking::SOURCE_CAMP => Camp::query()->find((int) $sourceId),
            default => null,
        };

        if ($listing === null) {
            return null;
        }

        if (app(BookableListingPolicy::class)->isBookable($listing)) {
            return null;
        }

        $message = __('vacations.draft_booking_rejected');

        if ($request->ajax() || $request->has('source_type')) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        return back()->withErrors(['listing' => $message]);
    }

    public function sendnewsletter(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'g-recaptcha-response' => Recaptcha::production(),
        ]);

        $locale = app()->getLocale();

        $check = Newsletter::where('email',$request->email)->first();
        if ($check) {
            return back()->with('message', 'Du bist bereits in unserem Newsletterverteiler aufgenommen. Du kannst diesen jederzeit wieder abbestellen!');
        }
        
        $newsletter = new Newsletter();
        $newsletter->email = $request->email;
        $newsletter->language = $locale;

        $newsletter->save();

        Mail::send(new NewsletterMail(
            $request->email,
            $locale,
            route('admin.newsletter-subscribers.index')
        ));
        Mail::send(new CustomerNewsletterMail($request->email, $locale));
        return back()->with('message', 'Vielen Dank wir haben Dich in unseren Newsletterverteiler aufgenommen. Du kannst diesen jederzeit wieder abbestellen!');
    }
}
