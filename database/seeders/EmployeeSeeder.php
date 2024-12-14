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

        Employee::create([
            'name' => 'Ben',
            'email' => 'ben@zois.codes',
            'password' => \Hash::make('admin')
        ]);

        Employee::create([
            'name' => 'Jonas',
            'email' => 'jonas@catchaguide.com',
            'password' => \Hash::make('admin')
        ]);

        Employee::create([
            'name' => 'Tim',
            'email' => 'tim@catchaguide.com',
            'password' => \Hash::make('admin')
        ]);

    }
}
