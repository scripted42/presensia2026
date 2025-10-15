<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\SuperAdmin;
use App\Models\School;
use App\Models\TenantSetting;
use App\Models\AttendanceSetting;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        // Create Super Admin
        $superAdmin = SuperAdmin::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@presensia.com',
            'password' => Hash::make('password'),
            'phone' => '081234567890',
            'company_name' => 'Presensia Solutions',
            'is_active' => true,
        ]);

        // Create demo schools with different branding
        $schools = [
            [
                'name' => 'SMP Negeri 14 Surabaya',
                'address' => 'Jl. Raya Darmo No. 14, Surabaya',
                'phone' => '031-1234567',
                'email' => 'info@smpn14sby.sch.id',
                'website' => 'https://smpn14sby.sch.id',
                'app_name' => 'Presensia SMPN 14',
                'primary_color' => '#059669', // Green
                'secondary_color' => '#047857',
                'accent_color' => '#F59E0B',
            ],
            [
                'name' => 'SMP Negeri 10 Surabaya',
                'address' => 'Jl. Raya Gubeng No. 10, Surabaya',
                'phone' => '031-7654321',
                'email' => 'info@smpn10sby.sch.id',
                'website' => 'https://smpn10sby.sch.id',
                'app_name' => 'Etrack SMPN 10',
                'primary_color' => '#DC2626', // Red
                'secondary_color' => '#B91C1C',
                'accent_color' => '#F59E0B',
            ],
            [
                'name' => 'SMA Negeri 1 Jakarta',
                'address' => 'Jl. Budi Utomo No. 1, Jakarta',
                'phone' => '021-1234567',
                'email' => 'info@sman1jkt.sch.id',
                'website' => 'https://sman1jkt.sch.id',
                'app_name' => 'SchoolTrack SMA 1',
                'primary_color' => '#7C3AED', // Purple
                'secondary_color' => '#6D28D9',
                'accent_color' => '#F59E0B',
            ],
        ];

        foreach ($schools as $schoolData) {
            // Create school
            $school = School::create([
                'name' => $schoolData['name'],
                'address' => $schoolData['address'],
                'phone' => $schoolData['phone'],
                'email' => $schoolData['email'],
                'website' => $schoolData['website'],
                'is_active' => true,
                'super_admin_id' => $superAdmin->id,
            ]);

            // Create tenant settings
            TenantSetting::create([
                'school_id' => $school->id,
                'app_name' => $schoolData['app_name'],
                'primary_color' => $schoolData['primary_color'],
                'secondary_color' => $schoolData['secondary_color'],
                'accent_color' => $schoolData['accent_color'],
                'branding' => [
                    'show_logo' => true,
                    'show_school_name' => true,
                    'footer_text' => 'Powered by Presensia',
                    'custom_css' => null,
                    'login_background' => null,
                ],
                'features' => [
                    'attendance' => true,
                    'leave_management' => true,
                    'reports' => true,
                    'qr_codes' => true,
                    'bulk_import' => true,
                    'rbac' => true,
                    'notifications' => true,
                    'api_access' => false,
                    'custom_branding' => true,
                ],
                'is_active' => true,
            ]);

            // Create attendance settings
            AttendanceSetting::create([
                'school_id' => $school->id,
                'check_in_time' => '07:00',
                'check_out_time' => '15:00',
                'location_latitude' => -6.2088,
                'location_longitude' => 106.8456,
                'location_name' => $school->name,
                'radius_meters' => 100,
                'qr_code_duration' => 10,
                'require_photo' => true,
                'require_location' => true,
                'is_active' => true,
            ]);
        }
    }
}