<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NationalHolidayService
{
    /**
     * Get national holidays from API
     */
    public static function getNationalHolidays($year = null)
    {
        $year = $year ?? now()->year;
        
        try {
            // API dari https://api-harilibur.vercel.app/
            $response = Http::timeout(10)->get("https://api-harilibur.vercel.app/api", [
                'year' => $year
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return self::formatHolidayData($data, $year);
            }
            
            // Fallback ke data statis jika API gagal
            return self::getStaticNationalHolidays($year);
            
        } catch (\Exception $e) {
            Log::error('Failed to fetch national holidays from API: ' . $e->getMessage());
            return self::getStaticNationalHolidays($year);
        }
    }
    
    /**
     * Format holiday data from API
     */
    private static function formatHolidayData($data, $year)
    {
        $holidays = [];
        
        if (isset($data['holidays']) && is_array($data['holidays'])) {
            foreach ($data['holidays'] as $holiday) {
                $date = Carbon::parse($holiday['date']);
                $holidays[] = [
                    'date' => $date->format('Y-m-d'),
                    'day_name' => $date->format('l'),
                    'holiday_name' => $holiday['name'],
                    'is_national_holiday' => true,
                    'is_weekend' => false,
                    'is_active' => true,
                    'description' => 'Hari Libur Nasional - ' . $holiday['name']
                ];
            }
        }
        
        return $holidays;
    }
    
    /**
     * Static national holidays data as fallback
     */
    private static function getStaticNationalHolidays($year)
    {
        $holidays = [];
        
        // Hari libur nasional tetap
        $staticHolidays = [
            '01-01' => 'Tahun Baru',
            '02-10' => 'Tahun Baru Imlek',
            '03-11' => 'Hari Raya Nyepi',
            '03-29' => 'Jumat Agung',
            '04-10' => 'Isra Mikraj',
            '05-01' => 'Hari Buruh Internasional',
            '05-09' => 'Kenaikan Isa Almasih',
            '05-23' => 'Hari Raya Waisak',
            '06-01' => 'Hari Lahir Pancasila',
            '06-17' => 'Hari Raya Idul Fitri',
            '06-18' => 'Hari Raya Idul Fitri',
            '07-07' => 'Hari Raya Idul Adha',
            '08-17' => 'Hari Kemerdekaan Republik Indonesia',
            '09-16' => 'Tahun Baru Islam',
            '12-25' => 'Hari Raya Natal'
        ];
        
        foreach ($staticHolidays as $date => $name) {
            $fullDate = $year . '-' . $date;
            $carbonDate = Carbon::parse($fullDate);
            
            $holidays[] = [
                'date' => $carbonDate->format('Y-m-d'),
                'day_name' => $carbonDate->format('l'),
                'holiday_name' => $name,
                'is_national_holiday' => true,
                'is_weekend' => false,
                'is_active' => true,
                'description' => 'Hari Libur Nasional - ' . $name
            ];
        }
        
        return $holidays;
    }
    
    /**
     * Import national holidays to database
     */
    public static function importNationalHolidays($year, $schoolId)
    {
        $holidays = self::getNationalHolidays($year);
        $imported = 0;
        $skipped = 0;
        
        foreach ($holidays as $holidayData) {
            // Check if holiday already exists
            $existing = \App\Models\HolidaySchedule::where('school_id', $schoolId)
                ->where('date', $holidayData['date'])
                ->where('is_national_holiday', true)
                ->first();
                
            if (!$existing) {
                \App\Models\HolidaySchedule::create([
                    'school_id' => $schoolId,
                    'date' => $holidayData['date'],
                    'day_name' => $holidayData['day_name'],
                    'holiday_name' => $holidayData['holiday_name'],
                    'is_weekend' => $holidayData['is_weekend'],
                    'is_national_holiday' => $holidayData['is_national_holiday'],
                    'is_active' => $holidayData['is_active'],
                    'description' => $holidayData['description']
                ]);
                $imported++;
            } else {
                $skipped++;
            }
        }
        
        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'total' => count($holidays)
        ];
    }
    
    /**
     * Get available years for import
     */
    public static function getAvailableYears()
    {
        $currentYear = now()->year;
        $years = [];
        
        for ($i = $currentYear - 1; $i <= $currentYear + 2; $i++) {
            $years[] = $i;
        }
        
        return $years;
    }
}
