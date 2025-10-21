<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SpecialSchedule;
use App\Models\DailyOverride;
use App\Models\HolidaySchedule;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    /**
     * Display schedule information for users.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        // Get special schedules for this month
        $specialSchedules = SpecialSchedule::where('school_id', $user->school_id)
            ->where('is_active', true)
            ->get()
            ->filter(function ($schedule) use ($user) {
                return $schedule->appliesTo(now(), $user);
            });
            
        // Get daily overrides for this month
        $dailyOverrides = DailyOverride::where('school_id', $user->school_id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('is_active', true)
            ->get()
            ->filter(function ($override) use ($user) {
                return $override->appliesTo(Carbon::parse($override->date), $user);
            });
            
        // Get holidays for this month
        $holidays = HolidaySchedule::where('school_id', $user->school_id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('is_active', true)
            ->get();
            
        return view('schedule.index', compact('specialSchedules', 'dailyOverrides', 'holidays', 'month', 'year', 'startDate', 'endDate'));
    }
    
    /**
     * Get today's schedule information.
     */
    public function today()
    {
        $user = Auth::user();
        $today = now('Asia/Jakarta');
        
        // Check if today is holiday
        $isHoliday = HolidaySchedule::isHoliday($today);
        $holidayName = $isHoliday ? HolidaySchedule::getHolidayName($today) : null;
        
        // Get special schedule for today
        $specialSchedule = SpecialSchedule::where('school_id', $user->school_id)
            ->where('day_of_week', strtolower($today->format('l')))
            ->where('is_active', true)
            ->get()
            ->first(function ($schedule) use ($today, $user) {
                return $schedule->appliesTo($today, $user);
            });
            
        // Get daily override for today
        $dailyOverride = DailyOverride::where('school_id', $user->school_id)
            ->where('date', $today->format('Y-m-d'))
            ->where('is_active', true)
            ->get()
            ->first(function ($override) use ($today, $user) {
                return $override->appliesTo($today, $user);
            });
            
        // Determine max check-in time
        $maxCheckInTime = null;
        $reason = null;
        
        if ($dailyOverride) {
            $maxCheckInTime = $dailyOverride->max_check_in_time->format('H:i');
            $reason = $dailyOverride->reason;
        } elseif ($specialSchedule) {
            $maxCheckInTime = $specialSchedule->max_check_in_time->format('H:i');
            $reason = $specialSchedule->name;
        } else {
            // Get default from settings
            $settings = \App\Models\AttendanceSetting::where('school_id', $user->school_id)
                ->where('is_active', true)
                ->first();
                
            if ($settings) {
                if ($user->hasRole(['teacher'])) {
                    $maxCheckInTime = $settings->teacher_max_time ? $settings->teacher_max_time->format('H:i') : '06:30';
                } elseif ($user->hasRole(['student'])) {
                    $maxCheckInTime = $settings->student_max_time ? $settings->student_max_time->format('H:i') : '06:30';
                } else {
                    $maxCheckInTime = $settings->other_roles_max_time ? $settings->other_roles_max_time->format('H:i') : '07:00';
                }
            } else {
                $maxCheckInTime = $user->hasRole(['teacher', 'student']) ? '06:30' : '07:00';
            }
        }
        
        return response()->json([
            'is_holiday' => $isHoliday,
            'holiday_name' => $holidayName,
            'max_check_in_time' => $maxCheckInTime,
            'reason' => $reason,
            'special_schedule' => $specialSchedule,
            'daily_override' => $dailyOverride
        ]);
    }
}