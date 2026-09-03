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

            // Calculate late attendance KPI
            $lateAttendanceKpi = $this->calculateLateAttendanceKpi($user, $startDate, $endDate);

            $metrics = [
                'usage' => $usage,
                'completeness' => $completeness,
                'kpi' => $kpi,
                'leak' => $leak,
                'late_attendance' => $lateAttendanceKpi,
                'non_users' => $this->getNonUserList($user, $startDate, $endDate, 10, $roleFilter),
                'incomplete_profiles' => $this->getIncompleteProfiles($user, 10),
                'thresholds' => $tenantConfig['thresholds'],
                'role_filter' => $roleFilter ?? 'all',
            ];
        }
        // 3 Panel Aktivitas Dashboard
        $lateToday = $this->getLateToday($user);
        $lateUserIds = $lateToday->pluck('user_id')->toArray();
        $onLeaveToday = $this->getOnLeaveToday($user);
        $recentAttendanceFeed = $this->getRecentAttendanceFeed($user, $lateUserIds);

        // Get recent activities
        $recentActivities = $this->getRecentActivities($user);
        
        // Get attendance chart data
        $attendanceChart = $this->getAttendanceChartData($user, $today);
        
        return view('dashboard', compact(
            'user', 'school', 'stats', 'recentActivities', 'attendanceChart', 'metrics',
            'startDate', 'endDate', 'lateToday', 'onLeaveToday', 'recentAttendanceFeed'
        ));
    }

    /**
     * Endpoint for live real-time attendance feed polling (no page refresh).
     */
    public function liveFeed(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $lateToday = $this->getLateToday($user);
        $lateUserIds = $lateToday->pluck('user_id')->filter()->unique()->values()->all();
        $recentAttendanceFeed = $this->getRecentAttendanceFeed($user, $lateUserIds);
        $onLeaveToday = $this->getOnLeaveToday($user);

        return response()->json([
            'status' => 'success',
            'feed' => $recentAttendanceFeed,
            'late_today' => $lateToday,
            'late_count' => $lateToday->count(),
            'on_leave_today' => $onLeaveToday,
            'on_leave_count' => $onLeaveToday->count(),
            'timestamp' => now()->timestamp,
        ]);
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
                    ->whereDoesntHave('roles', function($q){ $q->whereIn('name', ['super-admin', 'admin']); })
                    ->count(),
                'total_students' => User::where('user_type', 'student')->where('school_id', $user->school_id)
                    ->whereDoesntHave('roles', function($q){ $q->where('name','super-admin'); })
                    ->count(),
                'total_classes' => SchoolClass::where('school_id', $user->school_id)->count(),
                // Absensi hari ini (distinct user) untuk persentase
                'today_attendance' => Attendance::where('date', $today)->whereHas('user', function($query) use ($user) {
                    $query->where('school_id', $user->school_id)
                          ->whereDoesntHave('roles', function($q){ $q->whereIn('name', ['super-admin', 'admin']); });
                })->distinct('user_id')->count('user_id'),
                'today_total_users' => User::where('school_id', $user->school_id)->whereIn('user_type', ['employee','student'])
                    ->whereDoesntHave('roles', function($q){ $q->whereIn('name', ['super-admin', 'admin']); })
                    ->count(),
                'today_attendance_percent' => 0, // diisi di bawah
                'pending_leaves' => LeaveRequest::where('status', 'pending')->whereHas('user', function($query) use ($user) {
                    $query->where('school_id', $user->school_id)
                          ->whereDoesntHave('roles', function($q){ $q->whereIn('name', ['super-admin', 'admin']); });
                })->count(),
                'pending_leaves_total' => LeaveRequest::whereHas('user', function($query) use ($user) {
                    $query->where('school_id', $user->school_id)
                          ->whereDoesntHave('roles', function($q){ $q->whereIn('name', ['super-admin', 'admin']); });
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
                    ->whereDoesntHave('roles', function($q){ $q->whereIn('name', ['super-admin', 'admin']); })
                    ->count(),
                'total_students' => User::where('user_type', 'student')->where('school_id', $user->school_id)
                    ->whereDoesntHave('roles', function($q){ $q->where('name','super-admin'); })
                    ->count(),
                'total_classes' => SchoolClass::where('school_id', $user->school_id)->count(),
                'today_attendance' => Attendance::where('date', $today)->whereHas('user', function($query) use ($user) {
                    $query->where('school_id', $user->school_id)
                          ->whereDoesntHave('roles', function($q){ $q->whereIn('name', ['super-admin', 'admin']); });
                })->distinct('user_id')->count('user_id'),
                'today_total_users' => User::where('school_id', $user->school_id)->whereIn('user_type', ['employee','student'])
                    ->whereDoesntHave('roles', function($q){ $q->whereIn('name', ['super-admin', 'admin']); })
                    ->count(),
                'today_attendance_percent' => 0,
                'pending_leaves' => LeaveRequest::where('status', 'pending')->whereHas('user', function($query) use ($user) {
                    $query->where('school_id', $user->school_id)
                          ->whereDoesntHave('roles', function($q){ $q->whereIn('name', ['super-admin', 'admin']); });
                })->count(),
                'pending_leaves_total' => LeaveRequest::whereHas('user', function($query) use ($user) {
                    $query->where('school_id', $user->school_id)
                          ->whereDoesntHave('roles', function($q){ $q->whereIn('name', ['super-admin', 'admin']); });
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
        $today = Carbon::today('Asia/Jakarta')->format('Y-m-d');
        
        // 1. Status Keterlambatan Hari Ini
        $todayAttendanceCount = Attendance::where('date', $today)
            ->whereHas('user', function($q) use ($user) {
                $q->where('school_id', $user->school_id);
            })
            ->count();

        $todayLateCount = Attendance::where('date', $today)
            ->whereHas('user', function($q) use ($user) {
                $q->where('school_id', $user->school_id);
            })
            ->where('status', 'late')
            ->count();

        if ($todayAttendanceCount > 0 && $todayLateCount > 0) {
            $activities->push([
                'type' => 'late',
                'message' => "{$todayLateCount} absensi terlambat hari ini",
                'icon' => 'clock',
                'color' => 'text-warning',
            ]);
        } else {
            $activities->push([
                'type' => 'ontime',
                'message' => 'Tidak ada absensi terlambat hari ini — semua tepat waktu',
                'icon' => 'check-circle',
                'color' => 'text-success',
            ]);
        }

        // 2. Siswa / Pegawai Baru Ditambahkan
        $newStudentsToday = User::where('school_id', $user->school_id)
            ->where('user_type', 'student')
            ->whereDate('created_at', $today)
            ->count();

        if ($newStudentsToday > 0) {
            $activities->push([
                'type' => 'new_student',
                'message' => "{$newStudentsToday} siswa baru ditambahkan hari ini",
                'icon' => 'user-plus',
                'color' => 'text-info',
            ]);
        } else {
            $newStudentsRecent = User::where('school_id', $user->school_id)
                ->where('user_type', 'student')
                ->where('created_at', '>=', now('Asia/Jakarta')->subDays(7))
                ->count();
            if ($newStudentsRecent > 0) {
                $activities->push([
                    'type' => 'new_student',
                    'message' => "{$newStudentsRecent} siswa baru ditambahkan minggu ini",
                    'icon' => 'user-plus',
                    'color' => 'text-info',
                ]);
            }
        }

        // 3. Permohonan Izin Menunggu Persetujuan
        $pendingLeaves = LeaveRequest::where('status', 'pending')
            ->whereHas('user', function($q) use ($user) {
                $q->where('school_id', $user->school_id);
            })
            ->count();

        if ($pendingLeaves > 0) {
            $activities->push([
                'type' => 'leave',
                'message' => "{$pendingLeaves} permohonan izin menunggu persetujuan",
                'icon' => 'file-text',
                'color' => 'text-warning',
            ]);
        }

        return $activities;
    }

    /**
     * Dapatkan daftar pengguna yang terlambat hari ini.
     */
    private function getLateToday($user): \Illuminate\Support\Collection
    {
        $today = Carbon::today('Asia/Jakarta')->format('Y-m-d');
        $setting = \App\Models\AttendanceSetting::where('school_id', $user->school_id)->first();
        $defaultMaxTime = $setting?->check_in_time ? Carbon::parse($setting->check_in_time)->format('H:i') : '07:00';

        $todayAttendances = Attendance::with(['user.roles', 'user.studentClasses'])
            ->where('date', $today)
            ->whereHas('user', function($q) use ($user) {
                $q->where('school_id', $user->school_id);
            })
            ->whereNotNull('check_in')
            ->get();

        $lateList = collect();

        foreach ($todayAttendances as $att) {
            if (!$att->user) continue;

            $targetMaxTime = $defaultMaxTime;
            if ($att->user->user_type === 'employee') {
                if ($att->user->hasRole('teacher') && !empty($setting?->teacher_max_time)) {
                    $targetMaxTime = Carbon::parse($setting->teacher_max_time)->format('H:i');
                } elseif (!empty($setting?->other_roles_max_time)) {
                    $targetMaxTime = Carbon::parse($setting->other_roles_max_time)->format('H:i');
                }
            } elseif ($att->user->user_type === 'student') {
                if (!empty($setting?->student_max_time)) {
                    $targetMaxTime = Carbon::parse($setting->student_max_time)->format('H:i');
                }
            }

            $checkInCarbon = Carbon::parse($att->check_in);
            $limitCarbon = Carbon::parse($att->date->format('Y-m-d') . ' ' . $targetMaxTime);

            if ($checkInCarbon->gt($limitCarbon) || $att->status === 'late') {
                $diffMinutes = max(1, $limitCarbon->diffInMinutes($checkInCarbon, false));
                $hours = floor($diffMinutes / 60);
                $mins = $diffMinutes % 60;
                $lateDuration = $hours > 0 ? "{$hours}j {$mins}m" : "{$mins}m";

                $sub = '';
                if ($att->user->user_type === 'student') {
                    $cls = $att->user->studentClasses->first();
                    $sub = $cls ? $cls->name : 'Siswa';
                } else {
                    $roles = $att->user->roles->pluck('name')->map(fn($r) => ucfirst($r))->join(', ');
                    $sub = $roles ?: 'Pegawai';
                }

                $lateList->push([
                    'user_id' => $att->user_id,
                    'name' => $att->user->name,
                    'subtitle' => $sub,
                    'avatar' => $att->user->avatar ?? null,
                    'initials' => strtoupper(substr($att->user->name, 0, 2)),
                    'check_in_time' => $checkInCarbon->format('H:i'),
                    'late_duration' => $lateDuration,
                ]);
            }
        }

        return $lateList;
    }

    /**
     * Dapatkan daftar pegawai/siswa yang sedang dalam masa izin/cuti aktif hari ini.
     */
    private function getOnLeaveToday($user): \Illuminate\Support\Collection
    {
        $today = Carbon::today('Asia/Jakarta')->format('Y-m-d');

        $activeLeaves = LeaveRequest::with(['user.roles', 'user.studentClasses'])
            ->where('school_id', $user->school_id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->orderBy('start_date', 'desc')
            ->get();

        return $activeLeaves->map(function($leave) {
            $u = $leave->user;
            $sub = '';
            if ($u) {
                if ($u->user_type === 'student') {
                    $cls = $u->studentClasses->first();
                    $sub = $cls ? $cls->name : 'Siswa';
                } else {
                    $roles = $u->roles->pluck('name')->map(fn($r) => ucfirst($r))->join(', ');
                    $sub = $roles ?: 'Pegawai';
                }
            }

            $badgeClass = match($leave->type) {
                'sick' => 'badge-warning',
                'duty' => 'badge-info',
                'leave' => 'badge-primary',
                default => 'badge-neutral',
            };

            $typeLabel = match($leave->type) {
                'sick' => 'Sakit',
                'duty' => 'Dinas Luar',
                'leave' => 'Cuti',
                default => ucfirst($leave->type),
            };

            $startDateStr = $leave->start_date ? Carbon::parse($leave->start_date)->translatedFormat('d M') : '';
            $endDateStr = $leave->end_date ? Carbon::parse($leave->end_date)->translatedFormat('d M') : '';
            $dateRange = $startDateStr === $endDateStr ? $startDateStr : "{$startDateStr} - {$endDateStr}";

            return [
                'name' => $u ? $u->name : 'User',
                'subtitle' => $sub,
                'avatar' => $u?->avatar ?? null,
                'initials' => strtoupper(substr($u?->name ?? 'US', 0, 2)),
                'type_label' => $typeLabel,
                'badge_class' => $badgeClass,
                'date_range' => $dateRange,
            ];
        });
    }

    /**
     * Dapatkan feed absensi terbaru (real-time, maksimal 10), tidak redundan dengan terlambat.
     */
    private function getRecentAttendanceFeed($user, array $excludeUserIds = []): \Illuminate\Support\Collection
    {
        $query = Attendance::with(['user.roles', 'user.studentClasses'])
            ->whereHas('user', function($q) use ($user) {
                $q->where('school_id', $user->school_id);
            });

        if (!empty($excludeUserIds)) {
            $query->whereNotIn('user_id', $excludeUserIds);
        }

        $attendances = $query->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return $attendances->map(function($att) {
            $u = $att->user;
            $isCheckOut = !empty($att->check_out) && $att->check_out > $att->check_in;
            $eventTime = $isCheckOut ? Carbon::parse($att->check_out) : ($att->check_in ? Carbon::parse($att->check_in) : Carbon::parse($att->created_at));
            $eventType = $isCheckOut ? 'Absen keluar' : 'Absen masuk';
            $location = $att->location_name ?: ($att->latitude && $att->longitude ? round($att->latitude, 4) . ', ' . round($att->longitude, 4) : null);

            return [
                'name' => $u ? $u->name : 'User',
                'avatar' => $u?->avatar ?? null,
                'initials' => strtoupper(substr($u?->name ?? 'US', 0, 2)),
                'event_type' => $eventType,
                'time_str' => $eventTime->timezone('Asia/Jakarta')->format('H:i'),
                'relative_time' => $eventTime->timezone('Asia/Jakarta')->diffForHumans(),
                'location' => $location,
            ];
        });
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
     * Mengecualikan hari libur dari perhitungan.
     */
    private function calculateUsageMetrics($user, string $startDate, string $endDate, ?string $roleFilter = null): array
    {
        // Dapatkan daftar hari libur dalam periode
        $holidays = \App\Models\HolidaySchedule::where('school_id', $user->school_id)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('is_active', true)
            ->pluck('date')
            ->toArray();

        $targetTypes = ['employee','student'];
        if ($roleFilter === 'employee') { $targetTypes = ['employee']; }
        if ($roleFilter === 'student') { $targetTypes = ['student']; }

        $totalUsers = User::where('school_id', $user->school_id)
            ->whereIn('user_type', $targetTypes)
            ->whereDoesntHave('roles', function($q){ $q->whereIn('name', ['super-admin', 'admin']); })
            ->count();

        $activeUserIds = Attendance::whereBetween('date', [$startDate, $endDate])
            ->whereNotIn('date', $holidays) // Exclude holidays
            ->whereHas('user', function ($q) use ($user) {
                $q->where('school_id', $user->school_id)
                  ->whereDoesntHave('roles', function($r){ $r->whereIn('name', ['super-admin', 'admin']); })
                  ->where('email', 'not like', 'superadmin@%');
            })
            ->distinct('user_id')
            ->pluck('user_id');

        // Breakdown per role
        $employeeTotal = User::where('school_id', $user->school_id)->where('user_type', 'employee')
            ->whereDoesntHave('roles', function($q){ $q->whereIn('name', ['super-admin', 'admin']); })
            ->where('email', 'not like', 'superadmin@%')
            ->count();
        $studentTotal = User::where('school_id', $user->school_id)->where('user_type', 'student')
            ->whereDoesntHave('roles', function($q){ $q->where('name','super-admin'); })
            ->where('email', 'not like', 'superadmin@%')
            ->count();

        $activeEmployee = User::whereIn('id', $activeUserIds)->where('user_type','employee')
            ->whereDoesntHave('roles', function($q){ $q->whereIn('name', ['super-admin', 'admin']); })
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
     * Mengecualikan hari libur dari perhitungan KPI.
     */
    private function calculateAttendanceKpi($user, string $startDate, string $endDate, array $usage, array $completeness, array $leak, array $weights): array
    {
        // Dapatkan daftar hari libur dalam periode dengan caching
        $holidays = \App\Models\HolidaySchedule::where('school_id', $user->school_id)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('is_active', true)
            ->pluck('date')
            ->toArray();

        // Hitung total hari kerja (exclude holidays)
        $workingDays = $this->getWorkingDaysInPeriod($startDate, $endDate, $holidays);
        
        // Optimize queries with single query for both counts
        $attendanceStats = Attendance::whereBetween('date', [$startDate, $endDate])
            ->whereNotIn('date', $holidays) // Exclude holidays
            ->whereHas('user', function ($q) use ($user) {
                $q->where('school_id', $user->school_id);
            })
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status = "ontime" THEN 1 ELSE 0 END) as ontime')
            ->first();

        $totalRecords = $attendanceStats->total ?? 0;
        $ontimeRecords = $attendanceStats->ontime ?? 0;

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
            'working_days' => $workingDays,
            'holidays_count' => count($holidays),
        ];
    }

    /**
     * Hitung leak check-out: record dengan check_in ada dan check_out kosong.
     * Mengecualikan hari libur dari perhitungan.
     */
    private function calculateLeakMetrics($user, string $startDate, string $endDate, ?string $roleFilter = null): array
    {
        // Dapatkan daftar hari libur dalam periode
        $holidays = \App\Models\HolidaySchedule::where('school_id', $user->school_id)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('is_active', true)
            ->pluck('date')
            ->toArray();

        $totalCheckInsQuery = Attendance::whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('check_in')
            ->whereNotIn('date', $holidays) // Exclude holidays
            ->whereHas('user', function ($q) use ($user, $roleFilter) {
                $q->where('school_id', $user->school_id);
                if ($roleFilter === 'employee') { $q->where('user_type','employee'); }
                if ($roleFilter === 'student') { $q->where('user_type','student'); }
            });
        $totalCheckIns = $totalCheckInsQuery->count();

        $leaks = Attendance::whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('check_in')
            ->whereNull('check_out')
            ->whereNotIn('date', $holidays) // Exclude holidays
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
     * Check if user is admin or super admin
     */
    private function isAdminUser($user): bool
    {
        // Check by roles
        if ($user->hasRole(['admin', 'super-admin'])) {
            return true;
        }
        
        // Check by name patterns
        $adminNames = ['Super Administrator', 'Super Admin', 'Administrator', 'Admin'];
        foreach ($adminNames as $adminName) {
            if (stripos($user->name, $adminName) !== false) {
                return true;
            }
        }
        
        // Check by email patterns
        $adminEmails = ['admin@', 'superadmin@', 'super@'];
        foreach ($adminEmails as $emailPattern) {
            if (stripos($user->email, $emailPattern) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Daftar user yang tidak pernah melakukan absensi pada periode.
     * Mengecualikan hari libur dari perhitungan.
     */
    private function getNonUserList($user, string $startDate, string $endDate, int $limit = 10, ?string $roleFilter = null)
    {
        // Dapatkan daftar hari libur dalam periode
        $holidays = \App\Models\HolidaySchedule::where('school_id', $user->school_id)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('is_active', true)
            ->pluck('date')
            ->toArray();

        $targetTypes = ['employee','student'];
        if ($roleFilter === 'employee') { $targetTypes = ['employee']; }
        if ($roleFilter === 'student') { $targetTypes = ['student']; }

        $superAdminEmails = \App\Models\SuperAdmin::pluck('email');
        
        $allUserIds = User::where('school_id', $user->school_id)
            ->whereIn('user_type', $targetTypes)
            ->whereDoesntHave('roles', function($q){ $q->whereIn('name',['super-admin','admin']); })
            ->whereNotIn('email', $superAdminEmails)
            ->where('email', 'not like', 'superadmin@%')
            ->where('name', 'not like', '%Super Administrator%')
            ->where('name', 'not like', '%Super Admin%')
            ->where('name', 'not like', '%Administrator%')
            ->where('name', 'not like', '%Admin%')
            ->where('email', 'not like', '%admin%')
            ->where('email', 'not like', '%super%')
            ->where('name', 'not regexp', '^Siswa [0-9]+$')
            ->where('email', 'not like', '%@presensia.com')
            ->pluck('id');

        $activeIds = Attendance::whereBetween('date', [$startDate, $endDate])
            ->whereNotIn('date', $holidays) // Exclude holidays
            ->whereIn('user_id', $allUserIds)
            ->distinct('user_id')
            ->pluck('user_id');

        $nonActiveIds = $allUserIds->diff($activeIds)->take($limit);
        $nonActiveUsers = User::whereIn('id', $nonActiveIds)
            ->get(['id','name','user_type']);
            
        // Filter out admin users using the helper method
        return $nonActiveUsers->filter(function($user) {
            return !$this->isAdminUser($user);
        })->values();
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
     * Hitung total hari kerja dalam periode (exclude holidays)
     */
    private function getWorkingDaysInPeriod(string $startDate, string $endDate, array $holidays): int
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $workingDays = 0;
        
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            // Skip weekends (Saturday = 6, Sunday = 0)
            if ($date->dayOfWeek == 0 || $date->dayOfWeek == 6) {
                continue;
            }
            
            // Skip holidays
            if (in_array($date->format('Y-m-d'), $holidays)) {
                continue;
            }
            
            $workingDays++;
        }
        
        return $workingDays;
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
            ->whereDoesntHave('roles', function($q){ $q->whereIn('name',['super-admin','admin']); })
            ->whereNotIn('email', $superAdminEmails)
            ->where('email', 'not like', 'superadmin@%')
            ->where('name', 'not like', '%Super Administrator%')
            ->where('name', 'not like', '%Super Admin%')
            ->where('name', 'not like', '%Administrator%')
            ->where('name', 'not like', '%Admin%')
            ->where('email', 'not like', '%admin%')
            ->where('email', 'not like', '%super%')
            ->get(['id','name','user_type','phone','address','nik']);
        $students = User::with('studentProfile')
            ->where('school_id', $user->school_id)
            ->where('user_type', 'student')
            ->whereDoesntHave('roles', function($q){ $q->whereIn('name',['super-admin','admin']); })
            ->whereNotIn('email', $superAdminEmails)
            ->where('email', 'not like', 'superadmin@%')
            ->where('name', 'not like', '%Super Administrator%')
            ->where('name', 'not like', '%Super Admin%')
            ->where('name', 'not like', '%Administrator%')
            ->where('name', 'not like', '%Admin%')
            ->where('email', 'not like', '%admin%')
            ->where('email', 'not like', '%super%')
            ->get(['id','name','user_type','nis','nisn','phone']);

        $badEmp = $employees->map(function ($u) use ($employeeRequired) {
            $missing = [];
            foreach ($employeeRequired as $f) { if ($this->isFieldEmpty($u, $f, 'employee_profile')) $missing[] = $f; }
            $u->missing_fields = $missing; $u->missing_count = count($missing); return $u;
        })->filter(function($u){ 
            return $u->missing_count > 0 && !$this->isAdminUser($u); 
        });

        $badStu = $students->map(function ($u) use ($studentRequired) {
            $missing = [];
            foreach ($studentRequired as $f) { if ($this->isFieldEmpty($u, $f, 'student_profile')) $missing[] = $f; }
            $u->missing_fields = $missing; $u->missing_count = count($missing); return $u;
        })->filter(function($u){ 
            return $u->missing_count > 0 && !$this->isAdminUser($u); 
        });

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
            $rows[] = ['Nama', 'Tipe', 'Kolom Kosong'];
            foreach ($list as $u) {
                $rows[] = [$u->name, $u->user_type, $u->missing_count ?? 0];
            }
        } elseif ($type === 'all' || empty($type)) {
            $rows[] = ['Kategori Masalah', 'Nama', 'Tipe', 'Detail'];
            $incomplete = $this->getIncompleteProfiles($user, 1000);
            foreach ($incomplete as $u) {
                $rows[] = ['Profil Kosong', $u->name, $u->user_type, ($u->missing_count ?? 0) . ' kosong'];
            }
            $nonUsers = $this->getNonUserList($user, $startDate, $endDate, 1000);
            foreach ($nonUsers as $u) {
                $rows[] = ['Tidak Aktif', $u->name, $u->user_type, 'Tidak aktif'];
            }
            $leak = $this->calculateLeakMetrics($user, $startDate, $endDate);
            foreach ($leak['samples'] as $rec) {
                $rows[] = ['Leak Absensi', $rec->user->name, $rec->user->user_type, 'Date: ' . $rec->date . ' Check In: ' . optional($rec->check_in)->format('H:i')];
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

    /**
     * Calculate late attendance KPI with detailed information
     */
    private function calculateLateAttendanceKpi($user, string $startDate, string $endDate): array
    {
        // Get holidays to exclude from calculation
        $holidays = \App\Models\HolidaySchedule::where('school_id', $user->school_id)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('is_active', true)
            ->pluck('date')
            ->toArray();

        // Get total working days
        $workingDays = $this->getWorkingDaysInPeriod($startDate, $endDate, $holidays);

        // Get late attendance records with user details
        $lateAttendances = \App\Models\Attendance::whereBetween('date', [$startDate, $endDate])
            ->where('status', 'late')
            ->whereNotIn('date', $holidays) // Exclude holidays
            ->whereHas('user', function ($q) use ($user) {
                $q->where('school_id', $user->school_id);
            })
            ->with(['user.roles'])
            ->orderBy('date', 'desc')
            ->orderBy('check_in', 'desc')
            ->get();

        // Get total attendance records for percentage calculation
        $totalAttendances = \App\Models\Attendance::whereBetween('date', [$startDate, $endDate])
            ->whereNotIn('date', $holidays) // Exclude holidays
            ->whereHas('user', function ($q) use ($user) {
                $q->where('school_id', $user->school_id);
            })
            ->count();

        // Calculate statistics
        $lateCount = $lateAttendances->count();
        $latePercentage = $totalAttendances > 0 ? round(($lateCount / $totalAttendances) * 100, 1) : 0;

        // Group by user for detailed analysis
        $lateByUser = $lateAttendances->groupBy('user_id')->map(function ($userAttendances, $userId) {
            $user = $userAttendances->first()->user;
            return [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'roles' => $user->roles->pluck('name')->toArray(),
                ],
                'late_count' => $userAttendances->count(),
                'late_dates' => $userAttendances->pluck('date')->toArray(),
                'latest_late' => $userAttendances->first()->check_in->format('H:i:s'),
            ];
        })->values();

        // Calculate gauge value (0-100, where 100 is worst)
        $gaugeValue = min(100, $latePercentage);

        return [
            'total_late' => $lateCount,
            'total_attendance' => $totalAttendances,
            'late_percentage' => $latePercentage,
            'gauge_value' => $gaugeValue,
            'working_days' => $workingDays,
            'late_by_user' => $lateByUser,
            'recent_late' => $lateAttendances->take(10)->map(function ($attendance) {
                return [
                    'user_name' => $attendance->user->name,
                    'user_roles' => $attendance->user->roles->pluck('name')->toArray(),
                    'date' => $attendance->date,
                    'check_in' => $attendance->check_in->format('H:i:s'),
                    'status' => $attendance->status,
                ];
            })->toArray(),
        ];
    }
}
