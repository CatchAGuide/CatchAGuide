<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampVacationBooking;
use App\Models\EmailLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class CampVacationBookingsController extends Controller
{
    public function index()
    {
        $bookingRequests = CampVacationBooking::query()
            ->whereIn('source_type', [CampVacationBooking::SOURCE_CAMP, CampVacationBooking::SOURCE_VACATION])
            ->latest()
            ->get();

        return view('admin.pages.camp-vacation-bookings.index', compact('bookingRequests'));
    }

    public function updateStatus(Request $request, CampVacationBooking $campVacationBooking)
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:open,in_process,done'],
        ]);

        $campVacationBooking->update(['status' => $validated['status']]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => $campVacationBooking->status,
                'status_label' => CampVacationBooking::statusOptions()[$campVacationBooking->status] ?? $campVacationBooking->status,
            ]);
        }

        return redirect()
            ->route('admin.camp-vacation-bookings.index')
            ->with('success', 'Status updated.');
    }

    public function sendReply(Request $request)
    {
        $validated = $request->validate([
            'camp_vacation_booking_id' => ['required', 'integer', 'exists:camp_vacation_bookings,id'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        $booking = CampVacationBooking::findOrFail($validated['camp_vacation_booking_id']);

        if (empty($booking->email)) {
            return redirect()
                ->route('admin.camp-vacation-bookings.index')
                ->with('error', 'This booking request does not have a valid recipient email.');
        }

        try {
            Mail::send('emails.admin.contact-request-reply', ['body' => $validated['body']], function ($message) use ($booking, $validated) {
                $message->to($booking->email, $booking->name ?? null)
                    ->subject($validated['subject']);
            });
        } catch (\Throwable $exception) {
            return redirect()
                ->route('admin.camp-vacation-bookings.index')
                ->with('error', 'Failed to send reply email. Please try again.');
        }

        EmailLog::create([
            'email' => $booking->email,
            'language' => app()->getLocale(),
            'subject' => $validated['subject'],
            'type' => 'camp_vacation_booking_reply',
            'status' => 1,
            'target' => 'camp_vacation_booking_' . $booking->id,
            'additional_info' => json_encode([
                'camp_vacation_booking_id' => $booking->id,
                'source_type' => $booking->source_type,
                'source_id' => $booking->source_id,
                'body_html' => $validated['body'],
            ], JSON_UNESCAPED_UNICODE),
        ]);

        return redirect()
            ->route('admin.camp-vacation-bookings.index')
            ->with('success', 'Reply sent successfully to ' . e($booking->email) . '.');
    }

    public function emailHistory(CampVacationBooking $campVacationBooking)
    {
        $logs = EmailLog::query()
            ->where('type', 'camp_vacation_booking_reply')
            ->where('target', 'camp_vacation_booking_' . $campVacationBooking->id)
            ->orderByDesc('created_at')
            ->get(['id', 'email', 'subject', 'created_at', 'additional_info']);

        $items = $logs->map(function (EmailLog $log) {
            $info = [];
            if (!empty($log->additional_info)) {
                $decoded = json_decode($log->additional_info, true);
                if (is_array($decoded)) {
                    $info = $decoded;
                }
            }
            return [
                'id' => $log->id,
                'email' => $log->email,
                'subject' => $log->subject,
                'created_at' => optional($log->created_at)->format('M j, Y g:i A'),
                'body_html' => $info['body_html'] ?? null,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'items' => $items,
        ]);
    }
}

