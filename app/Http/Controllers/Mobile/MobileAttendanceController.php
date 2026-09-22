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
use App\Models\User;

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
        $checkInTime = now('Asia/Jakarta');
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
        return Attendance::determineStatusFor($user, $checkInTime);
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
            return AttendanceSetting::normalizeTimeString($dailyOverrideTime);
        }
        
        // Priority 2: Special Schedule (e.g., Upacara Senin)
        $specialScheduleTime = \App\Models\SpecialSchedule::getMaxCheckInTimeForDate($checkInTime, $user);
        if ($specialScheduleTime) {
            return AttendanceSetting::normalizeTimeString($specialScheduleTime);
        }
        
        // Priority 3: Regular settings
        $settings = AttendanceSetting::where('school_id', $user->school_id)
            ->where('is_active', true)
            ->first();
            
        $isStudent = ($user->user_type === 'student') || (method_exists($user, 'hasRole') && $user->hasRole('student'));
        $isTeacher = ($user->user_type === 'employee') || (method_exists($user, 'hasRole') && $user->hasRole(['teacher', 'employee', 'headmaster', 'tu', 'bk', 'kesiswaan']));

        if ($settings) {
            $checkInFallback = AttendanceSetting::normalizeTimeString($settings->check_in_time) ?: '06:30:00';

            if ($isStudent) {
                return AttendanceSetting::normalizeTimeString($settings->student_max_time) ?: $checkInFallback;
            } elseif ($isTeacher) {
                return AttendanceSetting::normalizeTimeString($settings->teacher_max_time) ?: $checkInFallback;
            } else {
                return AttendanceSetting::normalizeTimeString($settings->other_roles_max_time) ?: '07:00:00';
            }
        }
        
        // Fallback ke default jika tidak ada setting
        if ($isStudent || $isTeacher) {
            return '06:30:00';
        }
        
        return '07:00:00';
    }

    /**
     * Process student scan by teacher/admin via mobile API.
     */
    public function scanStudent(Request $request)
    {
        $request->validate([
            'qr_codes' => 'required|array|min:1',
            'qr_codes.*' => 'string',
        ]);

        $currentUser = Auth::user();
        $scannedStudents = [];
        $duplicates = [];
        $errors = [];
        $successCount = 0;

        foreach ($request->qr_codes as $qrCode) {
            $result = $this->processQRCode($qrCode);
            if ($result['success']) {
                $scannedStudents[] = [
                    'id' => $result['student']->id,
                    'name' => $result['student']->name,
                    'nis' => $result['student']->nis,
                    'status' => $result['status'] ?? 'present',
                ];
                $successCount++;
            } else {
                if (($result['type'] ?? '') === 'duplicate') {
                    $duplicates[] = $result['message'];
                } else {
                    $errors[] = $result['message'];
                }
            }
        }

        $duplicateCount = count($duplicates);
        $errorCount = count($errors);
        $totalCount = $successCount + $duplicateCount + $errorCount;

        $msg = $successCount . ' siswa berhasil diabsensi';
        if ($duplicateCount > 0) $msg .= ', ' . $duplicateCount . ' sudah tercatat';
        if ($errorCount > 0) $msg .= ', ' . $errorCount . ' gagal';

        return response()->json([
            'success' => true,
            'message' => $msg,
            'data' => [
                'success_count' => $successCount,
                'duplicate_count' => $duplicateCount,
                'error_count' => $errorCount,
                'total_count' => $totalCount,
                'students' => $scannedStudents,
                'duplicates' => $duplicates,
                'errors' => $errors,
            ]
        ]);
    }

    /**
     * Process individual QR code and create attendance.
     */
    private function processQRCode($qrCode)
    {
        $parsed = $this->parseQRCode($qrCode);
        $nis = $parsed['nis'] ?? $qrCode;

        $student = User::where(function($query) use ($nis, $qrCode) {
            $query->where('nis', $nis)->orWhere('qr_code', $qrCode);
        })
        ->where('user_type', 'student')
        ->where('school_id', Auth::user()->school_id)
        ->first();

        if (!$student) {
            return [
                'success' => false,
                'type' => 'error',
                'message' => 'Siswa dengan NIS/QR: ' . $nis . ' tidak ditemukan'
            ];
        }

        $today = Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $existing = Attendance::where('user_id', $student->id)->where('date', $today)->first();

        if ($existing) {
            return [
                'success' => false,
                'type' => 'duplicate',
                'message' => $student->name . ' sudah diabsensi hari ini'
            ];
        }

        $checkInTime = Carbon::now('Asia/Jakarta');
        $status = $this->determineStatus($student, $checkInTime);

        Attendance::create([
            'user_id' => $student->id,
            'date' => $today,
            'check_in' => $checkInTime,
            'status' => $status,
            'notes' => 'Absensi mobile oleh: ' . Auth::user()->name,
            'latitude' => null,
            'longitude' => null,
            'location_name' => 'Scan oleh ' . Auth::user()->name,
        ]);

        return [
            'success' => true,
            'student' => $student,
            'status' => $status
        ];
    }

    /**
     * Parse QR code to extract NIS and name.
     */
    private function parseQRCode($qrCode)
    {
        if (strpos($qrCode, '|') !== false) {
            $parts = explode('|', $qrCode, 2);
            return ['nis' => trim($parts[0]), 'name' => trim($parts[1])];
        } elseif (strpos($qrCode, '_') !== false) {
            $parts = explode('_', $qrCode, 2);
            return ['nis' => trim($parts[0]), 'name' => trim($parts[1])];
        } else {
            $parsed = json_decode($qrCode, true);
            if (is_array($parsed)) {
                return [
                    'nis' => $parsed['nis'] ?? $parsed['NIS'] ?? $qrCode,
                    'name' => $parsed['name'] ?? $parsed['nama'] ?? 'Unknown'
                ];
            }
            return ['nis' => $qrCode, 'name' => 'Unknown'];
        }
    }
}



