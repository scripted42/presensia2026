<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class HolidaySchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'date',
        'day_name',
        'holiday_name',
        'is_weekend',
        'is_national_holiday',
        'is_active',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
        'is_weekend' => 'boolean',
        'is_national_holiday' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Check if a date is a holiday
     */
    public static function isHoliday($date, $schoolId = null)
    {
        $date = $date instanceof Carbon ? $date : Carbon::parse($date);
        
        // Check if it's weekend (Saturday or Sunday)
        if ($date->isWeekend()) {
            return true;
        }
        
        // Check database for holiday
        $query = self::where('date', $date->format('Y-m-d'))
            ->where('is_active', true);
            
        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }
        
        return $query->exists();
    }

    /**
     * Get holiday name for a date
     */
    public static function getHolidayName($date, $schoolId = null)
    {
        $date = $date instanceof Carbon ? $date : Carbon::parse($date);
        
        // Check if it's weekend
        if ($date->isWeekend()) {
            return $date->isSaturday() ? 'Hari Sabtu' : 'Hari Minggu';
        }
        
        // Check database for holiday
        $query = self::where('date', $date->format('Y-m-d'))
            ->where('is_active', true);
            
        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }
        
        $holiday = $query->first();
        return $holiday ? $holiday->holiday_name : null;
    }

    /**
     * Get working days between two dates
     */
    public static function getWorkingDays($startDate, $endDate)
    {
        $start = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $end = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);
        
        $workingDays = 0;
        $current = $start->copy();
        
        while ($current->lte($end)) {
            if (!self::isHoliday($current)) {
                $workingDays++;
            }
            $current->addDay();
        }
        
        return $workingDays;
    }

    /**
     * Get holiday count between two dates
     */
    public static function getHolidayCount($startDate, $endDate)
    {
        $start = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $end = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);
        
        $holidayCount = 0;
        $current = $start->copy();
        
        while ($current->lte($end)) {
            if (self::isHoliday($current)) {
                $holidayCount++;
            }
            $current->addDay();
        }
        
        return $holidayCount;
    }
}