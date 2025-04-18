<?php

namespace App\Http\Controllers;
use Mail;
use Illuminate\Http\Request;

use App\Mail\ContactMail;
use App\Models\Newsletter;
use App\Mail\NewsletterMail;
use App\Mail\CustomerContactMail;
use App\Models\ContactSubmission;
use App\Mail\CustomerNewsletterMail;

class ZoisController extends Controller
{
    public function sendcontact(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'phone' => 'nullable|string|max:20',
            'g-recaptcha-response' => 'recaptcha',
        ]);

        // Get source information if available
        $sourceType = $request->input('source_type', null);
        $sourceId = $request->input('source_id', null);
        
        // Add source information to the description if available
        $description = $request->description;
        if ($sourceType && $sourceId) {
            $sourceInfo = "\n\nThis contact was submitted from: {$sourceType} ID: {$sourceId}";
            $description .= $sourceInfo;
        }

        // Save to database
        ContactSubmission::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'description' => $request->description,
            'source_type' => $sourceType,
            'source_id' => $sourceId
        ]);

        Mail::send(new ContactMail($request->name, $request->email, $description, $request->phone));
        Mail::send(new CustomerContactMail($request->name, $request->email, $request->description));
        
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
            'g-recaptcha-response' => 'recaptcha',
        ]);

        $newsletter = new Newsletter();
        $newsletter->email = $request->email;
        $newsletter->save();

        Mail::send(new NewsletterMail($request->email));
        Mail::send(new CustomerNewsletterMail($request->email));
        return back()->with('message', 'Vielen Dank wir haben Dich in unseren Newsletterverteiler aufgenommen. Du kannst diesen jederzeit wieder abbestellen!');
    }
}
