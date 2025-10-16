<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityLog extends Model
{
    protected $fillable = [
        'school_id',
        'ip_address',
        'mac_address',
        'user_agent',
        'attack_type',
        'severity',
        'description',
        'request_data',
        'response_data',
        'attempt_count',
        'first_attempt',
        'last_attempt',
        'is_blocked',
        'block_reason'
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
        'first_attempt' => 'datetime',
        'last_attempt' => 'datetime',
        'is_blocked' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the school that owns the security log
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Scope for filtering by school
     */
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Scope for filtering by IP address
     */
    public function scopeByIp($query, $ipAddress)
    {
        return $query->where('ip_address', $ipAddress);
    }

    /**
     * Scope for filtering by attack type
     */
    public function scopeByAttackType($query, $attackType)
    {
        return $query->where('attack_type', $attackType);
    }

    /**
     * Scope for filtering by severity
     */
    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope for blocked IPs
     */
    public function scopeBlocked($query)
    {
        return $query->where('is_blocked', true);
    }

    /**
     * Scope for active attacks
     */
    public function scopeActive($query)
    {
        return $query->where('is_blocked', false);
    }

    /**
     * Scope for recent attacks
     */
    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /**
     * Get formatted attack type
     */
    public function getFormattedAttackTypeAttribute()
    {
        $types = [
            'brute_force' => 'Brute Force',
            'ddos' => 'DDoS Attack',
            'sql_injection' => 'SQL Injection',
            'xss' => 'XSS Attack',
            'csrf' => 'CSRF Attack',
            'directory_traversal' => 'Directory Traversal',
            'file_upload' => 'Malicious File Upload',
            'suspicious_activity' => 'Suspicious Activity'
        ];

        return $types[$this->attack_type] ?? ucfirst(str_replace('_', ' ', $this->attack_type));
    }

    /**
     * Get formatted severity
     */
    public function getFormattedSeverityAttribute()
    {
        $severities = [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'critical' => 'Critical'
        ];

        return $severities[$this->severity] ?? ucfirst($this->severity);
    }

    /**
     * Get severity color
     */
    public function getSeverityColorAttribute()
    {
        $colors = [
            'low' => 'green',
            'medium' => 'yellow',
            'high' => 'orange',
            'critical' => 'red'
        ];

        return $colors[$this->severity] ?? 'gray';
    }

    /**
     * Check if this is a repeated attack
     */
    public function isRepeatedAttack()
    {
        return $this->attempt_count > 1;
    }

    /**
     * Get time since first attempt
     */
    public function getTimeSinceFirstAttemptAttribute()
    {
        if (!$this->first_attempt) {
            return null;
        }

        return $this->first_attempt->diffForHumans();
    }
}
