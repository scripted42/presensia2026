<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'super_admin_id',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the super admin that manages this school
     */
    public function superAdmin()
    {
        return $this->belongsTo(SuperAdmin::class);
    }

    /**
     * Get all users belonging to this school
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all classes belonging to this school
     */
    public function classes()
    {
        return $this->hasMany(SchoolClass::class);
    }

    /**
     * Get tenant settings for this school
     */
    public function tenantSettings()
    {
        return $this->hasOne(TenantSetting::class);
    }

    /**
     * Get attendance settings for this school
     */
    public function attendanceSettings()
    {
        return $this->hasOne(AttendanceSetting::class);
    }
}