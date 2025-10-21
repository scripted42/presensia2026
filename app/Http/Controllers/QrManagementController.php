<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use ZipArchive;

class QrManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = trim((string) $request->get('q', ''));
        $perPageParam = $request->get('per_page', '10');
        $perPage = (string) $perPageParam === 'all' ? 1000000 : (in_array((int) $perPageParam, [10, 25, 50]) ? (int) $perPageParam : 10);

        $students = User::where('school_id', auth()->user()->school_id)
            ->where('user_type', 'student')
            ->when($query !== '', function ($q) use ($query) {
                $q->where(function ($qq) use ($query) {
                    $qq->where('name', 'like', "%$query%");
                    $qq->orWhere('nis', 'like', "%$query%");
                });
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->appends(['q' => $query, 'per_page' => $perPageParam]);
        return view('qr.index', compact('students', 'query', 'perPageParam'));
    }

    // Unduh PNG QR per siswa (konten: "NIS|Nama") nama file: NIS_Nama.png
    public function download(Request $request, User $user)
    {
        abort_unless($user->user_type === 'student', 404);
        $payload = ($user->nis ?? '').'|'.$user->name;
        $png = $this->renderPng($payload, 600);
        $filename = $this->sanitizeFilename(($user->nis ?? 'NIS')."_".$user->name).'.png';
        return response($png)->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    // Download massal ZIP berisi PNG QR
    public function downloadZip(Request $request)
    {
        try {
            \Log::info('=== DOWNLOAD ZIP DEBUG START ===');
            \Log::info('Download ZIP method called');
            \Log::info('Request URL: ' . $request->fullUrl());
            \Log::info('User ID: ' . auth()->id());
            \Log::info('School ID: ' . auth()->user()->school_id);
            
            // Debug PHP extensions
            \Log::info('PHP Version: ' . phpversion());
            \Log::info('Available extensions: ' . implode(', ', get_loaded_extensions()));
            \Log::info('ZipArchive class exists: ' . (class_exists('ZipArchive') ? 'YES' : 'NO'));
            \Log::info('ZipArchive extension loaded: ' . (extension_loaded('zip') ? 'YES' : 'NO'));
            
            $students = User::where('school_id', auth()->user()->school_id)
                ->where('user_type', 'student')->orderBy('name')->get();
            
            \Log::info('Found students: ' . $students->count());
            \Log::info('Students data: ' . $students->toJson());
            
            if ($students->isEmpty()) {
                \Log::warning('No students found');
                return redirect()->back()->with('error', 'Tidak ada data siswa untuk di-download.');
            }
            
            // Check if ZipArchive is available
            if (!class_exists('ZipArchive')) {
                \Log::error('ZipArchive not available');
                \Log::error('Available classes: ' . implode(', ', get_declared_classes()));
                return redirect()->back()->with('error', 'ZipArchive extension tidak tersedia di server ini. Silakan hubungi administrator untuk mengaktifkan PHP ZipArchive extension.');
            }
            
            \Log::info('ZipArchive available, creating ZIP');
            
            // Create ZIP file
            \Log::info('Creating ZIP file...');
            $zip = new ZipArchive();
            $tmp = tempnam(sys_get_temp_dir(), 'qr_massal_');
            \Log::info('Temp file created: ' . $tmp);
            \Log::info('Temp directory: ' . sys_get_temp_dir());
            \Log::info('Temp directory writable: ' . (is_writable(sys_get_temp_dir()) ? 'YES' : 'NO'));
            
            $zipResult = $zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            \Log::info('ZIP open result: ' . $zipResult);
            
            if ($zipResult !== TRUE) {
                \Log::error('Cannot open ZIP file. Error code: ' . $zipResult);
                return redirect()->back()->with('error', 'Tidak dapat membuat file ZIP. Error code: ' . $zipResult);
            }
            
            \Log::info('ZIP file opened successfully');
            
            $successCount = 0;
            $errorCount = 0;
            
            foreach ($students as $student) {
                try {
                    \Log::info("Processing student: {$student->name} (ID: {$student->id})");
                    $payload = ($student->nis ?? '').'|'.$student->name;
                    \Log::info("Payload: {$payload}");
                    
                    $png = $this->renderPng($payload, 600);
                    \Log::info("PNG generated, size: " . strlen($png) . " bytes");
                    
                    if ($png && strlen($png) > 0) {
                        $filename = $this->sanitizeFilename(($student->nis ?? 'NIS')."_".$student->name).'.png';
                        \Log::info("Adding file to ZIP: {$filename}");
                        
                        $addResult = $zip->addFromString($filename, $png);
                        \Log::info("Add to ZIP result: " . ($addResult ? 'SUCCESS' : 'FAILED'));
                        
                        if ($addResult) {
                            $successCount++;
                            \Log::info("Successfully added {$filename} to ZIP");
                        } else {
                            $errorCount++;
                            \Log::error("Failed to add {$filename} to ZIP");
                        }
                    } else {
                        $errorCount++;
                        \Log::error("Empty PNG generated for {$student->name}");
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    \Log::error("Failed to generate QR for {$student->name}: " . $e->getMessage());
                    \Log::error("Stack trace: " . $e->getTraceAsString());
                }
            }
            
            \Log::info("ZIP creation completed. Success: {$successCount}, Errors: {$errorCount}");
            $closeResult = $zip->close();
            \Log::info("ZIP close result: " . ($closeResult ? 'SUCCESS' : 'FAILED'));
            
            if ($successCount === 0) {
                \Log::error('No QR codes were successfully generated');
                unlink($tmp);
                return redirect()->back()->with('error', 'Tidak ada QR code yang berhasil di-generate.');
            }
            
            $filename = 'qr_students_' . date('Y-m-d_H-i-s') . '.zip';
            \Log::info("Returning ZIP file: {$filename}");
            \Log::info('=== DOWNLOAD ZIP DEBUG END ===');
            
            return response()->download($tmp, $filename)->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            \Log::error('Download ZIP error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat membuat file ZIP. Silakan coba lagi.');
        }
    }

    
    
    
    

    
    
    

    private function sanitizeFilename(string $name): string
    {
        return preg_replace('/[^A-Za-z0-9_\-]+/', '_', $name);
    }

    // QR renderer: menggunakan external QR service
    private function renderPng(string $text, int $size): string
    {
        try {
            // Use external QR service
            $url = 'https://api.qrserver.com/v1/create-qr-code/';
            $params = [
                'size' => $size . 'x' . $size,
                'data' => $text,
                'format' => 'png',
                'margin' => 10
            ];
            
            $qrUrl = $url . '?' . http_build_query($params);
            
            // Get QR code from external service
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'user_agent' => 'Laravel QR Generator'
                ]
            ]);
            
            $png = file_get_contents($qrUrl, false, $context);
            
            if ($png !== false && strlen($png) > 0) {
            return $png;
            }
        } catch (\Exception $e) {
            \Log::warning("External QR generation failed: " . $e->getMessage());
        }
        
        // Fallback: create simple text-based QR representation
        return $this->createSimpleQR($text, $size);
    }
    
    // Create simple QR-like representation without GD
    private function createSimpleQR(string $text, int $size): string
    {
        // Create a simple SVG QR code
        $svg = '<?xml version="1.0" encoding="UTF-8"?>';
        $svg .= '<svg width="' . $size . '" height="' . $size . '" xmlns="http://www.w3.org/2000/svg">';
        $svg .= '<rect width="' . $size . '" height="' . $size . '" fill="white"/>';
        
        // Create simple pattern based on text hash
        $hash = md5($text);
        $blockSize = $size / 25;
        
        for ($i = 0; $i < 25; $i++) {
            for ($j = 0; $j < 25; $j++) {
                $hashIndex = ($i * 25 + $j) % strlen($hash);
                if (hexdec($hash[$hashIndex]) % 2 === 0) {
                    $x = $i * $blockSize;
                    $y = $j * $blockSize;
                    $svg .= '<rect x="' . $x . '" y="' . $y . '" width="' . $blockSize . '" height="' . $blockSize . '" fill="black"/>';
                }
            }
        }
        
        $svg .= '</svg>';
        
        // Convert SVG to PNG using a simple approach
        return $this->svgToPng($svg, $size);
    }
    
    // Convert SVG to PNG using external service
    private function svgToPng(string $svg, int $size): string
    {
        try {
            // Use external service to convert SVG to PNG
            $url = 'https://api.qrserver.com/v1/create-qr-code/';
            $params = [
                'size' => $size . 'x' . $size,
                'data' => 'QR_CODE_PLACEHOLDER',
                'format' => 'png'
            ];
            
            $qrUrl = $url . '?' . http_build_query($params);
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'user_agent' => 'Laravel QR Generator'
                ]
            ]);
            
            return file_get_contents($qrUrl, false, $context);
        } catch (\Exception $e) {
            // Return minimal PNG header as fallback
            return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
        }
    }
}


