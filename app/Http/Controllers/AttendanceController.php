<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use App\Models\LeaveRequest;
use App\Models\QrCode;
use App\Models\AttendanceSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $todayCarbon = Carbon::now('Asia/Jakarta');
        
        $attendances = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();
            
        $recent = Attendance::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->paginate(10);
            
        return view('attendance.index', compact('attendances', 'user', 'today', 'todayCarbon', 'recent'));
    }

    /**
     * Show check-in form.
     */
    public function showCheckIn()
    {
        $user = Auth::user();
        $today = Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $todayCarbon = Carbon::now('Asia/Jakarta');
        $settings = AttendanceSetting::where('school_id', $user->school_id)
            ->where('is_active', true)
            ->first();
        $recent = Attendance::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get();
        
        // Check if already checked in today
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();
            
        if ($attendance && $attendance->check_in) {
            return redirect()->route('attendance.index')
                ->with('info', 'Anda sudah absensi masuk hari ini.');
        }
        
        return view('attendance.check-in', compact('user', 'today', 'todayCarbon', 'settings', 'recent'));
    }

    /**
     * Process check-in.
     */
    public function checkIn(Request $request)
    {
        // Debug ngrok issues
        \Log::info('Check-in request received:', [
            'user_id' => Auth::id(),
            'qr_code' => $request->qr_code,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'location_name' => $request->location_name,
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
            'headers' => $request->headers->all()
        ]);
        
        $user = Auth::user();
        $today = Carbon::now('Asia/Jakarta');
        $todayFormatted = $today->format('Y-m-d');
        
        // Validasi: Cek apakah hari ini adalah hari libur
        if (\App\Models\HolidaySchedule::isHoliday($today, $user->school_id)) {
            $holidayName = \App\Models\HolidaySchedule::getHolidayName($today, $user->school_id);
            return redirect()->back()->withErrors(['holiday' => "Hari ini adalah {$holidayName}. Absensi tidak diperbolehkan pada hari libur."]);
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
            \Log::warning('User check-in without previous day checkout', [
                'user_id' => $user->id,
                'yesterday_date' => $yesterday,
                'yesterday_check_in' => $yesterdayAttendance->check_in,
                'current_time' => now()->setTimezone('Asia/Jakarta')
            ]);
        }
        
        // Validate QR code if provided (graceful fallback for generic QR)
        if ($request->qr_code) {
            // Sesuai flow: QR pada layar lobby bersifat "umum per sekolah", berubah tiap beberapa detik
            // Guru/BK/Kesiswaan/Kepsek memindai QR ini. Maka validasi cukup berdasarkan school_id + masa berlaku
            $qrCode = QrCode::where('code', $request->qr_code)
                ->where('school_id', $user->school_id)
                ->where('is_used', false)
                ->where('expires_at', '>', now())
                ->first();

            if (!$qrCode) {
                return redirect()->back()->withErrors(['qr_code' => 'QR Code tidak valid atau sudah kedaluwarsa. Silakan scan ulang.']);
            }

            // Tandai terpakai (one-time) agar tidak bisa dipakai ulang
            $qrCode->update(['is_used' => true, 'used_at' => now()]);
        }
        // Validate location inside radius if required
        $settings = AttendanceSetting::where('school_id', $user->school_id)
            ->where('is_active', true)
            ->first();
        if ($settings && $settings->require_location) {
            $lat = (float) $request->latitude; $lng = (float) $request->longitude;
            if (!$this->isWithinRadius($lat, $lng, (float) $settings->location_latitude, (float) $settings->location_longitude, (int) $settings->radius_meters)) {
                return redirect()->back()->withErrors(['location' => 'Lokasi Anda di luar radius absensi.']);
            }
        }

        // Create or update attendance
        $checkInTime = now()->setTimezone('Asia/Jakarta');
        $attendance = Attendance::updateOrCreate(
            [
                'user_id' => $user->id,
                'date' => $todayFormatted,
            ],
            [
                'check_in' => $checkInTime,
                'status' => $this->determineStatus($user, $checkInTime),
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'location_name' => $request->location_name,
                'photo' => $request->photo,
                'qr_code_used' => $request->qr_code,
            ]
        );
        
        \Log::info('Attendance created successfully:', [
            'attendance_id' => $attendance->id,
            'user_id' => $attendance->user_id,
            'check_in' => $attendance->check_in,
            'status' => $attendance->status
        ]);
        
        // Handle AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Absensi masuk berhasil.',
                'attendance' => $attendance
            ]);
        }
        
        return redirect()->route('attendance.index')
            ->with('success', 'Absensi masuk berhasil.');
    }

    /**
     * Show check-out form.
     */
    public function showCheckOut()
    {
        $user = Auth::user();
        $today = Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $todayCarbon = Carbon::now('Asia/Jakarta');
        $settings = AttendanceSetting::where('school_id', $user->school_id)
            ->where('is_active', true)
            ->first();
        
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();
            
        if (!$attendance || !$attendance->check_in) {
            return redirect()->route('attendance.check-in')
                ->with('error', 'Anda belum absensi masuk hari ini.');
        }
        
        if ($attendance->check_out) {
            return redirect()->route('attendance.index')
                ->with('info', 'Anda sudah absensi keluar hari ini.');
        }
        
        return view('attendance.check-out', compact('attendance', 'user', 'today', 'todayCarbon', 'settings'));
    }

    /**
     * Process check-out.
     */
    public function checkOut(Request $request)
    {
        // Debug ngrok issues for check-out
        \Log::info('Check-out request received:', [
            'user_id' => Auth::id(),
            'qr_code' => $request->qr_code,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'location_name' => $request->location_name,
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
            'headers' => $request->headers->all()
        ]);
        
        $user = Auth::user();
        $today = Carbon::now('Asia/Jakarta')->format('Y-m-d');
        
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();
            
        if (!$attendance) {
            return redirect()->route('attendance.check-in')
                ->with('error', 'Anda belum absensi masuk hari ini.');
        }
        
        $attendance->update([
            'check_out' => now()->setTimezone('Asia/Jakarta'),
        ]);
        
        \Log::info('Check-out completed successfully:', [
            'attendance_id' => $attendance->id,
            'user_id' => $attendance->user_id,
            'check_out' => $attendance->check_out
        ]);
        
        // Handle AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Absensi keluar berhasil.',
                'attendance' => $attendance
            ]);
        }
        
        return redirect()->route('attendance.index')
            ->with('success', 'Absensi keluar berhasil.');
    }

    /**
     * Get QR code for attendance.
     */
    public function getQrCode()
    {
        $user = Auth::user();
        
        // Generate new QR code
        $qrCode = QrCode::create([
            'school_id' => $user->school_id,
            'code' => Str::random(32),
            'user_id' => $user->id,
            'expires_at' => now()->addSeconds(10), // 10 seconds validity
        ]);
        
        return response()->json([
            'qr_code' => $qrCode->code,
            'expires_at' => $qrCode->expires_at,
        ]);
    }

    /**
     * Show fullscreen QR display for attendance (auto refresh every 10 seconds)
     */
    public function showDisplayQr()
    {
        return view('attendance.display-qr');
    }

    /**
     * Show student scan form.
     */
    public function showStudentScan()
    {
        $user = Auth::user();
        $classes = $user->taughtClasses()->with('activeStudents')->get();
        
        return view('attendance.student-scan', compact('classes'));
    }

    /**
     * Process student scan with photo QR and manual QR codes.
     */
    public function scanStudent(Request $request)
    {
        // Increase execution time for bulk processing
        set_time_limit(300); // 5 minutes
        
        $request->validate([
            'qr_photos' => 'sometimes|array',
            'qr_photos.*' => 'string',
            'qr_codes' => 'sometimes|array', 
            'qr_codes.*' => 'string',
        ]);
        
        // Debug: Log all request data
        \Log::info('Scan student request received:', [
            'qr_photos_count' => $request->has('qr_photos') ? count($request->qr_photos) : 0,
            'qr_codes_count' => $request->has('qr_codes') ? count($request->qr_codes) : 0,
            'qr_codes' => $request->qr_codes ?? [],
            'user_id' => Auth::id(),
            'school_id' => Auth::user()->school_id
        ]);
        
        $scannedStudents = [];
        $duplicates = [];
        $errors = [];
        $processedCount = 0;
        
        // Process manual QR codes first
        if ($request->has('qr_codes')) {
            \Log::info('Processing manual QR codes:', $request->qr_codes);
            foreach ($request->qr_codes as $qrCode) {
                $result = $this->processQRCode($qrCode);
                if ($result['success']) {
                    $scannedStudents[] = $result['student'];
                    $processedCount++;
                    \Log::info("Successfully processed manual QR: {$qrCode}");
                } else {
                    if ($result['type'] === 'duplicate') {
                        $duplicates[] = $result['message'];
                        \Log::info("Duplicate manual QR: {$qrCode}");
                    } else {
                        $errors[] = $result['message'];
                        \Log::error("Error processing manual QR: {$qrCode} - {$result['message']}");
                    }
                }
            }
        }
        
        // Process photo QR codes (decode from base64 images) - OPTIMIZED
        if ($request->has('qr_photos')) {
            \Log::info('Processing photo QR codes, count: ' . count($request->qr_photos));
            
            // Limit processing to prevent timeout
            $maxPhotos = 10; // Limit to 10 photos per request
            $photosToProcess = array_slice($request->qr_photos, 0, $maxPhotos);
            
            if (count($request->qr_photos) > $maxPhotos) {
                \Log::warning("Too many photos ({$maxPhotos}), processing first {$maxPhotos} only");
            }
            
            foreach ($photosToProcess as $index => $photoData) {
                try {
                    \Log::info("Processing photo {$index}, data length: " . strlen($photoData));
                    
                    // Enhanced QR decode with debugging
                    \Log::info("Starting QR decode for photo {$index}");
                    $qrCode = $this->decodeQRFromPhoto($photoData);
                    
                    if ($qrCode) {
                        \Log::info("✅ QR decoded from photo {$index}: {$qrCode}");
                        
                        $result = $this->processQRCode($qrCode);
                        if ($result['success']) {
                            $scannedStudents[] = $result['student'];
                            $processedCount++;
                            \Log::info("Successfully processed photo QR: {$qrCode}");
                        } else {
                            if ($result['type'] === 'duplicate') {
                                $duplicates[] = $result['message'];
                                \Log::info("Duplicate photo QR: {$qrCode}");
                            } else {
                                $errors[] = $result['message'];
                                \Log::error("Error processing photo QR: {$qrCode} - {$result['message']}");
                            }
                        }
                    } else {
                        \Log::warning("❌ No QR code found in photo {$index}");
                        $errors[] = "QR Code tidak terdeteksi dari foto {$index} - Coba ambil foto yang lebih jelas";
                        
                        // Add debugging info
                        \Log::info("Photo {$index} debugging info:", [
                            'data_length' => strlen($photoData),
                            'is_base64' => strpos($photoData, 'data:image') === 0,
                            'first_100_chars' => substr($photoData, 0, 100)
                        ]);
                    }
                } catch (\Exception $e) {
                    $errors[] = 'Gagal memproses foto: ' . $e->getMessage();
                    \Log::error("Exception processing photo {$index}: " . $e->getMessage());
                }
            }
        }
        
        // Create detailed message with counts
        $successCount = $processedCount;
        $duplicateCount = count($duplicates);
        $errorCount = count($errors);
        $totalCount = $successCount + $duplicateCount + $errorCount;
        
        $message = $successCount . ' siswa berhasil diabsensi';
        
        if ($duplicateCount > 0) {
            $message .= ', ' . $duplicateCount . ' siswa sudah diabsensi sebelumnya';
        }
        
        if ($errorCount > 0) {
            $message .= ', ' . $errorCount . ' siswa gagal diabsensi';
        }
        
        // Store detailed data in session for JavaScript parsing
        session([
            'sync_result' => [
                'success_count' => $successCount,
                'duplicate_count' => $duplicateCount,
                'error_count' => $errorCount,
                'total_count' => $totalCount,
                'errors' => $errors
            ]
        ]);
        
        return redirect()->route('attendance.student-scan')
            ->with('success', $message);
    }
    
    /**
     * Clear sync result session data
     */
    public function clearSyncResult()
    {
        session()->forget('sync_result');
        return response()->json(['success' => true]);
    }
    
    /**
     * Process individual QR code and create attendance.
     */
    private function processQRCode($qrCode)
    {
            // Parse QR code to extract NIS
            $parsed = $this->parseQRCode($qrCode);
            $nis = $parsed['nis'] ?? $qrCode;
            
            // Find student by NIS or QR code
            $student = User::where(function($query) use ($nis, $qrCode) {
                $query->where('nis', $nis)
                      ->orWhere('qr_code', $qrCode);
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
        
                // Check if student already has attendance today
        $today = Carbon::now('Asia/Jakarta')->format('Y-m-d');
                $existingAttendance = Attendance::where('user_id', $student->id)
            ->where('date', $today)
                    ->first();
                    
                if ($existingAttendance) {
            \Log::info("Duplicate attendance detected for student {$student->nis} on {$today}");
            return [
                'success' => false,
                'type' => 'duplicate',
                'message' => $student->name . ' (' . $student->nis . ')'
            ];
                }
                
                // Create attendance record for student
                $checkInTime = now()->setTimezone('Asia/Jakarta');
                Attendance::create([
                    'user_id' => $student->id,
                    'date' => Carbon::now('Asia/Jakarta')->format('Y-m-d'),
                    'check_in' => $checkInTime,
                    'status' => $this->determineStatus($student, $checkInTime),
                    'notes' => 'Absensi oleh guru: ' . Auth::user()->name,
                    'latitude' => null,
                    'longitude' => null,
                    'location_name' => 'Scan oleh ' . Auth::user()->name,
                ]);
                
        return [
            'success' => true,
            'student' => $student
        ];
    }
    
    /**
     * Decode QR code from base64 photo - SIMPLIFIED for performance.
     */
    private function decodeQRFromPhoto($base64Data)
    {
        try {
            // Remove data:image/jpeg;base64, prefix
            $imageData = base64_decode(preg_replace('#^data:image/[^;]+;base64,#', '', $base64Data));
            
            // Create single temporary file for faster processing
            $tempFile = tempnam(sys_get_temp_dir(), 'qr_');
            file_put_contents($tempFile, $imageData);
            
            // Try to decode QR from original image only
            $qrCode = $this->basicQRDecode($tempFile);
            
            // Clean up
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
            
            return $qrCode;
        } catch (\Exception $e) {
            \Log::error('QR decode error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Enhanced QR decode with multiple fallback methods.
     */
    private function basicQRDecode($imagePath)
    {
        $methods = [
            'khanamiryan' => function($path) {
                try {
                    $qrcode = new \Zxing\QrReader($path);
                    return $qrcode->text();
                } catch (\Exception $e) {
                    \Log::warning("khanamiryan method failed: " . $e->getMessage());
                    return null;
                }
            },
            'khanamiryan_enhanced' => function($path) {
                try {
                    // Try with enhanced image preprocessing
                    $enhancedPath = $this->preprocessImage($path);
                    if ($enhancedPath) {
                        $qrcode = new \Zxing\QrReader($enhancedPath);
                        $result = $qrcode->text();
                        unlink($enhancedPath); // Clean up
                        return $result;
                    }
                    return null;
                } catch (\Exception $e) {
                    \Log::warning("khanamiryan enhanced method failed: " . $e->getMessage());
                    return null;
                }
            },
        ];
        
        foreach ($methods as $methodName => $method) {
            try {
                \Log::info("Trying QR decode with method: {$methodName}");
                $text = $method($imagePath);
                
                if ($text && !empty(trim($text))) {
                    \Log::info("QR decoded successfully with {$methodName}: {$text}");
                    return $text;
                }
            } catch (\Exception $e) {
                \Log::warning("QR decode method {$methodName} failed: " . $e->getMessage());
            }
        }
        
        \Log::warning("All QR decode methods failed for image: {$imagePath}");
        return null;
    }
    
    /**
     * Preprocess image for better QR detection.
     */
    private function preprocessImage($imagePath)
    {
        try {
            // Create image resource
            $image = imagecreatefromstring(file_get_contents($imagePath));
            if (!$image) {
                return null;
            }
            
            // Create enhanced version
            $enhancedPath = tempnam(sys_get_temp_dir(), 'qr_enhanced_');
            
            // Apply image filters for better QR detection
            imagefilter($image, IMG_FILTER_CONTRAST, 50);
            imagefilter($image, IMG_FILTER_BRIGHTNESS, 20);
            imagefilter($image, IMG_FILTER_SMOOTH, 1);
            
            // Save enhanced image
            imagejpeg($image, $enhancedPath, 95);
            imagedestroy($image);
            
            return $enhancedPath;
        } catch (\Exception $e) {
            \Log::error("Image preprocessing failed: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Parse QR code to extract NIS and name.
     */
    private function parseQRCode($qrCode)
    {
        try {
            // Format QR: "NIS|Nama" atau "NIS_Nama" atau JSON
            if (strpos($qrCode, '|') !== false) {
                $parts = explode('|', $qrCode, 2);
                return ['nis' => trim($parts[0]), 'name' => trim($parts[1])];
            } elseif (strpos($qrCode, '_') !== false) {
                $parts = explode('_', $qrCode, 2);
                return ['nis' => trim($parts[0]), 'name' => trim($parts[1])];
            } else {
                // Try JSON format
                $parsed = json_decode($qrCode, true);
                if (is_array($parsed)) {
                    return [
                        'nis' => $parsed['nis'] ?? $parsed['NIS'] ?? $qrCode,
                        'name' => $parsed['name'] ?? $parsed['nama'] ?? 'Unknown'
                    ];
                }
                return ['nis' => $qrCode, 'name' => 'Unknown'];
            }
        } catch (Exception $e) {
            return ['nis' => $qrCode, 'name' => 'Unknown'];
        }
    }

    /**
     * Show attendance reports with role-based access.
     */
    public function reports(Request $request)
    {
        $user = Auth::user();
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $type = $request->get('type', 'all'); // all, employees, students
        
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        // Role-based data filtering
        $query = Attendance::whereHas('user', function($query) use ($user) {
                $query->where('school_id', $user->school_id);
            })
            ->whereBetween('date', [$startDate, $endDate])
            ->with('user');
            
        // Apply role-based restrictions
        if ($user->hasRole('admin') || $user->hasRole('headmaster')) {
            // Admin dan Headmaster dapat melihat semua data
            if ($type === 'employees') {
                $query->whereHas('user', function($q) {
                    $q->whereIn('user_type', ['admin', 'teacher', 'tu', 'bk', 'kesiswaan', 'employee']);
                });
            } elseif ($type === 'students') {
                $query->whereHas('user', function($q) {
                    $q->where('user_type', 'student');
                });
            }
        } elseif ($user->hasRole('teacher')) {
            // Teacher dapat melihat semua siswa di sekolah (tidak hanya kelas yang diajar)
            if ($type === 'employees') {
                $query->where('user_id', $user->id);
            } elseif ($type === 'students') {
                // Semua siswa di sekolah, tidak hanya yang diajar
                $query->whereHas('user', function($q) {
                    $q->where('user_type', 'student');
                });
            } else {
                // Show both own attendance and all students
                $query->where(function($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->orWhereHas('user', function($userQuery) {
                          $userQuery->where('user_type', 'student');
                      });
                });
            }
        } else {
            // Other roles can only see their own attendance
            $query->where('user_id', $user->id);
        }
        
        $attendances = $query->get()->groupBy('user_id');

        // Get all users for the report (not just those with attendance)
        $allUserIds = collect();
        
        if ($user->hasRole('admin') || $user->hasRole('headmaster')) {
            // Admin dan Headmaster dapat melihat semua user
            if ($type === 'employees') {
                $allUserIds = User::where('school_id', $user->school_id)
                    ->whereIn('user_type', ['admin', 'teacher', 'tu', 'bk', 'kesiswaan', 'employee'])
                ->pluck('id');
            } elseif ($type === 'students') {
                $allUserIds = User::where('school_id', $user->school_id)
                    ->where('user_type', 'student')
                    ->pluck('id');
            } else {
                $allUserIds = User::where('school_id', $user->school_id)->pluck('id');
            }
        } elseif ($user->hasRole('teacher')) {
            // Teacher dapat melihat semua siswa + dirinya sendiri
            if ($type === 'employees') {
                $allUserIds = collect([$user->id]);
            } elseif ($type === 'students') {
                $allUserIds = User::where('school_id', $user->school_id)
                    ->where('user_type', 'student')
                    ->pluck('id');
            } else {
                $allUserIds = User::where('school_id', $user->school_id)
                    ->where(function($q) {
                        $q->where('user_type', 'student')
                          ->orWhere('id', Auth::id());
                    })
                    ->pluck('id');
            }
        } else {
            $allUserIds = collect([$user->id]);
        }

        // Build map of approved leaves per user per date within month range
        $userIds = $attendances->keys()->merge($allUserIds)->unique();

        $approvedLeaves = LeaveRequest::whereIn('user_id', $userIds)
            ->where('status', 'approved')
            ->where(function($q) use ($startDate, $endDate) {
                // overlap with month range
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function($q2) use ($startDate, $endDate){
                      $q2->where('start_date', '<=', $startDate)
                         ->where('end_date', '>=', $endDate);
                  });
            })
            ->get();

        $leaveByUserDate = [];
        foreach ($approvedLeaves as $leave) {
            $cursor = $leave->start_date->copy();
            $to = $leave->end_date->copy();
            while ($cursor->lte($to)) {
                if ($cursor->gte($startDate) && $cursor->lte($endDate)) {
                    $leaveByUserDate[$leave->user_id][$cursor->format('Y-m-d')] = $leave->type; // sick|permit|duty|leave
                }
                $cursor->addDay();
            }
        }

        // Get holiday information for the month
        $holidays = \App\Models\HolidaySchedule::where('school_id', $user->school_id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('is_active', true)
            ->get()
            ->keyBy('date');
        
        return view('attendance.reports', compact('attendances', 'month', 'year', 'startDate', 'endDate', 'type', 'user', 'leaveByUserDate', 'holidays'));
    }

    /**
     * Export attendance reports.
     */
    public function exportReport(Request $request)
    {
        // TODO: Implement export logic
        return redirect()->route('attendance.reports')
            ->with('success', 'Export berhasil. (Fitur dalam pengembangan)');
    }

    /**
     * Show attendance settings.
     */
    public function showSettings()
    {
        $user = Auth::user();
        $settings = AttendanceSetting::where('school_id', $user->school_id)
            ->where('is_active', true)
            ->first();
            
        return view('attendance.settings', compact('settings'));
    }

    /**
     * Update attendance settings.
     */
    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'check_in_time' => 'required|date_format:H:i',
            'check_out_time' => 'required|date_format:H:i',
            'teacher_max_time' => 'nullable|date_format:H:i',
            'student_max_time' => 'nullable|date_format:H:i',
            'other_roles_max_time' => 'nullable|date_format:H:i',
            'location_latitude' => 'required|numeric',
            'location_longitude' => 'required|numeric',
            'location_name' => 'required|string',
            'radius_meters' => 'required|integer|min:10|max:1000',
        ]);
        
        AttendanceSetting::updateOrCreate(
            ['school_id' => $user->school_id, 'is_active' => true],
            [
                'check_in_time' => $request->check_in_time,
                'check_out_time' => $request->check_out_time,
                'teacher_max_time' => $request->teacher_max_time ?? '06:30',
                'student_max_time' => $request->student_max_time ?? '06:30',
                'other_roles_max_time' => $request->other_roles_max_time ?? '07:00',
                'location_latitude' => $request->location_latitude,
                'location_longitude' => $request->location_longitude,
                'location_name' => $request->location_name,
                'radius_meters' => $request->radius_meters,
                'qr_code_duration' => $request->qr_code_duration ?? 10,
                'require_photo' => $request->has('require_photo'),
                'require_location' => $request->has('require_location'),
            ]
        );
        
        return redirect()->route('settings.attendance')
            ->with('success', 'Pengaturan absensi berhasil diperbarui.');
    }

    /**
     * Determine attendance status based on time and role.
     */
    private function determineStatus($user, $checkInTime)
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

    private function isWithinRadius(float $lat, float $lng, float $centerLat, float $centerLng, int $radiusMeters): bool
    {
        if (!$lat && !$lng) return false;
        $earthRadius = 6371000; // meters
        $dLat = deg2rad($centerLat - $lat);
        $dLng = deg2rad($centerLng - $lng);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat)) * cos(deg2rad($centerLat)) * sin($dLng/2) * sin($dLng/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c;
        return $distance <= $radiusMeters;
    }

    /**
     * Export attendance report to Excel
     */
    public function export(Request $request)
    {
        $month = $request->get('month', date('n'));
        $year = $request->get('year', date('Y'));
        $type = $request->get('type', 'all');

        // Get date range
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        // Get users based on type and role
        $query = User::query()->where('school_id', Auth::user()->school_id);
        
        if ($type === 'employees') {
            $query->where('user_type', 'employee');
        } elseif ($type === 'students') {
            $query->where('user_type', 'student');
        }

        // Role-based filtering
        if (Auth::user()->hasRole('teacher')) {
            // Teachers can see their own attendance and their students
            $query->where(function($q) {
                $q->where('id', Auth::id())
                  ->orWhere('user_type', 'student');
            });
        } elseif (!Auth::user()->hasRole('admin')) {
            // Other roles can only see their own
            $query->where('id', Auth::id());
        }

        $users = $query->orderBy('name')->get();

        // Get attendances for the month
        $attendances = Attendance::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->whereIn('user_id', $users->pluck('id'))
            ->get()
            ->groupBy('user_id');

        // Create spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Absensi');

        // Set headers
        $headers = ['No.', 'Nama'];
        for ($day = 1; $day <= $endDate->day; $day++) {
            $headers[] = $day;
        }
        $headers[] = 'Total Ontime';
        $headers[] = 'Total Terlambat';
        $headers[] = 'Total Izin/Sakit';
        $headers[] = 'Total Alpha';

        // Set header row
        $col = 1;
        foreach ($headers as $header) {
            $sheet->setCellValue([$col, 1], $header);
            $col++;
        }

        // Style header row
        $headerRange = 'A1:' . $sheet->getCell([$col-1, 1])->getCoordinate();
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F3F4F6']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        // Fill data
        $row = 2;
        $statusColors = [
            'ontime' => '00FF00',
            'late' => 'FFFF00', 
            'sick' => 'FFA500',
            'permit' => 'FFA500',
            'duty' => 'FFA500',
            'leave' => 'FFA500',
            'alpha' => 'FF0000'
        ];

        $statusLabels = [
            'ontime' => 'Ontime',
            'late' => 'Terlambat',
            'sick' => 'Sakit',
            'permit' => 'Izin',
            'duty' => 'Dinas',
            'leave' => 'Cuti',
            'alpha' => 'Alpha'
        ];

        foreach ($users as $index => $user) {
            $userAttendances = $attendances->get($user->id, collect());
            
            // User info
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $user->name);

            // Counters
            $ontimeCount = 0;
            $lateCount = 0;
            $sickCount = 0;
            $alphaCount = 0;

            // Fill attendance data
            $col = 3; // Start from column C (day 1)
            for ($day = 1; $day <= $endDate->day; $day++) {
                $date = Carbon::create($year, $month, $day);
                $attendance = $userAttendances->where(function($item) use ($date) {
                    return $item->date->format('Y-m-d') === $date->format('Y-m-d');
                })->first();

                $status = $attendance ? $attendance->status : 'alpha';
                $time = $attendance && $attendance->check_in ? $attendance->check_in->format('H:i') : '';

                // Set cell value
                $cellValue = $statusLabels[$status] ?? 'Alpha';
                if (in_array($status, ['ontime', 'late']) && $time) {
                    $cellValue .= "\n" . $time;
                }
                
                $sheet->setCellValue([$col, $row], $cellValue);

                // Style cell with color
                $cellCoordinate = $sheet->getCell([$col, $row])->getCoordinate();
                $sheet->getStyle($cellCoordinate)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $statusColors[$status] ?? 'FF0000']
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ]
                ]);

                // Count statuses
                switch($status) {
                    case 'ontime': $ontimeCount++; break;
                    case 'late': $lateCount++; break;
                    case 'sick':
                    case 'permit':
                    case 'duty':
                    case 'leave': $sickCount++; break;
                    default: $alphaCount++; break;
                }

                $col++;
            }

            // Add summary columns
            $sheet->setCellValue([$col, $row], $ontimeCount);
            $sheet->setCellValue([$col+1, $row], $lateCount);
            $sheet->setCellValue([$col+2, $row], $sickCount);
            $sheet->setCellValue([$col+3, $row], $alphaCount);

            $row++;
        }

        // Auto-size columns
        foreach (range('A', $sheet->getCell([$col+3, 1])->getColumn()) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Create filename
        $typeLabel = $type === 'employees' ? 'Pegawai' : ($type === 'students' ? 'Siswa' : 'Semua');
        $filename = "Laporan_Absensi_{$typeLabel}_{$month}_{$year}.xlsx";

        // Create writer and save
        $writer = new Xlsx($spreadsheet);
        
        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    /**
     * Export detailed attendance report to Excel
     */
    public function exportDetail(Request $request)
    {
        $month = $request->get('month', date('n'));
        $year = $request->get('year', date('Y'));
        $type = $request->get('type', 'all');

        // Get date range
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        // Get users based on type and role
        $query = User::query()->where('school_id', Auth::user()->school_id);
        
        if ($type === 'employees') {
            $query->where('user_type', 'employee');
        } elseif ($type === 'students') {
            $query->where('user_type', 'student');
        }

        // Role-based filtering
        if (Auth::user()->hasRole('teacher')) {
            // Teachers can see their own attendance and their students
            $query->where(function($q) {
                $q->where('id', Auth::id())
                  ->orWhere('user_type', 'student');
            });
        } elseif (!Auth::user()->hasRole('admin')) {
            // Other roles can only see their own
            $query->where('id', Auth::id());
        }

        $users = $query->orderBy('name')->get();

        // Get all attendances for the month
        $attendances = Attendance::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->whereIn('user_id', $users->pluck('id'))
            ->with('user')
            ->orderBy('date')
            ->orderBy('user_id')
            ->get();

        // Create spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Detail Absensi');

        // Set headers
        $headers = [
            'No.',
            'Tanggal',
            'Nama',
            'NIS/NIP',
            'Jabatan/Kelas',
            'Absensi Masuk',
            'Absensi Keluar',
            'Status',
            'Keterangan',
            'Lokasi',
            'Foto'
        ];

        // Set header row
        $col = 1;
        foreach ($headers as $header) {
            $sheet->setCellValue([$col, 1], $header);
            $col++;
        }

        // Style header row
        $headerRange = 'A1:' . $sheet->getCell([$col-1, 1])->getCoordinate();
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'FFFFFF']
                ]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        // Set header text color to white
        $sheet->getStyle($headerRange)->getFont()->getColor()->setRGB('FFFFFF');

        // Status labels and colors
        $statusLabels = [
            'ontime' => 'Ontime',
            'late' => 'Terlambat',
            'sick' => 'Sakit',
            'permit' => 'Izin',
            'duty' => 'Dinas Luar',
            'leave' => 'Cuti',
            'alpha' => 'Alpha'
        ];

        $statusColors = [
            'ontime' => '00FF00',
            'late' => 'FFFF00',
            'sick' => 'FFA500',
            'permit' => 'FFA500',
            'duty' => 'FFA500',
            'leave' => 'FFA500',
            'alpha' => 'FF0000'
        ];

        // Fill data
        $row = 2;
        $no = 1;

        foreach ($attendances as $attendance) {
            $user = $attendance->user;
            
            // Basic info
            $sheet->setCellValue([1, $row], $no);
            $sheet->setCellValue([2, $row], $attendance->date->format('d/m/Y'));
            $sheet->setCellValue([3, $row], $user->name);
            
            // NIS/NIP
            if ($user->user_type === 'student') {
                $sheet->setCellValue([4, $row], $user->nis ?? '-');
            } else {
                $sheet->setCellValue([4, $row], $user->nik ?? '-');
            }
            
            // Jabatan/Kelas
            if ($user->user_type === 'student') {
                $sheet->setCellValue([5, $row], 'Siswa');
            } else {
                $roles = $user->roles->pluck('name')->implode(', ');
                $sheet->setCellValue([5, $row], $roles ?: '-');
            }
            
            // Absensi Masuk
            $checkIn = $attendance->check_in ? $attendance->check_in->format('H:i:s') : '-';
            $sheet->setCellValue([6, $row], $checkIn);
            
            // Absensi Keluar
            $checkOut = $attendance->check_out ? $attendance->check_out->format('H:i:s') : '-';
            $sheet->setCellValue([7, $row], $checkOut);
            
            // Status
            $statusLabel = $statusLabels[$attendance->status] ?? 'Alpha';
            $sheet->setCellValue([8, $row], $statusLabel);
            
            // Keterangan
            $notes = $attendance->notes ?: '-';
            $sheet->setCellValue([9, $row], $notes);
            
            // Lokasi
            $location = $attendance->location_name ?: '-';
            $sheet->setCellValue([10, $row], $location);
            
            // Foto
            $photo = $attendance->photo ? 'Ada' : 'Tidak ada';
            $sheet->setCellValue([11, $row], $photo);

            // Style status cell with color
            $statusCell = $sheet->getCell([8, $row])->getCoordinate();
            $sheet->getStyle($statusCell)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $statusColors[$attendance->status] ?? 'FF0000']
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]);

            // Style all cells with borders
            $cellRange = 'A' . $row . ':' . $sheet->getCell([11, $row])->getCoordinate();
            $sheet->getStyle($cellRange)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]);

            $row++;
            $no++;
        }

        // Auto-size columns
        foreach (range('A', 'K') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Create filename
        $typeLabel = $type === 'employees' ? 'Pegawai' : ($type === 'students' ? 'Siswa' : 'Semua');
        $filename = "Detail_Absensi_{$typeLabel}_{$month}_{$year}.xlsx";

        // Create writer and save
        $writer = new Xlsx($spreadsheet);
        
        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }
}
