<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'school_id',
        'name',
        'email',
        'password',
        'phone',
        'address',
        'birth_date',
        'gender',
        'photo',
        'qr_code',
        'nik',
        'nis',
        'nisn',
        'user_type',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the school that owns the user.
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the attendances for the user.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get the leave requests for the user.
     */
    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * Get the classes where the user is a teacher.
     */
    public function taughtClasses()
    {
        return $this->hasMany(SchoolClass::class, 'teacher_id');
    }

    /**
     * Get the classes where the user is a student.
     */
    public function studentClasses()
    {
        return $this->belongsToMany(SchoolClass::class, 'class_students', 'student_id', 'class_id')
                    ->withPivot('status', 'enrolled_at', 'left_at', 'notes')
                    ->withTimestamps();
    }

    /**
     * Dapodik profiles
     */
    public function employeeProfile()
    {
        return $this->hasOne(EmployeeProfile::class);
    }

    public function studentProfile()
    {
        return $this->hasOne(StudentProfile::class);
    }

    /**
     * Check if user is a student.
     */
    public function isStudent()
    {
        return $this->user_type === 'student';
    }

    /**
     * Check if user is an employee.
     */
    public function isEmployee()
    {
        return $this->user_type === 'employee';
    }
}
