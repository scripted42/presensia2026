<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClassStudent extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'student_id',
        'status',
        'enrolled_at',
        'left_at',
        'notes',
    ];

    protected $casts = [
        'enrolled_at' => 'date',
        'left_at' => 'date',
    ];

    /**
     * Get the class that owns the class student.
     */
    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Get the student that owns the class student.
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the status label for display.
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'active' => 'Aktif',
            'transferred' => 'Pindah',
            'dropped_out' => 'Drop Out',
            'graduated' => 'Lulus',
            default => 'Tidak Diketahui',
        };
    }

    /**
     * Get the status color for display.
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'active' => 'green',
            'transferred' => 'blue',
            'dropped_out' => 'red',
            'graduated' => 'purple',
            default => 'gray',
        };
    }
}
