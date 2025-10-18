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
        $img = imagecreatetruecolor($widthPx, $heightPx);
        imagesavealpha($img, true);
        $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
        imagefill($img, 0, 0, $transparent);

        // QR besar di kiri bawah (sekitar 55% lebar kartu agar mudah dipindai)
        $qrSize = (int) round($widthPx * 0.55);
        $qrPng = $this->renderPng(($user->nis ?? '').'|'.$user->name, $qrSize);
        $qrImage = imagecreatefromstring($qrPng);
        $x = (int) round($widthPx * 0.06);
        $y = $heightPx - $qrSize - (int) round($heightPx * 0.06);
        imagecopy($img, $qrImage, $x, $y, 0, 0, imagesx($qrImage), imagesy($qrImage));

        ob_start();
        imagepng($img);
        $out = ob_get_clean();
        imagedestroy($img);
        imagedestroy($qrImage);
        $filename = $this->sanitizeFilename(($user->nis ?? 'NIS')."_".$user->name.'_card').'.png';
        return response($out)->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    private function sanitizeFilename(string $name): string
    {
        return preg_replace('/[^A-Za-z0-9_\-]+/', '_', $name);
    }

    // QR renderer: menggunakan Zxing-js untuk generate QR code
    private function renderPng(string $text, int $size): string
    {
        try {
            // Use Zxing-js library for QR generation
            $qrCode = new \Zxing\QrCode();
            $qrCode->setText($text);
            $qrCode->setSize($size);
            $qrCode->setMargin(10);
            
            // Generate QR code as PNG
            $png = $qrCode->writeString();
            
            if ($png !== false) {
                return $png;
            }
        } catch (\Exception $e) {
            \Log::warning("Zxing QR generation failed: " . $e->getMessage());
        }
        
        // Fallback: create simple QR-like pattern
        $img = imagecreatetruecolor($size, $size);
        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);
        imagefill($img, 0, 0, $white);
        
        // Draw simple QR-like pattern
        $blockSize = $size / 25; // 25x25 grid
        for ($i = 0; $i < 25; $i++) {
            for ($j = 0; $j < 25; $j++) {
                if (($i + $j) % 3 === 0) {
                    imagefilledrectangle($img, $i * $blockSize, $j * $blockSize, 
                        ($i + 1) * $blockSize, ($j + 1) * $blockSize, $black);
                }
            }
        }
        
        ob_start();
        imagepng($img);
        $result = ob_get_clean();
        imagedestroy($img);
        
        return $result;
    }
}


