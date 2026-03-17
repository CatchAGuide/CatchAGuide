<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\TripBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TripBookingsController extends Controller
{
    public function index()
    {
        $bookingRequests = TripBooking::query()
            ->where('source_type', TripBooking::SOURCE_TRIP)
            ->latest()
            ->get();

        return view('admin.pages.trip-bookings.index', compact('bookingRequests'));
    }

    public function updateStatus(Request $request, TripBooking $tripBooking)
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:open,in_process,done'],
        ]);

        $tripBooking->update(['status' => $validated['status']]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => $tripBooking->status,
                'status_label' => TripBooking::statusOptions()[$tripBooking->status] ?? $tripBooking->status,
            ]);
        }

        return redirect()
            ->route('admin.trip-bookings.index')
            ->with('success', 'Status updated.');
    }

    public function sendReply(Request $request)
    {
        $validated = $request->validate([
            'trip_booking_id' => ['required', 'integer', 'exists:trip_bookings,id'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        $booking = TripBooking::findOrFail($validated['trip_booking_id']);

        if (empty($booking->email)) {
            return redirect()
                ->route('admin.trip-bookings.index')
                ->with('error', 'This booking request does not have a valid recipient email.');
        }

        try {
            Mail::send('emails.admin.contact-request-reply', ['body' => $validated['body']], function ($message) use ($booking, $validated) {
                $message->to($booking->email, $booking->name ?? null)
                    ->subject($validated['subject']);
            });
        } catch (\Throwable $exception) {
            return redirect()
                ->route('admin.trip-bookings.index')
                ->with('error', 'Failed to send reply email. Please try again.');
        }

        EmailLog::create([
            'email' => $booking->email,
            'language' => app()->getLocale(),
            'subject' => $validated['subject'],
            'type' => 'trip_booking_reply',
            'status' => 1,
            'target' => 'trip_booking_' . $booking->id,
            'additional_info' => json_encode([
                'trip_booking_id' => $booking->id,
                'source_type' => $booking->source_type,
                'source_id' => $booking->source_id,
                'body_html' => $validated['body'],
            ], JSON_UNESCAPED_UNICODE),
        ]);

        return redirect()
            ->route('admin.trip-bookings.index')
            ->with('success', 'Reply sent successfully to ' . e($booking->email) . '.');
    }

    public function emailHistory(TripBooking $tripBooking)
    {
        $logs = EmailLog::query()
            ->where('type', 'trip_booking_reply')
            ->where('target', 'trip_booking_' . $tripBooking->id)
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

