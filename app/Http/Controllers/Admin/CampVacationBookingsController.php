<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreManualCampVacationBookingRequest;
use App\Models\Camp;
use App\Models\CampVacationBooking;
use App\Models\EmailLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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

    public function storeManual(StoreManualCampVacationBookingRequest $request)
    {
        $data = $request->validated();

        $sourceType = strtolower((string) $data['source_type']);
        $sourceId = (int) $data['source_id'];

        // Camp-only manual creation (vacations excluded)
        Camp::query()->where('status', 'active')->findOrFail($sourceId);

        // Prevent accidental double-submit duplicates (client retries/double-click)
        $recentDuplicate = CampVacationBooking::query()
            ->where('source_type', CampVacationBooking::SOURCE_CAMP)
            ->where('source_id', $sourceId)
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
            CampVacationBooking::create([
                'source_type' => CampVacationBooking::SOURCE_CAMP,
                'source_id' => $sourceId,
                'preferred_date' => $data['preferred_date'],
                'number_of_persons' => (int) $data['number_of_persons'],
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'phone_country_code' => $data['phone_country_code'] ?? null,
                'phone' => $data['phone'] ?? null,
                'message' => $data['message'] ?? null,
                'status' => $data['status'] ?? CampVacationBooking::STATUS_OPEN,
            ]);
        }

        return redirect()
            ->route('admin.camp-vacation-bookings.index')
            ->with('success', 'Camp/Vacation booking request created successfully.');
    }

    public function searchSources(Request $request)
    {
        $perPage = (int) $request->input('per_page', 20);
        $perPage = max(5, min(50, $perPage));

        $page = max((int) $request->input('page', 1), 1);
        $term = trim((string) $request->input('q', ''));

        $campQuery = Camp::query()
            ->select(['id', 'title', 'location', 'thumbnail_path', 'city', 'region', 'country'])
            ->selectRaw("'camp' as type")
            ->where('status', 'active');

        if ($term !== '') {
            $termLower = Str::lower($term);

            $applyTerm = function ($q) use ($termLower, $term) {
                if (ctype_digit($term)) {
                    $q->where('id', (int) $term);
                } else {
                    $q->where(function ($sq) use ($termLower) {
                        $sq->whereRaw('LOWER(title) LIKE ?', ["%{$termLower}%"])
                            ->orWhereRaw('LOWER(location) LIKE ?', ["%{$termLower}%"])
                            ->orWhereRaw('LOWER(city) LIKE ?', ["%{$termLower}%"])
                            ->orWhereRaw('LOWER(region) LIKE ?', ["%{$termLower}%"])
                            ->orWhereRaw('LOWER(country) LIKE ?', ["%{$termLower}%"])
                            ->orWhereRaw('CAST(id AS CHAR) LIKE ?', ["%{$termLower}%"]);
                    });
                }
            };

            $applyTerm($campQuery);
        }

        $paginator = $campQuery->orderBy('title')->paginate($perPage, ['*'], 'page', $page);

        $items = $paginator->getCollection()->map(function (Camp $camp) {
            $thumbPath = trim((string) ($camp->thumbnail_path ?? ''));
            $thumb = null;
            if ($thumbPath !== '') {
                $thumb = str_starts_with($thumbPath, 'http') || str_starts_with($thumbPath, '//')
                    ? $thumbPath
                    : asset(ltrim($thumbPath, '/'));
            }

            $location = $camp->location ?? null;
            if (!$location) {
                $parts = array_filter([$camp->city ?? null, $camp->region ?? null, $camp->country ?? null]);
                $location = $parts ? implode(', ', $parts) : null;
            }

            return [
                'type' => 'camp',
                'id' => $camp->id,
                'title' => (string) ($camp->title ?? ('#' . $camp->id)),
                'location' => $location ? (string) $location : null,
                'thumbnail_url' => $thumb ?: asset('images/placeholder_guide.jpg'),
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

