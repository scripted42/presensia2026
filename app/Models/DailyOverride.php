<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class DailyOverride extends Model
{
    protected $fillable = [
        'school_id',
        'date',
        'max_check_in_time',
        'affected_roles',
        'reason',
        'is_active',
    ];

    protected $casts = [
        'date' => 'date',
        'max_check_in_time' => 'datetime:H:i:s',
        'affected_roles' => 'array',
        'is_active' => 'boolean',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Check if this daily override applies to a given date and user
     */
    public function appliesTo(Carbon $date, User $user): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Check if date matches
        if ($this->date->format('Y-m-d') !== $date->format('Y-m-d')) {
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
        $dailyOverride = static::where('school_id', $user->school_id)
            ->where('date', $date->format('Y-m-d'))
            ->where('is_active', true)
            ->get()
            ->first(function ($override) use ($date, $user) {
                return $override->appliesTo($date, $user);
            });

        return $dailyOverride ? $dailyOverride->max_check_in_time->format('H:i:s') : null;
    }
}
