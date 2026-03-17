<?php

namespace App\Http\Controllers;
use Mail;
use Illuminate\Http\Request;

use App\Mail\ContactMail;
use App\Models\Newsletter;
use App\Mail\NewsletterMail;
use App\Mail\CustomerContactMail;
use App\Models\ContactSubmission;
use App\Models\CampVacationBooking;
use App\Mail\CustomerNewsletterMail;

class ZoisController extends Controller
{
    public function sendcontact(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'countryCode' => 'required|string|max:10',
            'phone' => 'required|string|max:20',
            'preferred_date' => 'required|date',
            'number_of_persons' => 'required|integer|min:1|max:99',
            'g-recaptcha-response' => app()->environment('production') ? 'recaptcha' : '',
        ]);

        // Get source information if available
        $sourceType = $request->input('source_type', null);
        $sourceId = $request->input('source_id', null);
        
        // Add source information to the description if available (emails)
        $description = $request->description;
        if ($sourceType && $sourceId) {
            $sourceInfo = "\n\nThis contact was submitted from: {$sourceType} ID: {$sourceId}";
            $description .= $sourceInfo;
        }

        // Save to database
        if (in_array(strtolower((string) $sourceType), [CampVacationBooking::SOURCE_CAMP, CampVacationBooking::SOURCE_VACATION], true) && $sourceId) {
            CampVacationBooking::create([
                'source_type' => strtolower((string) $sourceType),
                'source_id' => (int) $sourceId,
                'preferred_date' => $request->input('preferred_date'),
                'number_of_persons' => (int) $request->input('number_of_persons'),
                'name' => $request->name,
                'email' => $request->email,
                'phone_country_code' => $request->countryCode,
                'phone' => $request->phone,
                'message' => $request->description,
                'status' => CampVacationBooking::STATUS_OPEN,
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

        Mail::send(new ContactMail($request->name, $request->email, $description, $request->phone, $request->countryCode));
        Mail::send(new CustomerContactMail($request->name, $request->email, $description));
        
        // If it's an AJAX request or from a modal, return JSON
        if ($request->ajax() || $request->has('source_type')) {
            return response()->json([
                'success' => true,
                'message' => 'Deine Kontaktanfrage wurde erfolgreich versand! Wir melden uns schnellstmöglich bei Dir'
            ]);
        }
        
        return back()->with('message', 'Deine Kontaktanfrage wurde erfolgreich versand! Wir melden uns schnellstmöglich bei Dir');
    }

    public function sendnewsletter(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'g-recaptcha-response' => app()->environment('production') ? 'recaptcha' : '',
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

        Mail::send(new NewsletterMail($request->email,$locale));
        Mail::send(new CustomerNewsletterMail($request->email,$locale));
        return back()->with('message', 'Vielen Dank wir haben Dich in unseren Newsletterverteiler aufgenommen. Du kannst diesen jederzeit wieder abbestellen!');
    }
}
