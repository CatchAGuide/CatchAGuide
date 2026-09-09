<?php

namespace Tests\Feature\Mail;

use App\Mail\Guest\GuestTourReminderMail;
use App\Models\Booking;
use App\Models\CalendarSchedule;
use App\Models\Guiding;
use App\Models\Media;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Bookings write their calendar slot via EventService::createBlockedEvent(), which
 * returns a CalendarSchedule row and stores its id in bookings.blocked_event_id — but
 * Booking::blocked_event() belongs-to's the legacy BlockedEvent model/table instead.
 * Since the two tables' ids are unrelated auto-increments, blocked_event_id almost never
 * matches a real blocked_events row for current bookings, so `$booking->blocked_event`
 * is null and dereferencing ->from crashed this mailer (matches production's
 * "Attempt to read property 'from' on null" log entries). Booking::getBookingDate()
 * already handles this correctly (calendar_schedule -> blocked_event -> book_date), so
 * the mailer should use it instead of reading blocked_event directly.
 */
class GuestTourReminderMailTest extends TestCase
{
    use DatabaseTransactions;

    public function test_renders_using_calendar_schedule_date_without_a_user(): void
    {
        $guide = User::factory()->create();
        $media = Media::factory()->create();
        $guiding = Guiding::create([
            'title' => 'Test Guiding Tour',
            'location' => 'Test Bay',
            'slug' => 'test-guiding-tour-reminder',
            'max_guests' => 4,
            'duration' => 3,
            'fishing_type_id' => 1,
            'thumbnail_id' => $media->id,
            'user_id' => $guide->id,
        ]);

        $bookDate = now()->addDays(2)->toDateString();

        $booking = Booking::create([
            'guiding_id' => $guiding->id,
            'user_id' => null,
            'is_guest' => true,
            'email' => 'legacy-guest@example.com',
            'count_of_users' => 2,
            'book_date' => $bookDate,
            'status' => 'accepted',
            'token' => 'test-reminder-token',
        ]);

        // Simulate the real calendar write: blocked_event_id points at calendar_schedule,
        // not at a blocked_events row.
        $schedule = CalendarSchedule::create([
            'type' => 'tour_request',
            'date' => $bookDate,
            'note' => 'Booking request',
            'guiding_id' => $guiding->id,
            'user_id' => $guide->id,
            'booking_id' => $booking->id,
        ]);
        $booking->blocked_event_id = $schedule->id;
        $booking->save();

        $html = (new GuestTourReminderMail($booking))->render();

        $this->assertStringContainsString(__('emails.guest_name'), $html);
        $this->assertStringContainsString(Carbon::parse($bookDate)->format('F j, Y'), $html);
    }
}
