<?php

namespace App\Http\Controllers;

use App\Mail\ContactMail;
use App\Mail\CustomerContactMail;
use App\Mail\CustomerNewsletterMail;
use App\Mail\NewsletterMail;
use App\Models\Newsletter;
use Illuminate\Http\Request;
use Mail;

class ZoisController extends Controller
{
    public function sendcontact(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'g-recaptcha-response' => 'recaptcha',
        ]);

        Mail::send(new ContactMail($request->name, $request->email, $request->description));
        Mail::send(new CustomerContactMail($request->name, $request->email, $request->description));
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
