<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SchoolClass extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'school_id',
        'name',
        'level',
        'major',
        'year',
        'teacher_id',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the school that owns the class.
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the teacher for the class.
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the students for the class.
     */
    public function students()
    {
        return $this->belongsToMany(User::class, 'class_students', 'class_id', 'student_id')
                    ->withPivot('status', 'enrolled_at', 'left_at', 'notes')
                    ->withTimestamps();
    }

    /**
     * Get the active students for the class.
     */
    public function activeStudents()
    {
        return $this->students()->wherePivot('status', 'active');
    }

    /**
     * Get the attendances for the class.
     */
    public function attendances()
    {
        return $this->hasManyThrough(Attendance::class, User::class, 'id', 'user_id')
                    ->whereIn('users.id', $this->students()->pluck('users.id'));
    }
}
