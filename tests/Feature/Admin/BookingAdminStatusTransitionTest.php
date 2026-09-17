<?php

namespace Tests\Feature\Admin;

use App\Mail\Ceo\BookingCancelMailToCEO;
use App\Mail\Guest\BookingCancelledMail;
use App\Mail\Guest\BookingRejectMail;
use App\Mail\Guide\GuideBookingCancelledMail;
use App\Models\BlockedEvent;
use App\Models\Booking;
use App\Models\CalendarSchedule;
use App\Models\Employee;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Admin can move an already-accepted booking to rejected/cancelled from the bookings
 * admin panel. Rejecting/cancelling an accepted booking must notify the guest/guide
 * (unlike the silent status-correction path for pending/rejected/cancelled bookings)
 * and must not allow an accepted booking to be silently reset back to pending.
 */
class BookingAdminStatusTransitionTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://cag.local']);
        URL::forceRootUrl('http://cag.local');
    }

    private function actingAsEmployee(): void
    {
        $employee = Employee::query()->first();
        if (!$employee) {
            $this->markTestSkipped('No employee available for admin auth.');
        }

        $this->actingAs($employee, 'employees');
    }

    private function createBooking(string $status, bool $tourDateInPast = false): Booking
    {
        $guide = User::factory()->create(['language' => 'en']);

        $guiding = new Guiding();
        $guiding->forceFill([
            'title' => 'Test Tour '.uniqid(),
            'slug' => 'test-tour-'.uniqid(),
            'location' => 'Somewhere',
            'status' => 1,
            'max_guests' => 4,
            'duration' => 4,
            'fishing_type_id' => FishingType::query()->value('id'),
            'user_id' => $guide->id,
        ])->save();

        $blockedEvent = new BlockedEvent();
        $blockedEvent->forceFill([
            'from' => $tourDateInPast ? now()->subDays(5) : now(),
            'due' => $tourDateInPast ? now()->subDays(5)->addHours(4) : now()->addHours(4),
            'type' => 'booking',
            'user_id' => $guide->id,
        ])->save();

        // Booking::calendar_schedule() and blocked_event() both key off the same
        // blocked_event_id column into two different tables. Against the real
        // (non-isolated) dev DB, this id can coincidentally collide with an
        // unrelated pre-existing calendar_schedule row, which would silently take
        // priority in isBookingOver() and make this test order-dependent.
        CalendarSchedule::where('id', $blockedEvent->id)->delete();

        $booking = new Booking();
        $booking->forceFill([
            'guiding_id' => $guiding->id,
            'blocked_event_id' => $blockedEvent->id,
            'status' => $status,
            'token' => 'test-token-'.uniqid(),
            'is_guest' => true,
            'email' => 'guest@example.com',
            'count_of_users' => 1,
            'price' => 100,
        ])->save();

        return $booking;
    }

    public function test_admin_can_reject_an_accepted_booking_and_guest_is_notified(): void
    {
        $this->actingAsEmployee();
        Mail::fake();

        $booking = $this->createBooking('accepted');

        $response = $this->postJson(route('admin.bookings.save', $booking), [
            'status' => 'rejected',
        ]);

        $response->assertOk();
        $this->assertSame('rejected', $booking->fresh()->status);
        Mail::assertSent(BookingRejectMail::class);
    }

    public function test_admin_can_cancel_an_accepted_booking_and_notifies_guest_and_guide(): void
    {
        $this->actingAsEmployee();
        Mail::fake();

        $booking = $this->createBooking('accepted');

        $response = $this->postJson(route('admin.bookings.save', $booking), [
            'status' => 'cancelled',
        ]);

        $response->assertOk();
        $this->assertSame('cancelled', $booking->fresh()->status);
        Mail::assertSent(BookingCancelledMail::class);
        Mail::assertSent(GuideBookingCancelledMail::class);
        Mail::assertSent(BookingCancelMailToCEO::class);
    }

    public function test_accepted_booking_cannot_be_reset_to_pending_via_admin_update(): void
    {
        $this->actingAsEmployee();

        $booking = $this->createBooking('accepted');

        $response = $this->postJson(route('admin.bookings.save', $booking), [
            'status' => 'pending',
        ]);

        $response->assertOk();
        $this->assertSame('accepted', $booking->fresh()->status, 'status must not be reset from accepted to pending');
    }

    public function test_rejecting_a_pending_booking_via_admin_update_stays_silent(): void
    {
        $this->actingAsEmployee();
        Mail::fake();

        $booking = $this->createBooking('pending');

        $response = $this->postJson(route('admin.bookings.save', $booking), [
            'status' => 'rejected',
        ]);

        $response->assertOk();
        $this->assertSame('rejected', $booking->fresh()->status);
        Mail::assertNothingSent();
    }

    public function test_an_accepted_booking_can_still_be_cancelled_once_the_tour_date_has_passed(): void
    {
        $this->actingAsEmployee();
        Mail::fake();

        $booking = $this->createBooking('accepted', tourDateInPast: true);

        $response = $this->postJson(route('admin.bookings.save', $booking), [
            'status' => 'cancelled',
        ]);

        $response->assertOk();
        $this->assertSame('cancelled', $booking->fresh()->status, 'a past tour must still be cancellable');
        Mail::assertSent(BookingCancelledMail::class);
    }

    public function test_an_accepted_booking_cannot_be_rejected_once_the_tour_date_has_passed(): void
    {
        $this->actingAsEmployee();
        Mail::fake();

        $booking = $this->createBooking('accepted', tourDateInPast: true);

        $response = $this->postJson(route('admin.bookings.save', $booking), [
            'status' => 'rejected',
        ]);

        $response->assertOk();
        $this->assertSame('accepted', $booking->fresh()->status, 'a past tour must not be rejectable');
        Mail::assertNothingSent();
    }

    public function test_edit_endpoint_only_exposes_cancel_for_a_past_accepted_booking(): void
    {
        $this->actingAsEmployee();

        $booking = $this->createBooking('accepted', tourDateInPast: true);

        $response = $this->getJson(route('admin.bookings.edit', $booking));

        $response->assertOk();
        $response->assertJson([
            'allowed_status_edit' => true,
            'allowed_status_options' => ['accepted', 'cancelled'],
        ]);
    }

    public function test_edit_endpoint_exposes_reject_and_cancel_for_an_upcoming_accepted_booking(): void
    {
        $this->actingAsEmployee();

        $booking = $this->createBooking('accepted');

        $response = $this->getJson(route('admin.bookings.edit', $booking));

        $response->assertOk();
        $response->assertJson([
            'allowed_status_edit' => true,
            'allowed_status_options' => ['accepted', 'rejected', 'cancelled'],
        ]);
    }
}
