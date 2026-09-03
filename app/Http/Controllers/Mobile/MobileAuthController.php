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

        $user = User::where(function ($query) use ($loginIdentifier) {
                $query->where('nis', $loginIdentifier)
                      ->orWhere('nik', $loginIdentifier)
                      ->orWhere('email', $loginIdentifier);
            })
            ->where('is_active', true)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
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
        $request->user()->currentAccessToken()->delete();

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
                'photo' => $user->photo ? asset('storage/' . $user->photo) : null,
                'qr_code' => $user->qr_code,
            ]
        ]);
    }
}






