<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SpecialSchedule;
use App\Models\School;

class SpecialSchedulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all schools
        $schools = School::all();
        
        foreach ($schools as $school) {
            // Upacara Senin - untuk semua role
            SpecialSchedule::create([
                'school_id' => $school->id,
                'name' => 'Upacara Senin',
                'description' => 'Upacara bendera setiap hari Senin',
                'day_of_week' => 'monday',
                'max_check_in_time' => '07:30:00',
                'affected_roles' => null, // null means all roles
                'is_active' => true,
            ]);
            
            // Rapat Guru - hanya untuk guru dan pegawai
            SpecialSchedule::create([
                'school_id' => $school->id,
                'name' => 'Rapat Guru',
                'description' => 'Rapat guru setiap hari Rabu',
                'day_of_week' => 'wednesday',
                'max_check_in_time' => '07:15:00',
                'affected_roles' => ['teacher', 'employee'],
                'is_active' => true,
            ]);
        }
    }
}
