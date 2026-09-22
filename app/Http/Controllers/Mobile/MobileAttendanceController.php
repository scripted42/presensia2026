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
        
        // Validate request: 1. Photo selfie, 2. Within radius (lat/long), 3. TV display QR code
        $request->validate([
            'qr_code' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'location_name' => 'required|string',
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:12288',
            'notes' => 'nullable|string',
        ], [
            'qr_code.required' => 'QR Code dari layar sekolah wajib discan.',
            'photo.required' => 'Foto selfie wajib diambil.',
        ]);

        // Check if already checked in today
        $existingAttendance = Attendance::where('user_id', $user->id)
            ->where('date', $todayFormatted)
            ->first();

        if ($existingAttendance && $existingAttendance->check_in) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan check-in hari ini'
            ], 400);
        }

        // Validate QR code (Display-QR TV with 15s grace period for scan network transit)
        $qrCode = QrCode::where('code', $request->qr_code)
            ->where('school_id', $user->school_id)
            ->where('is_used', false)
            ->where('expires_at', '>', now()->subSeconds(15))
            ->first();

        if (!$qrCode) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak valid atau sudah kedaluwarsa. Silakan scan ulang QR di layar sekolah.'
            ], 400);
        }

        // Mark QR code as used
        $qrCode->update(['is_used' => true, 'used_at' => now()]);

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
                    'message' => 'Anda berada di luar radius yang diizinkan. Jarak: ' . round($distance) . 'm dari sekolah (radius maksimal: ' . $settings->radius_meters . 'm)'
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

        // Create or update attendance record safely
        $attendance = Attendance::updateOrCreate(
            [
                'user_id' => $user->id,
                'date' => $todayFormatted,
            ],
            [
                'check_in' => $checkInTime->format('H:i:s'),
                'status' => $status,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'location_name' => $request->location_name,
                'photo' => $photoPath,
                'qr_code_used' => $request->qr_code,
                'notes' => $request->notes,
            ]
        );

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

        // Validate request: 1. Photo selfie, 2. Within radius (lat/long), 3. TV display QR code
        $request->validate([
            'qr_code' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'location_name' => 'required|string',
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:12288',
        ], [
            'qr_code.required' => 'QR Code dari layar sekolah wajib discan.',
            'photo.required' => 'Foto selfie pulang wajib diambil.',
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
                'message' => 'Anda sudah melakukan check-out hari ini'
            ], 400);
        }

        // Validate QR code (Display-QR TV with 15s grace period)
        $qrCode = QrCode::where('code', $request->qr_code)
            ->where('school_id', $user->school_id)
            ->where('is_used', false)
            ->where('expires_at', '>', now()->subSeconds(15))
            ->first();

        if (!$qrCode) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak valid atau sudah kedaluwarsa. Silakan scan ulang QR di layar sekolah.'
            ], 400);
        }

        // Mark QR code as used
        $qrCode->update(['is_used' => true, 'used_at' => now()]);

        // Validate location radius if required
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
                    'message' => 'Anda berada di luar radius yang diizinkan. Jarak: ' . round($distance) . 'm dari sekolah (radius maksimal: ' . $settings->radius_meters . 'm)'
                ], 400);
            }
        }

        // Handle photo upload if present
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('attendance_photos', 'public');
        }

        // Update check-out
        $checkOutTime = now('Asia/Jakarta');
        $updateData = [
            'check_out' => $checkOutTime->format('H:i:s'),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'location_name' => $request->location_name,
            'qr_code_used' => $request->qr_code,
        ];
        if ($photoPath) {
            $updateData['photo'] = $photoPath;
        }

        $attendance->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Check-out berhasil dicatat',
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
        if ($request->has('month') && $request->has('year')) {
            $year = (int) $request->year;
            $month = (int) $request->month;
            $startDate = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Jakarta')->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Jakarta')->endOfMonth()->format('Y-m-d');
        } else {
            $startDate = $request->start_date ?? Carbon::now('Asia/Jakarta')->startOfMonth()->format('Y-m-d');
            $endDate = $request->end_date ?? Carbon::now('Asia/Jakarta')->endOfMonth()->format('Y-m-d');
        }

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
            try {
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
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Scan student QR error: ' . $e->getMessage());
                $errors[] = 'Gagal memproses QR ' . $qrCode . ': ' . $e->getMessage();
            }
        }

        $duplicateCount = count($duplicates);
        $errorCount = count($errors);
        $totalCount = $successCount + $duplicateCount + $errorCount;

        $msgParts = [];
        if ($successCount > 0) {
            $msgParts[] = "{$successCount} siswa berhasil diabsensi";
        }
        if ($duplicateCount > 0) {
            $msgParts[] = "{$duplicateCount} sudah tercatat";
        }
        if ($errorCount > 0) {
            $msgParts[] = "{$errorCount} tidak ditemukan/gagal";
        }
        $msg = count($msgParts) > 0 ? implode(', ', $msgParts) : 'Tidak ada data absensi';

        $isSuccess = $successCount > 0 || ($duplicateCount > 0 && $errorCount === 0);

        return response()->json([
            'success' => $isSuccess,
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
        ], $isSuccess ? 200 : 422);
    }

    /**
     * Process individual QR code and create attendance.
     */
    private function processQRCode($qrCode)
    {
        $parsed = $this->parseQRCode($qrCode);
        $nis = $parsed['nis'] ?? $qrCode;

        $schoolId = Auth::user()?->school_id;

        $student = User::where(function($query) use ($nis, $qrCode) {
            $query->where('nis', $nis)
                  ->orWhere('qr_code', $qrCode)
                  ->orWhere('nisn', $nis);
        })
        ->where('user_type', 'student')
        ->when($schoolId, function($q) use ($schoolId) {
            $q->where('school_id', $schoolId);
        })
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
                'message' => $student->name . ' (' . ($student->nis ?? 'NIS') . ') sudah diabsensi hari ini'
            ];
        }

        $checkInTime = Carbon::now('Asia/Jakarta');
        $status = $this->determineStatus($student, $checkInTime);

        Attendance::updateOrCreate(
            [
                'user_id' => $student->id,
                'date' => $today,
            ],
            [
                'check_in' => $checkInTime->format('H:i:s'),
                'status' => $status,
                'notes' => 'Absensi mobile oleh: ' . (Auth::user()?->name ?? 'Petugas'),
                'latitude' => null,
                'longitude' => null,
                'location_name' => 'Scan oleh ' . (Auth::user()?->name ?? 'Petugas'),
            ]
        );

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

    /**
     * Get list of school classes for mobile filter.
     */
    public function getClasses(Request $request)
    {
        $user = Auth::user();
        $schoolId = $user->school_id ?? null;

        $classesQuery = \App\Models\SchoolClass::query();
        if ($schoolId) {
            $classesQuery->where('school_id', $schoolId);
        }

        $classes = $classesQuery->orderBy('level', 'asc')
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'level']);

        return response()->json([
            'success' => true,
            'data' => $classes,
        ]);
    }

    /**
     * Get student attendance history with date, class filter, search, and KPI statistics.
     */
    public function studentsHistory(Request $request)
    {
        $currentUser = Auth::user();
        
        // Date input (default to today Asia/Jakarta)
        $dateStr = $request->input('date', Carbon::now('Asia/Jakarta')->toDateString());
        try {
            $targetDate = Carbon::parse($dateStr, 'Asia/Jakarta')->toDateString();
        } catch (\Exception $e) {
            $targetDate = Carbon::now('Asia/Jakarta')->toDateString();
        }

        $classId = $request->input('class_id');
        $search = trim($request->input('q', $request->input('search', '')));
        $statusFilter = strtolower(trim($request->input('status', 'all')));

        // Query active students
        $studentsQuery = User::where('user_type', 'student')
            ->where('is_active', true);

        if (!empty($currentUser->school_id)) {
            $studentsQuery->where('school_id', $currentUser->school_id);
        }

        // Filter by class
        if (!empty($classId) && $classId !== 'all') {
            $studentsQuery->whereHas('studentClasses', function ($q) use ($classId) {
                $q->where('classes.id', $classId);
            });
        }

        // Search by keyword (name, nis, nisn)
        if (!empty($search)) {
            $studentsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        // Eager load class and attendance on target date
        $students = $studentsQuery->with([
            'studentClasses' => function ($q) {
                $q->select('classes.id', 'classes.name', 'classes.level');
            },
            'attendances' => function ($q) use ($targetDate) {
                $q->where('date', $targetDate);
            }
        ])
        ->orderBy('name', 'asc')
        ->get();

        // Calculate statistics & map output
        $totalStudents = $students->count();
        $ontimeCount = 0;
        $lateCount = 0;
        $sickCount = 0;
        $permitCount = 0;
        $alphaCount = 0;

        $mappedStudents = $students->map(function ($student) use (&$ontimeCount, &$lateCount, &$sickCount, &$permitCount, &$alphaCount) {
            $attendance = $student->attendances->first();
            $className = $student->studentClasses->first()->name ?? '-';

            $status = 'alpha'; // default if no attendance record
            $checkIn = null;
            $checkOut = null;
            $notes = null;
            $photoUrl = null;

            if ($attendance) {
                $st = strtolower($attendance->status ?? 'present');
                if (in_array($st, ['ontime', 'present', 'tepat_waktu'])) {
                    $status = 'ontime';
                    $ontimeCount++;
                } elseif (in_array($st, ['late', 'terlambat'])) {
                    $status = 'late';
                    $lateCount++;
                } elseif (in_array($st, ['sick', 'sakit'])) {
                    $status = 'sick';
                    $sickCount++;
                } elseif (in_array($st, ['permit', 'izin', 'leave'])) {
                    $status = 'permit';
                    $permitCount++;
                } else {
                    $status = 'alpha';
                    $alphaCount++;
                }

                $checkIn = $attendance->check_in ? Carbon::parse($attendance->check_in)->format('H:i') : null;
                $checkOut = $attendance->check_out ? Carbon::parse($attendance->check_out)->format('H:i') : null;
                $notes = $attendance->notes;
                if (!empty($attendance->photo)) {
                    $photoUrl = asset('storage/' . $attendance->photo);
                }
            } else {
                $alphaCount++;
            }

            return [
                'id' => $student->id,
                'name' => $student->name,
                'nis' => $student->nis ?? '-',
                'nisn' => $student->nisn ?? null,
                'gender' => $student->gender ?? null,
                'class_name' => $className,
                'class_id' => $student->studentClasses->first()->id ?? null,
                'status' => $status,
                'is_present' => in_array($status, ['ontime', 'late']),
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'notes' => $notes,
                'photo_url' => $photoUrl,
            ];
        });

        // Filter by status if requested
        if ($statusFilter !== 'all') {
            if ($statusFilter === 'present') {
                $mappedStudents = $mappedStudents->filter(fn($s) => $s['is_present'])->values();
            } else {
                $mappedStudents = $mappedStudents->filter(fn($s) => $s['status'] === $statusFilter)->values();
            }
        }

        $presentTotal = $ontimeCount + $lateCount;
        $attendanceRate = $totalStudents > 0 ? round(($presentTotal / $totalStudents) * 100, 1) : 0;

        // Indonesian date formatted
        $targetCarbon = Carbon::parse($targetDate, 'Asia/Jakarta')->locale('id');
        $dateFormatted = $targetCarbon->isoFormat('dddd, D MMMM Y');

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $targetDate,
                'date_formatted' => $dateFormatted,
                'statistics' => [
                    'total_students' => $totalStudents,
                    'present_count' => $presentTotal,
                    'ontime_count' => $ontimeCount,
                    'late_count' => $lateCount,
                    'sick_count' => $sickCount,
                    'permit_count' => $permitCount,
                    'alpha_count' => $alphaCount,
                    'attendance_rate' => $attendanceRate,
                ],
                'students' => $mappedStudents,
            ],
        ]);
    }
}



