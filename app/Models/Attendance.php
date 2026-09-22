<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'check_in',
        'check_out',
        'status',
        'latitude',
        'longitude',
        'location_name',
        'photo',
        'qr_code_used',
        'notes',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the user that owns the attendance.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who approved the attendance.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Determine attendance status based on user and check-in time.
     */
    public static function determineStatusFor($user, $checkInTime): string
    {
        if (!$checkInTime || !$user) {
            return 'alpha';
        }

        if ($checkInTime instanceof \Carbon\Carbon) {
            $checkInTimeInTz = $checkInTime->copy()->timezone('Asia/Jakarta');
        } else {
            $checkInTimeInTz = \Carbon\Carbon::parse($checkInTime, 'Asia/Jakarta');
        }
        $checkInTimeFormatted = $checkInTimeInTz->format('H:i:s');

        // Priority 1: Daily Override
        $dailyOverrideTime = \App\Models\DailyOverride::getMaxCheckInTimeForDate($checkInTimeInTz, $user);
        if ($dailyOverrideTime) {
            $maxTime = AttendanceSetting::normalizeTimeString($dailyOverrideTime);
            return ($checkInTimeFormatted <= $maxTime) ? 'ontime' : 'late';
        }

        // Priority 2: Special Schedule
        $specialScheduleTime = \App\Models\SpecialSchedule::getMaxCheckInTimeForDate($checkInTimeInTz, $user);
        if ($specialScheduleTime) {
            $maxTime = AttendanceSetting::normalizeTimeString($specialScheduleTime);
            return ($checkInTimeFormatted <= $maxTime) ? 'ontime' : 'late';
        }

        // Priority 3: Regular settings
        $settings = AttendanceSetting::where('school_id', $user->school_id)
            ->where('is_active', true)
            ->first();

        $isStudent = ($user->user_type === 'student') || (method_exists($user, 'hasRole') && $user->hasRole('student'));
        $isTeacher = ($user->user_type === 'employee') || (method_exists($user, 'hasRole') && $user->hasRole(['teacher', 'employee', 'headmaster', 'tu', 'bk', 'kesiswaan']));

        if ($settings) {
            $checkInFallback = AttendanceSetting::normalizeTimeString($settings->check_in_time) ?: '06:30:00';

            if ($isStudent) {
                $maxTime = AttendanceSetting::normalizeTimeString($settings->student_max_time) ?: $checkInFallback;
            } elseif ($isTeacher) {
                $maxTime = AttendanceSetting::normalizeTimeString($settings->teacher_max_time) ?: $checkInFallback;
            } else {
                $maxTime = AttendanceSetting::normalizeTimeString($settings->other_roles_max_time) ?: '07:00:00';
            }
        } else {
            $maxTime = ($isStudent || $isTeacher) ? '06:30:00' : '07:00:00';
        }

        return ($checkInTimeFormatted <= $maxTime) ? 'ontime' : 'late';
    }

    /**
     * Get the status color for display.
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'ontime' => 'green',
            'late' => 'orange',
            'sick' => 'yellow',
            'permit' => 'yellow',
            'duty' => 'yellow',
            'leave' => 'yellow',
            'alpha' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get the status label for display.
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'ontime' => 'Ontime',
            'late' => 'Terlambat',
            'sick' => 'Sakit',
            'permit' => 'Izin',
            'duty' => 'Dinas Luar',
            'leave' => 'Cuti',
            'alpha' => 'Alpha',
            default => 'Tidak Diketahui',
        };
    }

    /**
     * Scope for filtering by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope for filtering by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
