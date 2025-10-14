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
}
