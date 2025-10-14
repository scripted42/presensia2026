<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QrCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'code',
        'user_id',
        'expires_at',
        'is_used',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    /**
     * Get the school that owns the QR code.
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the user that owns the QR code.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if QR code is expired.
     */
    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if QR code is valid (not used and not expired).
     */
    public function isValid()
    {
        return !$this->is_used && !$this->isExpired();
    }

    /**
     * Scope for active QR codes.
     */
    public function scopeActive($query)
    {
        return $query->where('is_used', false)
                    ->where('expires_at', '>', now());
    }
}
