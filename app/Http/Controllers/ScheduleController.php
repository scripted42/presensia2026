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
        try {
            $user = Auth::user();
            $month = $request->get('month', now()->month);
            $year = $request->get('year', now()->year);
        
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        // Get special schedules for this month
        $allSpecialSchedules = SpecialSchedule::where('school_id', $user->school_id)
            ->where('is_active', true)
            ->get();
            
        \Log::info('ScheduleController::index - All special schedules: ' . $allSpecialSchedules->count());
        \Log::info('ScheduleController::index - User roles: ' . $user->roles->pluck('name')->toArray());
        
        $specialSchedules = $allSpecialSchedules->filter(function ($schedule) use ($user) {
            $applies = $schedule->appliesTo(now(), $user);
            \Log::info('ScheduleController::index - Schedule: ' . $schedule->name . ', applies: ' . ($applies ? 'true' : 'false'));
            return $applies;
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
        } catch (\Exception $e) {
            \Log::error('ScheduleController::index error: ' . $e->getMessage());
            return view('schedule.index', [
                'specialSchedules' => collect(),
                'dailyOverrides' => collect(),
                'holidays' => collect(),
                'month' => now()->month,
                'year' => now()->year,
                'startDate' => now()->startOfMonth(),
                'endDate' => now()->endOfMonth(),
                'error' => 'Gagal memuat informasi jadwal: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get today's schedule information.
     */
    public function today()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            $today = now('Asia/Jakarta');
            
            \Log::info('ScheduleController::today - User: ' . $user->id . ', School: ' . $user->school_id);
        
        // Check if today is holiday
        $isHoliday = false;
        $holidayName = null;
        try {
            $isHoliday = HolidaySchedule::isHoliday($today, $user->school_id);
            $holidayName = $isHoliday ? HolidaySchedule::getHolidayName($today, $user->school_id) : null;
        } catch (\Exception $e) {
            \Log::error('HolidaySchedule error: ' . $e->getMessage());
        }
        
        // Get special schedule for today
        $specialSchedule = null;
        try {
            $allTodaySchedules = SpecialSchedule::where('school_id', $user->school_id)
                ->where('day_of_week', strtolower($today->format('l')))
                ->where('is_active', true)
                ->get();
                
            \Log::info('ScheduleController::today - All today schedules: ' . $allTodaySchedules->count());
            \Log::info('ScheduleController::today - Today: ' . $today->format('Y-m-d l'));
            \Log::info('ScheduleController::today - User roles: ' . $user->roles->pluck('name')->toArray());
            
            $specialSchedule = $allTodaySchedules->first(function ($schedule) use ($today, $user) {
                $applies = $schedule->appliesTo($today, $user);
                \Log::info('ScheduleController::today - Schedule: ' . $schedule->name . ', applies: ' . ($applies ? 'true' : 'false'));
                return $applies;
            });
        } catch (\Exception $e) {
            \Log::error('SpecialSchedule error: ' . $e->getMessage());
        }
            
        // Get daily override for today
        $dailyOverride = null;
        try {
            $dailyOverride = DailyOverride::where('school_id', $user->school_id)
                ->where('date', $today->format('Y-m-d'))
                ->where('is_active', true)
                ->get()
                ->first(function ($override) use ($today, $user) {
                    return $override->appliesTo($today, $user);
                });
        } catch (\Exception $e) {
            \Log::error('DailyOverride error: ' . $e->getMessage());
        }
            
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
        
            $response = [
                'is_holiday' => $isHoliday,
                'holiday_name' => $holidayName,
                'max_check_in_time' => $maxCheckInTime,
                'reason' => $reason,
                'special_schedule' => $specialSchedule,
                'daily_override' => $dailyOverride
            ];
            
            \Log::info('ScheduleController::today - Response: ' . json_encode($response));
            
            return response()->json($response);
        } catch (\Exception $e) {
            \Log::error('ScheduleController::today error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal memuat informasi jadwal',
                'message' => $e->getMessage(),
                'is_holiday' => false,
                'holiday_name' => null,
                'max_check_in_time' => '07:00',
                'reason' => null
            ], 500);
        }
    }
}