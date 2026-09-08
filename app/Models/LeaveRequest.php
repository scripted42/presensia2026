<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'school_id',
        'type',
        'start_date',
        'end_date',
        'reason',
        'evidence',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'evidence' => 'array',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the user that owns the leave request.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the school that owns the leave request.
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the user who approved the leave request.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the type label for display.
     */
    public function getTypeLabelAttribute()
    {
        return match($this->type) {
            'sick' => 'Sakit',
            'leave' => 'Cuti',
            'duty' => 'Dinas Luar',
            default => 'Tidak Diketahui',
        };
    }

    /**
     * Get the status label for display.
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => 'Tidak Diketahui',
        };
    }

    /**
     * Get the status color for display.
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'yellow',
            'approved' => 'green',
            'rejected' => 'red',
            default => 'gray',
        };
    }

    /**
     * Scope for filtering by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for applying flexible filters.
     */
    public function scopeFilter($query, array $filters = [])
    {
        // 1. Search keyword (user name, NIS, NIK, reason)
        if (!empty($filters['q'])) {
            $search = trim($filters['q']);
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('nis', 'like', "%{$search}%")
                         ->orWhere('nik', 'like', "%{$search}%");
                  });
            });
        }

        // 2. Status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // 3. Type filter
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // 4. Date range filter
        if (!empty($filters['start_date'])) {
            $query->where('end_date', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->where('start_date', '<=', $filters['end_date']);
        }

        return $query;
    }
}
