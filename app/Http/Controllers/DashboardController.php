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
        $school = $user->school;
        $today = Carbon::now('Asia/Jakarta')->format('Y-m-d');
        
        // Get statistics based on user role
        $stats = $this->getDashboardStats($user, $today);
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities($user);
        
        // Get attendance chart data
        $attendanceChart = $this->getAttendanceChartData($user, $today);
        
        return view('dashboard', compact('user', 'school', 'stats', 'recentActivities', 'attendanceChart'));
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
                'total_employees' => User::where('user_type', 'employee')->where('school_id', $user->school_id)->count(),
                'total_students' => User::where('user_type', 'student')->where('school_id', $user->school_id)->count(),
                'total_classes' => SchoolClass::where('school_id', $user->school_id)->count(),
                'today_attendance' => Attendance::where('date', $today)->whereHas('user', function($query) use ($user) {
                    $query->where('school_id', $user->school_id);
                })->count(),
                'pending_leaves' => LeaveRequest::where('status', 'pending')->whereHas('user', function($query) use ($user) {
                    $query->where('school_id', $user->school_id);
                })->count(),
            ];
        } elseif ($user->hasRole('headmaster')) {
            // Headmaster statistics
            $stats = [
                'total_employees' => User::where('user_type', 'employee')->where('school_id', $user->school_id)->count(),
                'total_students' => User::where('user_type', 'student')->where('school_id', $user->school_id)->count(),
                'total_classes' => SchoolClass::where('school_id', $user->school_id)->count(),
                'today_attendance' => Attendance::where('date', $today)->whereHas('user', function($query) use ($user) {
                    $query->where('school_id', $user->school_id);
                })->count(),
                'pending_leaves' => LeaveRequest::where('status', 'pending')->whereHas('user', function($query) use ($user) {
                    $query->where('school_id', $user->school_id);
                })->count(),
                'approved_leaves' => LeaveRequest::where('status', 'approved')->whereHas('user', function($query) use ($user) {
                    $query->where('school_id', $user->school_id);
                })->where('approved_by', $user->id)->count(),
            ];
        } elseif ($user->hasRole('teacher')) {
            // Teacher statistics
            $stats = [
                'my_classes' => $user->taughtClasses()->count(),
                'my_students' => 0, // Simplified for now
                'today_attendance' => Attendance::where('user_id', $user->id)->where('date', $today)->count(),
                'pending_leaves' => LeaveRequest::where('user_id', $user->id)->where('status', 'pending')->count(),
            ];
        } elseif ($user->hasRole('student')) {
            // Student statistics
            $stats = [
                'my_classes' => 0, // Simplified for now
                'today_attendance' => Attendance::where('user_id', $user->id)->where('date', $today)->count(),
                'pending_leaves' => LeaveRequest::where('user_id', $user->id)->where('status', 'pending')->count(),
            ];
        } else {
            // Other roles (TU, BK, Kesiswaan)
            $stats = [
                'total_students' => User::where('user_type', 'student')->where('school_id', $user->school_id)->count(),
                'today_attendance' => Attendance::where('date', $today)->whereHas('user', function($query) use ($user) {
                    $query->where('school_id', $user->school_id);
                })->count(),
                'pending_leaves' => LeaveRequest::where('status', 'pending')->whereHas('user', function($query) use ($user) {
                    $query->where('school_id', $user->school_id);
                })->count(),
            ];
        }
        
        return $stats;
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
}
