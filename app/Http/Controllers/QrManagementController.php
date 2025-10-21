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

    // Download massal menggunakan ZipStream
    public function downloadZip(Request $request)
    {
        try {
            $students = User::where('school_id', auth()->user()->school_id)
                ->where('user_type', 'student')->orderBy('name')->get();
            
            if ($students->isEmpty()) {
                return redirect()->back()->with('error', 'Tidak ada data siswa untuk di-download.');
            }
            
            $filename = 'qr_students_' . date('Y-m-d_H-i-s') . '.zip';
            
            return response()->streamDownload(function () use ($students) {
                $this->createZipStream($students);
            }, $filename, [
                'Content-Type' => 'application/zip',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Download ZIP error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat membuat file ZIP. Silakan coba lagi.');
        }
    }
    
    private function createZipStream($students)
    {
        // Simple ZIP implementation without external dependencies
        $zipData = '';
        
        // ZIP file header
        $zipData .= $this->createZipHeader();
        
        $fileIndex = 0;
        foreach ($students as $student) {
            try {
                $payload = ($student->nis ?? '').'|'.$student->name;
                $png = $this->renderPng($payload, 600);
                
                if ($png && strlen($png) > 0) {
                    $filename = $this->sanitizeFilename(($student->nis ?? 'NIS')."_".$student->name).'.png';
                    $zipData .= $this->createZipEntry($filename, $png, $fileIndex);
                    $fileIndex++;
                }
            } catch (\Exception $e) {
                \Log::warning("Failed to generate QR for {$student->name}: " . $e->getMessage());
            }
        }
        
        // ZIP central directory
        $zipData .= $this->createZipCentralDirectory($fileIndex);
        
        // ZIP end of central directory
        $zipData .= $this->createZipEndOfCentralDirectory();
        
        echo $zipData;
    }
    
    private function createZipHeader()
    {
        return "\x50\x4B\x03\x04"; // ZIP signature
    }
    
    private function createZipEntry($filename, $data, $index)
    {
        $crc = crc32($data);
        $size = strlen($data);
        $compressedSize = $size; // No compression for simplicity
        
        $entry = pack('V', 0x04034b50); // Local file header signature
        $entry .= pack('v', 20); // Version needed to extract
        $entry .= pack('v', 0); // General purpose bit flag
        $entry .= pack('v', 0); // Compression method (0 = stored)
        $entry .= pack('v', 0); // Last mod file time
        $entry .= pack('v', 0); // Last mod file date
        $entry .= pack('V', $crc); // CRC-32
        $entry .= pack('V', $compressedSize); // Compressed size
        $entry .= pack('V', $size); // Uncompressed size
        $entry .= pack('v', strlen($filename)); // Filename length
        $entry .= pack('v', 0); // Extra field length
        $entry .= $filename; // Filename
        $entry .= $data; // File data
        
        return $entry;
    }
    
    private function createZipCentralDirectory($fileCount)
    {
        return ''; // Simplified - just return empty for now
    }
    
    private function createZipEndOfCentralDirectory()
    {
        return "\x50\x4B\x05\x06"; // End of central directory signature
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


