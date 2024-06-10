<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEmployeeRequest;
use App\Http\Requests\Admin\UpdateEmployeeRequest;
use App\Models\Employee;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class EmployeesController extends Controller
{
    /**
     * @return Factory|View|Application
     */
    public function index(): Factory|View|Application
    {
        return view('admin.pages.employees.index', [
            'employees' => Employee::all()
        ]);
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

    public function destroy(Employee $employee)
    {
        //
    }
}
