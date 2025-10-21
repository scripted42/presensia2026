<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HolidaySchedule;
use App\Models\School;
use Carbon\Carbon;

class HolidaySchedulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all schools
        $schools = School::all();
        
        if ($schools->isEmpty()) {
            $this->command->warn('No schools found. Please create schools first.');
            return;
        }
        
        // Generate weekend holidays for current year and next year
        $currentYear = Carbon::now()->year;
        $nextYear = $currentYear + 1;
        
        foreach ($schools as $school) {
            $this->seedWeekends($currentYear, $school->id);
            $this->seedWeekends($nextYear, $school->id);
            
            // Seed some national holidays for 2024-2025
            $this->seedNationalHolidays($school->id);
        }
    }
    
    private function seedWeekends($year, $schoolId)
    {
        $startDate = Carbon::create($year, 1, 1);
        $endDate = Carbon::create($year, 12, 31);
        
        $current = $startDate->copy();
        
        while ($current->lte($endDate)) {
            if ($current->isWeekend()) {
                HolidaySchedule::updateOrCreate(
                    ['school_id' => $schoolId, 'date' => $current->format('Y-m-d')],
                    [
                        'school_id' => $schoolId,
                        'day_name' => $current->locale('id')->dayName,
                        'holiday_name' => $current->isSaturday() ? 'Hari Sabtu' : 'Hari Minggu',
                        'is_weekend' => true,
                        'is_national_holiday' => false,
                        'is_active' => true,
                        'description' => 'Hari libur akhir pekan'
                    ]
                );
            }
            $current->addDay();
        }
    }
    
    private function seedNationalHolidays($schoolId)
    {
        $nationalHolidays = [
            // 2024
            ['2024-01-01', 'Tahun Baru 2024'],
            ['2024-02-10', 'Tahun Baru Imlek 2575'],
            ['2024-02-11', 'Hari Raya Nyepi'],
            ['2024-03-11', 'Isra Mikraj Nabi Muhammad SAW'],
            ['2024-03-29', 'Wafat Isa Almasih'],
            ['2024-04-10', 'Hari Raya Idul Fitri 1445 H'],
            ['2024-04-11', 'Hari Raya Idul Fitri 1445 H'],
            ['2024-05-01', 'Hari Buruh Internasional'],
            ['2024-05-09', 'Kenaikan Isa Almasih'],
            ['2024-05-23', 'Hari Raya Waisak 2568 BE'],
            ['2024-06-01', 'Hari Lahir Pancasila'],
            ['2024-06-17', 'Hari Raya Idul Adha 1445 H'],
            ['2024-07-07', 'Tahun Baru Islam 1446 H'],
            ['2024-08-17', 'Hari Kemerdekaan Republik Indonesia'],
            ['2024-09-16', 'Maulid Nabi Muhammad SAW'],
            ['2024-12-25', 'Hari Raya Natal'],
            
            // 2025
            ['2025-01-01', 'Tahun Baru 2025'],
            ['2025-01-29', 'Tahun Baru Imlek 2576'],
            ['2025-03-01', 'Hari Raya Nyepi'],
            ['2025-03-01', 'Isra Mikraj Nabi Muhammad SAW'],
            ['2025-04-18', 'Wafat Isa Almasih'],
            ['2025-03-30', 'Hari Raya Idul Fitri 1446 H'],
            ['2025-03-31', 'Hari Raya Idul Fitri 1446 H'],
            ['2025-05-01', 'Hari Buruh Internasional'],
            ['2025-05-29', 'Kenaikan Isa Almasih'],
            ['2025-05-12', 'Hari Raya Waisak 2569 BE'],
            ['2025-06-01', 'Hari Lahir Pancasila'],
            ['2025-06-07', 'Hari Raya Idul Adha 1446 H'],
            ['2025-06-26', 'Tahun Baru Islam 1447 H'],
            ['2025-08-17', 'Hari Kemerdekaan Republik Indonesia'],
            ['2025-09-05', 'Maulid Nabi Muhammad SAW'],
            ['2025-12-25', 'Hari Raya Natal'],
        ];
        
        foreach ($nationalHolidays as $holiday) {
            $date = Carbon::parse($holiday[0]);
            HolidaySchedule::updateOrCreate(
                ['school_id' => $schoolId, 'date' => $date->format('Y-m-d')],
                [
                    'school_id' => $schoolId,
                    'day_name' => $date->locale('id')->dayName,
                    'holiday_name' => $holiday[1],
                    'is_weekend' => $date->isWeekend(),
                    'is_national_holiday' => true,
                    'is_active' => true,
                    'description' => 'Hari libur nasional Indonesia'
                ]
            );
        }
    }
}