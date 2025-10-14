<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\AttendanceSetting;

class SchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create demo school
        $school = School::create([
            'name' => 'SMA Negeri 1 Jakarta',
            'address' => 'Jl. Pendidikan No. 1, Jakarta Pusat',
            'phone' => '021-12345678',
            'email' => 'info@sman1jakarta.sch.id',
            'website' => 'https://sman1jakarta.sch.id',
            'settings' => json_encode([
                'timezone' => 'Asia/Jakarta',
                'academic_year' => '2024/2025',
                'semester' => 1,
                'logo' => null,
                'theme_color' => '#3B82F6',
            ]),
            'is_active' => true,
        ]);

        // Create attendance settings for the school
        AttendanceSetting::create([
            'school_id' => $school->id,
            'check_in_time' => '07:00:00',
            'check_out_time' => '15:00:00',
            'location_latitude' => -6.2088,
            'location_longitude' => 106.8456,
            'location_name' => 'SMA Negeri 1 Jakarta',
            'radius_meters' => 100,
            'qr_code_duration' => 10,
            'require_photo' => true,
            'require_location' => true,
            'is_active' => true,
        ]);
    }
}
