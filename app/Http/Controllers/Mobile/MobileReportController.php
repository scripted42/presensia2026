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
        $year = (int) ($request->year ?? Carbon::now('Asia/Jakarta')->year);
        $month = (int) ($request->month ?? Carbon::now('Asia/Jakarta')->month);

        $startDate = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Jakarta')->startOfMonth();
        $endDate = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Jakarta')->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('date', 'desc')
            ->get();

        // Calculate working days in month (Monday - Friday)
        $daysInMonth = $startDate->daysInMonth;
        $workingDays = 0;
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dt = Carbon::create($year, $month, $d, 0, 0, 0, 'Asia/Jakarta');
            if (!$dt->isWeekend()) {
                $workingDays++;
            }
        }

        // Calculate statistics
        $presentDays = $attendances->whereNotNull('check_in')->count();
        $ontimeDays = $attendances->where('status', 'ontime')->count();
        $lateDays = $attendances->where('status', 'late')->count();
        $sickDays = $attendances->where('status', 'sick')->count();
        $permitDays = $attendances->where('status', 'permit')->count();
        $leaveDays = $attendances->whereIn('status', ['leave', 'duty'])->count();
        $absentDays = $attendances->where('status', 'alpha')->count();

        $attendancePercentage = $workingDays > 0 ? min(100, round(($presentDays / $workingDays) * 100, 1)) : 0;

        // Weekly chart analytics (M1 to M5)
        $weeklyChart = [];
        $weekRanges = [
            ['label' => 'M1', 'start' => 1, 'end' => 7],
            ['label' => 'M2', 'start' => 8, 'end' => 14],
            ['label' => 'M3', 'start' => 15, 'end' => 21],
            ['label' => 'M4', 'start' => 22, 'end' => 28],
            ['label' => 'M5', 'start' => 29, 'end' => $daysInMonth],
        ];

        foreach ($weekRanges as $wr) {
            if ($wr['start'] > $daysInMonth) continue;
            $endDay = min($wr['end'], $daysInMonth);
            $startDateStr = Carbon::create($year, $month, $wr['start'])->format('Y-m-d');
            $endDateStr = Carbon::create($year, $month, $endDay)->format('Y-m-d');

            $weekAttendances = $attendances->filter(function ($att) use ($startDateStr, $endDateStr) {
                $attDate = Carbon::parse($att->date)->format('Y-m-d');
                return $attDate >= $startDateStr && $attDate <= $endDateStr;
            });

            $weekPresent = $weekAttendances->whereNotNull('check_in')->count();
            $weekOntime = $weekAttendances->where('status', 'ontime')->count();
            $weekLate = $weekAttendances->where('status', 'late')->count();

            $weeklyChart[] = [
                'label' => $wr['label'],
                'range' => $wr['start'] . '-' . $endDay,
                'present' => $weekPresent,
                'ontime' => $weekOntime,
                'late' => $weekLate,
                'max_days' => 5,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'month' => $month,
                'year' => $year,
                'statistics' => [
                    'total_days' => $daysInMonth,
                    'working_days' => $workingDays,
                    'total_masuk' => $presentDays,
                    'ontime_days' => $ontimeDays,
                    'late_days' => $lateDays,
                    'sick_days' => $sickDays,
                    'permit_days' => $permitDays,
                    'leave_days' => $leaveDays,
                    'absent_days' => $absentDays,
                    'attendance_percentage' => $attendancePercentage,
                ],
                'weekly_chart' => $weeklyChart,
                'attendances' => $attendances->map(function ($attendance) {
                    $statusLabels = [
                        'ontime' => 'Tepat Waktu',
                        'late' => 'Terlambat',
                        'sick' => 'Sakit',
                        'permit' => 'Izin',
                        'leave' => 'Cuti',
                        'duty' => 'Dinas',
                        'alpha' => 'Alpha',
                    ];

                    $dateCarbon = Carbon::parse($attendance->date)->locale('id');
                    return [
                        'id' => $attendance->id,
                        'date' => $attendance->date,
                        'date_formatted' => $dateCarbon->isoFormat('dddd, D MMMM Y'),
                        'check_in' => $attendance->check_in ? Carbon::parse($attendance->check_in)->format('H:i') : null,
                        'check_out' => $attendance->check_out ? Carbon::parse($attendance->check_out)->format('H:i') : null,
                        'status' => $attendance->status,
                        'status_label' => $statusLabels[$attendance->status] ?? ucfirst($attendance->status),
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






