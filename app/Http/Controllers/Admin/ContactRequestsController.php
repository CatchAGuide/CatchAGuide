<?php

namespace App\Http\Controllers\Admin;

use App\Models\ContactSubmission;
use App\Http\Controllers\Controller;

class ContactRequestsController extends Controller
{
    public function index()
    {
        $contactRequests = ContactSubmission::all();
        return view('admin.pages.contact-requests.index', compact('contactRequests'));
    }
}
