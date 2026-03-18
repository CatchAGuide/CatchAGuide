<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreManualTripBookingRequest;
use App\Models\EmailLog;
use App\Models\Trip;
use App\Models\TripBooking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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

    public function storeManual(StoreManualTripBookingRequest $request)
    {
        $data = $request->validated();

        $trip = Trip::query()->findOrFail((int) $data['trip_id']);

        // Prevent accidental double-submit duplicates (client retries/double-click)
        $recentDuplicate = TripBooking::query()
            ->where('source_type', TripBooking::SOURCE_TRIP)
            ->where('source_id', $trip->id)
            ->where('preferred_date', $data['preferred_date'])
            ->where('number_of_persons', (int) $data['number_of_persons'])
            ->where('name', $data['name'])
            ->where('email', $data['email'] ?? null)
            ->where('phone_country_code', $data['phone_country_code'] ?? null)
            ->where('phone', $data['phone'] ?? null)
            ->where('message', $data['message'] ?? null)
            ->where('created_at', '>=', Carbon::now()->subSeconds(10))
            ->exists();

        if (!$recentDuplicate) {
            TripBooking::create([
                'source_type' => TripBooking::SOURCE_TRIP,
                'source_id' => $trip->id,
                'preferred_date' => $data['preferred_date'],
                'number_of_persons' => (int) $data['number_of_persons'],
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'phone_country_code' => $data['phone_country_code'] ?? null,
                'phone' => $data['phone'] ?? null,
                'message' => $data['message'] ?? null,
                'status' => $data['status'] ?? TripBooking::STATUS_OPEN,
            ]);
        }

        return redirect()
            ->route('admin.trip-bookings.index')
            ->with('success', 'Trip booking request created successfully.');
    }

    public function searchTrips(Request $request)
    {
        $perPage = (int) $request->input('per_page', 20);
        $perPage = max(5, min(50, $perPage));

        $page = max((int) $request->input('page', 1), 1);
        $term = trim((string) $request->input('q', ''));

        $query = Trip::query()
            ->select(['id', 'title', 'location', 'thumbnail_path', 'city', 'region', 'country'])
            ->where('status', 'active')
            ->orderBy('title');

        if ($term !== '') {
            $termLower = Str::lower($term);

            if (ctype_digit($term)) {
                $query->where('id', (int) $term);
            } else {
                $query->where(function ($q) use ($termLower) {
                    $q->whereRaw('LOWER(title) LIKE ?', ["%{$termLower}%"])
                        ->orWhereRaw('LOWER(location) LIKE ?', ["%{$termLower}%"])
                        ->orWhereRaw('LOWER(city) LIKE ?', ["%{$termLower}%"])
                        ->orWhereRaw('LOWER(region) LIKE ?', ["%{$termLower}%"])
                        ->orWhereRaw('LOWER(country) LIKE ?', ["%{$termLower}%"])
                        ->orWhereRaw('CAST(id AS CHAR) LIKE ?', ["%{$termLower}%"]);
                });
            }
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $items = $paginator->getCollection()->map(function (Trip $trip) {
            $thumbPath = trim((string) ($trip->thumbnail_path ?? ''));
            $thumbnailUrl = null;
            if ($thumbPath !== '') {
                if (str_starts_with($thumbPath, 'http') || str_starts_with($thumbPath, '//')) {
                    $thumbnailUrl = $thumbPath;
                } else {
                    $thumbnailUrl = asset(ltrim($thumbPath, '/'));
                }
            }

            $location = $trip->location ?: null;
            if (!$location) {
                $parts = array_filter([$trip->city ?? null, $trip->region ?? null, $trip->country ?? null]);
                $location = $parts ? implode(', ', $parts) : null;
            }

            return [
                'id' => $trip->id,
                'title' => (string) $trip->title,
                'location' => $location,
                'thumbnail_url' => $thumbnailUrl ?: asset('images/placeholder_guide.jpg'),
            ];
        })->values();

        return response()->json([
            'data' => $items,
            'current_page' => $paginator->currentPage(),
            'next_page' => $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null,
            'total' => $paginator->total(),
        ]);
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

