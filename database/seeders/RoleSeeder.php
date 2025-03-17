<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            'Admin',
            'User',
            'Auditor',
            'User_Manager',
            'Leave_Manager',
            'Payroll_Manager',
            'Requisition_Manager',
            'Performance_Manager',
            'Loan_Manager',
            'Vendor_Manager',
            'Procurement_Manager',
            'Asset_Manager',
            'Job_Manager',
        ];

        // Insert the modules into the database
        foreach ($roles as $role) {
            Role::create(['name' => $role]);
        }
    }
}
