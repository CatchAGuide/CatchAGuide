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

    public function test_an_explicit_guide_is_kept_when_staff_edits_the_listing(): void
    {
        $employee = Employee::factory()->create();
        $guide = User::factory()->create();
        Auth::guard('employees')->login($employee);

        $existing = new RentalBoat([
            'user_id' => $guide->id,
            'employee_id' => $employee->id,
        ]);

        $assigned = (new ListingOwnerAssignment())->forRequest(Request::create('/admin/rental-boats', 'POST', [
            'user_id' => $employee->id,
        ]), $existing);

        $this->assertSame($guide->id, $assigned['user_id']);
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
