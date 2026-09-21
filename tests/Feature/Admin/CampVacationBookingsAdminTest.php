<?php

namespace Tests\Feature\Admin;

use App\Models\CampVacationBooking;
use App\Models\Employee;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class CampVacationBookingsAdminTest extends TestCase
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

    private function createBooking(string $status = CampVacationBooking::STATUS_OPEN): CampVacationBooking
    {
        return CampVacationBooking::query()->create([
            'source_type' => CampVacationBooking::SOURCE_CAMP,
            'source_id' => 1,
            'preferred_date' => now()->addMonth()->toDateString(),
            'number_of_persons' => 2,
            'name' => 'Status Dropdown Guest',
            'email' => 'status-dropdown-guest@example.com',
            'phone_country_code' => '+49',
            'phone' => '1700000000',
            'message' => 'Please keep my preferred date.',
            'status' => $status,
        ]);
    }

    public function test_guest_is_redirected_from_camp_booking_admin(): void
    {
        $response = $this->get(route('admin.camp-vacation-bookings.index'));

        $response->assertRedirect();
    }

    public function test_status_dropdown_is_not_clipped_by_table_cell_overflow(): void
    {
        $this->actingAsEmployee();
        $booking = $this->createBooking();

        $response = $this->get(route('admin.camp-vacation-bookings.index'));

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

    public function test_admin_can_update_camp_booking_status(): void
    {
        $this->actingAsEmployee();
        $booking = $this->createBooking();

        $response = $this->patchJson(
            route('admin.camp-vacation-bookings.update-status', $booking),
            ['status' => CampVacationBooking::STATUS_IN_PROCESS]
        );

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'status' => CampVacationBooking::STATUS_IN_PROCESS,
        ]);

        $this->assertSame(
            CampVacationBooking::STATUS_IN_PROCESS,
            $booking->fresh()->status
        );
    }
}
