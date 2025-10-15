<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\School;

class SuperAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        // Get the first school (SMA Negeri 1 Jakarta)
        $school = School::first();
        
        if ($school) {
            // Create Super Admin user
            $superAdmin = User::create([
                'school_id' => $school->id,
                'name' => 'Super Admin',
                'email' => 'superadmin@presensia.com',
                'password' => Hash::make('password'),
                'phone' => '081234567890',
                'address' => 'Jakarta',
                'user_type' => 'employee',
                'is_active' => true,
            ]);
            
            // Assign admin role
            $superAdmin->assignRole('admin');
            
            $this->command->info('Super Admin user created successfully!');
            $this->command->info('Email: superadmin@presensia.com');
            $this->command->info('Password: password');
        } else {
            $this->command->error('No school found. Please run SchoolSeeder first.');
        }
    }
}