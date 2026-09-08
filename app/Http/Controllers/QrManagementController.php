<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\SchoolClass;
use ZipArchive;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;

class QrManagementController extends Controller
{
    /**
     * Helper to build the filtered student query
     */
    private function buildStudentQuery(Request $request)
    {
        return User::where('school_id', auth()->user()->school_id)
            ->where('user_type', 'student')
            ->with(['studentClasses', 'studentProfile'])
            ->filter($request->all())
            ->orderBy('name');
    }

    public function index(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $classes = SchoolClass::where('school_id', $schoolId)->orderBy('name')->get();
        $levels = SchoolClass::where('school_id', $schoolId)->whereNotNull('level')->distinct()->pluck('level');

        $query = trim((string) $request->get('q', ''));
        $perPageParam = $request->get('per_page', '10');
        $perPage = (string) $perPageParam === 'all' ? 1000000 : (in_array((int) $perPageParam, [10, 25, 50, 100]) ? (int) $perPageParam : 10);

        $students = $this->buildStudentQuery($request)
            ->paginate($perPage)
            ->appends($request->query());

        // Build active filters
        $activeFilters = [];
        if ($request->filled('q')) {
            $activeFilters[] = [
                'label' => 'Cari',
                'value' => $request->q,
                'remove_url' => request()->fullUrlWithQuery(['q' => null, 'page' => null]),
            ];
        }

        if ($request->filled('level')) {
            $activeFilters[] = [
                'label' => 'Tingkat',
                'value' => 'Kelas ' . $request->level,
                'remove_url' => request()->fullUrlWithQuery(['level' => null, 'page' => null]),
            ];
        }

        if ($request->filled('class_id')) {
            $classObj = $classes->firstWhere('id', $request->class_id);
            $activeFilters[] = [
                'label' => 'Kelas',
                'value' => $classObj ? $classObj->name : $request->class_id,
                'remove_url' => request()->fullUrlWithQuery(['class_id' => null, 'page' => null]),
            ];
        }

        // Summary metric cards
        $baseStudentQuery = User::where('school_id', $schoolId)->where('user_type', 'student');
        $totalStudents = (clone $baseStudentQuery)->count();
        $withNis = (clone $baseStudentQuery)->whereNotNull('nis')->where('nis', '!=', '')->count();
        $assignedClass = (clone $baseStudentQuery)->whereHas('studentClasses')->count();
        $noClass = (clone $baseStudentQuery)->whereDoesntHave('studentClasses')->count();

        $summaryCards = [
            [
                'title' => 'Total Siswa (QR)',
                'value' => number_format($totalStudents) . ' Siswa',
                'subtext' => 'Seluruh siswa terdaftar',
                'icon' => 'fas fa-qrcode',
                'color' => 'blue',
            ],
            [
                'title' => 'Siswa Memiliki NIS',
                'value' => number_format($withNis) . ' Siswa',
                'subtext' => $totalStudents > 0 ? round(($withNis / $totalStudents) * 100) . '% memiliki NIS valid' : 'Nomor Induk Siswa',
                'icon' => 'fas fa-id-badge',
                'color' => 'emerald',
            ],
            [
                'title' => 'Terdaftar di Rombel',
                'value' => number_format($assignedClass) . ' Siswa',
                'subtext' => 'Sudah masuk kelas/rombel',
                'icon' => 'fas fa-chalkboard-teacher',
                'color' => 'purple',
            ],
            [
                'title' => 'Belum Ada Kelas',
                'value' => number_format($noClass) . ' Siswa',
                'subtext' => $noClass > 0 ? '<span class="text-amber-600 font-medium">Perlu penempatan kelas</span>' : '<span class="text-emerald-600 font-medium">Semua telah terplot</span>',
                'icon' => 'fas fa-user-tag',
                'color' => 'amber',
            ],
        ];

        return view('qr.index', compact('students', 'classes', 'levels', 'activeFilters', 'query', 'perPageParam', 'summaryCards'));
    }

    /**
     * Dapatkan nama file gambar QR yang konsisten untuk siswa
     */
    public function getQrFilename(User $user): string
    {
        return $this->sanitizeFilename(($user->nis ?? 'NIS') . '_' . $user->name) . '.png';
    }

    /**
     * Dapatkan path penyimpanan QR di disk
     */
    private function getStoragePath(User $user): string
    {
        $schoolId = $user->school_id ?: auth()->user()->school_id;
        return "qrcodes/school_{$schoolId}/" . $this->getQrFilename($user);
    }

    /**
     * Ambil konten gambar PNG dari disk (jika sudah ada) atau generate sekali dan simpan permanen.
     */
    public function getOrGenerateQrImage(User $user, int $size = 600): string
    {
        $storagePath = $this->getStoragePath($user);

        // 1. Cek apakah sudah pernah tersimpan di disk lokal
        if (Storage::disk('public')->exists($storagePath)) {
            $existing = Storage::disk('public')->get($storagePath);
            if (!empty($existing)) {
                return $existing;
            }
        }

        // 2. Jika belum ada di disk, generate QR Code sekali saja
        $payload = ($user->nis ?? '') . '|' . $user->name;
        $png = $this->renderPng($payload, $size);

        // 3. Simpan permanen ke disk
        try {
            Storage::disk('public')->put($storagePath, $png);
        } catch (\Throwable $e) {
            \Log::warning("Failed to save QR to disk: " . $e->getMessage());
        }

        return $png;
    }

    /**
     * Unduh PNG QR per siswa
     */
    public function download(Request $request, User $user)
    {
        abort_unless($user->user_type === 'student', 404);
        $png = $this->getOrGenerateQrImage($user, 600);
        $filename = $this->getQrFilename($user);

        return response($png)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Tampilkan PNG QR secara inline untuk preview modal di browser
     * (Memastikan gambar di preview 100% sama persis dengan yang di-download)
     */
    public function viewQr(Request $request, User $user)
    {
        abort_unless($user->user_type === 'student', 404);
        $png = $this->getOrGenerateQrImage($user, 600);

        return response($png)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'inline');
    }

    /**
     * Export Excel daftar siswa dan nama file QR Code untuk vendor percetakan kartu
     */
    public function exportExcel(Request $request)
    {
        $students = $this->buildStudentQuery($request)->get();

        if ($students->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data siswa untuk di-export.');
        }

        $spreadsheet = $this->buildSpreadsheet($students);
        $writer = new Xlsx($spreadsheet);

        $classSuffix = '';
        if ($request->filled('class_id')) {
            $classObj = SchoolClass::find($request->class_id);
            if ($classObj) {
                $classSuffix = '_' . $this->sanitizeFilename($classObj->name);
            }
        }

        $filename = 'daftar_siswa_qrcode' . $classSuffix . '_' . date('Y-m-d_H-i') . '.xlsx';
        
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Download massal menggunakan native ZipArchive
     * Menyertakan seluruh file gambar PNG QR + file Excel rekap di dalamnya
     */
    public function downloadZip(Request $request)
    {
        set_time_limit(300); // 5 menit

        try {
            $students = $this->buildStudentQuery($request)->get();

            if ($students->isEmpty()) {
                return redirect()->back()->with('error', 'Tidak ada data siswa untuk di-download.');
            }

            $classSuffix = '';
            if ($request->filled('class_id')) {
                $classObj = SchoolClass::find($request->class_id);
                if ($classObj) {
                    $classSuffix = '_' . $this->sanitizeFilename($classObj->name);
                }
            }

            $zipFilename = 'qr_siswa' . $classSuffix . '_' . date('Y-m-d_H-i-s') . '.zip';
            $tempZipPath = tempnam(sys_get_temp_dir(), 'qrz_');

            $zip = new ZipArchive();
            if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                return redirect()->back()->with('error', 'Gagal membuat file ZIP di server.');
            }

            // 1. Masukkan semua gambar QR Code ke ZIP
            foreach ($students as $student) {
                try {
                    $png = $this->getOrGenerateQrImage($student, 600);
                    $filename = $this->getQrFilename($student);
                    if (!empty($png)) {
                        $zip->addFromString("qrcodes/" . $filename, $png);
                    }
                } catch (\Throwable $e) {
                    \Log::warning("Gagal menambahkan QR untuk {$student->name}: " . $e->getMessage());
                }
            }

            // 2. Buat dan masukkan file Excel rekap ke dalam ZIP
            try {
                $spreadsheet = $this->buildSpreadsheet($students);
                $writer = new Xlsx($spreadsheet);
                $tempExcelPath = tempnam(sys_get_temp_dir(), 'qrx_');
                $writer->save($tempExcelPath);
                $zip->addFile($tempExcelPath, "daftar_siswa_qrcode.xlsx");
            } catch (\Throwable $e) {
                \Log::warning("Gagal membuat file Excel dalam ZIP: " . $e->getMessage());
            }

            $zip->close();

            if (isset($tempExcelPath) && file_exists($tempExcelPath)) {
                @unlink($tempExcelPath);
            }

            return response()->download($tempZipPath, $zipFilename, [
                'Content-Type' => 'application/zip',
            ])->deleteFileAfterSend(true);

        } catch (\Throwable $e) {
            \Log::error('Download ZIP error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat membuat file ZIP: ' . $e->getMessage());
        }
    }

    /**
     * Membangun objek Spreadsheet Excel yang rapi untuk vendor percetakan
     */
    private function buildSpreadsheet($students): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Siswa & QR');

        // Header Style
        $headers = [
            'A1' => 'No',
            'B1' => 'NIS',
            'C1' => 'NISN',
            'D1' => 'Nama Lengkap Siswa',
            'E1' => 'Kelas',
            'F1' => 'Jenis Kelamin',
            'G1' => 'Nama File QR Code',
            'H1' => 'Isi Data QR Code',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        // Styling header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2563EB'], // Blue 600
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
        $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Data Rows
        $rowNum = 2;
        foreach ($students as $index => $s) {
            $className = $s->studentClasses->first()->name ?? '-';
            $nisn = $s->studentProfile->nisn ?? $s->nisn ?? '-';
            $gender = $s->gender === 'L' ? 'Laki-laki' : ($s->gender === 'P' ? 'Perempuan' : '-');
            $filename = $this->getQrFilename($s);
            $payload = ($s->nis ?? '') . '|' . $s->name;

            $sheet->setCellValue('A' . $rowNum, $index + 1);
            $sheet->setCellValueExplicit('B' . $rowNum, $s->nis ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $rowNum, $nisn, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('D' . $rowNum, $s->name);
            $sheet->setCellValue('E' . $rowNum, $className);
            $sheet->setCellValue('F' . $rowNum, $gender);
            $sheet->setCellValue('G' . $rowNum, $filename);
            $sheet->setCellValue('H' . $rowNum, $payload);

            // Zebra stripe
            if ($rowNum % 2 === 0) {
                $sheet->getStyle("A{$rowNum}:H{$rowNum}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F8FAFC');
            }

            $rowNum++;
        }

        $lastRow = $rowNum - 1;

        // Border styling
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E2E8F0'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
        $sheet->getStyle("A1:H{$lastRow}")->applyFromArray($borderStyle);

        // Center specific columns
        $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("B2:C{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("E2:F{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Auto column width
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    private function sanitizeFilename(string $name): string
    {
        return preg_replace('/[^A-Za-z0-9_\-]+/', '_', $name);
    }

    /**
     * QR renderer: Prioritaskan Endroid lokal yang stabil & cepat,
     * jika ada kendala otomatis fallback ke API eksternal.
     */
    private function renderPng(string $text, int $size = 600): string
    {
        // 1. Coba generator lokal Endroid (cepat, tajam, offline)
        try {
            if (class_exists('Endroid\QrCode\Builder\Builder')) {
                $result = Builder::create()
                    ->writer(new PngWriter())
                    ->data($text)
                    ->encoding(new Encoding('UTF-8'))
                    ->errorCorrectionLevel(ErrorCorrectionLevel::Medium)
                    ->size($size)
                    ->margin(10)
                    ->build();

                $png = $result->getString();
                if (!empty($png)) {
                    return $png;
                }
            }
        } catch (\Throwable $e) {
            \Log::warning("Local Endroid QR generation failed, falling back to external API: " . $e->getMessage());
        }

        // 2. Fallback: panggil API eksternal
        try {
            $url = 'https://api.qrserver.com/v1/create-qr-code/';
            $params = [
                'size' => $size . 'x' . $size,
                'data' => $text,
                'format' => 'png',
                'margin' => 10,
                'ecc' => 'M'
            ];

            $qrUrl = $url . '?' . http_build_query($params);
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'user_agent' => 'Presensia QR Generator'
                ]
            ]);

            $png = @file_get_contents($qrUrl, false, $context);
            if ($png !== false && strlen($png) > 0) {
                return $png;
            }
        } catch (\Throwable $e) {
            \Log::warning("External QR API failed: " . $e->getMessage());
        }

        // Minimal 1x1 PNG jika keduanya gagal total
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
    }
}
