<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PerformanceService
{
    /**
     * Get cached attendance settings
     */
    public static function getAttendanceSettings($schoolId)
    {
        return Cache::remember(
            "attendance_settings_{$schoolId}",
            config('performance.cache.attendance_settings.ttl', 3600),
            function() use ($schoolId) {
                return \App\Models\AttendanceSetting::where('school_id', $schoolId)
                    ->where('is_active', true)
                    ->first();
            }
        );
    }

    /**
     * Get cached holiday schedules
     */
    public static function getHolidaySchedules($schoolId, $startDate, $endDate)
    {
        $cacheKey = "holidays_{$schoolId}_{$startDate}_{$endDate}";
        
        return Cache::remember(
            $cacheKey,
            config('performance.cache.holiday_schedules.ttl', 1800),
            function() use ($schoolId, $startDate, $endDate) {
                return \App\Models\HolidaySchedule::where('school_id', $schoolId)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->where('is_active', true)
                    ->pluck('date')
                    ->toArray();
            }
        );
    }

    /**
     * Get cached special schedules
     */
    public static function getSpecialSchedules($schoolId, $date, $user)
    {
        $cacheKey = "special_schedules_{$schoolId}_{$date}_{$user->id}";
        
        return Cache::remember(
            $cacheKey,
            config('performance.cache.special_schedules.ttl', 1800),
            function() use ($schoolId, $date, $user) {
                return \App\Models\SpecialSchedule::getMaxCheckInTimeForDate($date, $user);
            }
        );
    }

    /**
     * Clear performance caches
     */
    public static function clearCaches($schoolId = null)
    {
        if ($schoolId) {
            Cache::forget("attendance_settings_{$schoolId}");
            Cache::forget("holidays_{$schoolId}_*");
            Cache::forget("special_schedules_{$schoolId}_*");
        } else {
            Cache::flush();
        }
    }

    /**
     * Get database performance stats
     */
    public static function getDatabaseStats()
    {
        $stats = [];
        
        // Get table sizes
        $tables = ['users', 'attendances', 'holiday_schedules', 'special_schedules'];
        foreach ($tables as $table) {
            $result = DB::select("SELECT COUNT(*) as count FROM {$table}");
            $stats[$table] = $result[0]->count ?? 0;
        }

        return $stats;
    }

    /**
     * Optimize database queries
     */
    public static function optimizeQueries()
    {
        // Clear query cache
        DB::statement('FLUSH QUERY CACHE');
        
        // Analyze tables for better performance
        $tables = ['attendances', 'users', 'holiday_schedules', 'special_schedules'];
        foreach ($tables as $table) {
            DB::statement("ANALYZE TABLE {$table}");
        }
    }
}
