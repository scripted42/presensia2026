<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceArchiveController extends Controller
{
    /**
     * Selesaikan objek School berdasarkan kunci akses (token), sesi, atau autentikasi.
     */
    protected function resolveSchool(Request $request): ?School
    {
        // 1. Jika pengguna sedang login di Presensia
        if (auth()->check() && auth()->user()->school_id) {
            return School::find(auth()->user()->school_id);
        }

        // 2. Ambil token dari query string, input post, atau session
        $key = $request->input('key', session('attendance_archive_key'));

        if (!$key) {
            return null;
        }

        $key = trim((string) $key);

        // 3. Cek kunci default / env (misal: smpn14, smpn14-surabaya, atau dari .env)
        $envKey = env('ATTENDANCE_ARCHIVE_KEY', 'smpn14-surabaya');
        $validKeys = array_map('strtolower', array_filter([
            $envKey,
            'smpn14',
            'smpn14-surabaya',
            'presensia14',
        ]));

        if (in_array(strtolower($key), $validKeys, true)) {
            $school = School::where('name', 'like', '%SMPN 14%')
                ->orWhere('name', 'like', '%14%')
                ->first() ?: School::where('is_active', true)->first();

            if ($school) {
                session(['attendance_archive_key' => $key, 'attendance_archive_school_id' => $school->id]);
                return $school;
            }
        }

        // 4. Cek custom key di database untuk setiap sekolah
        $schools = School::where('is_active', true)->get();
        foreach ($schools as $s) {
            $schoolCustomKey = $s->settings['archive_key'] ?? null;
            $schoolDefaultHash = substr(md5('presensia-archive-' . $s->id), 0, 12);

            if (($schoolCustomKey && hash_equals($schoolCustomKey, $key)) || hash_equals($schoolDefaultHash, $key)) {
                session(['attendance_archive_key' => $key, 'attendance_archive_school_id' => $s->id]);
                return $s;
            }
        }

        return null;
    }

    /**
     * Tampilan Folder Utama (Root) - Daftar Bulan
     */
    public function index(Request $request)
    {
        $school = $this->resolveSchool($request);
        $key = $request->input('key', session('attendance_archive_key'));

        if (!$school) {
            return view('archive.index', [
                'needsAuth' => true,
                'errorMessage' => $request->has('key') ? 'Kunci akses tidak valid. Silakan periksa kembali.' : null,
                'key' => $key,
            ]);
        }

        $now = Carbon::now('Asia/Jakarta');
        $months = [];

        // Buat daftar 12 bulan terakhir
        for ($i = 0; $i < 12; $i++) {
            $date = $now->copy()->subMonths($i);
            $year = $date->year;
            $month = $date->month;

            $totalDays = ($year === $now->year && $month === $now->month)
                ? $now->day
                : $date->daysInMonth;

            $months[] = [
                'year' => $year,
                'month' => $month,
                'month_name' => $date->translatedFormat('F'),
                'label' => $date->translatedFormat('F Y'),
                'is_current' => ($year === $now->year && $month === $now->month),
                'total_files' => $totalDays + 1, // 1 file bulanan + n file harian
                'days_count' => $totalDays,
            ];
        }

        return view('archive.index', [
            'needsAuth' => false,
            'isFolder' => false,
            'school' => $school,
            'months' => $months,
            'key' => $key,
            'today' => [
                'year' => $now->year,
                'month' => $now->month,
                'day' => $now->day,
                'date_formatted' => $now->translatedFormat('d F Y'),
            ],
        ]);
    }

    /**
     * Tampilan Isi Folder Bulan Terpilih
     */
    public function folder(Request $request, $year, $month)
    {
        $school = $this->resolveSchool($request);
        $key = $request->input('key', session('attendance_archive_key'));

        if (!$school) {
            return redirect()->route('archive.index', ['key' => $key]);
        }

        $year = (int) $year;
        $month = (int) $month;

        $now = Carbon::now('Asia/Jakarta');
        $monthDate = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Jakarta');
        $daysInMonth = $monthDate->daysInMonth;

        // Tentukan batas hari maksimum
        $maxDay = ($year === $now->year && $month === $now->month) ? $now->day : $daysInMonth;

        $days = [];
        // Urutkan hari dari yang terbaru ke terlama (Hari ini / tanggal akhir di atas)
        for ($d = $maxDay; $d >= 1; $d--) {
            $currentDate = Carbon::create($year, $month, $d, 0, 0, 0, 'Asia/Jakarta');
            $isToday = ($year === $now->year && $month === $now->month && $d === $now->day);
            $isWeekend = $currentDate->isWeekend();

            $days[] = [
                'day' => $d,
                'date_str' => $currentDate->format('Y-m-d'),
                'date_formatted' => $currentDate->translatedFormat('l, d F Y'),
                'short_date' => $currentDate->format('d/m/Y'),
                'is_today' => $isToday,
                'is_weekend' => $isWeekend,
                'filename' => sprintf('Rekap_Harian_%02d-%02d-%04d.xlsx', $d, $month, $year),
            ];
        }

        return view('archive.index', [
            'needsAuth' => false,
            'isFolder' => true,
            'school' => $school,
            'year' => $year,
            'month' => $month,
            'month_label' => $monthDate->translatedFormat('F Y'),
            'month_name' => $monthDate->translatedFormat('F'),
            'days' => $days,
            'key' => $key,
            'monthly_filename' => sprintf('Rekap_Bulanan_Siswa_%s_%04d.xlsx', $monthDate->translatedFormat('F'), $year),
        ]);
    }

    /**
     * Download Rekap Harian (1 Hari Tertentu)
     */
    public function downloadDaily(Request $request, $year, $month, $day): StreamedResponse
    {
        $school = $this->resolveSchool($request);
        if (!$school) {
            abort(403, 'Akses tidak sah atau kunci akses tidak valid.');
        }

        $year = (int) $year;
        $month = (int) $month;
        $day = (int) $day;

        $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
        $dateObj = Carbon::createFromFormat('Y-m-d', $dateStr, 'Asia/Jakarta');

        // Ambil semua siswa untuk arsip (dengan query fleksibel & jaring pengaman attendance)
        $students = $this->getStudentsForArchive($school->id, $dateStr);

        // Ambil data absensi pada tanggal tersebut
        $attendances = Attendance::where(function ($q) use ($dateStr) {
                $q->whereDate('date', $dateStr)
                  ->orWhere('date', $dateStr);
            })
            ->get()
            ->keyBy('user_id');

        // Ambil data izin/sakit pada tanggal tersebut
        $leaves = LeaveRequest::where('status', 'approved')
            ->where(function ($q) use ($dateStr) {
                $q->whereDate('start_date', '<=', $dateStr)
                  ->whereDate('end_date', '>=', $dateStr);
            })
            ->get()
            ->keyBy('user_id');

        $isToday = ($dateStr === Carbon::today('Asia/Jakarta')->format('Y-m-d'));

        $rows = [];
        $no = 1;
        $stats = [
            'total' => $students->count(),
            'ontime' => 0,
            'late' => 0,
            'permit' => 0,
            'sick' => 0,
            'alpha' => 0,
        ];

        foreach ($students as $student) {
            $att = $attendances->get($student->id);
            $leave = $leaves->get($student->id);

            $nis = $student->nis ?: ($student->studentProfile?->nis ?: ($student->nisn ?: ($student->studentProfile?->nisn ?: '-')));
            $nama = $student->name;
            $kelas = $student->studentClasses->first()?->name ?? ($student->studentProfile?->class_name ?? '-');

            $jamMasuk = '-';
            $status = 'Alpha';
            $keterangan = '-';

            if ($leave) {
                $lType = strtolower($leave->type ?? '');
                if (str_contains($lType, 'sick') || str_contains($lType, 'sakit')) {
                    $status = 'Sakit';
                    $stats['sick']++;
                } else {
                    $status = 'Izin';
                    $stats['permit']++;
                }
                $keterangan = $leave->reason ?: 'Izin disetujui';
            } elseif ($att && $att->check_in) {
                if ($att->check_in instanceof \Carbon\Carbon) {
                    $jamMasuk = $att->check_in->timezone('Asia/Jakarta')->format('H:i:s');
                } else {
                    $jamMasuk = (string) $att->check_in;
                }

                $attStatus = $att->status;

                if (!in_array($attStatus, ['ontime', 'late'])) {
                    $attStatus = Attendance::determineStatusFor($student, $att->check_in);
                }

                if ($attStatus === 'late') {
                    $status = 'Terlambat';
                    $stats['late']++;
                } else {
                    $status = 'Hadir (Tepat Waktu)';
                    $stats['ontime']++;
                }
                $keterangan = $att->notes ?: 'Hadir scan';
            } elseif ($att && in_array($att->status, ['ontime', 'late'])) {
                if ($att->status === 'late') {
                    $status = 'Terlambat';
                    $stats['late']++;
                } else {
                    $status = 'Hadir (Tepat Waktu)';
                    $stats['ontime']++;
                }
                $keterangan = $att->notes ?: 'Hadir';
            } elseif ($att && in_array($att->status, ['sick', 'permit', 'leave', 'duty'])) {
                if ($att->status === 'sick') {
                    $status = 'Sakit';
                    $stats['sick']++;
                } else {
                    $status = 'Izin';
                    $stats['permit']++;
                }
                $keterangan = $att->notes ?: '-';
            } else {
                $stats['alpha']++;
                if ($isToday) {
                    $status = 'Belum Hadir / Alpha';
                    $keterangan = 'Belum melakukan absensi';
                } else {
                    $status = 'Alpha';
                    $keterangan = 'Tanpa Keterangan';
                }
            }

            $rows[] = [
                'no' => $no++,
                'tanggal' => $dateObj->format('d/m/Y'),
                'nis' => $nis,
                'nama' => $nama,
                'kelas' => $kelas,
                'jam_masuk' => $jamMasuk,
                'status' => $status,
                'keterangan' => $keterangan,
            ];
        }

        $filename = sprintf('Rekap_Absensi_Siswa_%02d_%02d_%04d.xlsx', $day, $month, $year);
        $periodTitle = 'Hari: ' . $dateObj->translatedFormat('l, d F Y');

        return $this->buildSpreadsheetResponse($school, $periodTitle, $filename, $rows, $stats);
    }

    /**
     * Download Rekap Bulanan (Semua hari dalam bulan tersebut)
     */
    public function downloadMonthly(Request $request, $year, $month): StreamedResponse
    {
        $school = $this->resolveSchool($request);
        if (!$school) {
            abort(403, 'Akses tidak sah atau kunci akses tidak valid.');
        }

        $year = (int) $year;
        $month = (int) $month;

        $startDate = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Jakarta');
        $now = Carbon::now('Asia/Jakarta');
        $daysInMonth = $startDate->daysInMonth;
        $maxDay = ($year === $now->year && $month === $now->month) ? $now->day : $daysInMonth;

        $endDate = Carbon::create($year, $month, $maxDay, 23, 59, 59, 'Asia/Jakarta');

        $students = $this->getStudentsForArchive($school->id, null, $startDate, $endDate);

        // Ambil absensi satu bulan
        $attendances = Attendance::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get()
            ->groupBy(function ($item) {
                return Carbon::parse($item->date)->format('Y-m-d');
            });

        // Ambil izin satu bulan
        $leaves = LeaveRequest::where('status', 'approved')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                    ->orWhereBetween('end_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                    ->orWhere(function ($q2) use ($startDate, $endDate) {
                        $q2->where('start_date', '<=', $startDate->format('Y-m-d'))
                            ->where('end_date', '>=', $endDate->format('Y-m-d'));
                    });
            })
            ->get();

        // Index izin per user dan tanggal
        $leaveMap = [];
        foreach ($leaves as $leave) {
            $cursor = $leave->start_date->copy();
            $to = $leave->end_date->copy();
            while ($cursor->lte($to)) {
                $leaveMap[$leave->user_id][$cursor->format('Y-m-d')] = [
                    'type' => $leave->type,
                    'reason' => $leave->reason,
                ];
                $cursor->addDay();
            }
        }

        $rows = [];
        $no = 1;
        $stats = [
            'total' => $students->count(),
            'ontime' => 0,
            'late' => 0,
            'permit' => 0,
            'sick' => 0,
            'alpha' => 0,
        ];

        // Loop setiap hari (dari tanggal 1 s/d maxDay)
        for ($d = 1; $d <= $maxDay; $d++) {
            $curDate = Carbon::create($year, $month, $d, 0, 0, 0, 'Asia/Jakarta');
            $dateStr = $curDate->format('Y-m-d');
            $isToday = ($dateStr === $now->format('Y-m-d'));
            $isWeekend = $curDate->isWeekend();

            // Opsional: jika hari minggu dan tidak ada presensi sama sekali, bisa dilewati atau dicatat
            $dailyAttendances = ($attendances->get($dateStr) ?? collect())->keyBy('user_id');

            // Jika weekend dan tidak ada yang hadir sama sekali, lewati agar laporan tidak membengkak
            if ($isWeekend && $dailyAttendances->isEmpty()) {
                continue;
            }

            foreach ($students as $student) {
                $att = $dailyAttendances->get($student->id);
                $leaveInfo = $leaveMap[$student->id][$dateStr] ?? null;

                $nis = $student->nis ?: ($student->studentProfile?->nis ?: ($student->nisn ?: ($student->studentProfile?->nisn ?: '-')));
                $nama = $student->name;
                $kelas = $student->studentClasses->first()?->name ?? ($student->studentProfile?->class_name ?? '-');

                $jamMasuk = '-';
                $status = 'Alpha';
                $keterangan = '-';

                if ($leaveInfo) {
                    $lType = strtolower($leaveInfo['type'] ?? '');
                    if (str_contains($lType, 'sick') || str_contains($lType, 'sakit')) {
                        $status = 'Sakit';
                        $stats['sick']++;
                    } else {
                        $status = 'Izin';
                        $stats['permit']++;
                    }
                    $keterangan = $leaveInfo['reason'] ?: 'Izin disetujui';
                } elseif ($att && $att->check_in) {
                    if ($att->check_in instanceof \Carbon\Carbon) {
                        $jamMasuk = $att->check_in->timezone('Asia/Jakarta')->format('H:i:s');
                    } else {
                        $jamMasuk = (string) $att->check_in;
                    }

                    $attStatus = $att->status;

                    if (!in_array($attStatus, ['ontime', 'late'])) {
                        $attStatus = Attendance::determineStatusFor($student, $att->check_in);
                    }

                    if ($attStatus === 'late') {
                        $status = 'Terlambat';
                        $stats['late']++;
                    } else {
                        $status = 'Hadir (Tepat Waktu)';
                        $stats['ontime']++;
                    }
                    $keterangan = $att->notes ?: 'Hadir scan';
                } elseif ($att && in_array($att->status, ['ontime', 'late'])) {
                    if ($att->status === 'late') {
                        $status = 'Terlambat';
                        $stats['late']++;
                    } else {
                        $status = 'Hadir (Tepat Waktu)';
                        $stats['ontime']++;
                    }
                    $keterangan = $att->notes ?: 'Hadir';
                } elseif ($att && in_array($att->status, ['sick', 'permit', 'leave', 'duty'])) {
                    if ($att->status === 'sick') {
                        $status = 'Sakit';
                        $stats['sick']++;
                    } else {
                        $status = 'Izin';
                        $stats['permit']++;
                    }
                    $keterangan = $att->notes ?: '-';
                } else {
                    $stats['alpha']++;
                    if ($isToday) {
                        $status = 'Belum Hadir / Alpha';
                        $keterangan = 'Belum melakukan absensi';
                    } else {
                        $status = 'Alpha';
                        $keterangan = 'Tanpa Keterangan';
                    }
                }

                $rows[] = [
                    'no' => $no++,
                    'tanggal' => $curDate->format('d/m/Y'),
                    'nis' => $nis,
                    'nama' => $nama,
                    'kelas' => $kelas,
                    'jam_masuk' => $jamMasuk,
                    'status' => $status,
                    'keterangan' => $keterangan,
                ];
            }
        }

        $filename = sprintf('Rekap_Absensi_Siswa_Bulanan_%s_%04d.xlsx', $startDate->translatedFormat('F'), $year);
        $periodTitle = 'Bulan: ' . $startDate->translatedFormat('F Y');

        return $this->buildSpreadsheetResponse($school, $periodTitle, $filename, $rows, $stats);
    }

    /**
     * Dapatkan daftar siswa untuk laporan arsip.
     * Menggunakan query yang fleksibel & jaring pengaman attendance
     * agar seluruh data siswa dan absensi yang ada pasti termuat.
     */
    protected function getStudentsForArchive($schoolId, $dateStr = null, $startDate = null, $endDate = null)
    {
        // 1. Query dasar siswa
        $query = User::query();

        if ($schoolId && User::where('school_id', $schoolId)->exists()) {
            $query->where('school_id', $schoolId);
        }

        // PENTING: Jangan filter 'is_active = true' agar siswa dengan nilai null/0 tetap masuk
        $query->where(function ($q) {
            $q->where('user_type', 'student')
              ->orWhereHas('roles', function ($rq) {
                  $rq->where('name', 'student');
              })
              ->orWhereHas('studentClasses');
        });

        $students = $query->with(['studentClasses' => function ($q) {
            $q->orderBy('classes.level')->orderBy('classes.name');
        }, 'roles', 'studentProfile'])->get();

        // 2. Fallback jika masih kosong: cari semua siswa tanpa filter school_id
        if ($students->isEmpty()) {
            $students = User::where(function ($q) {
                $q->where('user_type', 'student')
                  ->orWhereHas('roles', function ($rq) {
                      $rq->where('name', 'student');
                  })
                  ->orWhereHas('studentClasses');
            })->with(['studentClasses' => function ($q) {
                $q->orderBy('classes.level')->orderBy('classes.name');
            }, 'roles', 'studentProfile'])->get();
        }

        // 3. JARING PENGAMAN UTAMA:
        // Cek data attendance pada tanggal / periode ini.
        // Jika ada siswa yang melakukan absensi, pastikan user pemilik absensi tersebut 100% masuk dalam list siswa!
        $attQuery = Attendance::query();
        if ($dateStr) {
            $attQuery->where(function ($q) use ($dateStr) {
                $q->whereDate('date', $dateStr)
                  ->orWhere('date', $dateStr);
            });
        } elseif ($startDate && $endDate) {
            $attQuery->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
        }

        $attUserIds = $attQuery->pluck('user_id')->filter()->unique();
        if ($attUserIds->isNotEmpty()) {
            $attUsers = User::whereIn('id', $attUserIds)
                ->where(function ($q) {
                    $q->where('user_type', '!=', 'admin')
                      ->orWhereNull('user_type');
                })
                ->with(['studentClasses' => function ($q) {
                    $q->orderBy('classes.level')->orderBy('classes.name');
                }, 'roles', 'studentProfile'])
                ->get();

            $students = $students->concat($attUsers)->unique('id');
        }

        return $students->sortBy([
            function ($a, $b) {
                $classA = $a->studentClasses->first()?->name ?? ($a->studentProfile?->class_name ?? 'ZZZ');
                $classB = $b->studentClasses->first()?->name ?? ($b->studentProfile?->class_name ?? 'ZZZ');
                return strnatcasecmp($classA, $classB);
            },
            ['name', 'asc'],
        ]);
    }

    /**
     * Buat dan streaming file Excel dengan PhpSpreadsheet
     */
    protected function buildSpreadsheetResponse($school, $periodTitle, $filename, array $rows, array $stats): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Absensi Siswa');

        // Header Judul Laporan
        $sheet->setCellValue('A1', 'LAPORAN REKAPITULASI PRESENSI SISWA');
        $sheet->setCellValue('A2', strtoupper($school->name));
        $sheet->setCellValue('A3', $periodTitle . ' • Dicetak pada: ' . Carbon::now('Asia/Jakarta')->translatedFormat('d F Y, H:i') . ' WIB');

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(15)->getColor()->setRGB('1E3A8A');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('111827');
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(10)->getColor()->setRGB('4B5563');

        // Kartu Ringkasan (Baris 5)
        $sheet->setCellValue('A5', 'RINGKASAN');
        $sheet->setCellValue('B5', 'Total Siswa: ' . $stats['total']);
        $sheet->setCellValue('C5', 'Tepat Waktu: ' . $stats['ontime']);
        $sheet->setCellValue('D5', 'Terlambat: ' . $stats['late']);
        $sheet->setCellValue('E5', 'Izin: ' . $stats['permit']);
        $sheet->setCellValue('F5', 'Sakit: ' . $stats['sick']);
        $sheet->setCellValue('G5', 'Alpha: ' . $stats['alpha']);

        $sheet->getStyle('A5:G5')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F1F5F9'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
        ]);
        $sheet->getRowDimension(5)->setRowHeight(24);

        // Header Tabel Data (Baris 7)
        // Urutan persis sesuai permintaan: No | Tanggal | NIS | Nama | Kelas | Jam Masuk | Status | Keterangan
        $headers = [
            'A7' => 'No',
            'B7' => 'Tanggal',
            'C7' => 'NIS',
            'D7' => 'Nama Siswa',
            'E7' => 'Kelas',
            'F7' => 'Jam Masuk',
            'G7' => 'Status',
            'H7' => 'Keterangan',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $sheet->getStyle('A7:H7')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E3A8A'], // Dark Navy Elegant
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '0F172A'],
                ],
            ],
        ]);
        $sheet->getRowDimension(7)->setRowHeight(26);

        // Styling Warna Badge Status
        $statusColors = [
            'Hadir (Tepat Waktu)' => ['bg' => 'DCFCE7', 'color' => '166534'], // Hijau
            'Terlambat'           => ['bg' => 'FEF3C7', 'color' => '92400E'], // Amber
            'Izin'                => ['bg' => 'DBEAFE', 'color' => '1E40AF'], // Biru
            'Sakit'               => ['bg' => 'F3E8FF', 'color' => '6B21A8'], // Ungu
            'Alpha'               => ['bg' => 'FEE2E2', 'color' => '991B1B'], // Merah
            'Belum Hadir / Alpha' => ['bg' => 'FEE2E2', 'color' => '991B1B'], // Merah
        ];

        // Isi Data Tabel (Mulai Baris 8)
        $currentRow = 8;
        foreach ($rows as $item) {
            $sheet->setCellValue("A{$currentRow}", $item['no']);
            $sheet->setCellValueExplicit("B{$currentRow}", $item['tanggal'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("C{$currentRow}", (string) $item['nis'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("D{$currentRow}", $item['nama']);
            $sheet->setCellValue("E{$currentRow}", $item['kelas']);
            $sheet->setCellValueExplicit("F{$currentRow}", $item['jam_masuk'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("G{$currentRow}", $item['status']);
            $sheet->setCellValue("H{$currentRow}", $item['keterangan']);

            // Zebra striping untuk baris genap
            $rowBg = ($currentRow % 2 === 0) ? 'F8FAFC' : 'FFFFFF';
            $sheet->getStyle("A{$currentRow}:H{$currentRow}")->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $rowBg],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E2E8F0'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Alignment spesifik kolom
            $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("E{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("H{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            // Styling status badge
            $statusText = $item['status'];
            if (isset($statusColors[$statusText])) {
                $sheet->getStyle("G{$currentRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => $statusColors[$statusText]['color']],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $statusColors[$statusText]['bg']],
                    ],
                ]);
            }

            $sheet->getRowDimension($currentRow)->setRowHeight(22);
            $currentRow++;
        }

        // Auto-fit kolom A sampai H
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Ekspor Stream Download
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }
}

