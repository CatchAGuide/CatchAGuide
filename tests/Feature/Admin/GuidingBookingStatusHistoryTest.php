<?php

namespace Tests\Feature\Admin;

use App\Models\BlockedEvent;
use App\Models\Booking;
use App\Models\BookingStatusChange;
use App\Models\CalendarSchedule;
use App\Models\Employee;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\User;
use App\Services\Booking\BookingStatusHistory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Status history on Admin â€º Bookings â€º Guidings, the same log camp/trip and contact requests have.
 * Logged on every status change, whoever made it (employee, guide, guest or the expiry command).
 */
class GuidingBookingStatusHistoryTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://cag.local']);
        URL::forceRootUrl('http://cag.local');
        Mail::fake();
        Queue::fake();
    }

    private function actingAsEmployee(): Employee
    {
        $employee = Employee::query()->first();
        if (! $employee) {
            $this->markTestSkipped('No employee available for admin auth.');
        }

        $this->actingAs($employee, 'employees');

        return $employee;
    }

    private function createBooking(string $status, array $attributes = []): Booking
    {
        $guide = User::factory()->create(['language' => 'en']);

        $guiding = new Guiding();
        $guiding->forceFill([
            'title' => 'History Tour '.uniqid(),
            'slug' => 'history-tour-'.uniqid(),
            'location' => 'Somewhere',
            'status' => 1,
            'max_guests' => 4,
            'duration' => 4,
            'fishing_type_id' => FishingType::query()->value('id'),
            'user_id' => $guide->id,
        ])->save();

        $blockedEvent = new BlockedEvent();
        $blockedEvent->forceFill([
            'from' => now()->addDays(10),
            'due' => now()->addDays(10)->addHours(4),
            'type' => 'booking',
            'user_id' => $guide->id,
        ])->save();
        // See BookingAdminStatusTransitionTest: blocked_event_id also keys into calendar_schedule.
        CalendarSchedule::where('id', $blockedEvent->id)->delete();

        $booking = new Booking();
        $booking->forceFill(array_merge([
            'guiding_id' => $guiding->id,
            'blocked_event_id' => $blockedEvent->id,
            'status' => $status,
            'token' => 'history-token-'.uniqid(),
            'is_guest' => true,
            'email' => 'guest@example.com',
            'count_of_users' => 1,
            'price' => 100,
        ], $attributes))->save();

        return $booking;
    }

    public function test_admin_status_change_is_logged_with_the_employee(): void
    {
        $employee = $this->actingAsEmployee();
        $booking = $this->createBooking('pending');

        $this->postJson(route('admin.bookings.save', $booking), ['status' => 'rejected'])->assertOk();

        $change = $booking->statusChanges()->first();
        $this->assertNotNull($change);
        $this->assertSame('pending', $change->from_status);
        $this->assertSame('rejected', $change->to_status);
        $this->assertSame($employee->id, $change->employee_id);

        $items = $this->getJson(route('admin.bookings.status-history', $booking))
            ->assertOk()
            ->json('items');

        $this->assertCount(2, $items);
        $this->assertSame('rejected', $items[0]['to']);
        $this->assertSame($employee->name, $items[0]['by']);
        $this->assertSame(BookingStatusChange::REASON_CREATED, $items[1]['to']);
    }

    public function test_saving_without_a_status_change_logs_nothing(): void
    {
        $this->actingAsEmployee();
        $booking = $this->createBooking('pending');

        $this->postJson(route('admin.bookings.save', $booking), ['admin_comment' => 'Called the guest'])->assertOk();

        $this->assertSame(0, $booking->statusChanges()->count());
    }

    public function test_guide_accepting_through_the_email_link_is_logged_as_the_guide(): void
    {
        $booking = $this->createBooking('pending');

        $this->get(route('booking.accept', $booking->token))->assertOk();

        $change = $booking->statusChanges()->first();
        $this->assertNotNull($change);
        $this->assertSame('accepted', $change->to_status);
        $this->assertNull($change->employee_id);
        $this->assertSame(BookingStatusChange::REASON_GUIDE, $change->reason);
        $this->assertSame(__('admin.booking_status_history.guide'), $change->actorName());
    }

    public function test_expired_request_is_logged_as_system_with_the_expired_reason(): void
    {
        $booking = $this->createBooking('pending', ['expires_at' => now()->subHour()]);

        $booking->status = 'cancelled';
        $booking->save();

        $change = $booking->statusChanges()->first();
        $this->assertSame(BookingStatusChange::REASON_EXPIRED, $change->reason);
        $this->assertSame(__('admin.booking_status_history.system'), $change->actorName());

        $items = app(BookingStatusHistory::class)->timeline($booking);
        $this->assertSame(__('admin.booking_status_history.reason_expired'), $items[0]['reason']);
    }

    public function test_bookings_list_links_the_status_history(): void
    {
        $this->actingAsEmployee();
        $booking = $this->createBooking('pending');

        $this->get(route('admin.bookings.index'))
            ->assertOk()
            ->assertSee(route('admin.bookings.status-history', $booking), false)
            ->assertSee('statusHistoryModal', false);
    }
}
