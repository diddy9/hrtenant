<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $modules = [
            'HR Management',
            'Leave Management',
            'Payroll Management',
            'Requisition Management',
            'Performance Management',
            'Loan Management',
            'Vendor Management',
            'Profile Management',
            'Timesheet Management',
            'Procurement Management',
            'Assets Register',
            'Job Management',
        ];

        // Insert the modules into the database
        foreach ($modules as $module) {
            Module::create(['name' => $module]);
        }
    }
}
