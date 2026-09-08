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

    /**
     * Scope a query to apply user filters.
     */
    public function scopeFilter($query, array $filters = [])
    {
        // 1. Keyword search (name, email, phone, nik, nis, nisn)
        if (!empty($filters['q'])) {
            $search = trim($filters['q']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        // 2. Class filter (via studentClasses)
        if (isset($filters['class_id']) && $filters['class_id'] !== '' && $filters['class_id'] !== null) {
            if ($filters['class_id'] === 'none') {
                $query->whereDoesntHave('studentClasses');
            } else {
                $classId = $filters['class_id'];
                $query->whereHas('studentClasses', function ($q) use ($classId) {
                    $q->where('classes.id', $classId);
                });
            }
        }

        // 3. Level filter (via studentClasses.level)
        if (!empty($filters['level'])) {
            $level = $filters['level'];
            $query->whereHas('studentClasses', function ($q) use ($level) {
                $q->where('classes.level', $level);
            });
        }

        // 4. Gender filter
        if (!empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        // 5. Account status filter (is_active)
        if (isset($filters['is_active']) && $filters['is_active'] !== '' && $filters['is_active'] !== null) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        // 6. Role filter (via Spatie roles)
        if (!empty($filters['role'])) {
            $role = $filters['role'];
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }

        // 7. Employment status filter (via employeeProfile)
        if (!empty($filters['employment_status'])) {
            $status = $filters['employment_status'];
            $query->whereHas('employeeProfile', function ($q) use ($status) {
                $q->where('employment_status', $status);
            });
        }

        // 8. PTK type filter (via employeeProfile)
        if (!empty($filters['ptk_type'])) {
            $ptk = $filters['ptk_type'];
            $query->whereHas('employeeProfile', function ($q) use ($ptk) {
                $q->where('ptk_type', $ptk);
            });
        }

        return $query;
    }
}
