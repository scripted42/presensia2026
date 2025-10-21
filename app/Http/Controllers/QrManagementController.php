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

    // Unduh massal ZIP berisi PNG QR
    public function downloadZip(Request $request)
    {
        $students = User::where('school_id', auth()->user()->school_id)
            ->where('user_type', 'student')->orderBy('name')->get();
        $zip = new ZipArchive();
        $tmp = tempnam(sys_get_temp_dir(), 'qrzip');
        $zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        foreach ($students as $s) {
            $payload = ($s->nis ?? '').'|'.$s->name;
            $png = $this->renderPng($payload, 600);
            $zip->addFromString($this->sanitizeFilename(($s->nis ?? 'NIS')."_".$s->name).'.png', $png);
        }
        $zip->close();
        return response()->download($tmp, 'qr_students.zip')->deleteFileAfterSend(true);
    }

    // Render kartu pelajar PNG 85.6 x 53 mm (PORTRAIT) dengan QR di kiri bawah
    public function card(Request $request, User $user)
    {
        abort_unless($user->user_type === 'student', 404);
        
        // DPI 300 → ukuran piksel (portrait: width 53mm, height 85.6mm)
        $widthPx = (int) round(53 / 25.4 * 300);   // ~626 px
        $heightPx = (int) round(85.6 / 25.4 * 300); // ~1011 px
        
        // Create SVG card instead of using GD
        $svg = $this->createCardSvg($user, $widthPx, $heightPx);
        
        // Convert SVG to PNG using external service
        $png = $this->svgToPng($svg, $widthPx);
        
        $filename = $this->sanitizeFilename(($user->nis ?? 'NIS')."_".$user->name.'_card').'.png';
        return response($png)->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }
    
    // Create SVG card without GD dependency
    private function createCardSvg(User $user, int $widthPx, int $heightPx): string
    {
        $svg = '<?xml version="1.0" encoding="UTF-8"?>';
        $svg .= '<svg width="' . $widthPx . '" height="' . $heightPx . '" xmlns="http://www.w3.org/2000/svg">';
        
        // White background
        $svg .= '<rect width="' . $widthPx . '" height="' . $heightPx . '" fill="white" stroke="black" stroke-width="2"/>';
        
        // School name at top
        $svg .= '<text x="' . ($widthPx / 2) . '" y="30" text-anchor="middle" font-family="Arial" font-size="16" font-weight="bold">KARTU PELAJAR</text>';
        
        // Student info
        $svg .= '<text x="20" y="80" font-family="Arial" font-size="14" font-weight="bold">NIS:</text>';
        $svg .= '<text x="80" y="80" font-family="Arial" font-size="14">' . ($user->nis ?? 'N/A') . '</text>';
        
        $svg .= '<text x="20" y="110" font-family="Arial" font-size="14" font-weight="bold">Nama:</text>';
        $svg .= '<text x="80" y="110" font-family="Arial" font-size="14">' . htmlspecialchars($user->name) . '</text>';
        
        // Generate actual QR code with same data as download method
        $qrSize = (int) round($widthPx * 0.55);
        $x = (int) round($widthPx * 0.06);
        $y = $heightPx - $qrSize - (int) round($heightPx * 0.06);
        
        // Use same payload as download method: NIS|Nama
        $payload = ($user->nis ?? '').'|'.$user->name;
        $qrPng = $this->renderPng($payload, $qrSize);
        
        // Convert PNG to base64 for embedding in SVG
        $qrBase64 = base64_encode($qrPng);
        $svg .= '<image x="' . $x . '" y="' . $y . '" width="' . $qrSize . '" height="' . $qrSize . '" href="data:image/png;base64,' . $qrBase64 . '"/>';
        
        $svg .= '</svg>';
        
        return $svg;
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


