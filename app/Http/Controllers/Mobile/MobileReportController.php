<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Attendance;

class MobileReportController extends Controller
{
    /**
     * Get monthly attendance report
     */
    public function monthly(Request $request)
    {
        $user = Auth::user();
        $year = $request->year ?? Carbon::now()->year;
        $month = $request->month ?? Carbon::now()->month;

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'asc')
            ->get();

        // Calculate statistics
        $totalDays = $startDate->diffInDays($endDate) + 1;
        $workingDays = $totalDays; // You can customize this based on your business logic
        $presentDays = $attendances->where('status', '!=', 'alpha')->count();
        $lateDays = $attendances->where('status', 'late')->count();
        $ontimeDays = $attendances->where('status', 'ontime')->count();
        $absentDays = $attendances->where('status', 'alpha')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'month' => $month,
                'year' => $year,
                'statistics' => [
                    'total_days' => $totalDays,
                    'working_days' => $workingDays,
                    'present_days' => $presentDays,
                    'late_days' => $lateDays,
                    'ontime_days' => $ontimeDays,
                    'absent_days' => $absentDays,
                    'attendance_percentage' => $workingDays > 0 ? round(($presentDays / $workingDays) * 100, 2) : 0,
                ],
                'attendances' => $attendances->map(function ($attendance) {
                    return [
                        'id' => $attendance->id,
                        'date' => $attendance->date,
                        'check_in' => $attendance->check_in,
                        'check_out' => $attendance->check_out,
                        'status' => $attendance->status,
                        'status_label' => $attendance->status_label,
                        'location_name' => $attendance->location_name,
                        'photo_url' => $attendance->photo ? asset('storage/' . $attendance->photo) : null,
                        'notes' => $attendance->notes,
                    ];
                })
            ]
        ]);
    }

    /**
     * Export attendance report
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        $startDate = $request->start_date ?? Carbon::now()->startOfMonth();
        $endDate = $request->end_date ?? Carbon::now()->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get();

        // Generate CSV content
        $csvContent = "Date,Check In,Check Out,Status,Location,Notes\n";
        
        foreach ($attendances as $attendance) {
            $csvContent .= sprintf(
                "%s,%s,%s,%s,%s,%s\n",
                $attendance->date,
                $attendance->check_in ?? 'N/A',
                $attendance->check_out ?? 'N/A',
                $attendance->status_label,
                $attendance->location_name ?? 'N/A',
                $attendance->notes ?? 'N/A'
            );
        }

        $filename = 'attendance_report_' . $user->id . '_' . $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d') . '.csv';

        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
