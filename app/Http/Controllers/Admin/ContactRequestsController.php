<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactRequestsController extends Controller
{
    public function index()
    {
        $contactRequests = ContactSubmission::query()
            ->latest()
            ->get();

        return view('admin.pages.contact-requests.index', compact('contactRequests'));
    }

    public function sendReply(Request $request)
    {
        $validated = $request->validate([
            'contact_submission_id' => ['required', 'integer', 'exists:contact_submissions,id'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        $contactSubmission = ContactSubmission::findOrFail($validated['contact_submission_id']);

        if (empty($contactSubmission->email)) {
            return redirect()
                ->route('admin.contact-requests.index')
                ->with('error', 'This contact request does not have a valid recipient email.');
        }

        try {
            Mail::send('emails.admin.contact-request-reply', ['body' => $validated['body']], function ($message) use ($contactSubmission, $validated) {
                $message->to($contactSubmission->email, $contactSubmission->name ?? null)
                    ->subject($validated['subject']);
            });
        } catch (\Throwable $exception) {
            return redirect()
                ->route('admin.contact-requests.index')
                ->with('error', 'Failed to send reply email. Please try again.');
        }

        return redirect()
            ->route('admin.contact-requests.index')
            ->with('success', 'Reply sent successfully to ' . e($contactSubmission->email) . '.');
    }
}
