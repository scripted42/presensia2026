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
