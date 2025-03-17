<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserRole;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tenant = 2;
        $user = 1;
        $role = 1;   
        
         // Create a user role assignment
         UserRole::create([
            'tenant_id' => $tenant,
            'user_id' => $user,
            'role_id' => $role,
        ]);

    }
}
