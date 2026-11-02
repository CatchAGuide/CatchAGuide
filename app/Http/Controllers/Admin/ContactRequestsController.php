<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DoneReason;
use App\Http\Controllers\Controller;
use App\Models\ContactSubmission;
use App\Services\Booking\BookingStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;

class ContactRequestsController extends Controller
{
    public function index(BookingStatusHistory $history)
    {
        $contactRequests = ContactSubmission::query()
            ->with('latestStatusChange.employee')
            ->latest()
            ->get();

        $statusSummaries = $contactRequests->mapWithKeys(fn (ContactSubmission $item) => [$item->id => $history->summary($item)]);

        return view('admin.pages.contact-requests.index', compact('contactRequests', 'statusSummaries'));
    }

    public function showComment(ContactSubmission $contactSubmission)
    {
        return response()->json([
            'id' => $contactSubmission->id,
            'admin_comment' => $contactSubmission->admin_comment,
        ]);
    }

    public function updateComment(Request $request, ContactSubmission $contactSubmission)
    {
        $validated = $request->validate([
            'admin_comment' => ['nullable', 'string'],
        ]);

        $contactSubmission->update([
            'admin_comment' => $validated['admin_comment'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comment saved.',
        ]);
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

    public function updateStatus(Request $request, ContactSubmission $contactSubmission, BookingStatusHistory $history)
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:open,in_process,done'],
            'done_reason' => ['nullable', Rule::enum(DoneReason::class)],
        ]);

        $history->change(
            $contactSubmission,
            $validated['status'],
            $request->user('employees'),
            doneReason: DoneReason::tryFrom((string) ($validated['done_reason'] ?? '')),
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => $contactSubmission->status,
                'status_label' => $contactSubmission->statusLabel(),
                'done_reason' => $contactSubmission->doneReason()?->value,
                'status_summary' => $history->summary($contactSubmission),
            ]);
        }

        return redirect()
            ->route('admin.contact-requests.index')
            ->with('success', 'Status updated.');
    }

    public function statusHistory(ContactSubmission $contactSubmission, BookingStatusHistory $history)
    {
        return response()->json([
            'success' => true,
            'items' => $history->timeline($contactSubmission),
        ]);
    }
}
