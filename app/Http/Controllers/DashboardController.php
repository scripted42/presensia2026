<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\SchoolClass;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Show the application dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        $school = $user->school->load('tenantSettings');
        $today = Carbon::now('Asia/Jakarta')->format('Y-m-d');
        
        // Debug: Log tenant settings data
        \Log::info('=== DASHBOARD DEBUG ===');
        \Log::info('School ID:', $school->id);
        \Log::info('Tenant Settings exists:', $school->tenantSettings ? 'Yes' : 'No');
        if ($school->tenantSettings) {
            \Log::info('Tenant Settings data:', [
                'banner_image' => $school->tenantSettings->banner_image,
                'school_photo' => $school->tenantSettings->school_photo,
                'banner_text' => $school->tenantSettings->banner_text,
                'school_photo_opacity' => $school->tenantSettings->school_photo_opacity,
                'school_photo_position_x' => $school->tenantSettings->school_photo_position_x,
                'school_photo_position_y' => $school->tenantSettings->school_photo_position_y,
                'school_photo_scale' => $school->tenantSettings->school_photo_scale
            ]);
        }

        // Optional filter role untuk agregasi: all|employee|student
        $roleFilter = request('role');
        if (!in_array($roleFilter, ['employee','student','all', null], true)) {
            $roleFilter = null;
        }

        // Periode filter (default: bulan berjalan)
        $startDate = request('start') ? Carbon::parse(request('start'))->format('Y-m-d') : Carbon::now('Asia/Jakarta')->startOfMonth()->format('Y-m-d');
        $endDate = request('end') ? Carbon::parse(request('end'))->format('Y-m-d') : Carbon::now('Asia/Jakarta')->endOfDay()->format('Y-m-d');
        
        // Get statistics based on user role
        $stats = $this->getDashboardStats($user, $today);

        // Metrik agregat untuk Admin & Headmaster
        $metrics = [];
        if ($user->hasRole(['admin', 'headmaster', 'bk', 'kesiswaan'])) {
            $tenantConfig = $this->getTenantDashboardConfig($school?->tenantSettings?->custom_fields ?? []);

            $usage = $this->calculateUsageMetrics($user, $startDate, $endDate, $roleFilter);
            $completeness = $this->calculateDataCompleteness($user);
            // Leak selalu untuk pegawai saja
            $leak = $this->calculateLeakMetrics($user, $startDate, $endDate, 'employee');
            $kpi = $this->calculateAttendanceKpi(
                $user,
                $startDate,
                $endDate,
                $usage,
                $completeness,
                $leak,
                $tenantConfig['weights']
            );

            $metrics = [
                'usage' => $usage,
                'completeness' => $completeness,
                'kpi' => $kpi,
                'leak' => $leak,
                'non_users' => $this->getNonUserList($user, $startDate, $endDate, 10, $roleFilter),
                'incomplete_profiles' => $this->getIncompleteProfiles($user, 10),
                'thresholds' => $tenantConfig['thresholds'],
                'role_filter' => $roleFilter ?? 'all',
            ];
        }
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities($user);
        
        // Get attendance chart data
        $attendanceChart = $this->getAttendanceChartData($user, $today);
        
        return view('dashboard', compact('user', 'school', 'stats', 'recentActivities', 'attendanceChart', 'metrics', 'startDate', 'endDate'));
    }

    /**
     * Get dashboard statistics based on user role.
     */
    private function getDashboardStats($user, $today)
    {
        $stats = [];
        
        if ($user->hasRole('admin')) {
            // Admin statistics
            $stats = [
                'total_employees' => User::where('user_type', 'employee')->where('school_id', $user->school_id)
                    ->whereDoesntHave('roles', function($q){ $q->where('name','super-admin'); })
                    ->count(),
                'total_students' => User::where('user_type', 'student')->where('school_id', $user->school_id)
                    ->whereDoesntHave('roles', function($q){ $q->where('name','super-admin'); })
                    ->count(),
                'total_classes' => SchoolClass::where('school_id', $user->school_id)->count(),
                // Absensi hari ini (distinct user) untuk persentase
                'today_attendance' => Attendance::where('date', $today)->whereHas('user', function($query) use ($user) {
                    $query->where('school_id', $user->school_id);
                })->distinct('user_id')->count('user_id'),
                'today_total_users' => User::where('school_id', $user->school_id)->whereIn('user_type', ['employee','student'])
                    ->whereDoesntHave('roles', function($q){ $q->where('name','super-admin'); })
                    ->count(),
                'today_attendance_percent' => 0, // diisi di bawah
                'pending_leaves' => LeaveRequest::where('status', 'pending')->whereHas('user', function($query) use ($user) {
                    $query->where('school_id', $user->school_id)
                          ->whereDoesntHave('roles', function($q){ $q->where('name','super-admin'); });
                })->count(),
                'pending_leaves_total' => LeaveRequest::whereHas('user', function($query) use ($user) {
                    $query->where('school_id', $user->school_id)
                          ->whereDoesntHave('roles', function($q){ $q->where('name','super-admin'); });
                })->count(),
                'pending_leaves_percent' => 0,
            ];
            $stats['today_attendance_percent'] = $stats['today_total_users'] > 0
                ? round(($stats['today_attendance'] / $stats['today_total_users']) * 100, 1)
                : 0;
            $stats['pending_leaves_percent'] = $stats['pending_leaves_total'] > 0
                ? round(($stats['pending_leaves'] / $stats['pending_leaves_total']) * 100, 1)
                : 0;
        } elseif ($user->hasRole('headmaster')) {
            // Headmaster statistics
            $stats = [
                'total_employees' => User::where('user_type', 'employee')->where('school_id', $user->school_id)
                    ->whereDoesntHave('roles', function($q){ $q->where('name','super-admin'); })
                    ->count(),
                'total_students' => User::where('user_type', 'student')->where('school_id', $user->school_id)
                    ->whereDoesntHave('roles', function($q){ $q->where('name','super-admin'); })
                    ->count(),
                'total_classes' => SchoolClass::where('school_id', $user->school_id)->count(),
                'today_attendance' => Attendance::where('date', $today)->whereHas('user', function($query) use ($user) {
                    $query->where('school_id', $user->school_id);
                })->distinct('user_id')->count('user_id'),
                'today_total_users' => User::where('school_id', $user->school_id)->whereIn('user_type', ['employee','student'])
                    ->whereDoesntHave('roles', function($q){ $q->where('name','super-admin'); })
                    ->count(),
                'today_attendance_percent' => 0,
                'pending_leaves' => LeaveRequest::where('status', 'pending')->whereHas('user', function($query) use ($user) {
                    $query->where('school_id', $user->school_id)
                          ->whereDoesntHave('roles', function($q){ $q->where('name','super-admin'); });
                })->count(),
                'pending_leaves_total' => LeaveRequest::whereHas('user', function($query) use ($user) {
                    $query->where('school_id', $user->school_id)
                          ->whereDoesntHave('roles', function($q){ $q->where('name','super-admin'); });
                })->count(),
                'pending_leaves_percent' => 0,
                'approved_leaves' => LeaveRequest::where('status', 'approved')->whereHas('user', function($query) use ($user) {
                    $query->where('school_id', $user->school_id);
                })->where('approved_by', $user->id)->count(),
            ];
            $stats['today_attendance_percent'] = $stats['today_total_users'] > 0
                ? round(($stats['today_attendance'] / $stats['today_total_users']) * 100, 1)
                : 0;
            $stats['pending_leaves_percent'] = $stats['pending_leaves_total'] > 0
                ? round(($stats['pending_leaves'] / $stats['pending_leaves_total']) * 100, 1)
                : 0;
        } elseif ($user->hasRole('teacher')) {
            // Teacher statistics
            $stats = [
                'my_classes' => $user->taughtClasses()->count(),
                'my_students' => 0, // Simplified for now
                'today_attendance' => Attendance::where('user_id', $user->id)->where('date', $today)->count(),
                'today_attendance_detail' => $this->getTodayAttendanceDetail($user, $today),
                'pending_leaves' => LeaveRequest::where('user_id', $user->id)->where('status', 'pending')->count(),
                'pending_leaves_total' => LeaveRequest::where('user_id', $user->id)->count(),
                'pending_leaves_percent' => 0,
            ];
            $stats['pending_leaves_percent'] = $stats['pending_leaves_total'] > 0
                ? round(($stats['pending_leaves'] / $stats['pending_leaves_total']) * 100, 1)
                : 0;
        } elseif ($user->hasRole('student')) {
            // Student statistics
            $stats = [
                'my_classes' => 0, // Simplified for now
                'today_attendance' => Attendance::where('user_id', $user->id)->where('date', $today)->count(),
                'today_attendance_detail' => $this->getTodayAttendanceDetail($user, $today),
                'pending_leaves' => LeaveRequest::where('user_id', $user->id)->where('status', 'pending')->count(),
                'pending_leaves_total' => LeaveRequest::where('user_id', $user->id)->count(),
                'pending_leaves_percent' => 0,
            ];
            $stats['pending_leaves_percent'] = $stats['pending_leaves_total'] > 0
                ? round(($stats['pending_leaves'] / $stats['pending_leaves_total']) * 100, 1)
                : 0;
        } else {
            // Other roles (TU, BK, Kesiswaan)
            $stats = [
                'total_students' => User::where('user_type', 'student')->where('school_id', $user->school_id)->count(),
                'today_attendance' => Attendance::where('user_id', $user->id)->where('date', $today)->count(),
                'today_attendance_detail' => $this->getTodayAttendanceDetail($user, $today),
                'pending_leaves' => LeaveRequest::where('status', 'pending')->whereHas('user', function($query) use ($user) {
                    $query->where('school_id', $user->school_id);
                })->count(),
                'pending_leaves_total' => LeaveRequest::whereHas('user', function($query) use ($user) {
                    $query->where('school_id', $user->school_id);
                })->count(),
                'pending_leaves_percent' => 0,
            ];
            $stats['pending_leaves_percent'] = $stats['pending_leaves_total'] > 0
                ? round(($stats['pending_leaves'] / $stats['pending_leaves_total']) * 100, 1)
                : 0;
        }
        
        return $stats;
    }

    /**
     * Detail status absensi hari ini untuk user saat ini
     */
    private function getTodayAttendanceDetail($user, string $today): array
    {
        $record = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first(['check_in','check_out']);

        $in = $record?->check_in ? $record->check_in->format('H:i:s') : null;
        $out = $record?->check_out ? $record->check_out->format('H:i:s') : null;

        // Khusus siswa: hanya jam masuk yang relevan, tidak ada check-out
        if ($user->hasRole('student')) {
            $status = $in ? 'completed' : 'none';
            $out = null; // pastikan tidak ditampilkan
        } else {
            if (!$in && !$out) {
                $status = 'none';
            } elseif ($in && !$out) {
                $status = 'in_only';
            } else {
                $status = 'completed';
            }
        }

        return [
            'status' => $status,
            'check_in' => $in,
            'check_out' => $out,
        ];
    }

    /**
     * Get recent activities for the user.
     */
    private function getRecentActivities($user)
    {
        $activities = collect();
        
        // Simplified activities for now
        $activities->push([
            'type' => 'welcome',
            'message' => "Selamat datang di Presensia, {$user->name}!",
            'time' => now(),
            'icon' => 'user',
            'color' => 'blue',
        ]);
        
        return $activities;
    }

    /**
     * Get attendance chart data for the last 7 days.
     */
    private function getAttendanceChartData($user, $today)
    {
        $chartData = [];
        
        // Convert string to Carbon object for date operations
        $todayCarbon = Carbon::parse($today);
        
        // Simplified chart data for now
        for ($i = 6; $i >= 0; $i--) {
            $date = $todayCarbon->copy()->subDays($i);
            $chartData[] = [
                'date' => $date->format('d/m'),
                'status' => 'ontime',
                'color' => 'green',
            ];
        }
        
        return $chartData;
    }

    /**
     * Hitung usage absensi periode: persentase user aktif yang melakukan absensi.
     */
    private function calculateUsageMetrics($user, string $startDate, string $endDate, ?string $roleFilter = null): array
    {
        $targetTypes = ['employee','student'];
        if ($roleFilter === 'employee') { $targetTypes = ['employee']; }
        if ($roleFilter === 'student') { $targetTypes = ['student']; }

        $totalUsers = User::where('school_id', $user->school_id)
            ->whereIn('user_type', $targetTypes)
            ->count();

        $activeUserIds = Attendance::whereBetween('date', [$startDate, $endDate])
            ->whereHas('user', function ($q) use ($user) {
                $q->where('school_id', $user->school_id)
                  ->whereDoesntHave('roles', function($r){ $r->where('name','super-admin'); })
                  ->where('email', 'not like', 'superadmin@%');
            })
            ->distinct('user_id')
            ->pluck('user_id');

        // Breakdown per role
        $employeeTotal = User::where('school_id', $user->school_id)->where('user_type', 'employee')
            ->whereDoesntHave('roles', function($q){ $q->where('name','super-admin'); })
            ->where('email', 'not like', 'superadmin@%')
            ->count();
        $studentTotal = User::where('school_id', $user->school_id)->where('user_type', 'student')
            ->whereDoesntHave('roles', function($q){ $q->where('name','super-admin'); })
            ->where('email', 'not like', 'superadmin@%')
            ->count();

        $activeEmployee = User::whereIn('id', $activeUserIds)->where('user_type','employee')
            ->whereDoesntHave('roles', function($q){ $q->where('name','super-admin'); })
            ->where('email', 'not like', 'superadmin@%')
            ->count();
        $activeStudent = User::whereIn('id', $activeUserIds)->where('user_type','student')
            ->whereDoesntHave('roles', function($q){ $q->where('name','super-admin'); })
            ->where('email', 'not like', 'superadmin@%')
            ->count();

        // Jika filter role diterapkan, batasi activeUsers sesuai filter
        $activeUsers = $roleFilter === 'employee' ? $activeEmployee : ($roleFilter === 'student' ? $activeStudent : $activeUserIds->count());
        $percentage = $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100, 1) : 0;

        return [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'percentage' => $percentage,
            'breakdown' => [
                'employee' => [
                    'active' => $activeEmployee,
                    'total' => $employeeTotal,
                    'percentage' => $employeeTotal > 0 ? round(($activeEmployee / $employeeTotal) * 100, 1) : 0,
                ],
                'student' => [
                    'active' => $activeStudent,
                    'total' => $studentTotal,
                    'percentage' => $studentTotal > 0 ? round(($activeStudent / $studentTotal) * 100, 1) : 0,
                ],
            ],
        ];
    }

    /**
     * Hitung kelengkapan data profil untuk pegawai & siswa (field kunci sederhana).
     */
    private function calculateDataCompleteness($user): array
    {
        // Field wajib default (bisa di-override via TenantSetting.custom_fields.required_fields)
        $defaultEmployeeRequired = [
            'phone', 'address', 'nik',
            'employee_profile.nuptk',
            'employee_profile.ptk_type',
            'employee_profile.employment_status',
            'employee_profile.address_line',
            'employee_profile.npwp',
            'employee_profile.bank_name',
            'employee_profile.bank_account',
            'employee_profile.certification_number',
            'employee_profile.certification_year',
            'employee_profile.main_subject',
            'employee_profile.teaching_hours_per_week',
        ];
        $defaultStudentRequired = [
            'nis', 'nisn', 'phone',
            'student_profile.birth_certificate_number',
            'student_profile.kk_number',
            'student_profile.kip_number',
            'student_profile.citizenship',
            'student_profile.residence_type',
            'student_profile.sibling_count',
            'student_profile.order_in_family',
            'student_profile.special_needs',
            'student_profile.blood_type',
        ];
        $tenantCustom = $user->school?->tenantSettings?->custom_fields ?? [];
        $employeeRequired = $tenantCustom['required_fields']['employee'] ?? $defaultEmployeeRequired;
        $studentRequired = $tenantCustom['required_fields']['student'] ?? $defaultStudentRequired;

        $superAdminEmails = \App\Models\SuperAdmin::pluck('email');

        $employees = User::with('employeeProfile')
            ->where('school_id', $user->school_id)
            ->where('user_type', 'employee')
            ->whereDoesntHave('roles', function($q){ $q->where('name','super-admin'); })
            ->whereNotIn('email', $superAdminEmails)
            ->where('email', 'not like', 'superadmin@%')
            ->get(['id','name','phone','address','nik']);
        $students = User::with('studentProfile')
            ->where('school_id', $user->school_id)
            ->where('user_type', 'student')
            ->whereDoesntHave('roles', function($q){ $q->where('name','super-admin'); })
            ->whereNotIn('email', $superAdminEmails)
            ->where('email', 'not like', 'superadmin@%')
            ->get(['id','name','nis','nisn','phone']);

        $empComplete = $employees->filter(function ($u) use ($employeeRequired) {
            foreach ($employeeRequired as $f) {
                if (empty($u->{$f})) return false;
            }
            return true;
        })->count();

        $stuComplete = $students->filter(function ($u) use ($studentRequired) {
            foreach ($studentRequired as $f) {
                if (empty($u->{$f})) return false;
            }
            return true;
        })->count();

        $empTotal = $employees->count();
        $stuTotal = $students->count();

        // Skor kelengkapan berbasis jumlah field kosong
        $totalEmpFields = count($employeeRequired) * max($empTotal, 1);
        $totalStuFields = count($studentRequired) * max($stuTotal, 1);

        $empMissing = $employees->reduce(function($carry, $u) use ($employeeRequired) {
            $m = 0; foreach ($employeeRequired as $f) { if ($this->isFieldEmpty($u, $f, 'employee_profile')) $m++; } return $carry + $m; }, 0);
        $stuMissing = $students->reduce(function($carry, $u) use ($studentRequired) {
            $m = 0; foreach ($studentRequired as $f) { if ($this->isFieldEmpty($u, $f, 'student_profile')) $m++; } return $carry + $m; }, 0);

        $empCompleteness = $totalEmpFields > 0 ? round((1 - ($empMissing / $totalEmpFields)) * 100, 1) : 0;
        $stuCompleteness = $totalStuFields > 0 ? round((1 - ($stuMissing / $totalStuFields)) * 100, 1) : 0;

        return [
            'employees' => [
                'complete' => $empComplete,
                'total' => $empTotal,
                'percentage' => $empCompleteness,
            ],
            'students' => [
                'complete' => $stuComplete,
                'total' => $stuTotal,
                'percentage' => $stuCompleteness,
            ],
        ];
    }

    /**
     * Hitung KPI absensi sederhana 0-100: 60% ontime rate, 40% coverage rate.
     */
    private function calculateAttendanceKpi($user, string $startDate, string $endDate, array $usage, array $completeness, array $leak, array $weights): array
    {
        $totalRecords = Attendance::whereBetween('date', [$startDate, $endDate])
            ->whereHas('user', function ($q) use ($user) {
                $q->where('school_id', $user->school_id);
            })->count();

        $ontimeRecords = Attendance::whereBetween('date', [$startDate, $endDate])
            ->where('status', 'ontime')
            ->whereHas('user', function ($q) use ($user) {
                $q->where('school_id', $user->school_id);
            })->count();

        $ontimeRate = $totalRecords > 0 ? ($ontimeRecords / $totalRecords) : 0;
        $coverageRate = ($usage['percentage'] ?? 0) / 100;
        $completenessRate = (
            (($completeness['employees']['percentage'] ?? 0) + ($completeness['students']['percentage'] ?? 0)) / 2
        ) / 100;
        $checkoutConsistency = 1 - (($leak['rate'] ?? 0) / 100);

        // Normalisasi bobot
        $wOn = $weights['ontime'] ?? 0.4;
        $wAd = $weights['adoption'] ?? 0.3;
        $wCo = $weights['completeness'] ?? 0.2;
        $wCc = $weights['checkout'] ?? 0.1;
        $wSum = max($wOn + $wAd + $wCo + $wCc, 0.0001);
        $wOn /= $wSum; $wAd /= $wSum; $wCo /= $wSum; $wCc /= $wSum;

        $score = round(($wOn * $ontimeRate + $wAd * $coverageRate + $wCo * $completenessRate + $wCc * $checkoutConsistency) * 100, 1);

        return [
            'score' => $score,
            'ontime_rate' => round($ontimeRate * 100, 1),
            'coverage_rate' => round($coverageRate * 100, 1),
            'completeness_rate' => round($completenessRate * 100, 1),
            'checkout_consistency' => round($checkoutConsistency * 100, 1),
        ];
    }

    /**
     * Hitung leak check-out: record dengan check_in ada dan check_out kosong.
     */
    private function calculateLeakMetrics($user, string $startDate, string $endDate, ?string $roleFilter = null): array
    {
        $totalCheckInsQuery = Attendance::whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('check_in')
            ->whereHas('user', function ($q) use ($user, $roleFilter) {
                $q->where('school_id', $user->school_id);
                if ($roleFilter === 'employee') { $q->where('user_type','employee'); }
                if ($roleFilter === 'student') { $q->where('user_type','student'); }
            });
        $totalCheckIns = $totalCheckInsQuery->count();

        $leaks = Attendance::whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('check_in')
            ->whereNull('check_out')
            ->whereHas('user', function ($q) use ($user, $roleFilter) {
                $q->where('school_id', $user->school_id);
                if ($roleFilter === 'employee') { $q->where('user_type','employee'); }
                if ($roleFilter === 'student') { $q->where('user_type','student'); }
            })
            ->with(['user:id,name'])
            ->orderByDesc('date')
            ->limit(10)
            ->get(['id','user_id','date','check_in']);

        $countLeaks = $leaks->count();
        $rate = $totalCheckIns > 0 ? round(($countLeaks / $totalCheckIns) * 100, 1) : 0;

        return [
            'count' => $countLeaks,
            'total_checkins' => $totalCheckIns,
            'rate' => $rate,
            'samples' => $leaks,
        ];
    }

    /**
     * Daftar user yang tidak pernah melakukan absensi pada periode.
     */
    private function getNonUserList($user, string $startDate, string $endDate, int $limit = 10, ?string $roleFilter = null)
    {
        $targetTypes = ['employee','student'];
        if ($roleFilter === 'employee') { $targetTypes = ['employee']; }
        if ($roleFilter === 'student') { $targetTypes = ['student']; }

        $allUserIds = User::where('school_id', $user->school_id)
            ->whereIn('user_type', $targetTypes)
            ->pluck('id');

        $activeIds = Attendance::whereBetween('date', [$startDate, $endDate])
            ->whereIn('user_id', $allUserIds)
            ->distinct('user_id')
            ->pluck('user_id');

        $nonActiveIds = $allUserIds->diff($activeIds)->take($limit);
        return User::whereIn('id', $nonActiveIds)
            ->whereDoesntHave('roles', function($q){ $q->where('name','super-admin'); })
            ->get(['id','name','user_type']);
    }

    /**
     * Ambil konfigurasi dashboard dari TenantSetting.custom_fields
     */
    private function getTenantDashboardConfig(array $customFields): array
    {
        $weights = $customFields['kpi_weights'] ?? [
            'ontime' => 0.4,
            'adoption' => 0.3,
            'completeness' => 0.2,
            'checkout' => 0.1,
        ];

        $thresholds = $customFields['metric_thresholds'] ?? [
            'good' => 90,
            'warn' => 75,
        ];

        return [
            'weights' => $weights,
            'thresholds' => $thresholds,
        ];
    }

    /**
     * Grafik: Tren Kehadiran (rate) vs jumlah terlambat per hari.
     */
    private function chartTrendAttendanceVsLate($user, string $startDate, string $endDate): array
    {
        $dates = $this->generateDateRange($startDate, $endDate);
        $labels = []; $rate = []; $late = [];
        foreach ($dates as $d) {
            $labels[] = $d->format('d M');
            $total = Attendance::whereDate('date', $d)
                ->whereHas('user', fn($q)=>$q->where('school_id',$user->school_id))
                ->count();
            $present = Attendance::whereDate('date', $d)
                ->whereHas('user', fn($q)=>$q->where('school_id',$user->school_id))
                ->whereIn('status', ['ontime','late'])
                ->count();
            $lateCount = Attendance::whereDate('date', $d)
                ->whereHas('user', fn($q)=>$q->where('school_id',$user->school_id))
                ->where('status','late')->count();
            $rate[] = $total > 0 ? round(($present/$total)*100,1) : 0;
            $late[] = $lateCount;
        }
        return compact('labels','rate','late');
    }

    /**
     * Grafik: Adoption mingguan per user_type.
     */
    private function chartWeeklyAdoptionByUserType($user, string $startDate, string $endDate): array
    {
        $weeks = $this->generateWeekBuckets($startDate, $endDate);
        $labels = []; $employee = []; $student = [];
        foreach ($weeks as [$ws,$we,$label]) {
            $labels[] = $label;
            $ids = Attendance::whereBetween('date', [$ws, $we])
                ->whereHas('user', fn($q)=>$q->where('school_id',$user->school_id))
                ->distinct('user_id')->pluck('user_id');
            $employee[] = User::whereIn('id',$ids)->where('user_type','employee')->count();
            $student[] = User::whereIn('id',$ids)->where('user_type','student')->count();
        }
        return compact('labels','employee','student');
    }

    /**
     * Grafik: Missed checkout mingguan.
     */
    private function chartWeeklyMissedCheckout($user, string $startDate, string $endDate): array
    {
        $weeks = $this->generateWeekBuckets($startDate, $endDate);
        $labels = []; $missed = [];
        foreach ($weeks as [$ws,$we,$label]) {
            $labels[] = $label;
            $count = Attendance::whereBetween('date', [$ws, $we])
                ->whereHas('user', fn($q)=>$q->where('school_id',$user->school_id))
                ->whereNotNull('check_in')->whereNull('check_out')->count();
            $missed[] = $count;
        }
        return compact('labels','missed');
    }

    /**
     * Grafik: alasan ketidakhadiran (permit/sick/alpha) per minggu.
     */
    private function chartWeeklyAbsenceReasons($user, string $startDate, string $endDate): array
    {
        $weeks = $this->generateWeekBuckets($startDate, $endDate);
        $labels = []; $permit=[]; $sick=[]; $alpha=[];
        foreach ($weeks as [$ws,$we,$label]) {
            $labels[] = $label;
            $base = Attendance::whereBetween('date', [$ws, $we])
                ->whereHas('user', fn($q)=>$q->where('school_id',$user->school_id)->where('user_type','student'));
            $permit[] = (clone $base)->where('status','permit')->count();
            $sick[] = (clone $base)->where('status','sick')->count();
            $alpha[] = (clone $base)->where('status','alpha')->count();
        }
        return compact('labels','permit','sick','alpha');
    }

    /**
     * Grafik: Top N siswa terlambat (periode).
     */
    private function chartTopLateStudents($user, string $startDate, string $endDate, int $limit=10): array
    {
        $late = Attendance::select('user_id')
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status','late')
            ->whereHas('user', fn($q)=>$q->where('school_id',$user->school_id)->where('user_type','student'))
            ->groupBy('user_id')
            ->selectRaw('user_id, COUNT(*) as c')
            ->orderByDesc('c')
            ->limit($limit)
            ->get();
        $labels = $late->map(fn($r)=> optional(User::find($r->user_id))->name ?? 'Siswa')->toArray();
        $values = $late->pluck('c')->toArray();
        return compact('labels','values');
    }

    /** Helpers untuk bucket tanggal **/
    private function generateDateRange(string $start, string $end): array
    {
        $dates=[]; $s=Carbon::parse($start)->startOfDay(); $e=Carbon::parse($end)->startOfDay();
        for ($d=$s->copy(); $d->lte($e); $d->addDay()) { $dates[] = $d->copy(); }
        return $dates;
    }

    private function generateWeekBuckets(string $start, string $end): array
    {
        $buckets=[]; $s=Carbon::parse($start)->startOfWeek(); $e=Carbon::parse($end)->endOfWeek();
        for ($w=$s->copy(); $w->lte($e); $w->addWeek()) {
            $ws=$w->copy()->startOfWeek(); $we=$w->copy()->endOfWeek();
            $label=$ws->format('d M').'–'.$we->format('d M');
            $buckets[] = [$ws->format('Y-m-d'), $we->format('Y-m-d'), $label];
        }
        return $buckets;
    }

    /**
     * Helper: cek kosongnya field, mendukung notasi relasi: employee_profile.field atau student_profile.field
     */
    private function isFieldEmpty($user, string $field, string $profileRelationPrefix): bool
    {
        if (strpos($field, '.') === false) {
            return empty($user->{$field});
        }
        [$rel, $attr] = explode('.', $field, 2);
        if ($rel === 'employee_profile' && method_exists($user, 'employeeProfile')) {
            return empty(optional($user->employeeProfile)->{$attr});
        }
        if ($rel === 'student_profile' && method_exists($user, 'studentProfile')) {
            return empty(optional($user->studentProfile)->{$attr});
        }
        return true;
    }

    /**
     * Daftar profil tidak lengkap (gabungan pegawai & siswa) hingga limit.
     */
    private function getIncompleteProfiles($user, int $limit = 10)
    {
        $defaultEmployeeRequired = [
            'phone', 'address', 'nik',
            'employee_profile.nuptk',
            'employee_profile.ptk_type',
            'employee_profile.employment_status',
            'employee_profile.address_line',
            'employee_profile.npwp',
            'employee_profile.bank_name',
            'employee_profile.bank_account',
            'employee_profile.certification_number',
            'employee_profile.certification_year',
            'employee_profile.main_subject',
            'employee_profile.teaching_hours_per_week',
        ];
        $defaultStudentRequired = [
            'nis', 'nisn', 'phone',
            'student_profile.birth_certificate_number',
            'student_profile.kk_number',
            'student_profile.kip_number',
            'student_profile.citizenship',
            'student_profile.residence_type',
            'student_profile.sibling_count',
            'student_profile.order_in_family',
            'student_profile.special_needs',
            'student_profile.blood_type',
        ];
        $tenantCustom = $user->school?->tenantSettings?->custom_fields ?? [];
        $employeeRequired = $tenantCustom['required_fields']['employee'] ?? $defaultEmployeeRequired;
        $studentRequired = $tenantCustom['required_fields']['student'] ?? $defaultStudentRequired;

        $superAdminEmails = \App\Models\SuperAdmin::pluck('email');

        $employees = User::with('employeeProfile')
            ->where('school_id', $user->school_id)
            ->where('user_type', 'employee')
            ->whereDoesntHave('roles', function($q){ $q->where('name','super-admin'); })
            ->whereNotIn('email', $superAdminEmails)
            ->get(['id','name','user_type','phone','address','nik']);
        $students = User::with('studentProfile')
            ->where('school_id', $user->school_id)
            ->where('user_type', 'student')
            ->whereDoesntHave('roles', function($q){ $q->where('name','super-admin'); })
            ->whereNotIn('email', $superAdminEmails)
            ->get(['id','name','user_type','nis','nisn','phone']);

        $badEmp = $employees->map(function ($u) use ($employeeRequired) {
            $missing = [];
            foreach ($employeeRequired as $f) { if ($this->isFieldEmpty($u, $f, 'employee_profile')) $missing[] = $f; }
            $u->missing_fields = $missing; $u->missing_count = count($missing); return $u;
        })->filter(function($u){ return $u->missing_count > 0; });

        $badStu = $students->map(function ($u) use ($studentRequired) {
            $missing = [];
            foreach ($studentRequired as $f) { if ($this->isFieldEmpty($u, $f, 'student_profile')) $missing[] = $f; }
            $u->missing_fields = $missing; $u->missing_count = count($missing); return $u;
        })->filter(function($u){ return $u->missing_count > 0; });

        // Filter defensif terakhir: buang nama yang sama persis "Super Admin"
        $merged = $badEmp->merge($badStu)
            ->reject(function($u){ return trim(strtolower($u->name)) === 'super admin'; })
            ->sortByDesc('missing_count')
            ->values();
        return $merged->take($limit);
    }

    /**
     * Export CSV sederhana untuk daftar tertentu.
     */
    public function export()
    {
        $user = Auth::user();
        $type = request('type'); // leak | non_users | incomplete
        $startDate = request('start') ? Carbon::parse(request('start'))->format('Y-m-d') : Carbon::now('Asia/Jakarta')->startOfMonth()->format('Y-m-d');
        $endDate = request('end') ? Carbon::parse(request('end'))->format('Y-m-d') : Carbon::now('Asia/Jakarta')->endOfDay()->format('Y-m-d');

        $rows = [];
        if ($type === 'leak') {
            $leak = $this->calculateLeakMetrics($user, $startDate, $endDate);
            $rows[] = ['Nama', 'Tanggal', 'Check In'];
            foreach ($leak['samples'] as $rec) {
                $rows[] = [$rec->user->name, (string)$rec->date, optional($rec->check_in)->format('H:i')];
            }
        } elseif ($type === 'non_users') {
            $list = $this->getNonUserList($user, $startDate, $endDate, 1000);
            $rows[] = ['Nama', 'Tipe'];
            foreach ($list as $u) {
                $rows[] = [$u->name, $u->user_type];
            }
        } elseif ($type === 'incomplete') {
            $list = $this->getIncompleteProfiles($user, 1000);
            $rows[] = ['Nama', 'Tipe'];
            foreach ($list as $u) {
                $rows[] = [$u->name, $u->user_type];
            }
        } else {
            abort(400, 'Jenis export tidak dikenal');
        }

        $callback = function () use ($rows) {
            $FH = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($FH, $row);
            }
            fclose($FH);
        };

        $filename = 'dashboard-' . $type . '-' . now()->format('Ymd-His') . '.csv';
        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
