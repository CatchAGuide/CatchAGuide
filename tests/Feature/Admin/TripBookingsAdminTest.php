<?php

namespace Tests\Feature\Admin;

use App\Models\BookingStatusChange;
use App\Models\Employee;
use App\Models\TripBooking;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class TripBookingsAdminTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://cag.local']);
        URL::forceRootUrl('http://cag.local');
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

    private function createBooking(string $status = TripBooking::STATUS_OPEN): TripBooking
    {
        return TripBooking::query()->create([
            'source_type' => TripBooking::SOURCE_TRIP,
            'source_id' => 1,
            'preferred_date' => now()->addMonth()->toDateString(),
            'number_of_persons' => 2,
            'name' => 'Trip Status Dropdown Guest',
            'email' => 'trip-status-dropdown-guest@example.com',
            'phone_country_code' => '+49',
            'phone' => '1700000001',
            'message' => 'Please keep my preferred date.',
            'status' => $status,
        ]);
    }

    public function test_guest_is_redirected_from_trip_booking_admin(): void
    {
        $response = $this->get(route('admin.trip-bookings.index'));

        $response->assertRedirect();
    }

    public function test_status_dropdown_is_not_clipped_by_table_cell_overflow(): void
    {
        $this->actingAsEmployee();
        $booking = $this->createBooking();

        $response = $this->get(route('admin.trip-bookings.index'));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('js-status-dropdown-btn', $html);
        $this->assertStringContainsString('data-bs-boundary="viewport"', $html);
        $this->assertStringContainsString("strategy: 'fixed'", $html);
        $this->assertStringContainsString('td.col-status', $html);
        $this->assertMatchesRegularExpression(
            '/td\.col-status[\s\S]*?overflow:\s*visible/',
            $html
        );
        $this->assertStringContainsString((string) $booking->id, $html);
        $this->assertStringContainsString(__('message.contact_request_status.open'), $html);
        $this->assertStringContainsString(__('message.contact_request_status.in_process'), $html);
        $this->assertStringContainsString(__('message.contact_request_status.done'), $html);
    }

    public function test_admin_can_update_trip_booking_status(): void
    {
        $this->actingAsEmployee();
        $booking = $this->createBooking();

        $response = $this->postJson(
            route('admin.trip-bookings.update-status', $booking),
            ['status' => TripBooking::STATUS_IN_PROCESS]
        );

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'status' => TripBooking::STATUS_IN_PROCESS,
        ]);

        $this->assertSame(
            TripBooking::STATUS_IN_PROCESS,
            $booking->fresh()->status
        );
    }

    public function test_status_change_is_logged_with_the_employee(): void
    {
        $employee = $this->actingAsEmployee();
        $booking = $this->createBooking();

        $response = $this->patchJson(
            route('admin.trip-bookings.update-status', $booking),
            ['status' => TripBooking::STATUS_DONE]
        );

        $response->assertOk();
        $this->assertStringContainsString($employee->name, (string) $response->json('status_summary'));

        $change = BookingStatusChange::query()
            ->where('bookable_type', $booking->getMorphClass())
            ->where('bookable_id', $booking->id)
            ->sole();
        $this->assertSame(TripBooking::STATUS_OPEN, $change->from_status);
        $this->assertSame(TripBooking::STATUS_DONE, $change->to_status);
        $this->assertSame($employee->id, $change->employee_id);
    }

    public function test_setting_the_same_status_logs_nothing(): void
    {
        $this->actingAsEmployee();
        $booking = $this->createBooking();

        $this->patchJson(route('admin.trip-bookings.update-status', $booking), ['status' => TripBooking::STATUS_OPEN])->assertOk();

        $this->assertSame(0, $booking->statusChanges()->count());
    }

    public function test_status_history_lists_changes_newest_first_then_the_request(): void
    {
        $employee = $this->actingAsEmployee();
        $booking = $this->createBooking();

        $this->patchJson(route('admin.trip-bookings.update-status', $booking), ['status' => TripBooking::STATUS_IN_PROCESS]);
        $this->patchJson(route('admin.trip-bookings.update-status', $booking), ['status' => TripBooking::STATUS_DONE]);

        $items = $this->getJson(route('admin.trip-bookings.status-history', $booking))
            ->assertOk()
            ->json('items');

        $this->assertCount(3, $items);
        $this->assertSame([TripBooking::STATUS_DONE, TripBooking::STATUS_IN_PROCESS, 'created'], array_column($items, 'to'));
        $this->assertSame($employee->name, $items[0]['by']);
        $this->assertSame(__('admin.booking_status_history.received'), $items[2]['to_label']);
    }

    public function test_list_shows_who_last_changed_the_status(): void
    {
        $employee = $this->actingAsEmployee();
        $booking = $this->createBooking();
        $this->patchJson(route('admin.trip-bookings.update-status', $booking), ['status' => TripBooking::STATUS_IN_PROCESS]);

        $this->get(route('admin.trip-bookings.index'))
            ->assertOk()
            ->assertSee(route('admin.trip-bookings.status-history', $booking), false)
            ->assertSee(__('admin.booking_status_history.last_changed', ['name' => $employee->name, 'date' => '']), false)
            ->assertSee('id="statusHistoryModal"', false);
    }
}
