<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class SpecialSchedule extends Model
{
    protected $fillable = [
        'school_id',
        'name',
        'description',
        'day_of_week',
        'max_check_in_time',
        'affected_roles',
        'is_active',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'max_check_in_time' => 'datetime:H:i:s',
        'affected_roles' => 'array',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Check if this special schedule applies to a given date and user
     */
    public function appliesTo(Carbon $date, User $user): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Check date range
        if ($this->start_date && $date->lt($this->start_date)) {
            return false;
        }
        if ($this->end_date && $date->gt($this->end_date)) {
            return false;
        }

        // Check day of week
        $dayOfWeek = strtolower($date->format('l')); // monday, tuesday, etc.
        if ($this->day_of_week !== $dayOfWeek) {
            return false;
        }

        // Check affected roles
        if ($this->affected_roles && !empty($this->affected_roles)) {
            $userRoles = $user->roles->pluck('name')->toArray();
            if (!array_intersect($this->affected_roles, $userRoles)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get max check-in time for a specific date and user
     */
    public static function getMaxCheckInTimeForDate(Carbon $date, User $user): ?string
    {
        $specialSchedule = static::where('school_id', $user->school_id)
            ->where('is_active', true)
            ->get()
            ->first(function ($schedule) use ($date, $user) {
                return $schedule->appliesTo($date, $user);
            });

        return $specialSchedule ? $specialSchedule->max_check_in_time->format('H:i:s') : null;
    }
}
