<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBlockedEventRequest;
use App\Http\Resources\EventResource;
use App\Models\BlockedEvent;
use App\Models\CalendarSchedule;
use App\Models\Guiding;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EventsController extends Controller
{
    public function index(Request $request)
    {
        $query = CalendarSchedule::where('user_id', auth()->id())
            ->with(['booking.user', 'guiding', 'vacation']);
        
        // Filter by guiding if provided
        if ($request->has('guiding_id') && $request->guiding_id) {
            $query->where('guiding_id', $request->guiding_id);
        }
        
        // Filter by type if provided
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }
        
        // Filter by status if provided
        if ($request->has('status') && $request->status) {
            $query->whereHas('booking', function($q) use ($request) {
                $q->where('status', $request->status);
            });
        }
        
        // Filter by date range if provided
        if ($request->has('start') && $request->has('end')) {
            $query->whereBetween('date', [$request->start, $request->end]);
        }
        
        $allSchedules = $query->get();
        $events = EventResource::collection($allSchedules)->all();
        
        // Filter out null events (blocked entries that shouldn't be shown)
        return array_values(array_filter($events, function($event) {
            return $event !== null;
        }));
    }

    public function store(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
            'type' => 'string|in:custom_schedule,vacation_schedule',
            'note' => 'string|max:255',
            'guiding_id' => 'nullable|exists:guidings,id',
            'day' => 'array'
        ]);

        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end);
        $dayOfWeek = $request->get('day', []);
        $type = $request->get('type', 'custom_schedule');
        $note = $request->get('note', 'Custom blocked date');

        // If specific days are selected, create entries for those days only
        if (!empty($dayOfWeek)) {
            foreach ($dayOfWeek as $day) {
                $currentDate = $start->copy();
                
                while ($currentDate->lte($end)) {
                    if ($currentDate->dayOfWeek == $day) {
                        CalendarSchedule::create([
                            'user_id' => auth()->id(),
                            'type' => $type,
                            'date' => $currentDate->format('Y-m-d'),
                            'note' => $note,
                            'guiding_id' => $request->guiding_id,
                        ]);
                    }
                    $currentDate->addDay();
                }
            }
        } else {
            // Create entries for all days in the range
            $currentDate = $start->copy();
            while ($currentDate->lte($end)) {
                CalendarSchedule::create([
                    'user_id' => auth()->id(),
                    'type' => $type,
                    'date' => $currentDate->format('Y-m-d'),
                    'note' => $note,
                    'guiding_id' => $request->guiding_id,
                ]);
                $currentDate->addDay();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Event created successfully!',
        ]);
    }

    /**
     * Create a custom schedule entry
     */
    public function storeCustomSchedule(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'note' => 'required|string|max:255',
            'guiding_id' => 'nullable|exists:guidings,id',
            'type' => 'required|string|in:custom_schedule,vacation_schedule'
        ]);

        $schedule = CalendarSchedule::create([
            'user_id' => auth()->id(),
            'type' => $request->type,
            'date' => $request->date,
            'note' => $request->note,
            'guiding_id' => $request->guiding_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Custom schedule created successfully!',
            'data' => new EventResource($schedule)
        ]);
    }

    public function delete($id)
    {
        $schedule = CalendarSchedule::where('user_id', auth()->id())->where('id', $id)->first();

        if (!$schedule) {
            return response()->json(['error' => 'Schedule not found'], 404);
        }

        // Prevent deletion of booking-related schedules
        if ($schedule->type === 'tour_request' && $schedule->booking_id) {
            return response()->json(['error' => 'Cannot delete booking schedules'], 403);
        }

        $schedule->delete();
        
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Schedule deleted successfully!'
            ]);
        }
        
        return back()->with('success', 'Die Blockade wurde erfolgreich gelöscht');
    }

    /**
     * Get guidings for filter dropdown
     */
    public function getUserGuidings()
    {
        $guidings = Guiding::where('user_id', auth()->id())
            ->select('id', 'title', 'location')
            ->get();

        return response()->json($guidings);
    }

    /**
     * Legacy method - kept for backward compatibility
     */
    public function blockedEvents($events)
    {
        return $events->map(function($event) {
            $event->status = $event->getBookingStatus();
            return $event;
        });
    }
}
