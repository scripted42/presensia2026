<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\User;

class MobileAuthenticate
{
    /**
     * Handle an incoming mobile API request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token autentikasi tidak ditemukan. Silakan login kembali.'
            ], 401);
        }

        // Check if token exists in Cache
        $userId = Cache::get("mobile_token_{$token}");

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi login telah kedaluwarsa atau tidak valid. Silakan login kembali.'
            ], 401);
        }

        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Akun pengguna tidak ditemukan.'
            ], 401);
        }

        // Set authenticated user for Auth facade and Request
        Auth::setUser($user);
        $request->setUserResolver(fn() => $user);

        return $next($request);
    }
}
