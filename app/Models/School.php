<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'website',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the users for the school.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the classes for the school.
     */
    public function classes()
    {
        return $this->hasMany(SchoolClass::class);
    }

    /**
     * Get the attendance settings for the school.
     */
    public function attendanceSettings()
    {
        return $this->hasMany(AttendanceSetting::class);
    }

    /**
     * Get the active attendance setting for the school.
     */
    public function activeAttendanceSetting()
    {
        return $this->hasOne(AttendanceSetting::class)->where('is_active', true);
    }

    /**
     * Get the QR codes for the school.
     */
    public function qrCodes()
    {
        return $this->hasMany(QrCode::class);
    }
}
