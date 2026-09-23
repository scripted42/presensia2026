<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class MobileAuthController extends Controller
{
    /**
     * Mobile login
     */
    public function login(Request $request)
    {
        $loginIdentifier = trim((string) ($request->input('login', $request->input('email', ''))));
        $request->merge(['login' => $loginIdentifier]);

        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ], [
            'login.required' => 'NIS, NIK, atau Email wajib diisi.',
            'password.required' => 'Password wajib diisi.'
        ]);

        $cleanDigits = preg_replace('/[^0-9]/', '', $loginIdentifier);

        $user = User::where(function ($query) use ($loginIdentifier, $cleanDigits) {
                $query->where('nis', $loginIdentifier)
                      ->orWhere('nik', $loginIdentifier)
                      ->orWhere('email', $loginIdentifier)
                      ->orWhere('name', $loginIdentifier)
                      ->orWhere('name', 'like', '%' . $loginIdentifier . '%');

                if (!empty($cleanDigits)) {
                    $query->orWhereRaw("REPLACE(REPLACE(REPLACE(nik, ' ', ''), '-', ''), '.', '') = ?", [$cleanDigits])
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(nis, ' ', ''), '-', ''), '.', '') = ?", [$cleanDigits]);
                }

                $query->orWhereHas('employeeProfile', function ($q) use ($loginIdentifier, $cleanDigits) {
                    $q->where('nip', $loginIdentifier)
                      ->orWhere('nik', $loginIdentifier)
                      ->orWhere('nuptk', $loginIdentifier);

                    if (!empty($cleanDigits)) {
                        $q->orWhereRaw("REPLACE(REPLACE(REPLACE(nip, ' ', ''), '-', ''), '.', '') = ?", [$cleanDigits])
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(nik, ' ', ''), '-', ''), '.', '') = ?", [$cleanDigits])
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(nuptk, ' ', ''), '-', ''), '.', '') = ?", [$cleanDigits]);
                    }
                });
            })
            ->where(function ($q) {
                $q->where('is_active', true)
                  ->orWhereNull('is_active');
            })
            ->first();

        $passwordInput = (string) $request->password;
        $isValidPassword = false;

        if ($user) {
            $isValidPassword = Hash::check($passwordInput, $user->password) 
                || Hash::check(trim($passwordInput), $user->password);

            // Fallback: Jika siswa login dengan password = NIS-nya sendiri, sinkronkan ke DB jika belum sesuai
            if (!$isValidPassword && $user->hasRole('student') && !empty($user->nis) && trim($passwordInput) === (string)$user->nis) {
                $user->password = Hash::make((string)$user->nis);
                $user->save();
                $isValidPassword = true;
            }

            // Fallback: Jika pegawai login dengan password = NIK/NIP-nya sendiri, sinkronkan ke DB jika belum sesuai
            $employeeNum = $user->nik ?: ($user->employeeProfile?->nip ?: $user->employeeProfile?->nik);
            if (!$isValidPassword && !$user->hasRole('student') && !empty($employeeNum) && trim($passwordInput) === (string)$employeeNum) {
                $user->password = Hash::make((string)$employeeNum);
                $user->save();
                $isValidPassword = true;
            }

            // Fallback: Jika input password 'password', cek juga variasi case
            if (!$isValidPassword && strtolower(trim($passwordInput)) === 'password') {
                if (Hash::check('password', $user->password) || Hash::check('Password', $user->password)) {
                    $isValidPassword = true;
                }
            }
        }

        if (!$user || !$isValidPassword) {
            return response()->json([
                'success' => false,
                'message' => 'NIS/NIK atau password salah'
            ], 401);
        }

        // Create token
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'nik' => $user->nik,
                    'nis' => $user->nis,
                    'user_type' => $user->user_type,
                    'school_id' => $user->school_id,
                    'photo' => $user->photo ? asset('storage/' . $user->photo) : null,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_at' => now()->addDays(30)->toISOString(),
            ]
        ]);
    }

    /**
     * Mobile logout
     */
    public function logout(Request $request)
    {
        $token = $request->bearerToken();
        if ($token) {
            \Illuminate\Support\Facades\Cache::forget("mobile_token_{$token}");

            try {
                $tokenFile = storage_path('app/mobile_tokens.json');
                if (file_exists($tokenFile)) {
                    $tokens = json_decode(@file_get_contents($tokenFile), true) ?: [];
                    unset($tokens[$token]);
                    @file_put_contents($tokenFile, json_encode($tokens), LOCK_EX);
                }
            } catch (\Throwable $e) {
                // Ignore
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }

    /**
     * Get current user info
     */
    public function me(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'user_type' => $user->user_type,
                'school_id' => $user->school_id,
                'nik' => $user->nik,
                'nis' => $user->nis,
                'nip' => $user->employeeProfile?->nip,
                'photo' => $user->photo ? asset('storage/' . $user->photo) : null,
            ]
        ]);
    }

    /**
     * Change user password from mobile app
     */
    public function changePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'Password lama wajib diisi.',
            'new_password.required' => 'Password baru wajib diisi.',
            'new_password.min' => 'Password baru minimal 6 karakter.',
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $currentPasswordInput = (string) $request->current_password;
        $isValidCurrentPassword = Hash::check($currentPasswordInput, $user->password)
            || Hash::check(trim($currentPasswordInput), $user->password);

        // Fallback jika user sebelumnya menggunakan NIK/NIS sebagai password default
        $idNum = $user->nik ?: ($user->nis ?: $user->employeeProfile?->nip);
        if (!$isValidCurrentPassword && !empty($idNum) && trim($currentPasswordInput) === (string)$idNum) {
            $isValidCurrentPassword = true;
        }

        // Fallback jika password default 'password'
        if (!$isValidCurrentPassword && strtolower(trim($currentPasswordInput)) === 'password') {
            if (Hash::check('password', $user->password) || Hash::check('Password', $user->password)) {
                $isValidCurrentPassword = true;
            }
        }

        if (!$isValidCurrentPassword) {
            return response()->json([
                'success' => false,
                'message' => 'Password saat ini yang Anda masukkan salah.'
            ], 422);
        }

        // Update password
        $user->password = Hash::make(trim($request->new_password));
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diperbarui! Silakan gunakan password baru ini saat login berikutnya.'
        ]);
    }
}






