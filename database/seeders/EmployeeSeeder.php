<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $employees = [
            [
                'name' => 'Ben',
                'email' => 'ben@zois.codes',
                'password' => \Hash::make('admin')
            ],
            [
                'name' => 'Jonas', 
                'email' => 'jonas@catchaguide.com',
                'password' => \Hash::make('admin')
            ],
            [
                'name' => 'Tim',
                'email' => 'tim@catchaguide.com', 
                'password' => \Hash::make('admin')
            ]
        ];

        foreach ($employees as $employee) {
            Employee::firstOrCreate(
                ['email' => $employee['email']],
                $employee
            );
        }

    }
}
