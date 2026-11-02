<?php

namespace Tests\Feature\Admin;

use App\Models\BookingStatusChange;
use App\Models\ContactSubmission;
use App\Models\Employee;
use App\Models\ProductReport;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Status history (who changed the status, when) on Admin › Contact requests and Product reports,
 * the same log camp and trip requests have.
 */
class RequestStatusHistoryAdminTest extends TestCase
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

    private function createContactRequest(): ContactSubmission
    {
        return ContactSubmission::query()->create([
            'name' => 'History Guest',
            'email' => 'history-guest@example.com',
            'description' => 'Question about a tour.',
            'status' => ContactSubmission::STATUS_OPEN,
        ]);
    }

    private function createProductReport(): ProductReport
    {
        return ProductReport::query()->create([
            'name' => 'History Reporter',
            'email' => 'history-reporter@example.com',
            'reason' => ProductReport::REASON_OTHER,
            'description' => 'Listing looks wrong.',
            'reported_url' => 'https://catchaguide.com/guidings/offer/example',
            'status' => ProductReport::STATUS_OPEN,
        ]);
    }

    public function test_contact_request_status_change_is_logged_with_the_employee(): void
    {
        $employee = $this->actingAsEmployee();
        $request = $this->createContactRequest();

        $response = $this->patchJson(route('admin.contact-requests.update-status', $request), ['status' => ContactSubmission::STATUS_DONE])
            ->assertOk()
            ->assertJson(['success' => true, 'status' => ContactSubmission::STATUS_DONE]);

        $this->assertStringContainsString($employee->name, (string) $response->json('status_summary'));
        $this->assertSame(ContactSubmission::STATUS_DONE, $request->fresh()->status);

        $change = BookingStatusChange::query()
            ->where('bookable_type', $request->getMorphClass())
            ->where('bookable_id', $request->id)
            ->sole();
        $this->assertSame(ContactSubmission::STATUS_OPEN, $change->from_status);
        $this->assertSame(ContactSubmission::STATUS_DONE, $change->to_status);
        $this->assertSame($employee->id, $change->employee_id);
    }

    public function test_contact_request_history_lists_changes_then_the_request(): void
    {
        $employee = $this->actingAsEmployee();
        $request = $this->createContactRequest();

        $this->patchJson(route('admin.contact-requests.update-status', $request), ['status' => ContactSubmission::STATUS_IN_PROCESS]);
        // Same status again: nothing new is logged.
        $this->patchJson(route('admin.contact-requests.update-status', $request), ['status' => ContactSubmission::STATUS_IN_PROCESS]);

        $items = $this->getJson(route('admin.contact-requests.status-history', $request))->assertOk()->json('items');

        $this->assertSame([ContactSubmission::STATUS_IN_PROCESS, 'created'], array_column($items, 'to'));
        $this->assertSame($employee->name, $items[0]['by']);
    }

    public function test_contact_request_list_shows_history_link_and_modal(): void
    {
        $employee = $this->actingAsEmployee();
        $request = $this->createContactRequest();
        $this->patchJson(route('admin.contact-requests.update-status', $request), ['status' => ContactSubmission::STATUS_IN_PROCESS]);

        $this->get(route('admin.contact-requests.index'))
            ->assertOk()
            ->assertSee(route('admin.contact-requests.status-history', $request), false)
            ->assertSee(__('admin.booking_status_history.last_changed', ['name' => $employee->name, 'date' => '']), false)
            ->assertSee('id="statusHistoryModal"', false);
    }

    public function test_product_report_status_change_is_logged_and_listed(): void
    {
        $employee = $this->actingAsEmployee();
        $report = $this->createProductReport();

        $this->patchJson(route('admin.product-reports.update-status', $report), ['status' => ProductReport::STATUS_IN_PROCESS])
            ->assertOk()
            ->assertJsonPath('status', ProductReport::STATUS_IN_PROCESS);

        $items = $this->getJson(route('admin.product-reports.status-history', $report))->assertOk()->json('items');
        $this->assertSame([ProductReport::STATUS_IN_PROCESS, 'created'], array_column($items, 'to'));
        $this->assertSame($employee->name, $items[0]['by']);

        $this->get(route('admin.product-reports.index'))
            ->assertOk()
            ->assertSee(route('admin.product-reports.status-history', $report), false)
            ->assertSee('id="statusHistoryModal"', false);
    }

    public function test_guest_cannot_read_status_history(): void
    {
        $request = $this->createContactRequest();

        $this->get(route('admin.contact-requests.status-history', $request))->assertRedirect();
    }
}
