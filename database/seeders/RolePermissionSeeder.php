<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Dashboard
            'view-dashboard',
            
            // User Management
            'view-users', 'create-users', 'edit-users', 'delete-users',
            
            // Student Management
            'view-students', 'create-students', 'edit-students', 'delete-students',
            
            // Class Management
            'view-classes', 'create-classes', 'edit-classes', 'delete-classes',
            
            // Attendance Management
            'view-attendance', 'create-attendance', 'edit-attendance', 'delete-attendance',
            'view-own-attendance', 'create-own-attendance',
            'view-student-attendance', 'create-student-attendance',
            
            // Reports
            'view-reports', 'export-reports',
            
            // Leave Management
            'view-leaves', 'create-leaves', 'edit-leaves', 'approve-leaves',
            'view-own-leaves', 'create-own-leaves',
            
            // Settings
            'view-settings', 'edit-settings',
            
            // Bulk Operations
            'bulk-import-users', 'bulk-import-students',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles and assign permissions
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->givePermissionTo(Permission::all());

        $headmasterRole = Role::create(['name' => 'headmaster', 'guard_name' => 'web']);
        $headmasterRole->givePermissionTo([
            'view-dashboard',
            'view-attendance', 'view-student-attendance',
            'view-reports', 'export-reports',
            'view-leaves', 'approve-leaves',
        ]);

        $teacherRole = Role::create(['name' => 'teacher', 'guard_name' => 'web']);
        $teacherRole->givePermissionTo([
            'view-dashboard',
            'view-own-attendance', 'create-own-attendance',
            'view-student-attendance', 'create-student-attendance',
            'view-own-leaves', 'create-own-leaves',
        ]);

        $tuRole = Role::create(['name' => 'tu', 'guard_name' => 'web']);
        $tuRole->givePermissionTo([
            'view-dashboard',
            'view-users', 'create-users', 'edit-users',
            'view-students', 'create-students', 'edit-students',
            'view-classes', 'create-classes', 'edit-classes',
            'view-attendance', 'create-attendance', 'edit-attendance',
            'view-reports', 'export-reports',
            'view-leaves', 'approve-leaves',
            'bulk-import-users', 'bulk-import-students',
        ]);

        $bkRole = Role::create(['name' => 'bk', 'guard_name' => 'web']);
        $bkRole->givePermissionTo([
            'view-dashboard',
            'view-students', 'edit-students',
            'view-student-attendance', 'create-student-attendance',
            'view-reports',
        ]);

        $kesiswaanRole = Role::create(['name' => 'kesiswaan', 'guard_name' => 'web']);
        $kesiswaanRole->givePermissionTo([
            'view-dashboard',
            'view-students', 'edit-students',
            'view-student-attendance', 'create-student-attendance',
            'view-reports',
        ]);

        $studentRole = Role::create(['name' => 'student', 'guard_name' => 'web']);
        $studentRole->givePermissionTo([
            'view-own-attendance',
            'view-own-leaves', 'create-own-leaves',
        ]);
    }
}
