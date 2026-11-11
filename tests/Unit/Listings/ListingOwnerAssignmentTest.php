<?php

namespace Tests\Unit\Listings;

use App\Models\Employee;
use App\Models\RentalBoat;
use App\Models\User;
use App\Services\Camp\CampDataProcessor;
use App\Services\Listings\ListingOwnerAssignment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ListingOwnerAssignmentTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_id_is_stored_as_staff_and_not_as_a_guide(): void
    {
        $employee = Employee::factory()->create();
        Auth::guard('employees')->login($employee);

        $assigned = (new ListingOwnerAssignment())->forRequest(Request::create('/admin/rental-boats', 'POST', [
            'user_id' => $employee->id,
        ]));

        $this->assertNull($assigned['user_id']);
        $this->assertSame($employee->id, $assigned['employee_id']);
    }

    public function test_a_previously_stored_employee_id_is_not_kept_as_the_guide(): void
    {
        $employee = Employee::factory()->create();
        Auth::guard('employees')->login($employee);

        $existing = new RentalBoat([
            'user_id' => $employee->id,
        ]);

        $assigned = (new ListingOwnerAssignment())->forRequest(Request::create('/admin/rental-boats', 'POST', [
            'user_id' => $employee->id,
        ]), $existing);

        $this->assertNull($assigned['user_id']);
        $this->assertSame($employee->id, $assigned['employee_id']);
    }

    public function test_an_explicit_provider_is_assigned_when_staff_picks_them(): void
    {
        $employee = Employee::factory()->create();
        $provider = User::factory()->create(['is_provider' => true]);
        Auth::guard('employees')->login($employee);

        $existing = new RentalBoat([
            'user_id' => null,
            'employee_id' => $employee->id,
        ]);

        $assigned = (new ListingOwnerAssignment())->forRequest(Request::create('/admin/rental-boats', 'POST', [
            'user_id' => $provider->id,
        ]), $existing);

        $this->assertSame($provider->id, $assigned['user_id']);
        $this->assertSame($employee->id, $assigned['employee_id']);
    }

    public function test_clearing_the_owner_picker_removes_the_assigned_user(): void
    {
        $employee = Employee::factory()->create();
        $provider = User::factory()->create(['is_provider' => true]);
        Auth::guard('employees')->login($employee);

        $existing = new RentalBoat([
            'user_id' => $provider->id,
            'employee_id' => $employee->id,
        ]);

        $assigned = (new ListingOwnerAssignment())->forRequest(Request::create('/admin/rental-boats', 'POST', [
            'user_id' => '',
        ]), $existing);

        $this->assertNull($assigned['user_id']);
        $this->assertSame($employee->id, $assigned['employee_id']);
    }

    public function test_posting_an_employee_id_as_user_id_does_not_attach_them_as_owner(): void
    {
        $employee = Employee::factory()->create();
        $provider = User::factory()->create(['is_provider' => true]);
        Auth::guard('employees')->login($employee);

        $existing = new RentalBoat([
            'user_id' => $provider->id,
            'employee_id' => $employee->id,
        ]);

        $assigned = (new ListingOwnerAssignment())->forRequest(Request::create('/admin/rental-boats', 'POST', [
            'user_id' => $employee->id,
        ]), $existing);

        $this->assertNull($assigned['user_id']);
        $this->assertSame($employee->id, $assigned['employee_id']);
    }

    public function test_camp_processor_does_not_copy_the_employee_id_onto_the_guide(): void
    {
        $employee = Employee::factory()->create();
        Auth::guard('employees')->login($employee);

        $data = (new CampDataProcessor())->processRequestData(Request::create('/admin/camps', 'POST', [
            'title' => 'Staff camp',
            'user_id' => $employee->id,
        ]));

        $this->assertNull($data['user_id']);
        $this->assertSame($employee->id, $data['employee_id']);
    }
}
