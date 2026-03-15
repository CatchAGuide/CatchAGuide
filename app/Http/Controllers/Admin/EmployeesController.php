<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEmployeeRequest;
use App\Http\Requests\Admin\UpdateEmployeeRequest;
use App\Models\Employee;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeesController extends Controller
{
    /**
     * @return Factory|View|Application
     */
    public function index(): Factory|View|Application
    {
        return view('admin.pages.employees.index', [
            'employees' => Employee::with(['deletedByUser', 'passwordResetByUser'])->get(),
        ]);
    }

    /**
     * List soft-deleted employees for audit.
     *
     * @return Factory|View|Application
     */
    public function trashed(): Factory|View|Application
    {
        return view('admin.pages.employees.trashed', [
            'employees' => Employee::onlyTrashed()->with('deletedByUser')->orderByDesc('deleted_at')->get(),
        ]);
    }

    /**
     * Restore a soft-deleted employee.
     */
    public function restore(int $id): RedirectResponse
    {
        $employee = Employee::onlyTrashed()->findOrFail($id);
        $employee->deleted_by = null;
        $employee->restore();
        return redirect()->route('admin.employees.index')->with('employees_success', __('admin.employees.restored'));
    }

    /**
     * @return Factory|View|Application
     */
    public function create( Employee $employee): Factory|View|Application
    {
        return view('admin.pages.employees.create', [
            'employee' => $employee,
        ]);
    }

    public function store(StoreEmployeeRequest $request)
    {
        $data = $request->validated();

        $data['password'] = \Hash::make('password');
        Employee::create($data);

        return redirect()->route('admin.employees.index');
    }

    public function show(Employee $employee)
    {
        //
    }

    /**
     * @param Employee $employee
     * @return Factory|View|Application
     */
    public function edit(Employee $employee): Factory|View|Application
    {
        return view('admin.pages.employees.edit', [
            'employee' => $employee
        ]);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $data = $request->validated();

        $employee->update($data);

        return redirect()->route('admin.employees.index');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $currentUser = auth('employees')->user();
        if ($employee->id === $currentUser->id) {
            return redirect()->route('admin.employees.index')->with('employees_error', __('admin.employees.cannot_delete_self'));
        }
        $employee->deleted_by = $currentUser->id;
        $employee->save();
        $employee->delete();
        return redirect()->route('admin.employees.index')->with('employees_success', __('admin.employees.deleted'));
    }

    /**
     * Reset an employee's password to a temporary one and record who did it.
     */
    public function resetPassword(Employee $employee): RedirectResponse
    {
        $temporaryPassword = \Str::random(12);
        $employee->password = Hash::make($temporaryPassword);
        $employee->password_reset_at = now();
        $employee->password_reset_by = auth('employees')->id();
        $employee->save();
        return redirect()->route('admin.employees.index')
            ->with('employees_success', __('admin.employees.password_reset_success'))
            ->with('temporary_password', $temporaryPassword);
    }
}
