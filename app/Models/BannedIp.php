<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BannedIp extends Model
{
    protected $fillable = [
        'school_id',
        'ip_address',
        'mac_address',
        'username',
        'ban_type',
        'reason',
        'description',
        'banned_at',
        'expires_at',
        'is_active',
        'banned_by'
    ];

    protected $casts = [
        'banned_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the school that owns the banned IP
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the user who banned this IP
     */
    public function bannedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'banned_by');
    }

    /**
     * Scope for active bans
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for expired bans
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Scope for permanent bans
     */
    public function scopePermanent($query)
    {
        return $query->where('ban_type', 'permanent');
    }

    /**
     * Scope for temporary bans
     */
    public function scopeTemporary($query)
    {
        return $query->where('ban_type', 'temporary');
    }

    /**
     * Scope for IP range bans
     */
    public function scopeIpRange($query)
    {
        return $query->where('ban_type', 'ip_range');
    }

    /**
     * Scope for MAC address bans
     */
    public function scopeMac($query)
    {
        return $query->where('ban_type', 'mac');
    }

    /**
     * Scope for username bans
     */
    public function scopeUsername($query)
    {
        return $query->where('ban_type', 'username');
    }

    /**
     * Check if ban is expired
     */
    public function isExpired()
    {
        if ($this->ban_type === 'permanent') {
            return false;
        }

        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if ban is active
     */
    public function isActive()
    {
        return $this->is_active && !$this->isExpired();
    }

    /**
     * Get formatted ban type
     */
    public function getFormattedBanTypeAttribute()
    {
        $types = [
            'temporary' => 'Temporary',
            'permanent' => 'Permanent',
            'ip_range' => 'IP Range',
            'mac' => 'MAC Address',
            'username' => 'Username'
        ];

        return $types[$this->ban_type] ?? ucfirst($this->ban_type);
    }

    /**
     * Get ban duration
     */
    public function getBanDurationAttribute()
    {
        if ($this->ban_type === 'permanent') {
            return 'Permanent';
        }

        if (!$this->expires_at) {
            return 'Unknown';
        }

        return $this->banned_at->diffForHumans($this->expires_at, true);
    }

    /**
     * Get time remaining
     */
    public function getTimeRemainingAttribute()
    {
        if ($this->ban_type === 'permanent' || !$this->expires_at) {
            return null;
        }

        if ($this->isExpired()) {
            return 'Expired';
        }

        return now()->diffForHumans($this->expires_at, true);
    }

    /**
     * Check if IP is banned
     */
    public static function isIpBanned($ipAddress, $schoolId = null)
    {
        $query = static::active()
            ->where('ip_address', $ipAddress);

        if ($schoolId) {
            $query->where(function($q) use ($schoolId) {
                $q->where('school_id', $schoolId)
                  ->orWhereNull('school_id');
            });
        }

        return $query->exists();
    }

    /**
     * Check if MAC address is banned
     */
    public static function isMacBanned($macAddress, $schoolId = null)
    {
        $query = static::active()
            ->where('mac_address', $macAddress);

        if ($schoolId) {
            $query->where(function($q) use ($schoolId) {
                $q->where('school_id', $schoolId)
                  ->orWhereNull('school_id');
            });
        }

        return $query->exists();
    }
}
