<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactformRequest;
use App\Models\ContactRequest;

class ContactFormController extends Controller
{
    public function index()
    {
        return view('backend.pages.contactrequests', [
            'contact_requests' => ContactRequest::all()
        ]);
    }

    public function store(ContactformRequest $request)
    {
        $data = $request->validated();
        ContactRequest::create($data);

        return back();
    }

    public function destroy($id)
    {
        $contact_request = ContactRequest::findOrFail($id);
        $contact_request->delete();
        return back();

    }

}
