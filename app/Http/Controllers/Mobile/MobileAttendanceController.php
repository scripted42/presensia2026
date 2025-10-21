<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\QrCode;

class MobileAttendanceController extends Controller
{
    /**
     * Mobile check-in
     */
    public function checkIn(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::now('Asia/Jakarta');
        $todayFormatted = $today->format('Y-m-d');
        
        // Validasi: Cek apakah hari ini adalah hari libur
        if (\App\Models\HolidaySchedule::isHoliday($today, $user->school_id)) {
            $holidayName = \App\Models\HolidaySchedule::getHolidayName($today, $user->school_id);
            return response()->json([
                'success' => false,
                'message' => "Hari ini adalah {$holidayName}. Absensi tidak diperbolehkan pada hari libur.",
                'error_type' => 'holiday'
            ], 400);
        }
        
        // Validasi: Cek apakah ada check-out kemarin yang belum dilakukan
        $yesterday = $today->copy()->subDay()->format('Y-m-d');
        $yesterdayAttendance = Attendance::where('user_id', $user->id)
            ->where('date', $yesterday)
            ->whereNotNull('check_in')
            ->whereNull('check_out')
            ->first();
            
        if ($yesterdayAttendance) {
            // Jika kemarin belum check-out, tetap boleh check-in hari ini
            // Tapi beri peringatan dan log untuk monitoring
            \Log::warning('Mobile user check-in without previous day checkout', [
                'user_id' => $user->id,
                'yesterday_date' => $yesterday,
                'yesterday_check_in' => $yesterdayAttendance->check_in,
                'current_time' => now()->setTimezone('Asia/Jakarta')
            ]);
        }
        
        // Validate request
        $request->validate([
            'qr_code' => 'nullable|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'location_name' => 'required|string',
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'notes' => 'nullable|string',
        ]);

        // Check if already checked in today
        $existingAttendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan check-in hari ini'
            ], 400);
        }

        // Validate QR code if provided
        if ($request->qr_code) {
            $qrCode = QrCode::where('code', $request->qr_code)
                ->where('school_id', $user->school_id)
                ->where('is_used', false)
                ->where('expires_at', '>', now())
                ->first();

            if (!$qrCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR Code tidak valid atau sudah kedaluwarsa'
                ], 400);
            }

            // Mark QR code as used
            $qrCode->update(['is_used' => true, 'used_at' => now()]);
        }

        // Validate location if required
        $settings = AttendanceSetting::where('school_id', $user->school_id)
            ->where('is_active', true)
            ->first();

        if ($settings && $settings->require_location) {
            $distance = $this->calculateDistance(
                $request->latitude,
                $request->longitude,
                $settings->location_latitude,
                $settings->location_longitude
            );

            if ($distance > $settings->radius_meters) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda berada di luar radius yang diizinkan. Jarak: ' . round($distance) . 'm dari sekolah'
                ], 400);
            }
        }

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('attendance_photos', 'public');
        }

        // Determine status
        $checkInTime = now();
        $status = $this->determineStatus($user, $checkInTime, $settings);

        // Create attendance record
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $todayFormatted,
            'check_in' => $checkInTime,
            'status' => $status,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'location_name' => $request->location_name,
            'photo' => $photoPath,
            'qr_code_used' => $request->qr_code,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Check-in berhasil',
            'data' => [
                'id' => $attendance->id,
                'date' => $attendance->date,
                'check_in' => $attendance->check_in,
                'status' => $attendance->status,
                'location_name' => $attendance->location_name,
                'photo_url' => $photoPath ? asset('storage/' . $photoPath) : null,
            ]
        ]);
    }

    /**
     * Mobile check-out
     */
    public function checkOut(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::now('Asia/Jakarta')->format('Y-m-d');

        // Validate request
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'location_name' => 'required|string',
        ]);

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Belum melakukan check-in hari ini'
            ], 400);
        }

        if ($attendance->check_out) {
            return response()->json([
                'success' => false,
                'message' => 'Sudah melakukan check-out hari ini'
            ], 400);
        }

        // Update check-out
        $attendance->update([
            'check_out' => now(),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'location_name' => $request->location_name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Check-out berhasil',
            'data' => [
                'id' => $attendance->id,
                'date' => $attendance->date,
                'check_in' => $attendance->check_in,
                'check_out' => $attendance->check_out,
                'status' => $attendance->status,
                'location_name' => $attendance->location_name,
            ]
        ]);
    }

    /**
     * Get attendance history
     */
    public function history(Request $request)
    {
        $user = Auth::user();
        $startDate = $request->start_date ?? Carbon::now()->startOfMonth();
        $endDate = $request->end_date ?? Carbon::now()->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $attendances->map(function ($attendance) {
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
        ]);
    }

    /**
     * Get today's attendance status
     */
    public function todayStatus(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::now('Asia/Jakarta')->format('Y-m-d');

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'has_checked_in' => $attendance ? true : false,
                'has_checked_out' => $attendance && $attendance->check_out ? true : false,
                'check_in_time' => $attendance ? $attendance->check_in : null,
                'check_out_time' => $attendance ? $attendance->check_out : null,
                'status' => $attendance ? $attendance->status : null,
                'status_label' => $attendance ? $attendance->status_label : null,
                'location_name' => $attendance ? $attendance->location_name : null,
                'photo_url' => $attendance && $attendance->photo ? asset('storage/' . $attendance->photo) : null,
            ]
        ]);
    }

    /**
     * Calculate distance between two coordinates
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meters

        $lat1Rad = deg2rad($lat1);
        $lon1Rad = deg2rad($lon1);
        $lat2Rad = deg2rad($lat2);
        $lon2Rad = deg2rad($lon2);

        $deltaLat = $lat2Rad - $lat1Rad;
        $deltaLon = $lon2Rad - $lon1Rad;

        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
             cos($lat1Rad) * cos($lat2Rad) *
             sin($deltaLon / 2) * sin($deltaLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Determine attendance status based on time and role.
     */
    private function determineStatus($user, $checkInTime, $settings = null)
    {
        $checkInTimeFormatted = $checkInTime->format('H:i:s');
        
        // Role-based time limits with special schedules and daily overrides
        $maxTime = $this->getMaxCheckInTime($user, $checkInTime);

        if ($checkInTimeFormatted <= $maxTime) {
            return 'ontime';
        } else {
            return 'late';
        }
    }

    /**
     * Get maximum check-in time based on user role.
     */
    private function getMaxCheckInTime($user, $checkInTime = null)
    {
        $checkInTime = $checkInTime ?: now('Asia/Jakarta');
        
        // Priority 1: Daily Override (highest priority)
        $dailyOverrideTime = \App\Models\DailyOverride::getMaxCheckInTimeForDate($checkInTime, $user);
        if ($dailyOverrideTime) {
            return $dailyOverrideTime;
        }
        
        // Priority 2: Special Schedule (e.g., Upacara Senin)
        $specialScheduleTime = \App\Models\SpecialSchedule::getMaxCheckInTimeForDate($checkInTime, $user);
        if ($specialScheduleTime) {
            return $specialScheduleTime;
        }
        
        // Priority 3: Regular settings
        $settings = AttendanceSetting::where('school_id', $user->school_id)
            ->where('is_active', true)
            ->first();
            
        if ($settings) {
            // Gunakan setting dari database jika tersedia
            if ($user->hasRole(['teacher'])) {
                return $settings->teacher_max_time ? $settings->teacher_max_time->format('H:i:s') : '06:30:00';
            } elseif ($user->hasRole(['student'])) {
                return $settings->student_max_time ? $settings->student_max_time->format('H:i:s') : '06:30:00';
            } else {
                return $settings->other_roles_max_time ? $settings->other_roles_max_time->format('H:i:s') : '07:00:00';
            }
        }
        
        // Fallback ke default jika tidak ada setting
        if ($user->hasRole(['teacher', 'student'])) {
            return '06:30:00';
        }
        
        return '07:00:00';
    }
}



