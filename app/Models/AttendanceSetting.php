<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AttendanceSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'check_in_time',
        'check_out_time',
        'teacher_max_time',
        'student_max_time',
        'other_roles_max_time',
        'location_latitude',
        'location_longitude',
        'location_name',
        'radius_meters',
        'qr_code_duration',
        'require_photo',
        'require_location',
        'is_active',
    ];

    protected $casts = [
        'check_in_time' => 'datetime:H:i',
        'check_out_time' => 'datetime:H:i',
        'teacher_max_time' => 'datetime:H:i',
        'student_max_time' => 'datetime:H:i',
        'other_roles_max_time' => 'datetime:H:i',
        'require_photo' => 'boolean',
        'require_location' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the school that owns the attendance setting.
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Normalize any time representation (Carbon, full datetime string, or H:i:s / H:i) to standard H:i:s.
     */
    public static function normalizeTimeString($time): ?string
    {
        if (!$time) return null;
        if ($time instanceof \DateTimeInterface) {
            return $time->format('H:i:s');
        }
        $time = trim((string) $time);
        if (preg_match('/(\d{2}:\d{2}(?::\d{2})?)/', $time, $matches)) {
            $matched = $matches[1];
            return strlen($matched) === 5 ? $matched . ':00' : $matched;
        }
        return null;
    }

    public function getFormattedCheckInTimeAttribute(): string
    {
        return $this->check_in_time ? $this->check_in_time->format('H:i') : '06:30';
    }

    public function getFormattedCheckOutTimeAttribute(): string
    {
        return $this->check_out_time ? $this->check_out_time->format('H:i') : '14:30';
    }

    public function getFormattedStudentMaxTimeAttribute(): string
    {
        if ($this->student_max_time) {
            return $this->student_max_time->format('H:i');
        }
        return $this->check_in_time ? $this->check_in_time->format('H:i') : '06:30';
    }

    public function getFormattedTeacherMaxTimeAttribute(): string
    {
        if ($this->teacher_max_time) {
            return $this->teacher_max_time->format('H:i');
        }
        return $this->check_in_time ? $this->check_in_time->format('H:i') : '06:30';
    }

    public function getFormattedOtherRolesMaxTimeAttribute(): string
    {
        if ($this->other_roles_max_time) {
            return $this->other_roles_max_time->format('H:i');
        }
        return '07:00';
    }
}
