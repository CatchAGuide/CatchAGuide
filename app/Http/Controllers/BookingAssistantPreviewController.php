<?php

namespace App\Http\Controllers;

use App\Support\BookingAssistantVisibility;

class BookingAssistantPreviewController extends Controller
{
    public function __invoke(string $token)
    {
        if (! BookingAssistantVisibility::isEnabled() || ! BookingAssistantVisibility::matchesPreviewToken($token)) {
            abort(404);
        }

        BookingAssistantVisibility::grantPreviewSession();

        return view('pages.booking-assistant.preview');
    }
}
